<nav class="topnav desktop-nav desktop-nav-primary" aria-label="Desktop primary navigation">
    <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">
        <span aria-hidden="true">⌂</span><br>Home
    </a>

    <a @class(['active' => request()->routeIs('music.download')]) href="{{ route('music.download') }}">
        <span aria-hidden="true">♫</span><br>Music
    </a>

    <a @class(['active' => request()->routeIs('music.naija')]) href="{{ route('music.naija') }}">
        Naija<br>Music
    </a>

    <a @class(['active' => request()->routeIs('artists.index')]) href="{{ route('artists.index') }}">
        All<br>Artiste
    </a>

    <a
        @class(['active' => request()->is('blogs/celebrity-news')])
        href="{{ route('blogs.category', 'celebrity-news') }}"
    >
        Celebrity<br>News
    </a>

    <a
        @class(['active' => request()->is('blogs/hot-gists')])
        href="{{ route('blogs.category', 'hot-gists') }}"
    >
        Hot<br>Gists
    </a>

    <a
        @class(['active' => request()->is('blogs/music-reviews')])
        href="{{ route('blogs.category', 'music-reviews') }}"
    >
        Music<br>Reviews
    </a>

    <a @class(['active' => request()->routeIs('blogs.index')]) href="{{ route('blogs.index') }}">
        News<br>Blog
    </a>
</nav>

<nav id="navRow2" class="topnav desktop-nav navRow2" aria-label="Desktop music navigation">
    <a @class(['active' => request()->routeIs('music.all')]) href="{{ route('music.all') }}">
        All<br>Music
    </a>

    <a @class(['active' => request()->routeIs('albums.index')]) href="{{ route('albums.index') }}">
        Albums<br>/EP
    </a>

    <a @class(['active' => request()->routeIs('music.gospel')]) href="{{ route('music.gospel') }}">
        Gospel<br>Music
    </a>

    <a @class(['active' => request()->routeIs('music.ghana')]) href="{{ route('music.ghana') }}">
        Ghana<br>Music
    </a>

    <a @class(['active' => request()->routeIs('music.african')]) href="{{ route('music.african') }}">
        African<br>Music
    </a>

    <a @class(['active' => request()->routeIs('music.highlife')]) href="{{ route('music.highlife') }}">
        HighLife<br>Music
    </a>

    <a @class(['active' => request()->routeIs('videos.index')]) href="{{ route('videos.index') }}">
        Video
    </a>

    <a @class(['active' => request()->routeIs('mixes.index')]) href="{{ route('mixes.index') }}">
        DJ<br>Mix
    </a>
</nav>

<nav id="mobileNav" class="topnav mobile-nav" aria-label="Mobile navigation">
    <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">
        Home<br>Page
    </a>

    <div class="mobile-dropdown">
        <button
            class="dropbtn {{ request()->routeIs('music.*', 'albums.*', 'videos.*', 'mixes.*') ? 'active' : '' }}"
            type="button"
            data-nav-toggle
            aria-expanded="false"
            aria-controls="musicDropdown"
        >
            Music<br><span aria-hidden="true">▼</span>
        </button>

        <div class="dropdown-content" id="musicDropdown" hidden>
            <a href="{{ route('music.all') }}">All Music</a>
            <a href="{{ route('albums.index') }}">Albums/EP</a>
            <a href="{{ route('music.naija') }}">Naija Music</a>
            <a href="{{ route('music.ghana') }}">Ghana Music</a>
            <a href="{{ route('music.african') }}">African Music</a>
            <a href="{{ route('music.gospel') }}">Gospel Music</a>
            <a href="{{ route('music.highlife') }}">Highlife Music</a>
            <a href="{{ route('videos.index') }}">Videos</a>
            <a href="{{ route('mixes.index') }}">DJ Mix</a>
        </div>
    </div>

    <a @class(['active' => request()->routeIs('music.naija')]) href="{{ route('music.naija') }}">
        Naija<br>Music
    </a>

    <a @class(['active' => request()->routeIs('artists.index')]) href="{{ route('artists.index') }}">
        All<br>Artiste
    </a>

    <a
        @class(['active' => request()->is('blogs/celebrity-news')])
        href="{{ route('blogs.category', 'celebrity-news') }}"
    >
        Celebrity<br>News
    </a>

    <a
        @class(['active' => request()->is('blogs/hot-gists')])
        href="{{ route('blogs.category', 'hot-gists') }}"
    >
        Hot<br>Gists
    </a>

    <a
        @class(['active' => request()->is('blogs/music-reviews')])
        href="{{ route('blogs.category', 'music-reviews') }}"
    >
        Music<br>Reviews
    </a>

    <a @class(['active' => request()->routeIs('blogs.index')]) href="{{ route('blogs.index') }}">
        News<br>Blog
    </a>
</nav>

<div class="search-wrapper">
    <form
        class="search-box"
        method="GET"
        action="{{ route('search') }}"
        role="search"
    >
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            aria-label="Search TrendyBeatz"
            placeholder="Search TrendyBeatz.."
            required
        >

        <button type="submit">Search</button>
    </form>
</div>