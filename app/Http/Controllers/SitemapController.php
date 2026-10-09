<?php

namespace App\Http\Controllers;

use App\Support\AlbumUrl;
use App\Support\DjMixUrl;
use App\Support\MusicUrl;
use App\Support\VideoUrl;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    private const PER_PAGE = 5000;

    private const TYPES = [
        'music',
        'video',
        'naija-music',
        'ghana-music',
        'african-music',
        'album',
        'blog',
        'dj-mix',
        'artists',
        'dj',
        'song-of-the-day',
        'song-of-the-week',
    ];

    public function index(): Response
    {
        $date = now('Africa/Lagos')->format('Y-m-d\TH:i:sP');

        $xml = $this->xmlHeader();
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach (self::TYPES as $type) {
            $total = $this->query($type)->count();

            $pages = max(
                1,
                (int) ceil($total / self::PER_PAGE)
            );

            for ($page = 1; $page <= $pages; $page++) {
                $location = $this->pageUrl($type, $page);

                $xml .= '<sitemap>';
                $xml .= '<loc>' . $this->escape($location) . '</loc>';
                $xml .= '<lastmod>' . $date . '</lastmod>';
                $xml .= '</sitemap>';
            }
        }

        $xml .= '</sitemapindex>';

        return $this->xmlResponse($xml);
    }

    public function show(
        string $type,
        string $page = '1'
    ): Response {
        abort_unless(in_array($type, self::TYPES, true), 404);

        $pageNumber = filter_var(
            $page,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        abort_if($pageNumber === false, 404);

        $query = $this->query($type);

        $total = (clone $query)->count();

        $pages = max(
            1,
            (int) ceil($total / self::PER_PAGE)
        );

        abort_if($pageNumber > $pages, 404);

        $items = $query
            ->orderByDesc('p.created_at')
            ->orderByDesc('p.id')
            ->offset(($pageNumber - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE)
            ->get();

        $date = now('Africa/Lagos')->format('Y-m-d\TH:i:sP');

        $xml = $this->xmlHeader();
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $seen = [];

        $indexUrl = $this->categoryIndexUrl($type);
        
        $seen[$indexUrl] = true;
        
        $xml .= '<url>';
        $xml .= '<loc>' . $this->escape($indexUrl) . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '<lastmod>' . $date . '</lastmod>';
        $xml .= '</url>';

        foreach ($items as $item) {
            $location = $this->itemUrl($type, $item);

            if ($location === null || isset($seen[$location])) {
                continue;
            }

            $seen[$location] = true;

            $xml .= '<url>';
            $xml .= '<loc>' . $this->escape($location) . '</loc>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>1.0</priority>';
            $xml .= '<lastmod>' . $date . '</lastmod>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    private function query(string $type): Builder
    {
        switch ($type) {
            case 'music':
            case 'video':
            case 'naija-music':
            case 'ghana-music':
            case 'african-music':
            case 'song-of-the-day':
            case 'song-of-the-week':
                $query = $this->listingQuery(
                    $type === 'video' ? 2 : 1
                );

                $countryId = match ($type) {
                    'naija-music' => 1,
                    'ghana-music' => 2,
                    'african-music' => 3,
                    default => null,
                };

                if ($countryId !== null) {
                    $query->where('p.country_id', $countryId);
                }

                if (in_array(
                    $type,
                    ['song-of-the-day', 'song-of-the-week'],
                    true
                )) {
                    $featureType = $type === 'song-of-the-day'
                        ? 1
                        : 2;

                    // EXISTS prevents repeated featured rows
                    // from creating duplicate song URLs.
                    $query->whereExists(function (Builder $featured) use (
                        $featureType
                    ) {
                        $featured
                            ->selectRaw('1')
                            ->from('listing_features as f')
                            ->whereColumn('f.listing_id', 'p.id')
                            ->where(
                                'f.listing_feature_type',
                                $featureType
                            );
                    });
                }

                return $query;

            case 'album':
                return DB::table('albums as p')
                    ->leftJoin('artists as a', 'a.id', '=', 'p.artist_id')
                    ->select(
                        'p.id',
                        'p.title',
                        'p.created_at'
                    )
                    ->selectRaw(
                        "COALESCE(NULLIF(a.stage_name, ''), 'TrendyBeatz') AS artist_name"
                    )
                    ->where('p.is_published', 1);

            case 'blog':
                return DB::table('blogs as p')
                    ->select(
                        'p.id',
                        'p.slug',
                        'p.created_at'
                    )
                    ->where('p.Is_published', 1)
                    ->whereNotNull('p.slug')
                    ->whereRaw("TRIM(p.slug) <> ''");

            case 'dj-mix':
                return DB::table('dj_mixs as p')
                    ->leftJoin('djs as d', 'd.id', '=', 'p.dj_id')
                    ->select(
                        'p.id',
                        'p.title as mix_title',
                        'p.created_at',
                        'd.name as dj_name'
                    )
                    ->where('p.is_published', 1);

            case 'artists':
                return DB::table('artists as p')
                    ->select(
                        'p.id',
                        'p.stage_name',
                        'p.created_at'
                    )
                    ->where('p.is_published', 1)
                    ->whereNotNull('p.stage_name')
                    ->whereRaw("TRIM(p.stage_name) <> ''");

            case 'dj':
                return DB::table('djs as p')
                    ->select(
                        'p.id',
                        'p.slug',
                        'p.name',
                        'p.created_at'
                    )
                    ->where('p.is_published', 1)
                    ->where(function (Builder $query) {
                        $query
                            ->whereRaw("TRIM(COALESCE(p.slug, '')) <> ''")
                            ->orWhereRaw("TRIM(COALESCE(p.name, '')) <> ''");
                    });

            default:
                abort(404);
        }
    }

    private function listingQuery(int $listingType): Builder
    {
        return DB::table('listings as p')
            ->leftJoin('artists as a', 'a.id', '=', 'p.artist_id')
            ->select(
                'p.id',
                'p.track_title',
                'p.featuring',
                'p.created_at'
            )
            ->selectRaw(
                "COALESCE(NULLIF(a.stage_name, ''), 'TrendyBeatz') AS artist_name"
            )
            ->where('p.is_published', 1)
            ->where('p.listing_type', $listingType);
    }

    private function itemUrl(string $type, object $item): ?string
    {
        switch ($type) {
            case 'music':
            case 'naija-music':
            case 'ghana-music':
            case 'african-music':
            case 'song-of-the-day':
            case 'song-of-the-week':
                return MusicUrl::detail($item);

            case 'video':
                return VideoUrl::detail($item);

            case 'album':
                return AlbumUrl::detail($item);

            case 'dj-mix':
                return DjMixUrl::detail($item);

            case 'blog':
                // Blog details use the stored database slug.
                return route('blogs.show', [
                    'slug' => $item->slug,
                ]);

            case 'artists':
                // Matches PageController::artistDetails().
                $slug = Str::slug(
                    trim((string) $item->stage_name)
                );

                return $slug !== ''
                    ? route('artists.show', ['slug' => $slug])
                    : null;

            case 'dj':
                // Matches PageController::djDetails().
                $slug = trim((string) ($item->slug ?? ''));

                if ($slug === '') {
                    $slug = Str::slug(
                        trim((string) $item->name)
                    );
                }

                return $slug !== ''
                    ? route('djs.show', ['slug' => $slug])
                    : null;

            default:
                abort(404);
        }
    }
    
    private function categoryIndexUrl(string $type): string
    {
        return match ($type) {
            'music' => url('/musics'),
            'naija-music' => url('/musics/naija'),
            'ghana-music' => url('/musics/ghana'),
            'african-music' => url('/musics/african'),
    
            'video' => route('videos.index'),
            'album' => route('albums.index'),
            'blog' => route('blogs.index'),
            'dj-mix' => route('mixes.index'),
            'artists' => route('artists.index'),
    
            // Uses the DJ mixes listing as the DJ directory entry.
            'dj' => route('mixes.index'),
    
            'song-of-the-day' => route('songs.day'),
            'song-of-the-week' => route('songs.week'),
    
            default => abort(404),
        };
    }

    private function pageUrl(string $type, int $page): string
    {
        if ($page === 1) {
            return route('sitemaps.first', [
                'type' => $type,
            ]);
        }

        return route('sitemaps.page', [
            'type' => $type,
            'page' => $page,
        ]);
    }

    private function xmlHeader(): string
    {
        // Relative URL keeps the stylesheet on the same host
        // as the sitemap being opened.
        $stylesheet = $this->escape(
            route('sitemaps.style', [], false)
        );
    
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n"
            . '<?xml-stylesheet type="text/xsl" href="'
            . $stylesheet
            . '"?>'
            . "\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }
    
    public function style(): Response
    {
        $stylesheet = view('sitemaps.style')->render();
    
        // Remove any leading whitespace or UTF-8 BOM.
        $stylesheet = preg_replace(
            '/\A(?:\xEF\xBB\xBF|\s)*/',
            '',
            $stylesheet
        ) ?? $stylesheet;
    
        return response(
            '<?xml version="1.0" encoding="UTF-8"?>'
                . "\n"
                . $stylesheet,
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'no-store',
            ]
        );
    }
}