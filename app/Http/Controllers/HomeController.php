<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private function publishedTracks(): Builder
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
                DB::raw("
                    COALESCE(
                        NULLIF(artist.stage_name, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->where('song.is_published', 1)
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '');
    }

    private function publishedAudio(): Builder
    {
        return $this->publishedTracks()
            ->where('song.listing_type', 1);
    }

    private function featuredSongs(int $featureType)
    {
        $features = DB::table('listing_features')
            ->select('listing_id')
            ->selectRaw('MAX(id) as latest_feature_id')
            ->where('listing_feature_type', $featureType)
            ->groupBy('listing_id');

        return $this->publishedAudio()
            ->joinSub(
                $features,
                'featured',
                'featured.listing_id',
                '=',
                'song.id'
            )
            ->orderByDesc('featured.latest_feature_id')
            ->orderByDesc('song.id')
            ->limit(5)
            ->get();
    }

    private function countrySongs(int $countryId)
    {
        return $this->publishedAudio()
            ->where('song.country_id', $countryId)
            ->orderByDesc('song.id')
            ->limit(5)
            ->get();
    }

    private function latestGospelSongs()
    {
        return $this->publishedAudio()
            ->where('song.is_gospel', 1)
            ->orderByDesc('song.id')
            ->limit(5)
            ->get();
    }

    private function latestHighlifeSongs()
    {
        return $this->publishedAudio()
            ->where('song.is_high_life', 1)
            ->orderByDesc('song.id')
            ->limit(5)
            ->get();
    }

    private function latestVideos()
    {
        return $this->publishedTracks()
            ->where('song.listing_type', 2)
            ->orderByDesc('song.id')
            ->limit(4)
            ->get();
    }

    private function latestAlbums()
    {
        return DB::table('albums as album')
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
                'album.released_year',
                'album.released_date',
                'album.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.stage_name, ''),
                        'TrendyBeatz'
                    ) as artist_name
                ")
            )
            ->selectSub(
                DB::table('listings as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->where('song.is_published', 1)
                    ->where('song.listing_type', 1),
                'track_count'
            )
            ->where('album.is_published', 1)
            ->orderByDesc('album.id')
            ->limit(5)
            ->get();
    }

    private function latestDjMixes()
    {
        return DB::table('dj_mixs as mix')
            ->leftJoin('djs as dj', 'dj.id', '=', 'mix.dj_id')
            ->select(
                'mix.id',
                'mix.title as mix_title',
                'mix.cover_url',
                'mix.track_url',
                'mix.slug',
                'mix.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(dj.name, ''),
                        'TrendyBeatz DJ'
                    ) as dj_name
                ")
            )
            ->where('mix.is_published', 1)
            ->whereNotNull('mix.title')
            ->where('mix.title', '<>', '')
            ->orderByDesc('mix.id')
            ->limit(3)
            ->get();
    }

    private function publishedBlogs(): Builder
    {
        return DB::table('blogs as blog')
            ->select(
                'blog.id',
                'blog.slug',
                'blog.blog_type as category_id',
                'blog.title',
                'blog.intro',
                'blog.photo',
                'blog.created_at'
            )
            ->where('blog.Is_published', 1)
            ->whereNotNull('blog.slug')
            ->whereRaw("TRIM(blog.slug) <> ''");
    }

    private function latestNews()
    {
        return $this->publishedBlogs()
            ->where(function (Builder $query) {
                $query->where('blog.blog_type', '<>', 5)
                    ->orWhereNull('blog.blog_type');
            })
            ->orderByDesc('blog.id')
            ->limit(6)
            ->get();
    }

    private function latestReviews()
    {
        return $this->publishedBlogs()
            ->where('blog.blog_type', 5)
            ->orderByDesc('blog.id')
            ->limit(6)
            ->get();
    }

    public function index(): View
    {
        return view('home', [
            'day' => $this->featuredSongs(1),
            'naija' => $this->countrySongs(1),
            'news' => $this->latestNews(),
            'week' => $this->featuredSongs(2),
            'ghana' => $this->countrySongs(2),
            'african' => $this->countrySongs(3),
            'albums' => $this->latestAlbums(),
            'gospel' => $this->latestGospelSongs(),
            'highlife' => $this->latestHighlifeSongs(),
            'mixes' => $this->latestDjMixes(),
            'videos' => $this->latestVideos(),
            'reviews' => $this->latestReviews(),
        ]);
    }
}