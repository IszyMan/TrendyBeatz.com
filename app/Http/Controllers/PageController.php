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


    public function songsByYear(int $year): \Illuminate\Contracts\View\View
    {
        $songs = \Illuminate\Support\Facades\DB::table('listing as song')
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
                'song.YearOfRelease as released_year',
                \Illuminate\Support\Facades\DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.YearOfRelease', (string) $year)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.songs-by-year', [
            'songs' => $songs,
            'year' => $year,
        ]);
    }

    public function latestVideos(): \Illuminate\Contracts\View\View
    {
        $videos = \Illuminate\Support\Facades\DB::table('listing as video')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'video.Artists_Id'
            )
            ->select(
                'video.id',
                'video.slug',
                'video.TrackTitle as track_title',
                'video.CoverUrl as cover_url',
                'video.Featuring as featuring',
                'video.is_video_comedy',
                'video.created_at',
                \Illuminate\Support\Facades\DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.latest-videos', compact('videos'));
    }


    public function videoDetails(
        int $id,
        string $slug
    ): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse {
        $video = DB::table('listing as video')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'video.Artists_Id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'video.posted_by'
            )
            ->select(
                'video.*',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                "),
                'poster.name as posted_by_name'
            )
            ->where('video.id', $id)
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->first();

        abort_unless($video, 404);

        $canonicalSlug = \App\Support\VideoUrl::slug($video);

        if ($slug !== $canonicalSlug) {
            return redirect(
                \App\Support\VideoUrl::detail($video),
                301
            );
        }

        $otherVideos = DB::table('listing as item')
            ->select(
                'item.id',
                'item.slug',
                'item.TrackTitle as track_title',
                'item.Featuring as featuring'
            )
            ->where('item.Artists_Id', $video->Artists_Id)
            ->where('item.id', '<>', $video->id)
            ->whereRaw('LOWER(item.ListingType) = ?', ['video'])
            ->where('item.IsPublished', 'YES')
            ->orderByDesc('item.id')
            ->limit(6)
            ->get();

        $artistSongs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle',
                'song.Featuring',
                'artist.Stage_Name as artist_name'
            )
            ->where('song.Artists_Id', $video->Artists_Id)
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->limit(6)
            ->get();

        $latestSongs = DB::table('listing as song')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'song.Artists_Id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle',
                'song.Featuring',
                'artist.Stage_Name as artist_name'
            )
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->limit(4)
            ->get();

        $latestVideos = DB::table('listing as item')
            ->select(
                'item.id',
                'item.slug',
                'item.TrackTitle as track_title',
                'item.Featuring as featuring'
            )
            ->where('item.id', '<>', $video->id)
            ->whereRaw('LOWER(item.ListingType) = ?', ['video'])
            ->where('item.IsPublished', 'YES')
            ->orderByDesc('item.id')
            ->limit(4)
            ->get();

        return view('pages.video-details', compact(
            'video',
            'canonicalSlug',
            'otherVideos',
            'artistSongs',
            'latestSongs',
            'latestVideos'
        ));
    }


    public function videosPostedBy(
        string $slug
    ): \Illuminate\Contracts\View\View {
        // The legacy site's public posters are users 4 and 5.
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $videos = DB::table('listing as video')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'video.Artists_Id'
            )
            ->select(
                'video.id',
                'video.slug',
                'video.TrackTitle as track_title',
                'video.CoverUrl as cover_url',
                'video.Featuring as featuring',
                'video.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('video.posted_by', $poster->id)
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.videos-posted-by', [
            'poster' => $poster,
            'videos' => $videos,
        ]);
    }


    private function publishedArtists(): Builder
    {
        return DB::table('artists')
            ->select(
                'id',
                'Artists_Id',
                'ArtistsName',
                'Stage_Name',
                'ProfilePic',
                'country_id'
            )
            ->where('IsPublished', 'YES');
    }

    public function artists(): View
    {
        $popularNames = [
            'Wizkid',
            'Davido',
            'Burna Boy',
            'Kizz Daniel',
            'Seyi Vibez',
            'Rema',
        ];

        $popularArtists = $this->publishedArtists()
            ->where(function (Builder $query) use ($popularNames) {
                $query
                    ->whereIn('Stage_Name', $popularNames)
                    ->orWhereIn('ArtistsName', $popularNames);
            })
            ->get()
            ->sortBy(function ($artist) use ($popularNames) {
                $name = trim((string) (
                    $artist->Stage_Name ?: $artist->ArtistsName
                ));

                $position = array_search($name, $popularNames, true);

                return $position === false ? count($popularNames) : $position;
            })
            ->values();

        $countries = [
            'naija' => [
                'heading' => 'Nigerian Artists',
                'artists' => $this->publishedArtists()
                    ->where('country_id', 'naija')
                    ->orderBy('Stage_Name')
                    ->orderBy('id')
                    ->paginate(18, ['*'], 'naija_page'),
            ],
            'ghana' => [
                'heading' => 'Ghanaian Artists',
                'artists' => $this->publishedArtists()
                    ->where('country_id', 'ghana')
                    ->orderBy('Stage_Name')
                    ->orderBy('id')
                    ->paginate(18, ['*'], 'ghana_page'),
            ],
            'african' => [
                'heading' => 'African Artists',
                'artists' => $this->publishedArtists()
                    ->where('country_id', 'african')
                    ->orderBy('Stage_Name')
                    ->orderBy('id')
                    ->paginate(18, ['*'], 'african_page'),
            ],
        ];

        return view('pages.artists', [
            'popularArtists' => $popularArtists,
            'countries' => $countries,
        ]);
    }

    public function artistCountrySection(string $country): View
    {
        abort_unless(
            in_array($country, ['naija', 'ghana', 'african'], true),
            404
        );

        $headings = [
            'naija' => 'Nigerian Artists',
            'ghana' => 'Ghanaian Artists',
            'african' => 'African Artists',
        ];

        $artists = $this->publishedArtists()
            ->where('country_id', $country)
            ->orderBy('Stage_Name')
            ->orderBy('id')
            ->paginate(18);

        return view('partials.artists.country-section', [
            'country' => $country,
            'heading' => $headings[$country],
            'artists' => $artists,
        ]);
    }



    public function artistDetails(string $slug): \Illuminate\Contracts\View\View
    {
        $matchingArtist = DB::table('artists')
            ->select('id', 'Stage_Name', 'ArtistsName')
            ->where('IsPublished', 'YES')
            ->get()
            ->first(function ($row) use ($slug) {
                $stageName = trim((string) $row->Stage_Name);
                $artistName = trim((string) $row->ArtistsName);

                return (
                    $stageName !== ''
                    && \Illuminate\Support\Str::slug($stageName) === $slug
                ) || (
                    $artistName !== ''
                    && \Illuminate\Support\Str::slug($artistName) === $slug
                );
            });

        abort_unless($matchingArtist, 404);

        $artist = DB::table('artists')
            ->where('id', $matchingArtist->id)
            ->firstOrFail();

        $artistName = trim((string) (
            $artist->Stage_Name ?: $artist->ArtistsName
        ));

        $albums = DB::table('albums')
            ->select(
                'id',
                'title',
                'cover_url',
                'released_year',
                'released_date'
            )
            ->where('artist_id', $artist->Artists_Id)
            ->where('IsPublished', 'YES')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $albumTracks = $albums->isEmpty()
        ? collect()
        : DB::table('listing as song')
            ->select(
                'song.id',
                'song.album_id',
                'song.TrackTitle as track_title',
                'song.Featuring as featuring',
                'song.track_number'
            )
            ->selectRaw('? as artist_name', [$artistName])
            ->whereIn('song.album_id', $albums->pluck('id')->all())
            ->where('song.Artists_Id', $artist->Artists_Id)
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->orderBy('song.album_id')
            ->orderBy('song.track_number')
            ->orderBy('song.id')
            ->get()
            ->groupBy('album_id');    

        $singles = DB::table('listing as song')
            ->select(
                'song.id',
                'song.slug',
                'song.TrackTitle as track_title',
                'song.Featuring as featuring',
                'song.CoverUrl as cover_url',
                'song.created_at'
            )
            ->selectRaw('? as artist_name', [$artistName])
            ->where('song.Artists_Id', $artist->Artists_Id)
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->where(function ($query) {
                $query
                    ->whereNull('song.album_id')
                    ->orWhere('song.album_id', 0);
            })
            ->orderByDesc('song.id')
            ->limit(18)
            ->get();

        $videos = DB::table('listing as video')
            ->select(
                'video.id',
                'video.slug',
                'video.TrackTitle as track_title',
                'video.Featuring as featuring',
                'video.CoverUrl as cover_url',
                'video.created_at'
            )
            ->selectRaw('? as artist_name', [$artistName])
            ->where('video.Artists_Id', $artist->Artists_Id)
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->orderByDesc('video.id')
            ->limit(12)
            ->get();

        return view('pages.artist-details', compact(
            'artist',
            'artistName',
            'albums',
            'albumTracks',
            'singles',
            'videos'
        ));
    }


    public function albums(): \Illuminate\Contracts\View\View
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
                'album.released_date',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->selectSub(
                DB::table('listing as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
                    ->where('song.IsPublished', 'YES'),
                'track_count'
            )
            ->where('album.IsPublished', 'YES')
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.albums', [
            'albums' => $albums,
        ]);
    }

    public function albumDetails(
        int $id,
        string $slug
    ): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse {
        $album = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'album.artist_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'album.posted_by'
            )
            ->select(
                'album.*',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                "),
                'poster.name as posted_by_name'
            )
            ->where('album.id', $id)
            ->where('album.IsPublished', 'YES')
            ->first();

        abort_unless($album, 404);

        $correctSlug = \Illuminate\Support\Str::slug(
            $album->artist_name . ' ' . $album->title
        );

        if ($slug !== $correctSlug) {
            return redirect()->route(
                'albums.show',
                [$album->id, $correctSlug],
                301
            );
        }

        $tracks = DB::table('listing as song')
            ->select(
                'song.id',
                'song.TrackTitle as track_title',
                'song.Featuring as featuring',
                'song.track_number',
                'song.created_at'
            )
            ->selectRaw('? as artist_name', [$album->artist_name])
            ->where('song.album_id', $album->id)
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->orderBy('song.track_number')
            ->orderBy('song.id')
            ->get();

        $otherAlbums = DB::table('albums as other')
            ->select(
                'other.id',
                'other.title',
                'other.released_year'
            )
            ->where('other.artist_id', $album->artist_id)
            ->where('other.id', '<>', $album->id)
            ->where('other.IsPublished', 'YES')
            ->orderByDesc('other.id')
            ->limit(5)
            ->get();

        return view('pages.album-details', [
            'album' => $album,
            'tracks' => $tracks,
            'otherAlbums' => $otherAlbums,
            'correctSlug' => $correctSlug,
        ]);
    }

    public function albumPostedBy(string $slug): \Illuminate\Contracts\View\View
    {
        // Public posters in the legacy database are users 4 and 5.
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

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
                'album.released_date',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->selectSub(
                DB::table('listing as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
                    ->where('song.IsPublished', 'YES'),
                'track_count'
            )
            ->where('album.posted_by', $poster->id)
            ->where('album.IsPublished', 'YES')
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.album-posted-by', [
            'poster' => $poster,
            'albums' => $albums,
        ]);
    }


    public function allMusic(): \Illuminate\Contracts\View\View
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
                'song.TrackUrl as track_url',
                'song.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
            ->where('song.IsPublished', 'YES')
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.music-all', [
            'songs' => $songs,
        ]);
    }


    public function djMixes(): \Illuminate\Contracts\View\View
    {
        $mixes = \Illuminate\Support\Facades\DB::table('dj_mixs as mix')
            ->leftJoin('dj', 'dj.id', '=', 'mix.dj_id')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'mix.created_at',
                \Illuminate\Support\Facades\DB::raw("
                    COALESCE(
                        NULLIF(dj.dj_name, ''),
                        'TrendyBeatz DJ'
                    ) as dj_name
                ")
            )
            ->where('mix.IsPublished', 'YES')
            ->whereNotNull('mix.mix_title')
            ->where('mix.mix_title', '<>', '')
            ->orderByDesc('mix.id')
            ->paginate(24);

        return view('pages.dj-mixes', [
            'mixes' => $mixes,
        ]);
    }


    public function mixDetails(int $id, string $slug): View|RedirectResponse
    {
        $mix = DB::table('dj_mixs as mix')
            ->leftJoin('dj', 'dj.id', '=', 'mix.dj_id')
            ->leftJoin('users as poster', 'poster.id', '=', 'mix.posted_by')
            ->select(
                'mix.*',
                'dj.dj_name',
                'dj.photo as dj_photo',
                'poster.name as posted_by_name'
            )
            ->where('mix.id', $id)
            ->where('mix.IsPublished', 'YES')
            ->first();

        abort_unless($mix, 404);

        // Preserve existing legacy slugs, including punctuation such as "feat.".
        $canonicalSlug = trim((string) $mix->slug, " \t\n\r\0\x0B/");

        if ($canonicalSlug === '') {
            $canonicalSlug = Str::slug(
                trim(($mix->dj_name ?: 'TrendyBeatz DJ') . ' ' . $mix->mix_title)
            );
        }

        if ($slug !== $canonicalSlug) {
            return redirect(
                route('mixes.show', [$mix->id, $canonicalSlug]),
                301
            );
        }

        $otherMixesByDj = DB::table('dj_mixs as mix')
            ->leftJoin('dj', 'dj.id', '=', 'mix.dj_id')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'dj.dj_name'
            )
            ->where('mix.dj_id', $mix->dj_id)
            ->where('mix.id', '<>', $mix->id)
            ->where('mix.IsPublished', 'YES')
            ->orderByDesc('mix.id')
            ->limit(6)
            ->get();

        $otherDjs = DB::table('dj_mixs as mix')
            ->join('dj', 'dj.id', '=', 'mix.dj_id')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'dj.dj_name',
                'dj.photo as dj_photo'
            )
            ->where('mix.dj_id', '<>', $mix->dj_id)
            ->where('mix.IsPublished', 'YES')
            ->where('dj.IsPublished', 'YES')
            ->whereIn('mix.id', function ($query) use ($mix) {
                $query->from('dj_mixs')
                    ->selectRaw('MAX(id)')
                    ->where('IsPublished', 'YES')
                    ->where('dj_id', '<>', $mix->dj_id)
                    ->groupBy('dj_id');
            })
            ->orderByDesc('mix.id')
            ->limit(6)
            ->get();

        return view('pages.djmix-details', [
            'mix' => $mix,
            'canonicalSlug' => $canonicalSlug,
            'otherMixesByDj' => $otherMixesByDj,
            'otherDjs' => $otherDjs,
        ]);
    }


    public function mixesPostedBy(
        string $slug
    ): \Illuminate\Contracts\View\View {
        $poster = \Illuminate\Support\Facades\DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => \Illuminate\Support\Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $mixes = \Illuminate\Support\Facades\DB::table('dj_mixs as mix')
            ->leftJoin('dj', 'dj.id', '=', 'mix.dj_id')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'mix.created_at',
                \Illuminate\Support\Facades\DB::raw("
                    COALESCE(
                        NULLIF(dj.dj_name, ''),
                        'TrendyBeatz DJ'
                    ) as dj_name
                ")
            )
            ->where('mix.posted_by', $poster->id)
            ->where('mix.IsPublished', 'YES')
            ->whereNotNull('mix.mix_title')
            ->where('mix.mix_title', '<>', '')
            ->orderByDesc('mix.id')
            ->paginate(24);

        return view('pages.djmixes-posted-by', [
            'poster' => $poster,
            'posterSlug' => $slug,
            'mixes' => $mixes,
        ]);
    }


  
    private function blogListingQuery(): Builder
    {
        return DB::table('blogs as blog')
            ->leftJoin(
                'blog_types as category',
                'category.id',
                '=',
                'blog.category_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'blog.posted_by'
            )
            ->select(
                'blog.id',
                'blog.slug',
                'blog.category_id',
                'blog.title',
                'blog.intro',
                'blog.photo',
                'blog.created_at',
                'blog.updated_at',
                'category.name as category_name',
                'category.slug as category_slug',
                DB::raw("
                    COALESCE(
                        NULLIF(poster.name, ''),
                        CASE
                            WHEN blog.posted_by REGEXP '^[0-9]+$'
                                THEN NULL
                            ELSE NULLIF(blog.posted_by, '')
                        END
                    ) as posted_by_name
                ")
            )
            ->where('blog.IsPublished', 'YES');
    }

    public function blogs(): View
    {
        $categories = DB::table('blog_types')
            ->select('id', 'name', 'slug')
            ->whereNotNull('slug')
            ->orderBy('name')
            ->get();

        $posts = $this->blogListingQuery()
            ->orderByDesc('blog.id')
            ->paginate(12);

        return view('pages.blogs', [
            'categories' => $categories,
            'posts' => $posts,
            'selectedCategory' => null,
        ]);
    }

    public function blogCategory(string $category): View
    {
        $categories = DB::table('blog_types')
            ->select('id', 'name', 'slug')
            ->whereNotNull('slug')
            ->orderBy('name')
            ->get();

        $selectedCategory = $categories->firstWhere('slug', $category);

        abort_unless($selectedCategory, 404);

        $posts = $this->blogListingQuery()
            ->where('blog.category_id', $selectedCategory->id)
            ->orderByDesc('blog.id')
            ->paginate(12);

        return view('pages.blogs', [
            'categories' => $categories,
            'posts' => $posts,
            'selectedCategory' => $selectedCategory,
        ]);
    }
    public function blogDetails(string $slug): \Illuminate\Contracts\View\View
    {
        $post = DB::table('blogs as blog')
            ->leftJoin(
                'blog_types as category',
                'category.id',
                '=',
                'blog.category_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'blog.posted_by'
            )
            ->select(
                'blog.*',
                'category.name as category_name',
                'category.slug as category_slug',
                DB::raw("
                    COALESCE(
                        NULLIF(poster.name, ''),
                        CASE
                            WHEN blog.posted_by REGEXP '^[0-9]+$'
                                THEN NULL
                            ELSE NULLIF(blog.posted_by, '')
                        END
                    ) as posted_by_name
                ")
            )
            ->where('blog.slug', $slug)
            ->where('blog.IsPublished', 'YES')
            ->first();

        abort_unless($post, 404);

        $categories = DB::table('blog_types')
            ->select('id', 'name', 'slug')
            ->whereNotNull('slug')
            ->orderBy('name')
            ->get();

        $relatedPosts = DB::table('blogs as blog')
            ->leftJoin(
                'blog_types as category',
                'category.id',
                '=',
                'blog.category_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'blog.posted_by'
            )
            ->select(
                'blog.id',
                'blog.slug',
                'blog.title',
                'blog.photo',
                'blog.updated_at',
                'category.name as category_name',
                'category.slug as category_slug',
                DB::raw("
                    COALESCE(
                        NULLIF(poster.name, ''),
                        CASE
                            WHEN blog.posted_by REGEXP '^[0-9]+$'
                                THEN NULL
                            ELSE NULLIF(blog.posted_by, '')
                        END
                    ) as posted_by_name
                ")
            )
            ->where('blog.IsPublished', 'YES')
            ->where('blog.id', '<>', $post->id)
            ->where('blog.category_id', $post->category_id)
            ->orderByDesc('blog.id')
            ->limit(5)
            ->get();

        $latestPosts = DB::table('blogs as blog')
            ->leftJoin(
                'blog_types as category',
                'category.id',
                '=',
                'blog.category_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'blog.posted_by'
            )
            ->select(
                'blog.id',
                'blog.slug',
                'blog.title',
                'blog.photo',
                'blog.updated_at',
                'category.name as category_name',
                'category.slug as category_slug',
                DB::raw("
                    COALESCE(
                        NULLIF(poster.name, ''),
                        CASE
                            WHEN blog.posted_by REGEXP '^[0-9]+$'
                                THEN NULL
                            ELSE NULLIF(blog.posted_by, '')
                        END
                    ) as posted_by_name
                ")
            )
            ->where('blog.IsPublished', 'YES')
            ->where('blog.id', '<>', $post->id)
            ->orderByDesc('blog.id')
            ->limit(5)
            ->get();

        return view('pages.blog-details', [
            'post' => $post,
            'categories' => $categories,
            'relatedPosts' => $relatedPosts,
            'latestPosts' => $latestPosts,
        ]);
    }
        
}