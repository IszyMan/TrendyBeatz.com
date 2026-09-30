<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PageController extends Controller
{
    private function tracks(): Builder
    {
        return DB::table('listings as l')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'a',
                'a.id',
                '=',
                'l.artist_id'
            )
            ->select('l.*', 'a.stage_name as artist_name')
            ->where('l.is_published', 1);
    }

    private function audio(): Builder
    {
        return $this->tracks()
            ->where('l.listing_type', 1);
    }

    private function category(string $category, int $limit = 60)
    {
        $query = $this->audio();

        if ($category === 'gospel') {
            $query->where('l.is_gospel', 1);
        } elseif ($category === 'highlife') {
            $query->where('l.is_high_life', 1);
        } else {
            $countryId = match ($category) {
                'naija' => 1,
                'ghana' => 2,
                'african' => 3,
                default => abort(404),
            };

            $query
                ->where('l.country_id', $countryId)
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

    public function musicDetails(
        int $id,
        string $slug
    ): View|RedirectResponse {
        $artistNameSql = "
            COALESCE(
                NULLIF(artist.Stage_Name, ''),
                NULLIF(artist.ArtistsName, ''),
                'TrendyBeatz'
            ) as artist_name
        ";

        $song = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
            ->leftJoinSub(
                self::recoverySource('albums'),
                'album',
                'album.id',
                '=',
                'song.album_id'
            )
            ->select(
                'song.*',
                'album.title as album_name',
                'artist.id as artist_record_id',
                DB::raw($artistNameSql),
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

        $featuredNames = collect(
            explode(',', (string) ($song->Featuring ?? ''))
        )
            ->map(fn ($name) => trim($name))
            ->filter(fn ($name) => $name !== '')
            ->values();

        $normalizeName = static fn ($name) =>
            mb_strtolower(trim((string) $name));

        $namesToMatch = $featuredNames
            ->map($normalizeName)
            ->unique()
            ->values()
            ->all();

        $profiles = collect();

        if ($namesToMatch !== []) {
            $profiles = self::recoveryTable('artists')
                ->select('Stage_Name', 'ArtistsName')
                ->where('IsPublished', 'YES')
                ->where(function ($query) use ($namesToMatch) {
                    $query
                        ->whereIn(
                            DB::raw('LOWER(TRIM(Stage_Name))'),
                            $namesToMatch
                        )
                        ->orWhereIn(
                            DB::raw('LOWER(TRIM(ArtistsName))'),
                            $namesToMatch
                        );
                })
                ->get();
        }

        $featuredArtists = $featuredNames->map(
            function ($name) use ($profiles, $normalizeName) {
                $matches = $profiles->filter(
                    fn ($artist) =>
                        $normalizeName($artist->Stage_Name)
                            === $normalizeName($name)
                        || $normalizeName($artist->ArtistsName)
                            === $normalizeName($name)
                );

                $profileSlug = null;

                if ($matches->count() === 1) {
                    $artist = $matches->first();
                    $profileName = trim((string) $artist->Stage_Name);

                    if ($profileName === '') {
                        $profileName = trim(
                            (string) $artist->ArtistsName
                        );
                    }

                    $profileSlug = Str::slug($profileName);
                }

                return [
                    'name' => $name,
                    'slug' => $profileSlug,
                ];
            }
        );

        $relatedListings = static function () use ($artistNameSql) {
            return self::recoveryTable('listing as related')
                ->leftJoinSub(
                    self::recoverySource('artists'),
                    'artist',
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
                    DB::raw($artistNameSql)
                )
                ->where('related.IsPublished', 'YES');
        };

        $artistSongs = $relatedListings()
            ->where('related.Artists_Id', $song->Artists_Id)
            ->where('related.id', '<>', $song->id)
            ->where('related.ListingType', 'Audio')
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        $artistVideos = $relatedListings()
            ->where('related.Artists_Id', $song->Artists_Id)
            ->whereRaw('LOWER(related.ListingType) = ?', ['video'])
            ->orderByDesc('related.id')
            ->limit(3)
            ->get();

        $artistAlbums = self::recoveryTable('albums')
            ->select('id', 'title', 'cover_url', 'released_year')
            ->where('artist_id', $song->Artists_Id)
            ->where('IsPublished', 'YES')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function ($album) use ($song) {
                $album->artist_name = $song->artist_name;

                return $album;
            });

        $collaborations = $relatedListings()
            ->where('related.Artists_Id', '<>', $song->Artists_Id)
            ->where('related.ListingType', 'Audio')
            ->where(
                'related.Featuring',
                'like',
                '%' . addcslashes($song->artist_name, '%_\\') . '%'
            )
            ->orderByDesc('related.id')
            ->limit(10)
            ->get();

        $latestMusic = $relatedListings()
            ->where('related.id', '<>', $song->id)
            ->whereIn(
                'related.ListingType',
                ['Audio', 'video', 'Video']
            )
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        $trackPath = trim((string) ($song->TrackUrl ?? ''));

        $cdnAudioUrl = rtrim(
            trim((string) config('cdn.audio_url')),
            '/'
        );

        $trackUrl = null;
        $hasAudioFile = false;

        if ($trackPath !== '') {
            if (preg_match('~^https?://~i', $trackPath)) {
                $trackUrl = $trackPath;
            } elseif ($cdnAudioUrl !== '') {
                $relativePath = ltrim($trackPath, '/');

                if (
                    str_ends_with($cdnAudioUrl, '/audio')
                    && str_starts_with($relativePath, 'audio/')
                ) {
                    $relativePath = substr($relativePath, 6);
                }

                $relativePath = implode(
                    '/',
                    array_map(
                        static fn ($segment) =>
                            rawurlencode(rawurldecode($segment)),
                        explode('/', $relativePath)
                    )
                );

                $trackUrl = $cdnAudioUrl . '/' . $relativePath;
            }
        }

        if ($trackUrl !== null) {
            $hasAudioFile = Cache::remember(
                'audio-file-available:v2:' . sha1($trackUrl),
                now()->addMinutes(5),
                function () use ($trackUrl): bool {
                    try {
                        $response = Http::connectTimeout(2)
                            ->timeout(4)
                            ->head($trackUrl);

                        if (!$response->successful()) {
                            return false;
                        }

                        $contentType = strtolower(trim(
                            explode(
                                ';',
                                (string) $response->header('Content-Type')
                            )[0]
                        ));

                        $isAudio = str_starts_with(
                            $contentType,
                            'audio/'
                        ) || in_array(
                            $contentType,
                            [
                                'application/octet-stream',
                                'binary/octet-stream',
                                'application/mp3',
                            ],
                            true
                        );

                        $contentLength = $response->header(
                            'Content-Length'
                        );

                        return $isAudio
                            && (
                                $contentLength === null
                                || trim($contentLength) !== '0'
                            );
                    } catch (ConnectionException $exception) {
                        return false;
                    }
                }
            );
        }

        return view('pages.music-details', compact(
            'song',
            'artistSongs',
            'artistVideos',
            'featuredArtists',
            'artistAlbums',
            'collaborations',
            'latestMusic',
            'trackUrl',
            'hasAudioFile'
        ));
    }

    public function songOfTheDay(): View
    {
        $songs = self::recoveryTable('featured_rated as featured')
            ->joinSub(
                self::recoverySource('listing'),
                'song',
                'song.id',
                '=',
                'featured.listing_id'
            )
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

    public function songsOfTheWeek(): View
    {
        $songs = self::recoveryTable('song_of_the_week as featured')
            ->joinSub(
                self::recoverySource('listing'),
                'song',
                'song.id',
                '=',
                'featured.listing_id'
            )
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
            ->orderByRaw('CAST(featured.rate_no AS UNSIGNED) ASC')
            ->orderBy('featured.id')
            ->paginate(24);

        return view('pages.songs-of-the-week', compact('songs'));
    }

    public function songsPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(fn ($user) => Str::slug($user->name) === $slug);

        abort_unless($poster, 404);

        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
        return self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
            ->joinSub(
                self::recoverySource('featured_rated'),
                'featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->orderByRaw('CAST(featured.rate_no AS UNSIGNED) ASC')
            ->limit(10)
            ->get();
    }

    private function musicDownloadWeek()
    {
        return $this->musicDownloadAudio()
            ->joinSub(
                self::recoverySource('song_of_the_week'),
                'featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->orderByRaw('CAST(featured.rate_no AS UNSIGNED) ASC')
            ->limit(10)
            ->get();
    }

    public function musicDownload(): View
    {
        $albums = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
            'ghana' => $this->musicDownloadCategory('ghana', 25),
            'african' => $this->musicDownloadCategory('african', 25),
            'gospel' => $this->musicDownloadCategory('gospel', 8),
            'highlife' => $this->musicDownloadCategory('highlife', 8),
            'week' => $this->musicDownloadWeek(),
        ]);
    }

    public function gospelSongs(): View
    {
        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

        abort_unless(isset($pages[$country]), 404);

        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

    public function songsByYear(int $year): View
    {
        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
                DB::raw("
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

    public function latestVideos(): View
    {
        $videos = self::recoveryTable('listing as video')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.latest-videos', compact('videos'));
    }

    public function videoDetails(
        int $id,
        string $slug
    ): View|RedirectResponse {
        $video = self::recoveryTable('listing as video')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

        $featuredNames = collect(
            explode(',', (string) $video->Featuring)
        )
            ->map(fn ($name) => trim($name))
            ->filter(fn ($name) => $name !== '')
            ->values();

        $normalizeName = static fn ($name) =>
            mb_strtolower(trim((string) $name));

        $namesToMatch = $featuredNames
            ->map($normalizeName)
            ->unique()
            ->values()
            ->all();

        $profiles = collect();

        if ($namesToMatch !== []) {
            $profiles = self::recoveryTable('artists')
                ->select('Stage_Name', 'ArtistsName')
                ->where('IsPublished', 'YES')
                ->where(function ($query) use ($namesToMatch) {
                    $query
                        ->whereIn(
                            DB::raw('LOWER(TRIM(Stage_Name))'),
                            $namesToMatch
                        )
                        ->orWhereIn(
                            DB::raw('LOWER(TRIM(ArtistsName))'),
                            $namesToMatch
                        );
                })
                ->get();
        }

        $featuredArtists = $featuredNames->map(
            function ($name) use ($profiles, $normalizeName) {
                $matches = $profiles->filter(
                    fn ($artist) =>
                        $normalizeName($artist->Stage_Name)
                            === $normalizeName($name)
                        || $normalizeName($artist->ArtistsName)
                            === $normalizeName($name)
                );

                $slug = null;

                if ($matches->count() === 1) {
                    $artist = $matches->first();
                    $profileName = trim((string) $artist->Stage_Name);

                    if ($profileName === '') {
                        $profileName = trim(
                            (string) $artist->ArtistsName
                        );
                    }

                    $slug = Str::slug($profileName);
                }

                return [
                    'name' => $name,
                    'slug' => $slug,
                ];
            }
        );

        $otherVideos = self::recoveryTable('listing as item')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'item.Artists_Id'
            )
            ->select(
                'item.*',
                'item.TrackTitle as track_title',
                'item.Featuring as featuring',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('item.Artists_Id', $video->Artists_Id)
            ->where('item.id', '<>', $video->id)
            ->whereRaw('LOWER(item.ListingType) = ?', ['video'])
            ->where('item.IsPublished', 'YES')
            ->orderByDesc('item.id')
            ->limit(6)
            ->get();

        $artistSongs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

        $latestSongs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

        $latestVideos = self::recoveryTable('listing as item')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'item.Artists_Id'
            )
            ->select(
                'item.*',
                'item.TrackTitle as track_title',
                'item.Featuring as featuring',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('item.id', '<>', $video->id)
            ->whereRaw('LOWER(item.ListingType) = ?', ['video'])
            ->where('item.IsPublished', 'YES')
            ->orderByDesc('item.id')
            ->limit(4)
            ->get();

        return view('pages.video-details', compact(
            'video',
            'featuredArtists',
            'canonicalSlug',
            'otherVideos',
            'artistSongs',
            'latestSongs',
            'latestVideos'
        ));
    }

    public function videosPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(fn ($user) => Str::slug($user->name) === $slug);

        abort_unless($poster, 404);

        $videos = self::recoveryTable('listing as video')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

    public function videosByYear(string $year): View
    {
        $videos = self::recoveryTable('listing as video')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'video.Artists_Id'
            )
            ->select(
                'video.id',
                'video.TrackTitle as track_title',
                'video.Featuring as featuring',
                'video.CoverUrl as cover_url',
                'video.YearOfRelease as released_year',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->where('video.YearOfRelease', $year)
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.videos-year', compact('videos', 'year'));
    }

    public function countryVideos(string $country): View
    {
        abort_unless(
            in_array($country, ['naija', 'ghana', 'african'], true),
            404
        );

        $countryName = match ($country) {
            'naija' => 'Naija',
            'ghana' => 'Ghana',
            'african' => 'African',
        };

        $description = match ($country) {
            'naija' => 'Watch the latest Naija music videos on TrendyBeatz. Discover Nigerian artists, explore new releases and find available music video downloads.',
            'ghana' => 'Watch the latest Ghana music videos on TrendyBeatz. Discover Ghanaian artists, explore new releases and find available music video downloads.',
            'african' => 'Watch the latest African music videos on TrendyBeatz. Discover artists across Africa, explore new releases and find available music video downloads.',
        };

        $videos = self::recoveryTable('listing as video')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'video.Artists_Id'
            )
            ->select(
                'video.id',
                'video.TrackTitle as track_title',
                'video.Featuring as featuring',
                'video.CoverUrl as cover_url',
                'video.country_id',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->whereRaw('LOWER(video.ListingType) = ?', ['video'])
            ->where('video.IsPublished', 'YES')
            ->where('video.country_id', $country)
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.videos-country', compact(
            'videos',
            'country',
            'countryName',
            'description'
        ));
    }

    private function publishedArtists(): Builder
    {
        return self::recoveryTable('artists')
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

                return $position === false
                    ? count($popularNames)
                    : $position;
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

    public function artistDetails(string $slug): View
    {
        $matchingArtist = self::recoveryTable('artists')
            ->select('id', 'Stage_Name', 'ArtistsName')
            ->where('IsPublished', 'YES')
            ->get()
            ->first(function ($row) use ($slug) {
                $stageName = trim((string) $row->Stage_Name);
                $artistName = trim((string) $row->ArtistsName);

                return (
                    $stageName !== ''
                    && Str::slug($stageName) === $slug
                ) || (
                    $artistName !== ''
                    && Str::slug($artistName) === $slug
                );
            });

        abort_unless($matchingArtist, 404);

        $artist = self::recoveryTable('artists')
            ->where('id', $matchingArtist->id)
            ->firstOrFail();

        $artistName = trim((string) (
            $artist->Stage_Name ?: $artist->ArtistsName
        ));

        $albums = self::recoveryTable('albums')
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
            : self::recoveryTable('listing as song')
                ->select(
                    'song.id',
                    'song.album_id',
                    'song.TrackTitle as track_title',
                    'song.Featuring as featuring',
                    'song.track_number'
                )
                ->selectRaw('? as artist_name', [$artistName])
                ->whereIn(
                    'song.album_id',
                    $albums->pluck('id')->all()
                )
                ->where('song.Artists_Id', $artist->Artists_Id)
                ->whereRaw('LOWER(song.ListingType) = ?', ['audio'])
                ->where('song.IsPublished', 'YES')
                ->orderBy('song.album_id')
                ->orderBy('song.track_number')
                ->orderBy('song.id')
                ->get()
                ->groupBy('album_id');

        $singles = self::recoveryTable('listing as song')
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

        $videos = self::recoveryTable('listing as video')
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

    public function albums(): View
    {
        $albums = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
                self::recoveryTable('listing as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->whereRaw(
                        'LOWER(song.ListingType) = ?',
                        ['audio']
                    )
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
    ): View|RedirectResponse {
        $album = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

        $correctSlug = Str::slug(
            $album->artist_name . ' ' . $album->title
        );

        if ($slug !== $correctSlug) {
            return redirect()->route(
                'albums.show',
                [$album->id, $correctSlug],
                301
            );
        }

        $tracks = self::recoveryTable('listing as song')
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

        $otherAlbums = self::recoveryTable('albums as other')
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

    public function albumPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(fn ($user) => Str::slug($user->name) === $slug);

        abort_unless($poster, 404);

        $albums = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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
                self::recoveryTable('listing as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->whereRaw(
                        'LOWER(song.ListingType) = ?',
                        ['audio']
                    )
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

    public function albumsByYear(string $year): View
    {
        $albums = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.*',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->selectSub(function ($query) {
                $query
                    ->fromSub(
                        self::recoverySource('listing'),
                        'track'
                    )
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('track.album_id', 'album.id')
                    ->where('track.ListingType', 'Audio')
                    ->where('track.IsPublished', 'YES');
            }, 'track_count')
            ->where('album.IsPublished', 'YES')
            ->where('album.released_year', $year)
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.albums-year', compact('albums', 'year'));
    }

    public function popularAlbums(): View
    {
        $albums = self::recoveryTable('albums as album')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
                'artist.Artists_Id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.*',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->selectSub(function ($query) {
                $query
                    ->fromSub(
                        self::recoverySource('listing'),
                        'track'
                    )
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('track.album_id', 'album.id')
                    ->whereRaw(
                        'LOWER(track.ListingType) = ?',
                        ['audio']
                    )
                    ->where('track.IsPublished', 'YES');
            }, 'track_count')
            ->selectSub(function ($query) {
                $query
                    ->fromSub(
                        self::recoverySource('album_of_the_day'),
                        'popular'
                    )
                    ->selectRaw('MAX(popular.rate_no)')
                    ->whereColumn('popular.album_id', 'album.id');
            }, 'popularity_rate')
            ->whereExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->fromSub(
                        self::recoverySource('album_of_the_day'),
                        'popular'
                    )
                    ->whereColumn('popular.album_id', 'album.id');
            })
            ->where('album.IsPublished', 'YES')
            ->orderByDesc('popularity_rate')
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.popular-albums', compact('albums'));
    }

    public function allMusic(): View
    {
        $songs = self::recoveryTable('listing as song')
            ->leftJoinSub(
                self::recoverySource('artists'),
                'artist',
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

    public function djMixes(): View
    {
        $mixes = self::recoveryTable('dj_mixs as mix')
            ->leftJoinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'mix.created_at',
                DB::raw("
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

    public function mixDetails(
        int $id,
        string $slug
    ): View|RedirectResponse {
        $mix = self::recoveryTable('dj_mixs as mix')
            ->leftJoinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'mix.posted_by'
            )
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

        $canonicalSlug = trim(
            (string) $mix->slug,
            " \t\n\r\0\x0B/"
        );

        if ($canonicalSlug === '') {
            $canonicalSlug = Str::slug(
                trim(
                    ($mix->dj_name ?: 'TrendyBeatz DJ')
                    . ' '
                    . $mix->mix_title
                )
            );
        }

        if ($slug !== $canonicalSlug) {
            return redirect(
                route('mixes.show', [$mix->id, $canonicalSlug]),
                301
            );
        }

        $otherMixesByDj = self::recoveryTable('dj_mixs as mix')
            ->leftJoinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
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

        $otherDjs = self::recoveryTable('dj_mixs as mix')
            ->joinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
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
                $query
                    ->fromSub(
                        self::recoverySource('dj_mixs'),
                        'dj_mixs'
                    )
                    ->selectRaw('MAX(id)')
                    ->where('IsPublished', 'YES')
                    ->where('dj_id', '<>', $mix->dj_id)
                    ->groupBy('dj_id');
            })
            ->orderByDesc('mix.id')
            ->limit(6)
            ->get();

        $trackPath = trim((string) ($mix->track_url ?? ''));

        $cdnMixUrl = rtrim(
            trim((string) config('cdn.djmix_url')),
            '/'
        );

        $trackUrl = null;
        $hasAudioFile = false;

        if ($trackPath !== '') {
            if (preg_match('~^https?://~i', $trackPath)) {
                $trackUrl = $trackPath;
            } elseif ($cdnMixUrl !== '') {
                $relativePath = ltrim($trackPath, '/');

                if (
                    str_ends_with($cdnMixUrl, '/djmix')
                    && str_starts_with($relativePath, 'djmix/')
                ) {
                    $relativePath = substr($relativePath, 6);
                }

                $relativePath = implode(
                    '/',
                    array_map(
                        static fn ($segment) =>
                            rawurlencode(rawurldecode($segment)),
                        explode('/', $relativePath)
                    )
                );

                $trackUrl = $cdnMixUrl . '/' . $relativePath;
            }
        }

        if ($trackUrl !== null) {
            $hasAudioFile = Cache::remember(
                'djmix-audio-available:v1:' . sha1($trackUrl),
                now()->addMinutes(5),
                function () use ($trackUrl): bool {
                    try {
                        $response = Http::connectTimeout(2)
                            ->timeout(4)
                            ->head($trackUrl);

                        if (!$response->successful()) {
                            return false;
                        }

                        $contentType = strtolower(trim(
                            explode(
                                ';',
                                (string) $response->header('Content-Type')
                            )[0]
                        ));

                        $isAudio = str_starts_with(
                            $contentType,
                            'audio/'
                        ) || in_array(
                            $contentType,
                            [
                                'application/octet-stream',
                                'binary/octet-stream',
                                'application/mp3',
                            ],
                            true
                        );

                        $contentLength = $response->header(
                            'Content-Length'
                        );

                        return $isAudio
                            && (
                                $contentLength === null
                                || trim($contentLength) !== '0'
                            );
                    } catch (ConnectionException $exception) {
                        return false;
                    }
                }
            );
        }

        return view('pages.djmix-details', [
            'mix' => $mix,
            'canonicalSlug' => $canonicalSlug,
            'otherMixesByDj' => $otherMixesByDj,
            'otherDjs' => $otherDjs,
            'trackUrl' => $trackUrl,
            'hasAudioFile' => $hasAudioFile,
        ]);
    }

    public function djs(): View
    {
        $djs = self::recoveryTable('dj')
            ->where('IsPublished', 'YES')
            ->whereNotNull('dj_name')
            ->where('dj_name', '<>', '')
            ->orderBy('dj_name')
            ->orderBy('id')
            ->paginate(20);

        return view('pages.djs', compact('djs'));
    }

    public function djDetails(string $slug): View
    {
        $dj = self::recoveryTable('dj')
            ->where('IsPublished', 'YES')
            ->get()
            ->first(function ($row) use ($slug) {
                $djSlug = trim((string) ($row->slug ?? ''));

                if ($djSlug === '') {
                    $djSlug = Str::slug($row->dj_name);
                }

                return $djSlug === $slug;
            });

        abort_unless($dj, 404);

        $mixes = self::recoveryTable('dj_mixs as mix')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url'
            )
            ->where('mix.dj_id', $dj->id)
            ->where('mix.IsPublished', 'YES')
            ->whereNotNull('mix.mix_title')
            ->where('mix.mix_title', '<>', '')
            ->orderByDesc('mix.id')
            ->paginate(20);

        return view('pages.djs-details', compact(
            'dj',
            'mixes',
            'slug'
        ));
    }

    public function mixesPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(fn ($user) => Str::slug($user->name) === $slug);

        abort_unless($poster, 404);

        $mixes = self::recoveryTable('dj_mixs as mix')
            ->leftJoinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'mix.created_at',
                DB::raw("
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

    public function mixesByYear(string $year): View
    {
        $mixes = self::recoveryTable('dj_mixs as mix')
            ->leftJoinSub(
                self::recoverySource('dj'),
                'dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.id',
                'mix.slug',
                'mix.mix_title',
                'mix.cover_url',
                'mix.released_year',
                DB::raw("
                    COALESCE(
                        NULLIF(dj.dj_name, ''),
                        'TrendyBeatz DJ'
                    ) as dj_name
                ")
            )
            ->where('mix.IsPublished', 'YES')
            ->where('mix.released_year', $year)
            ->whereNotNull('mix.mix_title')
            ->where('mix.mix_title', '<>', '')
            ->orderByDesc('mix.id')
            ->paginate(24);

        return view('pages.djmix-year', compact('mixes', 'year'));
    }

    private function blogListingQuery(): Builder
    {
        return self::recoveryTable('blogs as blog')
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

        $selectedCategory = $categories->firstWhere(
            'slug',
            $category
        );

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

    public function blogDetails(string $slug): View
    {
        $post = self::recoveryTable('blogs as blog')
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

        $relatedPosts = self::recoveryTable('blogs as blog')
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

        $latestPosts = self::recoveryTable('blogs as blog')
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

    public function blogsPublishedBy(string $slug): View
    {
        $publishers = DB::query()
            ->fromSub(
                $this->blogListingQuery(),
                'published_posts'
            )
            ->select('posted_by_name')
            ->whereNotNull('posted_by_name')
            ->where('posted_by_name', '<>', '')
            ->distinct()
            ->get();

        $matches = $publishers->filter(
            fn ($publisher) =>
                Str::slug($publisher->posted_by_name) === $slug
        );

        abort_unless($matches->count() === 1, 404);

        $publisherName = $matches->first()->posted_by_name;

        $posts = DB::query()
            ->fromSub(
                $this->blogListingQuery(),
                'published_posts'
            )
            ->where('posted_by_name', $publisherName)
            ->orderByDesc('id')
            ->paginate(12);

        return view('pages.blogs-published-by', compact(
            'posts',
            'publisherName',
            'slug'
        ));
    }

    /**
     * Read the recovered schema while retaining the field names
     * expected by existing Blade templates and URL helpers.
     *
     * Artists_Id below represents the numeric artists.id.
     * These queries do not create or modify database records.
     */
    private static function recoveryTable(string $reference): Builder
    {
        $parts = explode(' as ', $reference, 2);

        return DB::query()->fromSub(
            self::recoverySource($parts[0]),
            $parts[1] ?? $parts[0]
        );
    }

    private static function recoverySource(string $table): Builder
    {
        [$physicalTable, $columns] = match ($table) {
            'listing' => ['listings', [
                "source.`id`",
                "source.`artist_id`",
                "source.`album_id`",
                "source.`released_year`",
                "source.`track_title`",
                "source.`track_number`",
                "source.`track_url`",
                "source.`buy_song`",
                "source.`cover_url`",
                "source.`listing_type`",
                "source.`track_info`",
                "source.`track_page`",
                "source.`clicks`",
                "source.`track_info1`",
                "source.`track_info2`",
                "source.`script_url`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`directed_by`",
                "source.`produced_by`",
                "source.`meta_keyword`",
                "source.`is_video_comedy`",
                "source.`is_published`",
                "source.`slug`",
                "source.`stream_count`",
                "source.`country_order`",
                "source.`introduction`",
                "source.`additional_link_id`",
                "source.`is_gospel`",
                "source.`is_high_life`",
                "source.`show_ads`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.`youtube_embed_url`",
                "source.`audiomack_embed_url`",
                "source.artist_id AS `Artists_Id`",
                "source.featuring AS `Featuring`",
                "source.released_year AS `YearOfRelease`",
                "source.track_title AS `TrackTitle`",
                "source.track_url AS `TrackUrl`",
                "source.cover_url AS `CoverUrl`",
                "
                    CASE source.listing_type
                        WHEN '1' THEN 'Audio'
                        WHEN '2' THEN 'Video'
                        ELSE NULL
                    END AS `ListingType`
                ",
                "source.track_info AS `TrackInfo`",
                "source.track_page AS `TrackPage`",
                "source.track_info1 AS `trackinfo1`",
                "source.track_info2 AS `trackinfo2`",
                "source.script_url AS `scriptUrl`",
                "source.directed_by AS `directedby`",
                "source.produced_by AS `producedby`",
                "
                    CASE WHEN source.is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
                "source.is_gospel AS `isgospel`",
                "source.is_high_life AS `ishighlife`",
                "
                    CASE source.country_id
                        WHEN '1' THEN 'naija'
                        WHEN '2' THEN 'ghana'
                        WHEN '3' THEN 'african'
                        ELSE source.country_id
                    END AS `country_id`
                ",
                "NULL AS `AlbumName`",
            ]],

            'artists' => ['artists', [
                "source.`id`",
                "source.`slug`",
                "source.`artist_id`",
                "source.`full_name`",
                "source.`artist_profile`",
                "source.`img`",
                "source.`record_label`",
                "source.`place_of_birth`",
                "source.`genre`",
                "source.`award1`",
                "source.`award2`",
                "source.`award3`",
                "source.`networth_id`",
                "source.`is_published`",
                "source.`order_id`",
                "source.`artist_type`",
                "source.`is_popular`",
                "source.`meta_keyword`",
                "source.`show_ads`",
                "source.`user_id`",
                "source.`posted_by`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.id AS `Artists_Id`",
                "source.stage_name AS `ArtistsName`",
                "source.stage_name AS `Stage_Name`",
                "source.img AS `ProfilePic`",
                "source.artist_profile AS `ArtistsProfile`",
                "source.record_label AS `RecordLabel`",
                "source.full_name AS `Fullname`",
                "source.place_of_birth AS `Place_Birth`",
                "source.genre AS `Genres`",
                "source.award1 AS `Awards1`",
                "source.award2 AS `Awards2`",
                "source.award3 AS `Awards3`",
                "source.endorsement1 AS `Endorsement1`",
                "source.endorsement2 AS `Endorsement2`",
                "
                    CASE WHEN source.is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
                "
                    CASE source.country_id
                        WHEN '1' THEN 'naija'
                        WHEN '2' THEN 'ghana'
                        WHEN '3' THEN 'african'
                        ELSE source.country_id
                    END AS `country_id`
                ",
                "source.artist_type AS `category_id`",
                "source.is_also_comedian AS `Is_also_comedian`",
                "source.posted_by AS `posteb_by`",
            ]],

            'albums' => ['albums', [
                "source.`id`",
                "source.`artist_id`",
                "source.`title`",
                "source.`slug`",
                "source.`featuring`",
                "source.`cover_url`",
                "source.`description`",
                "source.`released_year`",
                "source.`released_date`",
                "source.`sort_order`",
                "source.`album_type`",
                "source.`is_published`",
                "source.`is_popular`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`show_ads`",
                "source.`created_at`",
                "source.`updated_at`",
                "
                    CASE WHEN source.is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
                "source.album_type AS `category_id`",
            ]],

            'dj' => ['djs', [
                "source.`id`",
                "source.`slug`",
                "source.`name`",
                "source.`full_name`",
                "source.`place_of_birth`",
                "source.`profile_img`",
                "source.`award`",
                "source.`endorsement`",
                "source.`is_published`",
                "source.`networth_id`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`order_id`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.name AS `dj_name`",
                "source.full_name AS `fullname`",
                "source.profile_img AS `photo`",
                "
                    CASE WHEN source.is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
                "
                    CASE source.country_id
                        WHEN '1' THEN 'naija'
                        WHEN '2' THEN 'ghana'
                        WHEN '3' THEN 'african'
                        ELSE source.country_id
                    END AS `country_id`
                ",
            ]],

            'dj_mixs' => ['dj_mixs', [
                "source.`id`",
                "source.`dj_id`",
                "source.`title`",
                "source.`introduction`",
                "source.`details`",
                "source.`details2`",
                "source.`cover_url`",
                "source.`back_cover`",
                "source.`track_url`",
                "source.`mix_count`",
                "source.`description1`",
                "source.`description2`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`is_published`",
                "source.`sort_order`",
                "source.`is_popular`",
                "source.`released_year`",
                "source.`stream_count`",
                "source.`slug`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.title AS `mix_title`",
                "
                    CASE WHEN source.is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
            ]],

            'blogs' => ['blogs', [
                "source.`id`",
                "source.`blog_type`",
                "source.`posted_by`",
                "source.`title`",
                "source.`intro`",
                "source.`photo`",
                "source.`description`",
                "source.`desc2`",
                "source.`desc3`",
                "source.`photo2`",
                "source.`desc4`",
                "source.`desc5`",
                "source.`desc6`",
                "source.`photo3`",
                "source.`desc7`",
                "source.`desc8`",
                "source.`desc9`",
                "source.`photo4`",
                "source.`desc10`",
                "source.`Desc11`",
                "source.`Desc12`",
                "source.`Desc13`",
                "source.`photo5`",
                "source.`Desc14`",
                "source.`Desc15`",
                "source.`Desc16`",
                "source.`photo6`",
                "source.`Desc17`",
                "source.`Desc18`",
                "source.`Desc19`",
                "source.`photo7`",
                "source.`Desc20`",
                "source.`Desc21`",
                "source.`Desc22`",
                "source.`Desc23`",
                "source.`photo8`",
                "source.`Desc24`",
                "source.`Desc25`",
                "source.`Desc26`",
                "source.`photo9`",
                "source.`Desc27`",
                "source.`Desc28`",
                "source.`Desc29`",
                "source.`photo10`",
                "source.`Desc30`",
                "source.`track_url`",
                "source.`file_extension`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.`related_news_link`",
                "source.`related_news_title`",
                "source.`script_url`",
                "source.`Is_published`",
                "source.`blog_album_id`",
                "source.`slug`",
                "source.`user_id`",
                "source.blog_type AS `category_id`",
                "source.track_url AS `TrackUrl`",
                "source.script_url AS `scriptUrl`",
                "
                    CASE WHEN source.Is_published = 1
                        THEN 'YES' ELSE 'NO'
                    END AS `IsPublished`
                ",
                "source.blog_album_id AS `album_id`",
            ]],

            'featured_rated' => ['listing_features', [
                "source.`id`",
                "source.`listing_id`",
                "source.`listing_feature_type`",
                "source.`rating`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.rating AS `rate_no`",
            ]],

            'song_of_the_week' => ['listing_features', [
                "source.`id`",
                "source.`listing_id`",
                "source.`listing_feature_type`",
                "source.`rating`",
                "source.`posted_by`",
                "source.`user_id`",
                "source.`created_at`",
                "source.`updated_at`",
                "source.rating AS `rate_no`",
            ]],

            'album_of_the_day' => ['album_populars', [
                "source.`id`",
                "source.`album_id`",
                "source.`rate_no`",
                "source.`created_at`",
                "source.`updated_at`",
            ]],

            default => throw new \InvalidArgumentException(
                'Unsupported recovery table: ' . $table
            ),
        };

        $query = DB::table($physicalTable . ' as source')
            ->selectRaw(implode(', ', $columns));

        if ($table === 'featured_rated') {
            $query->where('source.listing_feature_type', 1);
        } elseif ($table === 'song_of_the_week') {
            $query->where('source.listing_feature_type', 2);
        }

        return $query;
    }
}