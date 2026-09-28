<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;


class PageController extends Controller
{
    private function tracks(): \Illuminate\Database\Query\Builder
    {
        return DB::table('listings as l')
            ->leftJoin('artists as a', 'a.id', '=', 'l.artist_id')
            ->select('l.*', 'a.stage_name as artist_name')
            ->where('l.is_published', 1);
    }

    private function audio(): \Illuminate\Database\Query\Builder
    {
        return $this->tracks()
            ->where('l.listing_type_id', 1);
    }

    private function category(string $category, int $limit = 60)
    {
        $query = $this->audio();

        if ($category === 'gospel') {
            $query->where('l.is_gospel', 1);
        } elseif ($category === 'highlife') {
            $query->where('l.is_high_life', 1);
        } else {
            $query
                ->where('l.music_category', $category)
                ->where('l.is_gospel', 0)
                ->where('l.is_high_life', 0);
        }

        return $query
            ->orderByDesc('l.id')
            ->limit($limit)
            ->get();
    }

    public function music(string $category): View
    {
        return view('index', [
            'title' => ucfirst($category) . ' Songs',
            'items' => $this->category($category),
            'type' => 'song',
        ]);
    }

    public function musicDetails(int $id, string $slug): View|RedirectResponse
    {
        $song = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'song.posted_by'
            )
            ->select(
                'song.*',
                'artist.id as artist_record_id',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                "),
                'poster.name as posted_by_name'
            )
            ->where('song.id', $id)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->first();

        abort_unless($song, 404);

        $canonicalSlug = \App\Support\MusicUrl::slug($song);

        if ($slug !== $canonicalSlug) {
            return redirect(
                route('music_details', [$song->id, $canonicalSlug]),
                301
            );
        }

        $artistSongs = DB::table('listing as related')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'related.Artists_Id'
            )
            ->select(
                'related.id',
                'related.TrackTitle as track_title',
                'related.Featuring as featuring',
                'related.CoverUrl as cover_url',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('related.Artists_Id', $song->Artists_Id)
            ->where('related.id', '<>', $song->id)
            ->where('related.ListingType', 'Audio')
            ->where('related.IsPublished', 'YES')
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        $artistVideos = DB::table('listing as related')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'related.Artists_Id'
            )
            ->select(
                'related.id',
                'related.TrackTitle as track_title',
                'related.CoverUrl as cover_url',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('related.Artists_Id', $song->Artists_Id)
            ->whereRaw('LOWER(related.ListingType) = ?', ['video'])
            ->where('related.IsPublished', 'YES')
            ->orderByDesc('related.id')
            ->limit(3)
            ->get();

        $artistAlbums = DB::table('albums')
            ->select('id', 'title', 'cover_url', 'released_year')
            ->where('artist_id', $song->Artists_Id)
            ->where('IsPublished', 'YES')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Songs by other lead artists that name this artist in Featuring.
        $collaborations = DB::table('listing as related')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'related.Artists_Id'
            )
            ->select(
                'related.id',
                'related.TrackTitle as track_title',
                'related.Featuring as featuring',
                'related.CoverUrl as cover_url',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('related.Artists_Id', '<>', $song->Artists_Id)
            ->where('related.ListingType', 'Audio')
            ->where('related.IsPublished', 'YES')
            ->where('related.Featuring', 'like', '%' . addcslashes(
                $song->artist_name,
                '%_\\'
            ) . '%')
            ->orderByDesc('related.id')
            ->limit(10)
            ->get();

        $latestMusic = DB::table('listing as related')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'related.Artists_Id'
            )
            ->select(
                'related.id',
                'related.TrackTitle as track_title',
                'related.Featuring as featuring',
                'related.CoverUrl as cover_url',
                'related.ListingType as listing_type',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('related.id', '<>', $song->id)
            ->where('related.IsPublished', 'YES')
            ->whereIn('related.ListingType', ['Audio', 'video', 'Video'])
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        return view('pages.music-details', compact(
            'song',
            'artistSongs',
            'artistVideos',
            'artistAlbums',
            'collaborations',
            'latestMusic'
        ));
    }

    public function songOfTheDay(): View
    {
        $songs = DB::table('featured_rated as featured')
            ->join(
                'listing as song',
                'song.id',
                '=',
                'featured.listing_id'
            )
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                'featured.rate_no',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('featured.id')
            ->paginate(30);

        return view('pages.song-of-the-day', [
            'songs' => $songs,
        ]);
    }


    public function songsPostedBy(string $slug): View
    {
        /*
        * The users table stores names, not URL slugs.
        */
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $songs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.posted_by', $poster->id)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.songs-posted-by', [
            'poster' => $poster,
            'songs' => $songs,
        ]);
    }

    private function musicDownloadAudio(): Builder
    {
        return DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES');
    }

    private function musicDownloadCategory(
        string $category,
        int $limit
    ) {
        $query = $this->musicDownloadAudio();

        if ($category === 'gospel') {
            $query->where('song.isgospel', 1);
        } elseif ($category === 'highlife') {
            $query->where('song.ishighlife', 1);
        } else {
            $query->where('song.country_id', $category);
        }

        return $query
            ->orderByDesc('song.id')
            ->limit($limit)
            ->get();
    }

    private function musicDownloadDay()
    {
        return $this->musicDownloadAudio()
            ->join(
                'featured_rated as featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->orderByRaw(
                'CAST(featured.rate_no AS UNSIGNED) ASC'
            )
            ->limit(10)
            ->get();
    }

    private function musicDownloadWeek()
    {
        return $this->musicDownloadAudio()
            ->join(
                'song_of_the_week as featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->orderByRaw(
                'CAST(featured.rate_no AS UNSIGNED) ASC'
            )
            ->limit(10)
            ->get();
    }

    public function musicDownload(): View
    {
        $albums = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.id',
                'album.title',
                'album.cover_url',
                'album.released_year',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('album.IsPublished', 'YES')
            ->orderByDesc('album.id')
            ->limit(7)
            ->get();

        return view('pages.music-download', [
            'day' => $this->musicDownloadDay(),
            'albums' => $albums,
            'naija' => $this->musicDownloadCategory('naija', 25),
            'ghana' => $this->musicDownloadCategory('ghana', 15),
            'african' => $this->musicDownloadCategory('african', 15),
            'gospel' => $this->musicDownloadCategory('gospel', 6),
            'highlife' => $this->musicDownloadCategory('highlife', 6),
            'week' => $this->musicDownloadWeek(),
        ]);
    }

    public function gospelSongs(): View
    {
        $songs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.isgospel', 1)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.gospel-songs', [
            'songs' => $songs,
        ]);
    }

    public function highlifeSongs(): View
    {
        $songs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.ishighlife', 1)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.highlife-songs', [
            'songs' => $songs,
        ]);
    }

    public function countrySongs(string $country): View
    {
        $pages = [
            'naija' => [
                'title' => 'Download Latest Naija Music Mp3 Here | Trendybeatz',
                'description' => 'Discover the latest Naija songs on TrendyBeatz. Explore new Nigerian music, stream tracks from your favourite artists, and find available MP3 downloads.',
                'heading' => 'Download Latest Naija Music Mp3',
                'section_heading' => 'Latest Naija Songs',
                'badge' => 'Naija Music',
            ],

            'ghana' => [
                'title' => 'Download Latest Ghana Music Mp3 Here | Trendybeatz',
                'description' => 'Discover the latest Ghanaian songs on TrendyBeatz. Stream new music from Ghanaian artists and explore available MP3 downloads across popular genres.',
                'heading' => 'Download Latest Ghana Music Mp3',
                'section_heading' => 'Latest Ghana Songs',
                'badge' => 'Ghana Music',
            ],

            'african' => [
                'title' => 'Download Latest African Music Mp3 Here | Trendybeatz',
                'description' => 'Explore the latest African songs on TrendyBeatz. Discover artists from across the continent, stream new releases, and find available MP3 downloads.',
                'heading' => 'Download Latest African Music Mp3',
                'section_heading' => 'Latest African Songs',
                'badge' => 'African Music',
            ],
        ];

        // The route constraint handles this too, but retain the guard
        // if the method is ever called from another route.
        abort_unless(isset($pages[$country]), 404);

        $songs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.CoverUrl as cover_url',
                'song.Featuring as featuring',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.country_id', $country)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.music-country', [
            'country' => $country,
            'page' => $pages[$country],
            'songs' => $songs,
        ]);
    }


    public function artists(): View
    {
        return view('artists', [
            'artists' => DB::table('artists')
                ->where('is_published', 1)
                ->orderBy('stage_name')
                ->limit(150)
                ->get(),
        ]);
    }

    public function blogCategory(string $category): View
    {
        $databaseSlug = $category === 'hot-gists'
            ? 'hot-topics'
            : $category;

        $categoryRow = DB::table('blog_categories')
            ->where('slug', $databaseSlug)
            ->firstOrFail();

        return view('index', [
            'title' => $category === 'hot-gists'
                ? 'Hot Gists'
                : $categoryRow->name,
            'items' => DB::table('blogs')
                ->where('is_published', 1)
                ->where('blog_category_id', $categoryRow->id)
                ->orderByDesc('id')
                ->limit(60)
                ->get(),
            'type' => 'blog',
        ]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->query('search', ''));

        if (mb_strlen($term) > 100) {
            abort(422);
        }

        $escaped = addcslashes($term, '%_\\');

        $items = $term === ''
            ? collect()
            : $this->tracks()
                ->where(function ($query) use ($escaped) {
                    $query
                        ->where('l.track_title', 'like', '%' . $escaped . '%')
                        ->orWhere('a.stage_name', 'like', '%' . $escaped . '%');
                })
                ->orderByDesc('l.id')
                ->limit(60)
                ->get();

        return view('index', [
            'title' => $term === ''
                ? 'Search'
                : 'Search results for ' . $term,
            'items' => $items,
            'type' => 'song',
        ]);
    }

    public function videos(): View
    {
        return view('index', [
            'title' => 'Latest Videos',
            'items' => $this->tracks()
                ->where('l.listing_type_id', 2)
                ->orderByDesc('l.id')
                ->limit(60)
                ->get(),
            'type' => 'song',
        ]);
    }

    public function albums(): View
    {
        return view('index', [
            'title' => 'Latest Albums',
            'items' => DB::table('albums')
                ->where('is_published', 1)
                ->orderByDesc('id')
                ->limit(60)
                ->get(),
            'type' => 'album',
        ]);
    }

    public function mixes(): View
    {
        return view('index', [
            'title' => 'Latest DJ Mix',
            'items' => DB::table('dj_mixes as m')
                ->leftJoin('djs as d', 'd.id', '=', 'm.dj_id')
                ->select('m.*', 'd.dj_name')
                ->where('m.is_published', 1)
                ->orderByDesc('m.id')
                ->limit(60)
                ->get(),
            'type' => 'mix',
        ]);
    }

    public function blogs(): View
    {
        return view('index', [
            'title' => 'Latest Blog & News',
            'items' => DB::table('blogs')
                ->where('is_published', 1)
                ->orderByDesc('id')
                ->limit(60)
                ->get(),
            'type' => 'blog',
        ]);
    }

    public function song(int $id, string $slug): View
    {
        $item = $this->tracks()
            ->where('l.id', $id)
            ->first();

        abort_unless($item && $item->slug === $slug, 404);

        return view('detail', [
            'title' => trim(
                ($item->artist_name ? $item->artist_name . ' – ' : '')
                . $item->track_title
            ),
            'item' => $item,
            'type' => 'song',
        ]);
    }

    public function album(int $id, string $slug): View
    {
        $item = DB::table('albums')
            ->where('id', $id)
            ->where('is_published', 1)
            ->first();

        abort_unless($item && $item->slug === $slug, 404);

        return view('detail', [
            'title' => $item->title,
            'item' => $item,
            'type' => 'album',
        ]);
    }

    public function mix(int $id, string $slug): View
    {
        $item = DB::table('dj_mixes')
            ->where('id', $id)
            ->where('is_published', 1)
            ->first();

        abort_unless($item && $item->slug === $slug, 404);

        return view('detail', [
            'title' => $item->mix_title,
            'item' => $item,
            'type' => 'mix',
        ]);
    }

    public function blog(int $id, string $slug): View
    {
        $item = DB::table('blogs')
            ->where('id', $id)
            ->where('is_published', 1)
            ->first();

        abort_unless($item && $item->slug === $slug, 404);

        return view('detail', [
            'title' => $item->title,
            'item' => $item,
            'type' => 'blog',
        ]);
    }
}