<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

    public function allMusic(): View
    {
        return view('index', [
            'title' => 'All Music',
            'items' => $this->audio()
                ->orderByDesc('l.id')
                ->limit(60)
                ->get(),
            'type' => 'song',
        ]);
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

    public function naija(): View
    {
        return $this->music('naija');
    }

    public function ghana(): View
    {
        return $this->music('ghana');
    }

    public function african(): View
    {
        return $this->music('african');
    }

    public function gospel(): View
    {
        return $this->music('gospel');
    }

    public function highlife(): View
    {
        return $this->music('highlife');
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