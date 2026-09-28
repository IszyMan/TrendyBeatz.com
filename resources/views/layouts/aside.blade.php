@php
    $trendingArtistes = [
        '2Baba', 'Burna Boy', 'Joeboy', 'Davido',
        'Zlatan', 'Falz', 'Fireboy', 'Kizz Daniel',
        'Rema', 'Mayorkun', 'Naira Marley', 'Simi',
        'Wizkid', 'Phyno', 'Olamide', 'Omah Lay',
        'Wande Coal', 'Tiwa Savage', 'Patoranking',
        'Mr Eazi', 'Bella Shmurda', 'Zinoleesky',
        'Shallipopi', 'Fola',
    ];

    $topArtistes = [
        'Adekunle Gold', 'Skales', 'Niniola', 'T Classic',
        'Ladipoe', 'Vector', 'Asa', 'Demmie Vee',
        'Buju BNXN', 'Skiibii', 'Timaya', 'Slimcase',
        'DJ Frenzy', 'Ice Prince', 'Peruzzi', 'Yung6ix',
    ];

    $popularAlbums = collect();
    $featuredSongs = collect();
    $recentBlogs = collect();
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

    <a class="aside-link" href="{{ route('home') }}#song-of-the-week">
        Discover "Top 10 Songs Of The Week"
    </a>

    <a class="aside-link" href="{{ route('home') }}#song-of-the-day">
        Discover "Top 10 Songs Of Today"
    </a>

    <a class="aside-link" href="{{ route('albums.index') }}">
        Discover "Music Albums / EP"
    </a>
</section>

<section class="aside-panel aside-artists">
    <h2 class="aside-title">Trending Artistes</h2>

    <div class="artists-list">
        @foreach ($trendingArtistes as $name)
            <a
                class="artist-pill"
                href="{{ route('search', ['search' => $name]) }}"
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
                href="{{ route('search', ['search' => $name]) }}"
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
            href="{{ route('albums.show', [$album->id, $album->slug]) }}"
        >
            <span class="aside-image-placeholder" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                <strong class="album-name">
                    {{ $album->title }}
                </strong>

                @if ($album->released_year)
                    <span class="album-year">
                        Year: {{ $album->released_year }}
                    </span>
                @endif
            </span>
        </a>
    @empty
        <p class="aside-empty">No albums available yet.</p>
    @endforelse

    <a class="aside-view-all" href="{{ route('albums.index') }}">
        View All Popular Albums →
    </a>
</section>

<section class="aside-panel aside-featured">
    <h2 class="aside-title">Song of the Day</h2>

    @forelse ($featuredSongs as $song)
        <a
            class="aside-song aside-media-card"
            href="{{ route('music_details', [$song->id, $song->slug]) }}"
        >
            <span class="aside-image-placeholder" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                <strong>
                    {{ $song->artist_name ?: 'TrendyBeatz' }}
                </strong>

                <span>{{ $song->track_title }}</span>
            </span>
        </a>
    @empty
        <p class="aside-empty">Featured songs will appear here.</p>
    @endforelse
</section>

<section class="aside-panel aside-blog">
    <h2 class="aside-title">Latest Blog & News</h2>

    @forelse ($recentBlogs as $blog)
        <a
            class="aside-blog-item aside-media-card"
            href="{{ route('blogs.show', [$blog->id, $blog->slug]) }}"
        >
            <span class="aside-image-placeholder aside-image-placeholder-news" aria-hidden="true">
                <span>TB</span>
            </span>

            <span class="aside-media-details">
                <strong>{{ $blog->title }}</strong>
                <span>Read more →</span>
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