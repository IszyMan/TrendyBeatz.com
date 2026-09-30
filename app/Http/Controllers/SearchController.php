<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'type' => [
                'nullable',
                'in:all,music,video,mix,album,blog,artist,dj',
            ],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $type = $validated['type'] ?? 'all';

        if ($type === '') {
            $type = 'all';
        }

        $normalizedQuery = preg_replace(
            '/[^\p{L}\p{N}\s]+/u',
            ' ',
            mb_strtolower($query)
        );

        $normalizedQuery = trim(
            preg_replace('/\s+/u', ' ', $normalizedQuery)
        );

        $terms = array_values(array_unique(
            preg_split(
                '/\s+/u',
                $normalizedQuery,
                -1,
                PREG_SPLIT_NO_EMPTY
            )
        ));

        $results = null;

        if ($terms !== []) {
            $search = DB::query()
                ->fromSub($this->searchSources(), 'results')
                ->select('results.*');

            if ($type !== 'all') {
                $search->where('results.type', $type);
            }

            foreach ($terms as $term) {
                $search->whereRaw(
                    "
                    CONCAT_WS(
                        _utf8mb4' ' COLLATE utf8mb4_unicode_ci,

                        CONVERT(results.artist_name USING utf8mb4)
                            COLLATE utf8mb4_unicode_ci,

                        CONVERT(results.title USING utf8mb4)
                            COLLATE utf8mb4_unicode_ci,

                        CONVERT(results.featuring USING utf8mb4)
                            COLLATE utf8mb4_unicode_ci,

                        CONVERT(results.search_extra USING utf8mb4)
                            COLLATE utf8mb4_unicode_ci
                    )
                    LIKE (
                        CONVERT(? USING utf8mb4)
                            COLLATE utf8mb4_unicode_ci
                    )
                    ",
                    ['%' . $term . '%']
                );
            }

            $results = $search
                ->orderByRaw('results.created_at IS NULL ASC')
                ->orderByDesc('results.created_at')
                ->orderBy('results.type')
                ->orderByDesc('results.id')
                ->paginate(24)
                ->appends([
                    'q' => $query,
                    'type' => $type,
                ]);

            $results->getCollection()->transform(
                function ($result) {
                    $result->url = $this->resultUrl($result);
                    $result->display_title = $this->displayTitle($result);
                    $result->image_url = $this->imageUrl($result->cover_url);

                    return $result;
                }
            );
        }

        return view('pages.search', compact(
            'query',
            'type',
            'results',
            'normalizedQuery'
        ));
    }
    private function searchSources(): Builder
    {
        /*
         * These optional columns were not shown in your controller.
         * Inspect them so missing featuring/timestamp fields do not
         * cause SQL errors.
         */
        $columns = [];

        foreach (['listing', 'dj_mixs', 'albums', 'blogs', 'artists', 'dj'] as $table) {
            $columns[$table] = Schema::getColumnListing($table);
        }

        $artistName = "
            COALESCE(
                NULLIF(artist.Stage_Name, ''),
                NULLIF(artist.ArtistsName, ''),
                'TrendyBeatz'
            )
        ";

        $listings = DB::table('listing as listing')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'listing.Artists_Id'
            )
            ->selectRaw("
                listing.id as id,
                CASE
                    WHEN LOWER(listing.ListingType) = 'audio'
                    THEN 'music'
                    ELSE 'video'
                END as type,
                listing.TrackTitle as title,
                {$artistName} as artist_name,
                COALESCE(listing.Featuring, '') as featuring,
                listing.CoverUrl as cover_url,
                COALESCE(listing.slug, '') as slug,
                COALESCE(artist.ArtistsName, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('listing', 'listing', $columns)
                . ' as created_at'
            )
            ->where('listing.IsPublished', 'YES')
            ->whereIn(
                DB::raw('LOWER(listing.ListingType)'),
                ['audio', 'video']
            );

        $mixFeaturing = $this->optionalExpression(
            'dj_mixs',
            'mix',
            ['featuring', 'Featuring'],
            $columns
        );

        $mixes = DB::table('dj_mixs as mix')
            ->leftJoin('dj', 'dj.id', '=', 'mix.dj_id')
            ->selectRaw("
                mix.id as id,
                'mix' as type,
                mix.mix_title as title,
                COALESCE(NULLIF(dj.dj_name, ''), 'TrendyBeatz DJ')
                    as artist_name,
                {$mixFeaturing} as featuring,
                mix.cover_url as cover_url,
                COALESCE(mix.slug, '') as slug,
                COALESCE(dj.fullname, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('dj_mixs', 'mix', $columns)
                . ' as created_at'
            )
            ->where('mix.IsPublished', 'YES');

        $albumFeaturing = $this->optionalExpression(
            'albums',
            'album',
            ['featuring', 'Featuring'],
            $columns
        );

        $albums = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.Artists_Id',
                '=',
                'album.artist_id'
            )
            ->selectRaw("
                album.id as id,
                'album' as type,
                album.title as title,
                {$artistName} as artist_name,
                {$albumFeaturing} as featuring,
                album.cover_url as cover_url,
                '' as slug,
                COALESCE(artist.ArtistsName, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('albums', 'album', $columns)
                . ' as created_at'
            )
            ->where('album.IsPublished', 'YES');

        $blogs = DB::table('blogs as blog')
            ->selectRaw("
                blog.id as id,
                'blog' as type,
                blog.title as title,
                '' as artist_name,
                '' as featuring,
                blog.photo as cover_url,
                blog.slug as slug,
                COALESCE(blog.intro, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('blogs', 'blog', $columns)
                . ' as created_at'
            )
            ->where('blog.IsPublished', 'YES')
            ->whereNotNull('blog.slug')
            ->where('blog.slug', '<>', '');

        $artists = DB::table('artists as artist')
            ->selectRaw("
                artist.id as id,
                'artist' as type,
                {$artistName} as title,
                '' as artist_name,
                '' as featuring,
                artist.ProfilePic as cover_url,
                '' as slug,
                COALESCE(artist.ArtistsName, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('artists', 'artist', $columns)
                . ' as created_at'
            )
            ->where('artist.IsPublished', 'YES')
            ->where(function (Builder $builder) {
                $builder
                    ->whereRaw("TRIM(COALESCE(artist.Stage_Name, '')) <> ''")
                    ->orWhereRaw("TRIM(COALESCE(artist.ArtistsName, '')) <> ''");
            });

        $djSlug = $this->optionalExpression(
            'dj',
            'dj',
            ['slug'],
            $columns
        );

        $djs = DB::table('dj')
            ->selectRaw("
                dj.id as id,
                'dj' as type,
                dj.dj_name as title,
                '' as artist_name,
                '' as featuring,
                dj.photo as cover_url,
                {$djSlug} as slug,
                COALESCE(dj.fullname, '') as search_extra
            ")
            ->selectRaw(
                $this->dateExpression('dj', 'dj', $columns)
                . ' as created_at'
            )
            ->where('dj.IsPublished', 'YES')
            ->whereNotNull('dj.dj_name')
            ->where('dj.dj_name', '<>', '');

        return $listings
            ->unionAll($mixes)
            ->unionAll($albums)
            ->unionAll($blogs)
            ->unionAll($artists)
            ->unionAll($djs);
    }

    private function dateExpression(
        string $table,
        string $alias,
        array $columns
    ): string {
        foreach ($columns[$table] as $column) {
            if (strtolower($column) === 'created_at') {
                return "{$alias}.`{$column}`";
            }
        }

        // Undated legacy entries appear after dated entries.
        return 'NULL';
    }

    private function optionalExpression(
        string $table,
        string $alias,
        array $candidates,
        array $columns
    ): string {
        foreach ($candidates as $candidate) {
            foreach ($columns[$table] as $column) {
                if (strtolower($column) === strtolower($candidate)) {
                    return "COALESCE({$alias}.`{$column}`, '')";
                }
            }
        }

        return "''";
    }

    private function resultUrl(object $result): string
    {
        /*
         * Supply both legacy and normalized field names to your
         * existing music/video URL helpers.
         */
        $listing = (object) [
            'id' => $result->id,
            'slug' => $result->slug,
            'artist_name' => $result->artist_name,
            'TrackTitle' => $result->title,
            'track_title' => $result->title,
            'Featuring' => $result->featuring,
            'featuring' => $result->featuring,
        ];

        return match ($result->type) {
            'music' => route('music_details', [
                $result->id,
                \App\Support\MusicUrl::slug($listing),
            ]),

            'video' => \App\Support\VideoUrl::detail($listing),

            'mix' => route('mixes.show', [
                $result->id,
                trim((string) $result->slug, " \t\n\r\0\x0B/")
                    ?: Str::slug(
                        $result->artist_name . ' ' . $result->title
                    ),
            ]),

            'album' => route('albums.show', [
                $result->id,
                Str::slug(
                    $result->artist_name . ' ' . $result->title
                ),
            ]),

            'blog' => route('blogs.show', $result->slug),

            'artist' => route(
                'artists.show',
                Str::slug($result->title)
            ),

            'dj' => route(
                'djs.show',
                trim((string) $result->slug)
                    ?: Str::slug($result->title)
            ),
        };
    }

    private function displayTitle(object $result): string
    {
        $title = trim((string) $result->title);

        if (in_array(
            $result->type,
            ['music', 'video', 'mix', 'album'],
            true
        )) {
            $featuring = trim((string) $result->featuring);

            if (
                $featuring !== ''
                && !preg_match(
                    '/\b(?:ft|feat|featuring)\.?\s/i',
                    $title
                )
            ) {
                $title .= ' feat. ' . $featuring;
            }

            $title = $result->artist_name . ' - ' . $title;
        }

        return $title;
    }

    private function imageUrl(?string $value): ?string
    {
        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        return asset(
            str_starts_with($path, 'images/')
                ? $path
                : 'images/' . $path
        );
    }
}