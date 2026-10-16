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
        return DB::table('listings as listing')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'listing.artist_id'
            )
            ->select(
                'listing.*',
                'artist.stage_name as artist_name'
            )
            ->where('listing.is_published', 1);
    }

    private function audio(): Builder
    {
        return $this->tracks()
            ->where('listing.listing_type', 1);
    }

    private function category(string $category, int $limit = 60)
    {
        $query = $this->audio();

        if ($category === 'gospel') {
            $query->where('listing.is_gospel', 1);
        } elseif ($category === 'highlife') {
            $query->where('listing.is_high_life', 1);
        } else {
            $countryId = match ($category) {
                'naija' => 1,
                'ghana' => 2,
                'african' => 3,
                default => abort(404),
            };

            $query
                ->where('listing.country_id', $countryId)
                ->where('listing.is_gospel', 0)
                ->where('listing.is_high_life', 0);
        }

        return $query
            ->orderByDesc('listing.id')
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
                NULLIF(artist.stage_name, ''),
                'TrendyBeatz'
            ) AS artist_name
        ";

        $song = DB::table('listings as song')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'song.artist_id'
            )
            ->leftJoin(
                'albums as album',
                'album.id',
                '=',
                'song.album_id'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'song.posted_by'
            )
            ->select(
                'song.*',
                'song.artist_id as Artists_Id',
                'song.track_title as TrackTitle',
                'song.featuring as Featuring',
                'song.released_year as YearOfRelease',
                'song.track_url as TrackUrl',
                'song.cover_url as CoverUrl',
                'song.track_info as TrackInfo',
                'song.track_info1 as trackinfo1',
                'song.track_info2 as trackinfo2',
                'song.script_url as scriptUrl',
                'song.produced_by as producedby',
                'song.directed_by as directedby',
                'song.is_gospel as isgospel',
                'song.is_high_life as ishighlife',
                'album.title as album_name',
                'album.featuring as album_featuring',
                'artist.id as artist_record_id',
                'poster.name as posted_by_name'
            )
            ->selectRaw($artistNameSql)
            ->selectRaw("
                CASE song.country_id
                    WHEN '1' THEN 'naija'
                    WHEN '2' THEN 'ghana'
                    WHEN '3' THEN 'african'
                    ELSE song.country_id
                END AS country_id
            ")
            ->where('song.id', $id)
            ->where('song.listing_type', 1)
            ->where('song.is_published', 1)
            ->first();

        abort_unless($song, 404);

        /*
        * Keep the existing canonical URL behavior.
        */
        $canonicalSlug = \App\Support\MusicUrl::slug($song);

        if ($slug !== $canonicalSlug) {
            return redirect(
                route('music_details', [$song->id, $canonicalSlug]),
                301
            );
        }

        /*
        * Match featured artistes to published artist profiles.
        * Only link a name when exactly one profile matches.
        */
        $featuredNames = collect(
            explode(',', (string) ($song->featuring ?? ''))
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
            $profiles = DB::table('artists')
                ->select('id', 'stage_name')
                ->where('is_published', 1)
                ->whereIn(
                    DB::raw('LOWER(TRIM(stage_name))'),
                    $namesToMatch
                )
                ->get();
        }

        $featuredArtists = $featuredNames->map(
            function ($name) use ($profiles, $normalizeName) {
                $matches = $profiles->filter(
                    fn ($artist) =>
                        $normalizeName($artist->stage_name)
                            === $normalizeName($name)
                );

                $profileSlug = null;

                if ($matches->count() === 1) {
                    $profileSlug = Str::slug(
                        trim((string) $matches->first()->stage_name)
                    );
                }

                return [
                    'name' => $name,
                    'slug' => $profileSlug,
                ];
            }
        );

        /*
        * Shared query for related songs and videos.
        * A fresh query is created for each section.
        */
        $relatedListings = static function () use ($artistNameSql): Builder {
            return DB::table('listings as related')
                ->leftJoin(
                    'artists as artist',
                    'artist.id',
                    '=',
                    'related.artist_id'
                )
                ->select(
                    'related.id',
                    'related.track_title',
                    'related.featuring',
                    'related.cover_url'
                )
                ->selectRaw("
                    CASE related.listing_type
                        WHEN '1' THEN 'Audio'
                        WHEN '2' THEN 'Video'
                        ELSE NULL
                    END AS listing_type
                ")
                ->selectRaw($artistNameSql)
                ->where('related.is_published', 1);
        };

        $artistSongs = $relatedListings()
            ->where('related.artist_id', $song->artist_id)
            ->where('related.id', '<>', $song->id)
            ->where('related.listing_type', 1)
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        $artistVideos = $relatedListings()
            ->where('related.artist_id', $song->artist_id)
            ->where('related.listing_type', 2)
            ->orderByDesc('related.id')
            ->limit(3)
            ->get();

        $artistAlbums = DB::table('albums')
            ->select(
                'id',
                'title',
                'featuring',
                'cover_url',
                'released_year'
            )
            ->where('artist_id', $song->artist_id)
            ->where('is_published', 1)
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function ($album) use ($song) {
                $album->artist_name = $song->artist_name;

                return $album;
            });

        $collaborations = $relatedListings()
            ->where('related.artist_id', '<>', $song->artist_id)
            ->where('related.listing_type', 1)
            ->where(
                'related.featuring',
                'like',
                '%' . addcslashes($song->artist_name, '%_\\') . '%'
            )
            ->orderByDesc('related.id')
            ->limit(10)
            ->get();

        $latestMusic = $relatedListings()
            ->where('related.id', '<>', $song->id)
            ->whereIn('related.listing_type', [1, 2])
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        /*
        * Build the audio URL without changing stored filenames.
        */
        $trackPath = trim((string) ($song->track_url ?? ''));

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

        /*
        * Preserve the existing cached CDN availability check.
        */
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

                        $isAudio = str_starts_with($contentType, 'audio/')
                            || in_array(
                                $contentType,
                                [
                                    'application/octet-stream',
                                    'binary/octet-stream',
                                    'application/mp3',
                                ],
                                true
                            );

                        $contentLength = $response->header('Content-Length');

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

    private function publishedAudioQuery(): Builder
    {
        return DB::table('listings as song')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'song.artist_id'
            )
            ->select(
                'song.id',
                'song.slug',
                'song.track_title',
                'song.cover_url',
                'song.featuring',
                'song.track_url',
                'song.created_at',
                'song.released_year'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(artist.stage_name, ''),
                    'TrendyBeatz'
                ) AS artist_name
            ")
            ->where('song.listing_type', 1)
            ->where('song.is_published', 1);
    }

    private function featuredAudioQuery(int $featureType): Builder
    {
        return $this->publishedAudioQuery()
            ->join(
                'listing_features as featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->addSelect('featured.rating as rate_no')
            ->where('featured.listing_feature_type', $featureType);
    }

    public function songOfTheDay(): View
    {
        $songs = $this->featuredAudioQuery(1)
            ->orderByDesc('featured.id')
            ->paginate(30);

        return view('pages.song-of-the-day', [
            'songs' => $songs,
        ]);
    }

    public function topRatedSongs(): View
    {
        $songs = $this->featuredAudioQuery(1)
            ->orderByDesc('featured.id')
            ->paginate(30);

        return view('pages.top-rated-songs', [
            'songs' => $songs,
        ]);
    }

    public function topTrendingSongs(): View
    {
        $songs = $this->featuredAudioQuery(1)
            ->orderByDesc('featured.id')
            ->paginate(30);

        return view('pages.top-trending-songs', [
            'songs' => $songs,
        ]);
    }

    public function songsOfTheWeek(): View
    {
        $songs = $this->featuredAudioQuery(2)
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('featured.id')
            ->paginate(24);

        return view('pages.songs-of-the-week', compact('songs'));
    }

    public function songsPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $songs = $this->publishedAudioQuery()
            ->where('song.posted_by', $poster->id)
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.songs-posted-by', [
            'poster' => $poster,
            'songs' => $songs,
        ]);
    }

    private function musicDownloadAudio(): Builder
    {
        return $this->publishedAudioQuery();
    }

    private function musicDownloadCategory(
        string $category,
        int $limit
    ) {
        $query = $this->musicDownloadAudio();

        if ($category === 'gospel') {
            $query->where('song.is_gospel', 1);
        } elseif ($category === 'highlife') {
            $query->where('song.is_high_life', 1);
        } else {
            $countryId = match ($category) {
                'naija' => 1,
                'ghana' => 2,
                'african' => 3,
                default => abort(404),
            };

            $query->where('song.country_id', $countryId);
        }

        return $query
            ->orderByDesc('song.id')
            ->limit($limit)
            ->get();
    }

    private function musicDownloadDay()
    {
        return $this->featuredAudioQuery(1)
            ->orderByDesc('featured.id')
            ->limit(10)
            ->get();
    }

    private function musicDownloadWeek()
    {
        return $this->featuredAudioQuery(2)
            ->orderByDesc('featured.id')
            ->limit(10)
            ->get();
    }

    public function musicDownload(): View
    {
        $albums = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.id',
                'album.title',
                'album.cover_url',
                'album.released_year'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(artist.stage_name, ''),
                    'TrendyBeatz'
                ) AS artist_name
            ")
            ->where('album.is_published', 1)
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
        $songs = $this->publishedAudioQuery()
            ->where('song.is_gospel', 1)
            ->orderByDesc('song.id')
            ->paginate(50);

        return view('pages.gospel-songs', [
            'songs' => $songs,
        ]);
    }

    public function highlifeSongs(): View
    {
        $songs = $this->publishedAudioQuery()
            ->where('song.is_high_life', 1)
            ->orderByDesc('song.id')
            ->paginate(50);

        return view('pages.highlife-songs', [
            'songs' => $songs,
        ]);
    }

    public function countrySongs(string $country): View
    {
        $pages = [
            'naija' => [
                'title' => 'Discover Latest Naija Music - Legally Stream and Download | Trendybeatz',
                'description' => 'Discover, Stream and Legally Download the latest Naija songs on TrendyBeatz. Discover artists from across the continent, stream new releases, and find available Legal Streaming and downloads.',
                'heading' => 'Discover Latest Naija Music',
                'section_heading' => 'Latest Naija Songs',
                'badge' => 'Naija Music',
            ],
            'ghana' => [
                'title' => 'Discover Latest Ghana Music - Legally Stream and Download | Trendybeatz',
                'description' => 'Discover, Stream and Legally Download the latest Ghana Music on TrendyBeatz. Discover artists from across the continent, stream new releases, and find available Legal Streaming and song downloads.',
                'heading' => 'Discover Latest Ghana Music',
                'section_heading' => 'Latest Ghana Songs',
                'badge' => 'Ghana Music',
            ],
            'african' => [
                'title' => 'Discover Latest African Music - Legally Stream and Download | Trendybeatz',
                'description' => 'Discover, Stream and Legally Download the latest African Music on TrendyBeatz. Discover artists from across the continent, stream new releases, and find available Legal Streaming and song downloads.',
                'heading' => 'Discover Latest African Music',
                'section_heading' => 'Latest African Songs',
                'badge' => 'African Music',
            ],
        ];

        abort_unless(isset($pages[$country]), 404);

        $countryId = match ($country) {
            'naija' => 1,
            'ghana' => 2,
            'african' => 3,
            default => abort(404),
        };

        $songs = $this->publishedAudioQuery()
            ->where('song.country_id', $countryId)
            ->orderByDesc('song.id')
            ->paginate(50);

        return view('pages.music-country', [
            'country' => $country,
            'page' => $pages[$country],
            'songs' => $songs,
        ]);
    }

    public function songsByYear(int $year): View
    {
        $songs = $this->publishedAudioQuery()
            ->where('song.released_year', (string) $year)
            ->orderByDesc('song.id')
            ->paginate(50);

        return view('pages.songs-by-year', [
            'songs' => $songs,
            'year' => $year,
        ]);
    }

    private function publishedVideoQuery(): Builder
    {
        return DB::table('listings as video')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'video.artist_id'
            )
            ->select(
                'video.*',
                'video.artist_id as Artists_Id',
                'video.track_title as TrackTitle',
                'video.featuring as Featuring',
                'video.cover_url as CoverUrl',
                'video.track_url as TrackUrl',
                'video.released_year as YearOfRelease',
                'video.track_info as TrackInfo',
                'video.track_info1 as trackinfo1',
                'video.track_info2 as trackinfo2',
                'video.script_url as scriptUrl',
                'video.directed_by as directedby',
                'video.produced_by as producedby',
                'video.is_gospel as isgospel',
                'video.is_high_life as ishighlife'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(artist.stage_name, ''),
                    'TrendyBeatz'
                ) AS artist_name
            ")
            ->selectRaw("
                CASE video.country_id
                    WHEN '1' THEN 'naija'
                    WHEN '2' THEN 'ghana'
                    WHEN '3' THEN 'african'
                    ELSE video.country_id
                END AS country_id
            ")
            ->where('video.listing_type', 2)
            ->where('video.is_published', 1);
    }

    public function latestVideos(): View
    {
        $videos = $this->publishedVideoQuery()
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.latest-videos', compact('videos'));
    }

    public function videoDetails(
        int $id,
        string $slug
    ): View|RedirectResponse {
        $video = $this->publishedVideoQuery()
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'video.posted_by'
            )
            ->addSelect('poster.name as posted_by_name')
            ->where('video.id', $id)
            ->first();

        abort_unless($video, 404);

        $canonicalSlug = \App\Support\VideoUrl::slug($video);

        if ($slug !== $canonicalSlug) {
            return redirect(
                \App\Support\VideoUrl::detail($video),
                301
            );
        }

        /*
        * Match featured artistes to published artist profiles.
        */
        $featuredNames = collect(
            explode(',', (string) ($video->featuring ?? ''))
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
            $profiles = DB::table('artists')
                ->select('id', 'stage_name')
                ->where('is_published', 1)
                ->whereIn(
                    DB::raw('LOWER(TRIM(stage_name))'),
                    $namesToMatch
                )
                ->get();
        }

        $featuredArtists = $featuredNames->map(
            function ($name) use ($profiles, $normalizeName) {
                $matches = $profiles->filter(
                    fn ($artist) =>
                        $normalizeName($artist->stage_name)
                            === $normalizeName($name)
                );

                $profileSlug = null;

                if ($matches->count() === 1) {
                    $profileSlug = Str::slug(
                        trim((string) $matches->first()->stage_name)
                    );
                }

                return [
                    'name' => $name,
                    'slug' => $profileSlug,
                ];
            }
        );

        /*
        * Other videos by the same artiste.
        */
        $otherVideos = $this->publishedVideoQuery()
            ->where('video.artist_id', $video->artist_id)
            ->where('video.id', '<>', $video->id)
            ->orderByDesc('video.id')
            ->limit(6)
            ->get();

        /*
        * Songs by the same artiste.
        *
        * These aliases preserve the fields currently used
        * by the video details Blade.
        */
        $artistSongs = $this->publishedAudioQuery()
            ->addSelect(
                'song.track_title as TrackTitle',
                'song.featuring as Featuring'
            )
            ->where('song.artist_id', $video->artist_id)
            ->orderByDesc('song.id')
            ->limit(6)
            ->get();

        /*
        * Latest songs across all artistes.
        */
        $latestSongs = $this->publishedAudioQuery()
            ->addSelect(
                'song.track_title as TrackTitle',
                'song.featuring as Featuring'
            )
            ->orderByDesc('song.id')
            ->limit(4)
            ->get();

        /*
        * Latest videos, excluding the current video.
        */
        $latestVideos = $this->publishedVideoQuery()
            ->where('video.id', '<>', $video->id)
            ->orderByDesc('video.id')
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
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $videos = $this->publishedVideoQuery()
            ->where('video.posted_by', $poster->id)
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.videos-posted-by', [
            'poster' => $poster,
            'videos' => $videos,
        ]);
    }

    public function videosByYear(string $year): View
    {
        $videos = $this->publishedVideoQuery()
            ->where('video.released_year', $year)
            ->orderByDesc('video.id')
            ->paginate(24);

        return view('pages.videos-year', compact(
            'videos',
            'year'
        ));
    }

    public function countryVideos(string $country): View
    {
        abort_unless(
            in_array($country, ['naija', 'ghana', 'african'], true),
            404
        );

        $countryId = match ($country) {
            'naija' => 1,
            'ghana' => 2,
            'african' => 3,
        };

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

        $videos = $this->publishedVideoQuery()
            ->where('video.country_id', $countryId)
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
        return DB::table('artists as artist')
            ->select(
                'artist.*',
                'artist.id as Artists_Id',
                'artist.stage_name as ArtistsName',
                'artist.stage_name as Stage_Name',
                'artist.img as ProfilePic',
                'artist.artist_profile as ArtistsProfile',
                'artist.record_label as RecordLabel',
                'artist.full_name as Fullname',
                'artist.place_of_birth as Place_Birth',
                'artist.genre as Genres',
                'artist.award1 as Awards1',
                'artist.award2 as Awards2',
                'artist.award3 as Awards3',
                'artist.endorsement1 as Endorsement1',
                'artist.endorsement2 as Endorsement2',
                'artist.artist_type as category_id',
                'artist.is_also_comedian as Is_also_comedian',
                'artist.posted_by as posteb_by'
            )
            ->selectRaw("
                CASE artist.country_id
                    WHEN '1' THEN 'naija'
                    WHEN '2' THEN 'ghana'
                    WHEN '3' THEN 'african'
                    ELSE artist.country_id
                END AS country_id
            ")
            ->selectRaw("'YES' AS IsPublished")
            ->where('artist.is_published', 1);
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
            ->whereIn('artist.stage_name', $popularNames)
            ->get()
            ->sortBy(function ($artist) use ($popularNames) {
                $name = trim((string) $artist->stage_name);

                $position = array_search(
                    $name,
                    $popularNames,
                    true
                );

                return $position === false
                    ? count($popularNames)
                    : $position;
            })
            ->values();

        $sections = [
            'naija' => [
                'id' => 1,
                'heading' => 'Nigerian Artists',
            ],
            'ghana' => [
                'id' => 2,
                'heading' => 'Ghanaian Artists',
            ],
            'african' => [
                'id' => 3,
                'heading' => 'African Artists',
            ],
        ];

        $countries = [];

        foreach ($sections as $country => $section) {
            $countries[$country] = [
                'heading' => $section['heading'],
                'artists' => $this->filteredArtists()
                    ->where('artist.country_id', $section['id'])
                    ->orderBy('artist.stage_name')
                    ->orderBy('artist.id')
                    ->paginate(18, ['*'], $country . '_page')
                    ->appends(request()->only('search', 'letter')),
            ];
        }

        return view('pages.artists', [
            'popularArtists' => $popularArtists,
            'countries' => $countries,
        ]);
    }

    public function artistCountrySection(string $country): View
    {
        $sections = [
            'naija' => [
                'id' => 1,
                'heading' => 'Nigerian Artists',
            ],
            'ghana' => [
                'id' => 2,
                'heading' => 'Ghanaian Artists',
            ],
            'african' => [
                'id' => 3,
                'heading' => 'African Artists',
            ],
        ];

        abort_unless(isset($sections[$country]), 404);

        $section = $sections[$country];

        $artists = $this->filteredArtists()
            ->where('artist.country_id', $section['id'])
            ->orderBy('artist.stage_name')
            ->orderBy('artist.id')
            ->paginate(18)
            ->appends(request()->only('search', 'letter'));

        return view('partials.artists.country-section', [
            'country' => $country,
            'heading' => $section['heading'],
            'artists' => $artists,
        ]);
    }

    public function artistDetails(string $slug): View
    {
        /*
        * Preserve existing profile URLs generated from stage names.
        */
        $matchingArtist = DB::table('artists')
            ->select('id', 'stage_name')
            ->where('is_published', 1)
            ->get()
            ->first(function ($row) use ($slug) {
                $name = trim((string) $row->stage_name);

                return $name !== ''
                    && Str::slug($name) === $slug;
            });

        abort_unless($matchingArtist, 404);

        $artist = $this->publishedArtists()
            ->where('artist.id', $matchingArtist->id)
            ->firstOrFail();

        $artistName = trim((string) $artist->stage_name);

        /*
        * Albums by this artiste.
        */
        $albums = DB::table('albums')
            ->select(
                'id',
                'title',
                'cover_url',
                'released_year',
                'released_date'
            )
            ->where('artist_id', $artist->id)
            ->where('is_published', 1)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        /*
        * Published audio tracks belonging to the displayed albums.
        */
        $albumTracks = $albums->isEmpty()
            ? collect()
            : DB::table('listings as song')
                ->select(
                    'song.id',
                    'song.album_id',
                    'song.track_title',
                    'song.featuring',
                    'song.track_number'
                )
                ->selectRaw('? AS artist_name', [$artistName])
                ->whereIn(
                    'song.album_id',
                    $albums->pluck('id')->all()
                )
                ->where('song.artist_id', $artist->id)
                ->where('song.listing_type', 1)
                ->where('song.is_published', 1)
                ->orderBy('song.album_id')
                ->orderBy('song.track_number')
                ->orderBy('song.id')
                ->get()
                ->groupBy('album_id');

        /*
        * Singles without an assigned album.
        */
        $singles = DB::table('listings as song')
            ->select(
                'song.id',
                'song.slug',
                'song.track_title',
                'song.featuring',
                'song.cover_url',
                'song.created_at'
            )
            ->selectRaw('? AS artist_name', [$artistName])
            ->where('song.artist_id', $artist->id)
            ->where('song.listing_type', 1)
            ->where('song.is_published', 1)
            ->where(function (Builder $query) {
                $query
                    ->whereNull('song.album_id')
                    ->orWhere('song.album_id', 0);
            })
            ->orderByDesc('song.id')
            ->limit(18)
            ->get();

        /*
        * Videos by this artiste.
        */
        $videos = DB::table('listings as video')
            ->select(
                'video.id',
                'video.slug',
                'video.track_title',
                'video.featuring',
                'video.cover_url',
                'video.created_at'
            )
            ->selectRaw('? AS artist_name', [$artistName])
            ->where('video.artist_id', $artist->id)
            ->where('video.listing_type', 2)
            ->where('video.is_published', 1)
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

    private function filteredArtists(): Builder
    {
        $query = $this->publishedArtists();

        $search = trim(
            (string) request()->query('search', '')
        );

        $letter = strtoupper(trim(
            (string) request()->query('letter', '')
        ));

        if ($search !== '') {
            $query->where(
                'artist.stage_name',
                'like',
                '%' . $search . '%'
            );
        }

        if (preg_match('/^[A-Z]$/', $letter)) {
            $query->whereRaw(
                'TRIM(artist.stage_name) LIKE ?',
                [$letter . '%']
            );
        }

        return $query;
    }

    private function publishedAlbumQuery(): Builder
    {
        return DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'album.artist_id'
            )
            ->select(
                'album.*',
                'album.album_type as category_id'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(artist.stage_name, ''),
                    'TrendyBeatz'
                ) AS artist_name
            ")
            ->selectRaw("'YES' AS IsPublished")
            ->where('album.is_published', 1);
    }

    private function albumTrackCountQuery(): Builder
    {
        return DB::table('listings as track')
            ->selectRaw('COUNT(*)')
            ->whereColumn('track.album_id', 'album.id')
            ->where('track.listing_type', 1)
            ->where('track.is_published', 1);
    }

    public function albums(): View
    {
        $albums = $this->publishedAlbumQuery()
            ->selectSub(
                $this->albumTrackCountQuery(),
                'track_count'
            )
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
        $album = $this->publishedAlbumQuery()
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'album.posted_by'
            )
            ->addSelect('poster.name as posted_by_name')
            ->where('album.id', $id)
            ->first();

        abort_unless($album, 404);

        /*
        * The direct album query already includes buy_song.
        */
        $digitalStoreUrl = trim(
            (string) ($album->buy_song ?? '')
        );

        $correctSlug = \App\Support\AlbumUrl::slug($album);

        if ($slug !== $correctSlug) {
            return redirect()->route(
                'albums.show',
                [$album->id, $correctSlug],
                301
            );
        }

        /*
        * Preserve the existing tracklist ordering.
        */
        $tracks = DB::table('listings as song')
            ->select(
                'song.id',
                'song.track_title',
                'song.featuring',
                'song.track_number',
                'song.created_at'
            )
            ->selectRaw(
                '? AS artist_name',
                [$album->artist_name]
            )
            ->where('song.album_id', $album->id)
            ->where('song.listing_type', 1)
            ->where('song.is_published', 1)
            ->orderBy('song.track_number')
            ->orderBy('song.id')
            ->get();

        /*
        * Include cover_url for the More Albums and EPs section.
        */
        $otherAlbums = DB::table('albums as other')
            ->select(
                'other.id',
                'other.title',
                'other.cover_url',
                'other.released_year'
            )
            ->where('other.artist_id', $album->artist_id)
            ->where('other.id', '<>', $album->id)
            ->where('other.is_published', 1)
            ->orderByDesc('other.id')
            ->limit(5)
            ->get();

        return view('pages.album-details', [
            'album' => $album,
            'tracks' => $tracks,
            'otherAlbums' => $otherAlbums,
            'correctSlug' => $correctSlug,
            'digitalStoreUrl' => $digitalStoreUrl,
        ]);
    }

    public function albumPostedBy(string $slug): View
    {
        $poster = DB::table('users')
            ->select('id', 'name')
            ->whereIn('id', [4, 5])
            ->get()
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $albums = $this->publishedAlbumQuery()
            ->selectSub(
                $this->albumTrackCountQuery(),
                'track_count'
            )
            ->where('album.posted_by', $poster->id)
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.album-posted-by', [
            'poster' => $poster,
            'albums' => $albums,
        ]);
    }

    public function albumsByYear(string $year): View
    {
        $albums = $this->publishedAlbumQuery()
            ->selectSub(
                $this->albumTrackCountQuery(),
                'track_count'
            )
            ->where('album.released_year', $year)
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.albums-year', compact(
            'albums',
            'year'
        ));
    }

    public function popularAlbums(): View
    {
        $albums = $this->publishedAlbumQuery()
            ->selectSub(
                $this->albumTrackCountQuery(),
                'track_count'
            )
            ->selectSub(function (Builder $query) {
                $query
                    ->from('album_populars as popular')
                    ->selectRaw('MAX(popular.rate_no)')
                    ->whereColumn(
                        'popular.album_id',
                        'album.id'
                    );
            }, 'popularity_rate')
            ->whereExists(function (Builder $query) {
                $query
                    ->selectRaw('1')
                    ->from('album_populars as popular')
                    ->whereColumn(
                        'popular.album_id',
                        'album.id'
                    );
            })
            ->orderByDesc('popularity_rate')
            ->orderByDesc('album.id')
            ->paginate(20);

        return view('pages.popular-albums', compact('albums'));
    }

    public function allMusic(): View
    {
        $songs = $this->publishedAudioQuery()
            ->orderByDesc('song.id')
            ->paginate(24);

        return view('pages.music-all', [
            'songs' => $songs,
        ]);
    }

    private function publishedMixQuery(): Builder
    {
        return DB::table('dj_mixs as mix')
            ->leftJoin(
                'djs as dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.*',
                'mix.title as mix_title',
                'dj.profile_img as dj_photo'
            )
            ->selectRaw("
                COALESCE(
                    NULLIF(dj.name, ''),
                    'TrendyBeatz DJ'
                ) AS dj_name
            ")
            ->selectRaw("'YES' AS IsPublished")
            ->where('mix.is_published', 1);
    }

    private function publishedDjQuery(): Builder
    {
        return DB::table('djs as dj')
            ->select(
                'dj.*',
                'dj.name as dj_name',
                'dj.full_name as fullname',
                'dj.profile_img as photo'
            )
            ->selectRaw("
                CASE dj.country_id
                    WHEN '1' THEN 'naija'
                    WHEN '2' THEN 'ghana'
                    WHEN '3' THEN 'african'
                    ELSE dj.country_id
                END AS country_id
            ")
            ->selectRaw("'YES' AS IsPublished")
            ->where('dj.is_published', 1);
    }

    public function djMixes(): View
    {
        $mixes = $this->publishedMixQuery()
            ->whereNotNull('mix.title')
            ->where('mix.title', '<>', '')
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
        /*
        * mix.* includes copyright_image, back_cover
        * and audiomack_embed_url.
        */
        $mix = $this->publishedMixQuery()
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'mix.posted_by'
            )
            ->addSelect('poster.name as posted_by_name')
            ->where('mix.id', $id)
            ->first();

        abort_unless($mix, 404);

        /*
        * Preserve the raw DJ name used by the existing details page
        * and canonical URL helper.
        */
        $mix->dj_name = DB::table('djs')
            ->where('id', $mix->dj_id)
            ->value('name');

        $canonicalSlug = \App\Support\DjMixUrl::slug($mix);

        if ($slug !== $canonicalSlug) {
            return redirect(
                \App\Support\DjMixUrl::detail($mix),
                301
            );
        }

        $otherMixesByDj = DB::table('dj_mixs as related')
            ->leftJoin(
                'djs as dj',
                'dj.id',
                '=',
                'related.dj_id'
            )
            ->select(
                'related.id',
                'related.slug',
                'related.title as mix_title',
                'related.cover_url',
                'dj.name as dj_name'
            )
            ->where('related.dj_id', $mix->dj_id)
            ->where('related.id', '<>', $mix->id)
            ->where('related.is_published', 1)
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        /*
        * Latest published mix from each other published DJ.
        */
        $otherDjs = DB::table('dj_mixs as related')
            ->join(
                'djs as dj',
                'dj.id',
                '=',
                'related.dj_id'
            )
            ->select(
                'related.id',
                'related.slug',
                'related.title as mix_title',
                'related.cover_url',
                'dj.name as dj_name',
                'dj.profile_img as dj_photo'
            )
            ->where('related.dj_id', '<>', $mix->dj_id)
            ->where('related.is_published', 1)
            ->where('dj.is_published', 1)
            ->whereIn('related.id', function (Builder $query) use ($mix) {
                $query
                    ->from('dj_mixs as latest')
                    ->selectRaw('MAX(latest.id)')
                    ->where('latest.is_published', 1)
                    ->where('latest.dj_id', '<>', $mix->dj_id)
                    ->groupBy('latest.dj_id');
            })
            ->orderByDesc('related.id')
            ->limit(6)
            ->get();

        /*
        * Preserve the CDN audio URL handling.
        */
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

        /*
        * Preserve the five-minute audio availability cache.
        */
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

                        $isAudio = str_starts_with($contentType, 'audio/')
                            || in_array(
                                $contentType,
                                [
                                    'application/octet-stream',
                                    'binary/octet-stream',
                                    'application/mp3',
                                ],
                                true
                            );

                        $contentLength = $response->header('Content-Length');

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
        $djs = $this->publishedDjQuery()
            ->whereNotNull('dj.name')
            ->where('dj.name', '<>', '')
            ->orderBy('dj.name')
            ->orderBy('dj.id')
            ->paginate(20);

        return view('pages.djs', compact('djs'));
    }

    public function djDetails(string $slug): View
    {
        $dj = $this->publishedDjQuery()
            ->get()
            ->first(function ($row) use ($slug) {
                $djSlug = trim((string) ($row->slug ?? ''));

                if ($djSlug === '') {
                    $djSlug = Str::slug((string) $row->name);
                }

                return $djSlug === $slug;
            });

        abort_unless($dj, 404);

        $mixes = DB::table('dj_mixs as mix')
            ->select(
                'mix.id',
                'mix.slug',
                'mix.title as mix_title',
                'mix.cover_url'
            )
            ->where('mix.dj_id', $dj->id)
            ->where('mix.is_published', 1)
            ->whereNotNull('mix.title')
            ->where('mix.title', '<>', '')
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
            ->first(
                fn ($user) => Str::slug($user->name) === $slug
            );

        abort_unless($poster, 404);

        $mixes = $this->publishedMixQuery()
            ->where('mix.posted_by', $poster->id)
            ->whereNotNull('mix.title')
            ->where('mix.title', '<>', '')
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
        $mixes = $this->publishedMixQuery()
            ->where('mix.released_year', $year)
            ->whereNotNull('mix.title')
            ->where('mix.title', '<>', '')
            ->orderByDesc('mix.id')
            ->paginate(24);

        return view('pages.djmix-year', compact(
            'mixes',
            'year'
        ));
    }

    private function publishedBlogQuery(): Builder
    {
        return DB::table('blogs as blog')
            ->leftJoin(
                'blog_types as category',
                'category.id',
                '=',
                'blog.blog_type'
            )
            ->leftJoin(
                'users as poster',
                'poster.id',
                '=',
                'blog.posted_by'
            )
            ->where('blog.Is_published', 1);
    }

    private function blogPublisherSql(): string
    {
        return "
            COALESCE(
                NULLIF(poster.name, ''),
                CASE
                    WHEN blog.posted_by REGEXP '^[0-9]+$'
                        THEN NULL
                    ELSE NULLIF(blog.posted_by, '')
                END
            )
        ";
    }

    private function blogListingQuery(): Builder
    {
        return $this->publishedBlogQuery()
            ->select(
                'blog.id',
                'blog.slug',
                'blog.blog_type as category_id',
                'blog.title',
                'blog.intro',
                'blog.photo',
                'blog.created_at',
                'blog.updated_at',
                'category.name as category_name',
                'category.slug as category_slug'
            )
            ->selectRaw(
                $this->blogPublisherSql() . ' AS posted_by_name'
            );
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
            ->where('blog.blog_type', $selectedCategory->id)
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
        /*
        * blog.* includes the article paragraphs, images,
        * description and both saved embed fields.
        */
        $post = $this->publishedBlogQuery()
            ->select(
                'blog.*',
                'blog.blog_type as category_id',
                'blog.track_url as TrackUrl',
                'blog.script_url as scriptUrl',
                'blog.blog_album_id as album_id',
                'category.name as category_name',
                'category.slug as category_slug'
            )
            ->selectRaw("'YES' AS IsPublished")
            ->selectRaw(
                $this->blogPublisherSql() . ' AS posted_by_name'
            )
            ->where('blog.slug', $slug)
            ->first();

        abort_unless($post, 404);

        $categories = DB::table('blog_types')
            ->select('id', 'name', 'slug')
            ->whereNotNull('slug')
            ->orderBy('name')
            ->get();

        $relatedPosts = $this->blogListingQuery()
            ->where('blog.id', '<>', $post->id)
            ->where('blog.blog_type', $post->category_id)
            ->orderByDesc('blog.id')
            ->limit(5)
            ->get();

        $latestPosts = $this->blogListingQuery()
            ->where('blog.id', '<>', $post->id)
            ->orderByDesc('blog.id')
            ->limit(5)
            ->get();

        $commentCount = DB::table('comments')
            ->where('post_type', 'blog')
            ->where('post_id', $post->id)
            ->where('is_allowed', 1)
            ->count();

        return view('pages.blog-details', [
            'post' => $post,
            'categories' => $categories,
            'relatedPosts' => $relatedPosts,
            'latestPosts' => $latestPosts,
            'commentCount' => $commentCount,
        ]);
    }

    public function blogsPublishedBy(string $slug): View
    {
        /*
        * Group by the displayed publisher name, preserving
        * the existing publisher URL behavior.
        */
        $publishers = $this->publishedBlogQuery()
            ->selectRaw(
                $this->blogPublisherSql() . ' AS posted_by_name'
            )
            ->distinct()
            ->get()
            ->filter(
                fn ($publisher) =>
                    trim((string) $publisher->posted_by_name) !== ''
            );

        $matches = $publishers->filter(
            fn ($publisher) =>
                Str::slug($publisher->posted_by_name) === $slug
        );

        abort_unless($matches->count() === 1, 404);

        $publisherName = $matches->first()->posted_by_name;

        $posts = $this->blogListingQuery()
            ->whereRaw(
                '(' . $this->blogPublisherSql() . ') = ?',
                [$publisherName]
            )
            ->orderByDesc('blog.id')
            ->paginate(12);

        return view('pages.blogs-published-by', compact(
            'posts',
            'publisherName',
            'slug'
        ));
    }

    public function blogDescription(int $id): \Illuminate\Http\Response
    {
        $post = DB::table('blogs')
            ->select('description')
            ->where('id', $id)
            ->where('Is_published', 1)
            ->where('hide_from_source', 1)
            ->first();

        abort_unless($post, 404);

        return response(
            (string) $post->description,
            200,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Robots-Tag' => 'noindex',
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
    
    
}