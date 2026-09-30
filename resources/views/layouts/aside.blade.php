@php
    $trendingArtistes = [
        '2Baba', 'Burna Boy', 'Joeboy', 'Davido',
        'Zlatan', 'Falz', 'Fireboy DML', 'Kizz Daniel',
        'Rema', 'Young Jonn', 'Naira Marley', 'Simi',
        'Wizkid', 'Phyno', 'Olamide', 'Omah Lay',
        'Wande Coal', 'Tiwa Savage', 'Patoranking',
        'Mr Eazi', 'Bella Shmurda', 'Zinoleesky',
        'Shallipopi', 'Fola',
    ];

    $topArtistes = [
        'Adekunle Gold', 'Ayo Maff', 'Famous Pluto', 'Jeriq',
        'Ladipoe', 'Asake', 'Asa', 'Mavo',
        'Buju BNXN', 'Odumodublvck', 'Timaya', 'DJ Tunez',
        'DJ Frenzy', 'Qing Madi', 'Peruzzi', 'Yung6ix',
    ];

    
    
    $popularAlbums = \Illuminate\Support\Facades\DB::table('albums as album')
        ->leftJoin(
            'artists as artist',
            'artist.Artists_Id',
            '=',
            'album.artist_id'
        )
        ->select(
            'album.*',
            \Illuminate\Support\Facades\DB::raw("
                COALESCE(
                    NULLIF(artist.Stage_Name, ''),
                    NULLIF(artist.ArtistsName, ''),
                    'TrendyBeatz'
                ) as artist_name
            ")
        )
        ->selectSub(function ($query) {
            $query
                ->from('album_of_the_day as popular')
                ->selectRaw('MAX(popular.rate_no)')
                ->whereColumn('popular.album_id', 'album.id');
        }, 'popularity_rate')
        ->whereExists(function ($query) {
            $query
                ->selectRaw('1')
                ->from('album_of_the_day as popular')
                ->whereColumn('popular.album_id', 'album.id');
        })
        ->where('album.IsPublished', 'YES')
        ->orderByDesc('popularity_rate')
        ->orderByDesc('album.id')
        ->limit(4)
        ->get();

    $featuredSongs = \Illuminate\Support\Facades\DB::table('featured_rated as featured')
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
            \Illuminate\Support\Facades\DB::raw("
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
        ->limit(5)
        ->get();    
    
    $recentBlogs = \Illuminate\Support\Facades\DB::table('blogs as blog')
        ->select(
            'blog.id',
            'blog.slug',
            'blog.title',
            'blog.intro'
        )
        ->where('blog.IsPublished', 'YES')
        ->orderByDesc('blog.id')
        ->limit(6)
        ->get();
   
@endphp

<section class="aside-panel aside-top-pages">
    <h2 class="aside-title">OUR TOP PAGES</h2>

    <a class="aside-link" href="{{ route('music.naija') }}">
        Discover Latest "Naija Music"
    </a>

    <a class="aside-link" href="{{ route('music.ghana') }}">
        Discover Latest "Ghana Music"
    </a>

    <a class="aside-link" href="{{ route('music.african') }}">
        Discover Latest "African Music"
    </a>

    <a class="aside-link" href="{{ route('songs.week') }}">
        Discover "Top 10 Songs Of The Week"
    </a>

    <a class="aside-link" href="{{ route('songs.day') }}">
        Discover "Top 10 Songs Of Today"
    </a>

    <a class="aside-link" href="{{ route('albums.index') }}">
        Discover "Music Albums / EP"
    </a>

    <a class="aside-link" href="{{ route('page.promote') }}">
        Promote Your Music<br >(For Artistes Only)
    </a>

    <a class="aside-link" href="{{ route('page.advertise') }}">
        Advertise On<br>TrendyBeatz
    </a>

    <a class="aside-link" href="{{ route('page.about') }}">
        About Us
    </a>
</section>

<section class="aside-panel aside-artists">
    <h2 class="aside-title">Trending Artistes</h2>

    <div class="artists-list">
        @foreach ($trendingArtistes as $name)
            <a
                class="artist-pill"
                href="{{ route('artists.show', [
                    'slug' => \Illuminate\Support\Str::slug($name)
                ]) }}"
            >
                {{ $name }}
            </a>
        @endforeach
    </div>

    <h2 class="aside-title aside-subtitle">Top Artistes</h2>

    <div class="artists-list">
        @foreach ($topArtistes as $name)
            <a
                class="artist-pill"
                href="{{ route('artists.show', [
                    'slug' => \Illuminate\Support\Str::slug($name)
                ]) }}"
            >
                {{ $name }}
            </a>
        @endforeach
    </div>
</section>

<section class="aside-panel aside-albums">
    <h2 class="aside-title">Popular Albums</h2>

    @forelse ($popularAlbums as $album)
        <a
            class="album-card aside-media-card"
            href="{{ \App\Support\AlbumUrl::detail($album) }}"
        >
            <span class="aside-image-placeholder" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                
                    <strong class="album-name">
                         {{ $album->artist_name }} 
                    </strong>
                    {{ $album->title }}

                @if ($album->released_year)
                    <span class="album-year">
                        Released Year: {{ $album->released_year }}
                    </span>
                @endif
            </span>
        </a>
    @empty
        <p class="aside-empty">No albums available yet.</p>
    @endforelse

    <a class="aside-view-all" href="{{ route('albums.popular') }}">
        View All Popular Albums →
    </a>
</section>

<section class="aside-panel aside-albums">
    <h2 class="aside-title">Song of the Day</h2>

    @forelse ($featuredSongs as $song)
        <a
            class="album-card aside-media-card"
            href="{{ \App\Support\MusicUrl::detail($song) }}"
        >
            <span class="aside-image-placeholder" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                <strong class="album-name">
                    {{ $song->artist_name }}
                </strong>

                {{ $song->track_title }}

                @if (filled($song->featuring))
                    <span class="album-year">
                        Featuring: {{ $song->featuring }}
                    </span>
                @endif
            </span>
        </a>
    @empty
        <p class="aside-empty">No songs available yet.</p>
    @endforelse

    <a class="aside-view-all" href="{{ route('songs.day') }}">
        View All Songs of the Day →
    </a>
</section>

<section class="aside-panel aside-albums">
    <h2 class="aside-title">Latest Blog & News</h2>

    @forelse ($recentBlogs as $blog)
        <a
            class="album-card aside-media-card"
            href="{{ route('blogs.show', [$blog->id, $blog->slug]) }}"
        >
            <span class="aside-image-placeholder" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                <strong class="album-name">
                    {{ $blog->title }}
                </strong>

                @if (filled($blog->intro))
                    <span>
                        {{ \Illuminate\Support\Str::limit(
                            strip_tags($blog->intro),
                            120
                        ) }}
                    </span>
                @endif

                <span class="album-year">
                    Read more →
                </span>
            </span>
        </a>
    @empty
        <p class="aside-empty">No published stories yet.</p>
    @endforelse

    <a class="aside-view-all" href="{{ route('blogs.index') }}">
        View All News →
    </a>
</section>

<section class="aside-panel aside-social">
    <h2 class="aside-title">TrendyBeatz Social Media</h2>

    <a
        class="aside-link"
        href="https://facebook.com/Trendybeatzmedia"
        target="_blank"
        rel="noopener noreferrer nofollow"
    >
        TrendyBeatz Official Facebook Page
    </a>

    <a
        class="aside-link"
        href="https://www.instagram.com/trendybeatzmedia/"
        target="_blank"
        rel="noopener noreferrer nofollow"
    >
        TrendyBeatz Official Instagram Page
    </a>

    <a
        class="aside-link"
        href="https://twitter.com/trendybeatzng"
        target="_blank"
        rel="noopener noreferrer nofollow"
    >
        TrendyBeatz Official Twitter Page
    </a>
</section>