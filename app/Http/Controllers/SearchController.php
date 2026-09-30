<?php

namespace App\Http\Controllers;

use App\Support\MusicUrl;
use App\Support\VideoUrl;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $type = ($validated['type'] ?? '') ?: 'all';

        $normalizedQuery = preg_replace(
            '/[^\p{L}\p{N}\s]+/u',
            ' ',
            mb_strtolower($query, 'UTF-8')
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
                        results.artist_name,
                        results.title,
                        results.featuring,
                        results.search_extra
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
        $artistName = "
            COALESCE(
                NULLIF(TRIM(artist.stage_name), ''),
                'TrendyBeatz'
            )
        ";

        // Audio = 1. Video = 2.
        $listings = DB::table('listings as listing')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'listing.artist_id'
            )
            ->where('listing.is_published', 1)
            ->whereIn('listing.listing_type', [1, 2]);

        $this->selectResultColumns(
            $listings,
            'listing.id',
            [
                'type' => "
                    CASE
                        WHEN listing.listing_type = 1 THEN 'music'
                        ELSE 'video'
                    END
                ",
                'title' => 'listing.track_title',
                'artist_name' => $artistName,
                'featuring' => 'listing.featuring',
                'cover_url' => 'listing.cover_url',
                'slug' => 'listing.slug',
                'search_extra' => 'artist.full_name',
            ],
            'listing.created_at'
        );

        // The recovered DJ mixes table has no featuring column.
        $mixes = DB::table('dj_mixs as mix')
            ->leftJoin('djs as dj', 'dj.id', '=', 'mix.dj_id')
            ->where('mix.is_published', 1);

        $this->selectResultColumns(
            $mixes,
            'mix.id',
            [
                'type' => "'mix'",
                'title' => 'mix.title',
                'artist_name' => "
                    COALESCE(
                        NULLIF(TRIM(dj.name), ''),
                        'TrendyBeatz DJ'
                    )
                ",
                'featuring' => "''",
                'cover_url' => 'mix.cover_url',
                'slug' => 'mix.slug',
                'search_extra' => 'dj.full_name',
            ],
            'mix.created_at'
        );

        $albums = DB::table('albums as album')
            ->leftJoin(
                'artists as artist',
                'artist.id',
                '=',
                'album.artist_id'
            )
            ->where('album.is_published', 1);

        $this->selectResultColumns(
            $albums,
            'album.id',
            [
                'type' => "'album'",
                'title' => 'album.title',
                'artist_name' => $artistName,
                'featuring' => 'album.featuring',
                'cover_url' => 'album.cover_url',
                'slug' => 'album.slug',
                'search_extra' => 'artist.full_name',
            ],
            'album.created_at'
        );

        $blogs = DB::table('blogs as blog')
            ->where('blog.Is_published', 1)
            ->whereNotNull('blog.slug')
            ->whereRaw("TRIM(blog.slug) <> ''");

        $this->selectResultColumns(
            $blogs,
            'blog.id',
            [
                'type' => "'blog'",
                'title' => 'blog.title',
                'artist_name' => "''",
                'featuring' => "''",
                'cover_url' => 'blog.photo',
                'slug' => 'TRIM(blog.slug)',
                'search_extra' => 'blog.intro',
            ],
            'blog.created_at'
        );

        $artists = DB::table('artists as artist')
            ->where('artist.is_published', 1)
            ->whereRaw(
                "TRIM(COALESCE(artist.stage_name, '')) <> ''"
            );

        $this->selectResultColumns(
            $artists,
            'artist.id',
            [
                'type' => "'artist'",
                'title' => 'TRIM(artist.stage_name)',
                'artist_name' => "''",
                'featuring' => "''",
                'cover_url' => 'artist.img',
                'slug' => 'artist.slug',
                'search_extra' => 'artist.full_name',
            ],
            'artist.created_at'
        );

        $djs = DB::table('djs as dj')
            ->where('dj.is_published', 1)
            ->whereRaw("TRIM(COALESCE(dj.name, '')) <> ''");

        $this->selectResultColumns(
            $djs,
            'dj.id',
            [
                'type' => "'dj'",
                'title' => 'TRIM(dj.name)',
                'artist_name' => "''",
                'featuring' => "''",
                'cover_url' => 'dj.profile_img',
                'slug' => 'dj.slug',
                'search_extra' => 'dj.full_name',
            ],
            'dj.created_at'
        );

        return $listings
            ->unionAll($mixes)
            ->unionAll($albums)
            ->unionAll($blogs)
            ->unionAll($artists)
            ->unionAll($djs);
    }

    /**
     * Give every search source the same columns and string collation.
     *
     * Expressions here are internal SQL, never request input.
     */
    private function selectResultColumns(
        Builder $builder,
        string $idExpression,
        array $expressions,
        string $dateExpression
    ): void {
        $columns = [$idExpression . ' as id'];

        foreach ([
            'type',
            'title',
            'artist_name',
            'featuring',
            'cover_url',
            'slug',
            'search_extra',
        ] as $name) {
            $expression = $expressions[$name];

            $columns[] = "
                CONVERT(
                    COALESCE({$expression}, '')
                    USING utf8mb4
                ) COLLATE utf8mb4_unicode_ci as {$name}
            ";
        }

        $columns[] = $dateExpression . ' as created_at';

        $builder->selectRaw(implode(",\n", $columns));
    }

    private function resultUrl(object $result): string
    {
        // Keep compatibility with your existing URL helpers.
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
                'id' => $result->id,
                'slug' => MusicUrl::slug($listing),
            ]),

            'video' => VideoUrl::detail($listing),

            'mix' => route('mixes.show', [
                'id' => $result->id,
                'slug' => trim(
                    (string) $result->slug,
                    " \t\n\r\0\x0B/"
                ) ?: Str::slug(
                    $result->artist_name . ' ' . $result->title
                ),
            ]),

            'album' => route('albums.show', [
                'id' => $result->id,
                'slug' => Str::slug(
                    $result->artist_name . ' ' . $result->title
                ),
            ]),

            'blog' => route('blogs.show', [
                'slug' => $result->slug,
            ]),

            'artist' => route('artists.show', [
                'slug' => Str::slug($result->title),
            ]),

            'dj' => route('djs.show', [
                'slug' => trim(
                    (string) $result->slug,
                    " \t\n\r\0\x0B/"
                ) ?: Str::slug($result->title),
            ]),
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