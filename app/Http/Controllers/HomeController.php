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
            ->leftJoin('artists as artist', 'artist.id', '=', 'song.artist_id')
            ->select('song.*', 'artist.stage_name as artist_name')
            ->where('song.is_published', 1);
    }

    private function publishedAudio(): Builder
    {
        return $this->publishedTracks()
            ->where('song.listing_type_id', 1);
    }



    private function songsOfTheDay()
    {
        return DB::table('featured_rated as featured')
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
            ->limit(10)
            ->get();
    }

    private function latestNaijaSongs()
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
            ->where('song.country_id', 'naija')
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(25)
            ->get();
    }

    private function songsOfTheWeek()
    {
        return DB::table('song_of_the_week as featured')
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
            ->limit(10)
            ->get();
    }

    private function latestGhanaSongs()
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
            ->where('song.country_id', 'ghana')
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(25)
            ->get();
    }

    private function latestNews()
    {
        return DB::table('blogs as blog')
            ->select(
                'blog.id',
                'blog.category_id',
                'blog.title',
                'blog.intro',
                'blog.photo',
                'blog.created_at'
            )
            ->where('blog.IsPublished', 'YES')
            ->where(function (Builder $query) {
                $query->where('blog.category_id', '<>', 8)
                    ->orWhereNull('blog.category_id');
            })
            ->orderByDesc('blog.id')
            ->limit(9)
            ->get();
    }


    private function latestAfricanSongs()
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
            ->where('song.country_id', 'african')
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(25)
            ->get();
    }

    private function latestAlbums()
    {
        return DB::table('albums as album')
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
                'album.created_at',
                DB::raw("
                    COALESCE(
                        NULLIF(artist.Stage_Name, ''),
                        NULLIF(artist.ArtistsName, '')
                    ) as artist_name
                ")
            )
            ->selectSub(
                DB::table('listing as song')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('song.album_id', 'album.id')
                    ->where('song.IsPublished', 'YES')
                    ->where('song.ListingType', 'Audio'),
                'track_count'
            )
            ->where('album.IsPublished', 'YES')
            ->orderByDesc('album.id')
            ->limit(6)
            ->get();
    }


    private function latestGospelSongs()
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
            ->where('song.isgospel', 1)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(8)
            ->get();
    }

    private function latestHighlifeSongs()
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
            ->where('song.ishighlife', 1)
            ->where('song.ListingType', 'Audio')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(8)
            ->get();
    }

    private function latestDjMixes()
    {
        return DB::table('dj_mixs as mix')
            ->leftJoin(
                'dj as dj',
                'dj.id',
                '=',
                'mix.dj_id'
            )
            ->select(
                'mix.id',
                'mix.mix_title',
                'mix.cover_url',
                'mix.track_url',
                'mix.slug',
                'mix.created_at',
                DB::raw("COALESCE(NULLIF(dj.dj_name, ''), 'TrendyBeatz DJ') as dj_name")
            )
            ->where('mix.IsPublished', 'YES')
            ->whereNotNull('mix.mix_title')
            ->where('mix.mix_title', '<>', '')
            ->orderByDesc('mix.id')
            ->limit(3)
            ->get();
    }

    private function latestVideos()
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
            ->where('song.ListingType', 'video')
            ->where('song.IsPublished', 'YES')
            ->whereNotNull('song.slug')
            ->where('song.slug', '<>', '')
            ->orderByDesc('song.id')
            ->limit(4)
            ->get();
    }


    private function latestReviews()
    {
        return DB::table('blogs as blog')
            ->select(
                'blog.id',
                'blog.category_id',
                'blog.title',
                'blog.intro',
                'blog.photo',
                'blog.created_at'
            )
            ->where('blog.IsPublished', 'YES')
            ->where('blog.category_id', 8)
            ->orderByDesc('blog.id')
            ->limit(6)
            ->get();
    }

    public function index(): View
    {
        return view('home', [
            'day' => $this->songsOfTheDay(),
            'naija' => $this->latestNaijaSongs(),
            'news' => $this->latestNews(),
            'week' => $this->songsOfTheWeek(),
            'ghana' => $this->latestGhanaSongs(),
            'african' => $this->latestAfricanSongs(),
            'albums' => $this->latestAlbums(),
            'gospel' => $this->latestGospelSongs(),
            'highlife' => $this->latestHighlifeSongs(),
            'mixes' => $this->latestDjMixes(),
            'videos' => $this->latestVideos(),
            'reviews' => $this->latestReviews(),
        ]);
    }
}