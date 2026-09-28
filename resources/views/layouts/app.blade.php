<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $metaTitle = trim($__env->yieldContent(
            'title',
            ($title ?? 'Discover Latest Naija Music and Entertainment')
                . ' | TrendyBeatz'
        ));

        $metaDescription = trim($__env->yieldContent(
            'meta_description',
            'Stream and discover the latest Naija songs, Ghana and African music, videos, DJ mixes, albums and entertainment news.'
        ));

        $canonicalUrl = trim($__env->yieldContent(
            'canonical',
            url()->current()
        ));

        $socialImage = trim($__env->yieldContent('social_image'));
    @endphp

    <title>{{ $metaTitle }}</title>

    <meta name="description" content="{{ $metaDescription }}">

    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif

    <meta name="author" content="Israel Wonah">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    @hasSection('previous_url')
        <link rel="prev" href="@yield('previous_url')">
    @endif

    @hasSection('next_url')
        <link rel="next" href="@yield('next_url')">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TrendyBeatz">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">

    @if ($socialImage !== '')
        <meta property="og:image" content="{{ $socialImage }}">
    @endif

    <meta
        name="twitter:card"
        content="{{ $socialImage !== '' ? 'summary_large_image' : 'summary' }}"
    >
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">

    @if ($socialImage !== '')
        <meta name="twitter:image" content="{{ $socialImage }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/trendybeatz.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trendybeatz-nav.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/faviconn.png') }}">

<style>

/* Logo */

.site-logo-bar {
    min-height: 65px;
    padding: 12px max(12px, calc((100vw - 1160px) / 2));
    background: #fff;
}

.site-logo {
    color: #111;
    font-size: 27px;
    font-weight: 900;
    line-height: 1;
}

.site-logo span {
    color: green;
}

/* Shared navigation */

.topnav {
    width: 100%;
    color: #fff;
    background: #000;
}

/* Desktop navigation: Row 1 is sticky; Row 2 scrolls normally. */

.desktop-nav {
    display: flex;
    align-items: stretch;
    justify-content: flex-start;
    gap: 0;
    min-height: 50px;
}

.desktop-nav > a {
    display: flex;
    flex: 0 0 82px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    min-height: 50px;
    padding: 6px 4px;
    color: #fff;
    font-size: 12px;
    line-height: 1.25;
    text-align: center;
    text-decoration: none;
}

.desktop-nav > a:hover,
.desktop-nav > a.active {
    color: #fff;
    background: green;
    border-radius: 6px;
}

.desktop-nav-primary {
    position: sticky;
    top: 0;
    z-index: 9999;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

.navRow2 {
    position: static;
    border-top: 1px solid #272727;
}

.mobile-nav {
    display: none;
}

/* Search scrolls normally on desktop and mobile. */

.search-wrapper {
    position: static;
    padding: 10px 0;
    background: #000;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 90%;
    max-width: 500px;
    margin: 0 auto;
}

.search-box input {
    flex: 1;
    min-width: 0;
    height: 45px;
    padding: 0 15px;
    border: 0;
    border-radius: 30px 0 0 30px;
    outline: none;
    font: inherit;
}

.search-box button {
    height: 40px;
    padding: 0 18px;
    border: 0;
    border-radius: 0 30px 30px 0;
    color: #fff;
    background: green;
    font-size: 14px;
    cursor: pointer;
}

.search-box button:hover {
    background: #0056b3;
}

/* Mobile navigation */

@media (max-width: 768px) {
    .desktop-nav {
        display: none;
    }

    .mobile-nav {
        position: sticky;
        top: 0;
        z-index: 9999;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: stretch;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }

    .mobile-nav > a,
    .mobile-nav > .mobile-dropdown {
        min-width: 0;
        min-height: 55px;
    }

    .mobile-nav > a,
    .mobile-nav .dropbtn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        width: 100%;
        height: 100%;
        padding: 8px 3px;
        border: 0;
        color: #fff;
        background: #000;
        font: 12px/1.25 system-ui, sans-serif;
        text-align: center;
        text-decoration: none;
    }

    .mobile-nav > a:hover,
    .mobile-nav > a.active,
    .mobile-nav .dropbtn.active,
    .mobile-nav .dropbtn[aria-expanded="true"] {
        color: #fff;
        background: green;
        border-radius: 6px;
    }

    .mobile-nav .dropbtn {
        cursor: pointer;
    }

    .mobile-dropdown {
        position: relative;
    }

    .mobile-nav .dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 10000;
        width: min(75vw, 260px);
        max-height: min(70vh, 450px);
        overflow-y: auto;
        background: #000;
        box-shadow: 0 9px 22px rgba(0, 0, 0, 0.4);
    }

    .mobile-nav .dropdown-content[hidden] {
        display: none;
    }

    .mobile-nav .dropdown-content a {
        display: block;
        padding: 13px 16px;
        border-top: 1px solid #333;
        color: #fff;
        font-size: 13px;
        text-align: left;
        text-decoration: none;
    }

    .mobile-nav .dropdown-content a:hover {
        background: green;
    }

    .search-box {
        width: 95%;
    }

    .search-box input {
        font-size: 14px;
    }

    .search-box button {
        padding: 0 14px;
        font-size: 13px;
    }
}

/* =========================================
   TRENDYBEATZ HOME SECTIONS
========================================= */

.section-heading {
    display: inline-block;
    margin: 30px 0 12px;
    padding-bottom: 10px;
    border-bottom: 4px solid #22c55e;
    color: #111;
    font-family: 'Poppins', sans-serif;
    font-size: 30px;
    font-weight: 700;
    line-height: 1.2;
}

.home-date {
    margin: 5px 0 35px;
    color: #333;
    font-size: 13px;
    text-align: center;
    font-weight: 600;
}

.sub-section-heading {
    margin: 22px 0 14px;
    padding-left: 12px;
    border-left: 5px solid #22c55e;
    color: #222;
    font-family: 'Poppins', sans-serif;
    font-size: 22px;
    font-weight: 600;
    line-height: 1.3;
}

.tb-home-section {
    margin-bottom: 35px;
}

.tb-home-empty {
    padding: 18px;
    border-radius: 8px;
    color: #666;
    background: #f7f7f7;
}

.tb-home-view-all {
    display: flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    margin: 14px auto 0;
    padding: 10px 20px;
    border-right: 3px solid #16a34a;
    border-bottom: 3px solid #16a34a;
    border-radius: 25px;
    color: #16a34a;
    background: #fff;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.15);
}

.tb-home-view-all:hover {
    color: #fff;
    background: #16a34a;
}

/* Shared card layout for day, week and song categories */

.tb-home-song-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.tb-home-song {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 156px;
    padding: 8px;
    border-radius: 10px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.tb-home-song:hover {
    color: #111;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.tb-home-song-thumb {
    display: flex;
    flex: 0 0 230px;
    align-items: center;
    justify-content: center;
    width: 230px;
    height: 140px;
    border-radius: 8px;
    color: #fff;
    background: linear-gradient(135deg, #1b2b24, #25874b);
    font-size: 38px;
    font-weight: 900;
    letter-spacing: -3px;
}

.tb-home-song-thumb {
    position: relative;
    overflow: hidden;
}

.tb-home-song-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tb-home-song-thumb img {
    position: absolute;
    inset: 0;
    z-index: 1;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
}

.tb-home-song-info {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
}

.tb-home-song-badge {
    padding: 3px 8px;
    border-radius: 6px;
    color: #fff;
    background: #111;
    font-size: 11px;
    font-weight: 700;
}

.tb-home-song-artist {
    margin-top: 12px;
    color: #ff3b3b;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.5px;
}

.tb-home-song-title {
    margin-top: 6px;
    color: #111;
    font-size: 18px;
    font-weight: 700;
}

.tb-home-song-featuring {
    margin-top: 4px;
    color: #555;
    font-size: 13px;
}

.tb-home-song-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
}

.tb-home-discover,
.tb-home-stream {
    padding: 5px 12px;
    border-radius: 20px;
    color: #fff;
    font-size: 12px;
}

.tb-home-discover {
    background: linear-gradient(135deg, #6366f1, #06b6d4);
}

.tb-home-stream {
    background: linear-gradient(135deg, #22c55e, #10b981);
}

.tb-home-action-divider {
    color: #111;
}

/* Blog news and music reviews */

.tb-home-blog-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.tb-home-blog {
    display: flex;
    gap: 12px;
    padding: 10px;
    border-radius: 10px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.tb-home-blog-thumb {
    display: flex;
    flex: 0 0 110px;
    align-items: center;
    justify-content: center;
    width: 110px;
    min-height: 105px;
    border-radius: 8px;
    color: #fff;
    background: linear-gradient(135deg, #1e293b, #418a57);
    font-size: 25px;
    font-weight: 900;
}

.tb-home-blog-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.tb-home-blog-category {
    color: #16813e;
    font-size: 11px;
    font-weight: 800;
}

.tb-home-blog-title {
    color: #111;
    font-size: 14px;
    line-height: 1.35;
}

.tb-home-blog-intro {
    color: #666;
    font-size: 12px;
}

.tb-home-blog-read {
    margin-top: auto;
    color: #16813e;
    font-size: 12px;
    font-weight: 700;
}

/* Albums */

.tb-home-album-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.tb-home-album {
    display: flex;
    flex-direction: column;
    gap: 7px;
    padding: 10px;
    border-radius: 10px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.tb-home-album-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    aspect-ratio: 1;
    border-radius: 8px;
    color: #fff;
    background: linear-gradient(135deg, #694b3b, #ce8a51);
    font-size: 35px;
    font-weight: 900;
}

.tb-home-album-title {
    font-size: 14px;
    line-height: 1.35;
}

.tb-home-album-year {
    color: #666;
    font-size: 12px;
}

/* Latest DJ Mix */

.tb-home-mix-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 16px;
}

.tb-home-mix {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding: 9px;
    border-radius: 11px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
}

.tb-home-mix-thumb {
    position: relative;
    display: block;
    flex: 0 0 230px;
    width: 230px;
    height: 140px;
    overflow: hidden;
    border-radius: 7px;
    background: #e8eee9;
}

.tb-home-mix-thumb img {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-home-mix-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    color: #287a37;
    font-size: 16px;
    font-weight: 800;
    text-align: center;
}

.tb-home-mix-info {
    display: flex;
    flex: 1 1 0;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    gap: 10px;
}

.tb-home-mix-label {
    padding: 3px 9px;
    border-radius: 5px;
    color: #fff;
    background: #111;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.2;
}

.tb-home-mix-dj {
    max-width: 100%;
    color: #ff3b3b;
    font-size: 19px;
    font-weight: 900;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.tb-home-mix-title {
    max-width: 100%;
    color: #111;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.tb-home-mix-actions {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-top: 3px;
}

.tb-home-mix-discover,
.tb-home-mix-listen {
    padding: 6px 12px;
    border-radius: 18px;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
}

.tb-home-mix-discover {
    background: linear-gradient(135deg, #6366f1, #06b6d4);
}

.tb-home-mix-listen {
    background: linear-gradient(135deg, #22c55e, #10b981);
}

.tb-home-mix-separator {
    color: #111;
    font-weight: 700;
}

.tb-home-mix-more {
    display: flex;
    justify-content: center;
    margin-top: 18px;
}

@media (max-width: 768px) {
    .tb-home-mix-grid {
        padding: 0 8px;
    }

    .tb-home-mix {
        gap: 8px;
        padding: 8px;
    }

    .tb-home-mix-thumb {
        flex: 0 0 44%;
        width: 44%;
        height: 125px;
    }

    .tb-home-mix-info {
        gap: 7px;
    }

    .tb-home-mix-dj {
        font-size: 16px;
    }

    .tb-home-mix-title {
        font-size: 13px;
    }

    .tb-home-mix-discover,
    .tb-home-mix-listen {
        padding: 5px 9px;
        font-size: 10px;
    }

    .tb-home-mix-placeholder {
        font-size: 12px;
        overflow-wrap: anywhere;
    }
}

/* Videos reuse the song card layout. */

.tb-home-videos .tb-home-video-thumb,
.tb-home-videos .tb-home-video-thumb img {
    border-radius: 0;
}

.tb-home-videos .tb-home-video-thumb {
    position: relative;
    overflow: hidden;
}

.tb-home-video-play {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    color: #fff;
    background: rgba(0, 0, 0, 0.7);
    font-size: 17px;
    line-height: 1;
    transform: translate(-50%, -50%);
}

.tb-home-video-more {
    display: flex;
    justify-content: center;
    margin-top: 18px;
}

@media (max-width: 768px) {
    .tb-home-video-play {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* Responsive layouts */

@media (max-width: 768px) {
    .sub-section-heading {
        margin: 18px 0 12px;
        font-size: 19px;
    }

    .tb-home-song {
        min-height: 106px;
        gap: 8px;
        padding: 8px;
    }

    .tb-home-song-thumb {
        flex-basis: 50%;
        width: 50%;
        height: 90px;
        font-size: 26px;
    }

    .tb-home-song-info {
        flex-basis: 50%;
    }

    .tb-home-song-badge {
        padding: 2px 6px;
        font-size: 9px;
    }

    .tb-home-song-artist {
        margin-top: 3px;
        font-size: 17px;
    }

    .tb-home-song-title {
        margin-top: 2px;
        font-size: 13px;
    }

    .tb-home-song-featuring {
        font-size: 11px;
    }

    .tb-home-song-actions {
        gap: 5px;
        margin-top: 5px;
    }

    .tb-home-discover,
    .tb-home-stream {
        padding: 3px 8px;
        font-size: 10px;
    }

    .tb-home-blog-grid,
    .tb-home-mix-grid {
        grid-template-columns: 1fr;
    }

    .tb-home-album-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tb-home-video-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 420px) {
    .tb-home-blog-thumb,
    .tb-home-mix-thumb {
        flex-basis: 90px;
        width: 90px;
    }
}



/* Latest Blog & News on home */

.tb-home-blog-section {
    width: 100%;
    margin: 25px 0 35px;
}

.tb-home-blog-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.tb-home-blog-post {
    display: flex;
    min-width: 0;
    flex-direction: column;
    overflow: hidden;
    border-radius: 10px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tb-home-blog-post:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
}

.tb-home-blog-thumb {
    position: relative;
    flex: 0 0 auto;
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #e8eee9;
}

.tb-home-blog-thumb img {
    position: absolute;
    inset: 0;
    z-index: 1;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-home-blog-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    color: #287a37;
    font-size: 18px;
    font-weight: 800;
    text-align: center;
}

.tb-home-blog-info {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    padding: 12px;
}

.tb-home-blog-category {
    align-self: flex-start;
    margin-bottom: 8px;
    padding: 4px 9px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
}

.tb-cat-sports-news {
    color: #0756a8;
    background: #e3f0ff;
}

.tb-cat-celebrity-news {
    color: #ad2873;
    background: #ffe5f2;
}

.tb-cat-hot-topics {
    color: #ad4025;
    background: #ffe8dd;
}

.tb-cat-net-worth {
    color: #80610a;
    background: #fff2ca;
}

.tb-cat-news {
    color: #226b38;
    background: #e4f5e8;
}

.tb-cat-music-reviews {
    color: #6435a8;
    background: #efe7ff;
}

.tb-home-blog-title {
    display: -webkit-box;
    overflow: hidden;
    margin: 0 0 9px;
    color: #161616;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.35;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}

.tb-home-blog-intro {
    display: -webkit-box;
    overflow: hidden;
    margin: 0 0 15px;
    color: #666;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.5;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.tb-home-blog-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 5px 10px;
    margin-top: auto;
    font-size: 11px;
}

.tb-home-blog-meta time {
    color: #888;
}

.tb-home-blog-read {
    color: #0782d0;
    font-weight: 700;
}

.tb-home-blog-more {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.tb-home-blog-empty {
    grid-column: 1 / -1;
    color: #666;
}

/* Latest Blog & News: mobile */

@media (max-width: 768px) {
    .tb-home-blog-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 18px;
    }

    .tb-home-blog-post {
        flex-direction: row;
        min-height: 190px;
    }

    .tb-home-blog-thumb {
        flex: 0 0 35%;
        width: 35%;
        height: auto;
        min-height: 190px;
    }

    .tb-home-blog-info {
        padding: 10px;
    }

    .tb-home-blog-category {
        margin-bottom: 6px;
        font-size: 10px;
    }

    .tb-home-blog-title {
        margin-bottom: 6px;
        font-size: 14px;
        -webkit-line-clamp: 3;
    }

    .tb-home-blog-intro {
        margin-bottom: 8px;
        font-size: 11px;
        -webkit-line-clamp: 3;
    }

    .tb-home-blog-meta {
        font-size: 10px;
    }

    .tb-home-blog-placeholder {
        font-size: 12px;
        overflow-wrap: anywhere;
    }
}



/* Latest Albums on home */

.tb-home-albums {
    width: 100%;
    margin: 25px 0 35px;
}

.tb-home-album-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.tb-home-album-card {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    padding: 9px;
    border-radius: 11px;
    color: inherit;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
}

.tb-home-album-cover {
    position: relative;
    display: block;
    flex: 0 0 230px;
    width: 230px;
    height: 140px;
    overflow: hidden;
    border-radius: 7px;
    background: #e8eee9;
}

.tb-home-album-cover img {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-home-album-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    color: #287a37;
    font-size: 16px;
    font-weight: 800;
    text-align: center;
}

.tb-home-album-info {
    display: flex;
    flex: 1 1 0;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    gap: 9px;
}

.tb-home-album-badge {
    padding: 3px 8px;
    border-radius: 5px;
    color: #fff;
    background: #111;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.2;
}

.tb-home-album-title {
    max-width: 100%;
    color: #ff3b3b;
    font-size: 19px;
    font-weight: 900;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.tb-home-album-meta {
    color: #60677a;
    font-size: 13px;
    font-weight: 800;
    line-height: 1.35;
}

.tb-home-album-more {
    display: flex;
    justify-content: center;
    margin-top: 18px;
}

.tb-home-album-empty {
    color: #666;
}

/* Mobile: keep the image in place when the title wraps. */

@media (max-width: 768px) {
    .tb-home-album-list {
        gap: 16px;
        padding: 0 8px;
    }

    .tb-home-album-card {
        align-items: flex-start;
        gap: 8px;
        padding: 8px;
    }

    .tb-home-album-cover {
        flex: 0 0 48%;
        width: 48%;
        height: 150px;
    }

    .tb-home-album-info {
        flex: 1 1 0;
        min-width: 0;
        gap: 8px;
    }

    .tb-home-album-title {
        font-size: 18px;
    }

    .tb-home-album-meta {
        font-size: 12px;
    }

    .tb-home-album-placeholder {
        font-size: 12px;
        overflow-wrap: anywhere;
    }
}



/* =========================================
   MUSIC DETAILS PAGE
   ========================================= */

.tb-music-detail {
    width: 100%;
    max-width: 880px;
    min-width: 0;
    margin: 18px auto 35px;
    padding: 30px 16px 36px;
    border: 1px solid #dedede;
    border-radius: 9px;
    background: #fff;
    color: #111;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
    box-sizing: border-box;
}

/* Separate the download panel from the discovery panel */

.tb-music-detail-primary {
    margin-bottom: 0;
}

.tb-music-detail-related {
    margin-top: 32px;
}

.tb-music-detail-related .tb-music-detail-discovery:first-child {
    margin-top: 0;
}

/* Title, cover and posting details */

.tb-music-detail-header {
    text-align: center;
}

.tb-music-detail-header h1 {
    display: inline-block;
    max-width: 100%;
    margin: 0 auto 32px;
    padding-bottom: 4px;
    border-bottom: 4px solid #19b954;
    color: #111;
    font-family: Arial, sans-serif;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.35;
}

.tb-music-detail-cover {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: min(100%, 336px);
    aspect-ratio: 1 / 1;
    margin: 0 auto 17px;
    overflow: hidden;
    border-radius: 10px;
    background: #ededed;
    box-shadow: 0 9px 20px rgba(0, 0, 0, 0.13);
}

.tb-music-detail-cover img {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-music-detail-placeholder {
    position: absolute;
    color: #777;
    font-family: Arial, sans-serif;
    font-size: 64px;
    font-weight: 900;
}

.tb-music-detail-posted {
    margin: 0 0 27px;
    color: #444;
    font-family: Georgia, serif;
    font-size: 13px;
    line-height: 1.5;
}

.tb-music-detail-posted strong {
    color: #0000ee;
}

.tb-music-detail-posted span {
    margin: 0 5px;
}

.tb-music-detail-posted time {
    color: #111;
    font-family: Arial, sans-serif;
    font-size: 11px;
    font-weight: 700;
}

.tb-music-detail-ad-label {
    margin: 26px 0 20px;
    color: #888;
    font-family: Georgia, serif;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
}

/* Section headings */

.tb-music-detail-section,
.tb-music-detail-discovery {
    margin: 0 0 28px;
}

.tb-music-detail-heading {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    margin: 0 0 12px;
    padding: 3px 10px;
    border-left: 6px solid #22c55e;
    border-bottom: 2px solid #e9e9e9;
    color: #111;
    background: transparent;
    font-family: Arial, sans-serif;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.4;
    text-align: center;
}

/* Track details */

.tb-music-detail-facts {
    padding: 0;
    font-family: Arial, sans-serif;
    font-size: 15px;
    font-weight: 700;
}

.tb-music-detail-facts p {
    margin: 0 0 12px;
    line-height: 1.35;
}

.tb-music-detail-facts strong {
    color: #111;
}

.tb-music-detail-blue {
    color: #0000ee;
}

.tb-music-detail-red {
    color: #f00;
}

/* About This Song */

.tb-music-detail-description {
    max-width: 580px;
    margin: 20px auto 36px;
    font-family: Georgia, Garamond, serif;
}

.tb-music-detail-introduction {
    margin: 0 0 35px;
    color: #006600;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.4;
}

.tb-music-detail-description p:not(.tb-music-detail-introduction) {
    margin: 0 0 17px;
    color: #111;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.6;
}

/* YouTube, Audiomack, audio and download */

.tb-music-detail-listening {
    max-width: 620px;
    margin: 42px auto 0;
    text-align: center;
}

.tb-music-detail-listening h2 {
    margin: 0 0 20px;
    color: green;
    font-family: Georgia, serif;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.5;
}

.tb-music-detail-youtube {
    width: 100%;
    margin: 0 auto 18px;
    aspect-ratio: 16 / 9;
    background: #000;
}

.tb-music-detail-audiomack {
    width: 100%;
    height: 252px;
    margin: 0 auto 18px;
}

.tb-music-detail-youtube iframe,
.tb-music-detail-audiomack iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}

.tb-music-detail-audio {
    margin: 20px 0;
}

.tb-music-detail-audio audio {
    display: block;
    width: 100%;
    margin: 0 auto 17px;
}

.tb-music-detail-download,
.tb-music-detail-store {
    display: inline-block;
    max-width: 100%;
    margin: 5px auto;
    padding: 10px 16px;
    border-radius: 4px;
    color: #fff;
    background: #11a83e;
    font-family: Arial, sans-serif;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.4;
    text-align: center;
    text-decoration: none;
    box-sizing: border-box;
}

.tb-music-detail-download:hover,
.tb-music-detail-store:hover {
    color: #fff;
    background: #07852e;
}

.tb-music-detail-store {
    display: table;
    margin-top: 12px;
}

/* Discovery sections */

.tb-music-detail-discovery {
    margin-top: 24px;
}

.tb-music-detail-discovery-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.tb-music-detail-discovery-list a {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 60px;
    padding: 9px 11px;
    border-radius: 10px;
    color: inherit;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 2px 13px rgba(0, 0, 0, 0.07);
    box-sizing: border-box;
}

.tb-music-detail-discovery-list a:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.13);
}

.tb-music-detail-icon {
    display: flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    color: #655999;
    background: #f2f2f4;
    font-family: Arial, sans-serif;
    font-size: 21px;
    font-weight: 700;
}

.tb-music-detail-discovery-text {
    display: block;
    min-width: 0;
}

.tb-music-detail-discovery-text strong {
    display: block;
    color: #00a638;
    font-family: Arial, sans-serif;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.tb-music-detail-discovery-text small {
    display: block;
    margin-top: 2px;
    color: #333;
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: 700;
}

.tb-music-detail-more {
    margin: 25px 0 0;
    text-align: center;
}

.tb-music-detail-more a {
    color: #00a638;
    font-family: Arial, sans-serif;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}

/* Mobile */

@media (max-width: 768px) {
    .tb-music-detail {
        margin: 10px auto 25px;
        padding: 20px 12px 28px;
    }

    .tb-music-detail-primary {
        margin-bottom: 0;
    }

    .tb-music-detail-related {
        margin-top: 25px;
    }

    .tb-music-detail-header h1 {
        margin-bottom: 23px;
        font-size: 19px;
    }

    .tb-music-detail-cover {
        width: min(100%, 300px);
    }

    .tb-music-detail-heading {
        font-size: 16px;
    }

    .tb-music-detail-facts {
        font-size: 14px;
    }

    .tb-music-detail-description {
        padding: 0 12px;
    }

    .tb-music-detail-introduction {
        font-size: 19px;
    }

    .tb-music-detail-description p:not(.tb-music-detail-introduction) {
        font-size: 15px;
    }

    .tb-music-detail-discovery-text strong {
        font-size: 13px;
    }
}




/* Album details: text-only tracklist */

.tb-album-detail-track {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 58px;
    padding: 10px 12px;
    border-bottom: 1px solid #e9e9e9;
    color: #111;
    background: #fff;
    text-decoration: none;
}

.tb-album-detail-track:hover {
    color: #087c2a;
    background: #f4fbf5;
}

.tb-album-detail-track-number {
    flex: 0 0 26px;
    color: #777;
    font-size: 13px;
    font-weight: 700;
}

.tb-album-detail-track-info {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 3px;
}

.tb-album-detail-track-info strong {
    font-size: 15px;
    font-weight: 800;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.tb-album-detail-track-info small {
    color: #555;
    font-size: 12px;
    font-weight: 500;
}

.tb-album-detail-track-arrow {
    margin-left: auto;
    color: #16a34a;
    font-size: 18px;
    font-weight: 700;
}

.tb-album-detail-empty {
    padding: 12px;
    color: #555;
    font-size: 14px;
}

@media (max-width: 768px) {
    .tb-album-detail-track {
        gap: 8px;
        padding: 10px 6px;
    }

    .tb-album-detail-track-info strong {
        font-size: 13px;
    }
}

/* Songs posted by a user */

.tb-posted-songs-page {
    min-width: 0;
}

.tb-posted-songs-heading {
    display: block;
    width: fit-content;
    max-width: 100%;
    margin: 22px auto 8px;
    text-align: center;
}

.tb-posted-songs-date {
    margin: 0 0 22px;
    color: #333;
    font-size: 12px;
    text-align: center;
}

@media (max-width: 768px) {
    .tb-posted-songs-heading {
        font-size: 23px;
        line-height: 1.35;
    }
}

.tb-music-detail-posted .tb-music-detail-poster-link {
    color: #0000ee;
    text-decoration: none;
}

.tb-music-detail-posted .tb-music-detail-poster-link:hover {
    text-decoration: underline;
}


/* =========================================
   MUSIC DOWNLOAD PAGE
   ========================================= */

.tb-download-page {
    min-width: 0;
}

.tb-download-quick-links {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin: 10px auto 18px;
}

.tb-download-quick-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 36px;
    padding: 7px 19px;
    border-right: 2px solid #00b844;
    border-bottom: 2px solid #00b844;
    border-radius: 24px;
    color: #00a638;
    background: #fff;
    font-family: Arial, sans-serif;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
    text-align: center;
    text-decoration: none;
    box-shadow: 1px 2px 7px rgba(0, 0, 0, 0.12);
    box-sizing: border-box;
}

.tb-download-quick-links a:hover {
    color: #fff;
    background: #00a638;
}

.tb-download-quick-links a span {
    font-size: 18px;
    line-height: 1;
}

.tb-download-ad-label {
    margin: 10px 0 22px;
    color: #888;
    font-family: Georgia, serif;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
}

.tb-download-page .tb-home-section,
.tb-download-albums {
    margin-bottom: 36px;
    scroll-margin-top: 75px;
}

/* Popular albums */

.tb-download-album-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.tb-download-album {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding: 9px;
    border-radius: 9px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.07);
}

.tb-download-album:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}

.tb-download-album-cover {
    position: relative;
    display: flex;
    flex: 0 0 160px;
    align-items: center;
    justify-content: center;
    width: 160px;
    height: 110px;
    overflow: hidden;
    border-radius: 6px;
    background: #eee;
}

.tb-download-album-cover img {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-download-album-placeholder {
    position: absolute;
    color: #777;
    font-size: 30px;
    font-weight: 900;
}

.tb-download-album-info {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    gap: 5px;
}

.tb-download-album-badge {
    padding: 3px 8px;
    border-radius: 5px;
    color: #fff;
    background: #111;
    font-size: 10px;
    font-weight: 700;
}

.tb-download-album-artist {
    color: #e83838;
    font-size: 18px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.tb-download-album-title {
    color: #111;
    font-size: 15px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.tb-download-album-info small {
    color: #555;
    font-size: 12px;
}

.tb-download-section-more {
    margin-top: 17px;
    text-align: center;
}

@media (max-width: 768px) {
    .tb-download-quick-links {
        gap: 7px;
    }

    .tb-download-quick-links a {
        padding: 7px 12px;
        font-size: 12px;
    }

    .tb-download-album {
        gap: 9px;
    }

    .tb-download-album-cover {
        flex-basis: 42%;
        width: 42%;
        height: 95px;
    }

    .tb-download-album-artist {
        font-size: 15px;
    }

    .tb-download-album-title {
        font-size: 13px;
    }
}



/* DJ mix details additions */

.tb-mix-detail-cover {
    border-radius: 10px;
}

.tb-mix-detail-related {
    margin-top: 32px;
}

.tb-mix-detail-related .tb-music-detail-discovery:first-child {
    margin-top: 0;
}

.tb-mix-detail-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.tb-mix-detail-list a {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    padding: 9px;
    border-radius: 9px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.tb-mix-detail-list a:hover {
    box-shadow: 0 5px 16px rgba(0, 0, 0, 0.14);
}

.tb-mix-detail-thumb {
    position: relative;
    display: flex;
    flex: 0 0 90px;
    align-items: center;
    justify-content: center;
    width: 90px;
    height: 90px;
    overflow: hidden;
    border-radius: 5px;
    color: #777;
    background: #ededed;
    font-size: 20px;
    font-weight: 800;
}

.tb-mix-detail-thumb img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-mix-detail-item-text {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 5px;
}

.tb-mix-detail-item-text strong {
    color: #16a34a;
    font-size: 16px;
    line-height: 1.35;
}

.tb-mix-detail-item-text > span {
    color: #111;
    font-size: 14px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

@media (max-width: 768px) {
    .tb-mix-detail-related {
        margin-top: 25px;
    }

    .tb-mix-detail-thumb {
        flex-basis: 72px;
        width: 72px;
        height: 72px;
    }

    .tb-mix-detail-item-text strong {
        font-size: 14px;
    }

    .tb-mix-detail-item-text > span {
        font-size: 12px;
    }
}

/* Main content and right sidebar */

.page-layout {
    display: grid;
    grid-template-columns: minmax(0, 75%) minmax(280px, 25%);
    align-items: start;
    width: 100%;
}

.page-main {
    min-width: 0;
    padding: 20px 16px 40px 5px;
    background: #fff;
}

.page-aside {
    min-width: 0;
    min-height: 100%;
    padding: 15px;
    background: #f4fbf6;
    border-left: 1px solid #dfe9e2;
}

.aside-panel {
    margin-bottom: 25px;
    padding: 18px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.aside-top-pages {
    box-shadow: 0 10px 30px rgba(26, 127, 55, 0.25);
}

.aside-title {
    margin: 0 0 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #eee;
    color: #1a1a1a;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.2;
    text-align: center;
}

.aside-top-pages .aside-title {
    font-size: 28px;
}

.aside-subtitle {
    margin-top: 25px;
}

.aside-link {
    display: block;
    margin-bottom: 6px;
    padding: 10px 12px;
    border: 4px solid #e5e7eb;
    border-radius: 8px;
    color: #333;
    background: #fafafa;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.aside-link:hover {
    color: #fff;
    background: #1a7f37;
}

.artists-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}

.artist-pill {
    display: inline-block;
    padding: 8px 13px;
    border: 2px solid #1a7f37;
    border-radius: 30px;
    color: #333;
    background: #fff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.artist-pill:hover {
    color: #fff;
    background: #1a7f37;
}

.album-card,
.aside-song,
.aside-blog-item {
    display: block;
    margin-bottom: 12px;
    padding: 12px;
    border-radius: 10px;
    color: #222;
    background: #fafafa;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.album-card {
    border-right: 6px solid #1a7f37;
    border-bottom: 6px solid #1a7f37;
}

.album-name,
.aside-song strong,
.aside-blog-item strong {
    display: block;
    font-size: 15px;
    line-height: 1.4;
}

.album-year,
.aside-song span,
.aside-blog-item span {
    display: block;
    margin-top: 5px;
    color: #667085;
    font-size: 12px;
}

.aside-empty {
    color: #667085;
    font-size: 14px;
    text-align: center;
}

.aside-view-all {
    display: block;
    padding: 10px;
    border-radius: 8px;
    color: #fff;
    background: #1a7f37;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
}

.aside-view-all:hover {
    color: #fff;
    background: #111;
}

/* Sidebar image placeholders */

.aside-media-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.aside-image-placeholder {
    display: flex;
    flex: 0 0 72px;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    overflow: hidden;
    border-radius: 8px;
    color: #176b31;
    background: linear-gradient(135deg, #e4f5e9, #cce8d4);
    font-size: 19px;
    font-weight: 900;
    letter-spacing: -1px;
}

.aside-image-placeholder-news {
    color: #fff;
    background: linear-gradient(135deg, #202d41, #348e53);
}

.aside-media-details {
    display: block;
    flex: 1;
    min-width: 0;
}

.aside-media-details strong {
    display: block;
    overflow-wrap: anywhere;
}

.aside-media-details > span {
    display: block;
    margin-top: 5px;
    font-size: 12px;
}

@media (max-width: 420px) {
    .aside-image-placeholder {
        flex-basis: 64px;
        width: 64px;
        height: 64px;
    }
}

/* Footer */

.site-footer {
    padding: 35px 20px 15px;
    color: #fff;
    background: linear-gradient(135deg, #1e1b4b, #312e81);
}

.footer-columns {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 25px;
    max-width: 1400px;
    margin: 0 auto;
}

.footer-columns h2 {
    margin: 0 0 16px;
    color: #e6ae76;
    font-size: 23px;
}

.footer-columns a {
    display: block;
    margin-bottom: 12px;
    color: #fff;
    text-decoration: none;
}

.footer-columns a:hover {
    color: #ffe788;
    text-decoration: underline;
}

.footer-address p {
    margin: 0 0 14px;
}

.back-to-top {
    padding: 9px 14px;
    border: 0;
    border-radius: 6px;
    color: #fff;
    background: #1a7f37;
    cursor: pointer;
}

.footer-copyright {
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffe788;
    text-align: center;
}

@media (max-width: 900px) {
    .page-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .page-main {
        padding: 20px 12px;
    }

    .page-aside {
        border-left: 0;
    }

    .footer-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .footer-columns {
        grid-template-columns: 1fr;
    }
}


/* Song of the Day page */

.tb-day-page {
    padding-bottom: 35px;
}

.tb-day-page-date {
    margin: 0 0 18px;
    color: #333;
    font-size: 12px;
    text-align: center;
}

.tb-day-page-related {
    display: flex;
    justify-content: center;
    margin: 12px 0 22px;
    text-align: center;
}

/* Reusable pagination */

.tb-pagination {
    display: flex;
    justify-content: center;
    margin: 28px 0;
}

.tb-pagination-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.tb-pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    min-height: 38px;
    padding: 5px 10px;
    border: 1px solid #d6dfd8;
    border-radius: 6px;
    color: #176e30;
    background: #fff;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}

a.tb-pagination-link:hover {
    border-color: green;
    color: #fff;
    background: green;
}

.tb-pagination-link.is-current {
    border-color: green;
    color: #fff;
    background: green;
}

.tb-pagination-link.is-disabled {
    color: #999;
    background: #f4f4f4;
}

@media (max-width: 768px) {
    .tb-day-page-related {
        padding: 0 8px;
    }

    .tb-day-page-related .tb-home-view-all {
        font-size: 13px;
    }

    .tb-pagination-link {
        min-width: 34px;
        min-height: 34px;
        padding: 4px 8px;
        font-size: 12px;
    }
}

/* Video details: reuse the music details layout. */

.tb-video-detail .tb-music-detail-cover {
    border-radius: 0;
}

.tb-video-detail .tb-music-detail-cover img {
    border-radius: 0;
}

.tb-video-detail-player {
    display: block;
    width: 100%;
    max-width: 620px;
    margin: 0 auto 18px;
    background: #000;
}


/* Artists directory */

.tb-artists-page {
    width: 100%;
    min-width: 0;
    padding-bottom: 35px;
}

.tb-artists-header {
    text-align: center;
}

.tb-artists-header .section-heading {
    margin-bottom: 10px;
}

.tb-artists-intro {
    max-width: 650px;
    margin: 14px auto 28px;
    color: #444;
    font-size: 14px;
    line-height: 1.6;
}

.tb-artists-popular,
.tb-artists-find,
.tb-artists-country {
    margin: 25px 0 35px;
}

.tb-artists-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.tb-artist-card {
    display: flex;
    min-width: 0;
    flex-direction: column;
    padding: 10px;
    border: 1px solid #e8e8e8;
    border-radius: 9px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
}

.tb-artist-card:hover {
    border-color: #16a34a;
    box-shadow: 0 5px 16px rgba(0, 0, 0, 0.11);
}

.tb-artist-card[hidden] {
    display: none;
}

.tb-artist-card-photo {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: 6px;
    background: #ececec;
}

.tb-artist-card-photo img {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-artist-card-placeholder {
    position: absolute;
    color: #777;
    font-size: 30px;
    font-weight: 900;
}

.tb-artist-card-name {
    display: block;
    margin-top: 10px;
    font-size: 15px;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.tb-artist-card-action {
    margin-top: 5px;
    color: #16a34a;
    font-size: 12px;
    font-weight: 700;
}

.tb-artists-search-label {
    display: block;
    margin: 0 0 7px;
    font-size: 13px;
    font-weight: 700;
}

.tb-artists-search {
    display: block;
    width: 100%;
    max-width: 450px;
    height: 44px;
    padding: 0 14px;
    border: 1px solid #ccc;
    border-radius: 22px;
    font: inherit;
}

.tb-artists-search:focus {
    border-color: #16a34a;
    outline: 2px solid rgba(22, 163, 74, 0.15);
}

.tb-artists-alphabet {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 16px;
}

.tb-artists-alphabet button {
    min-width: 32px;
    padding: 7px 9px;
    border: 1px solid #dedede;
    border-radius: 5px;
    color: #111;
    background: #fff;
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.tb-artists-alphabet button:hover,
.tb-artists-alphabet button[aria-pressed="true"] {
    border-color: green;
    color: #fff;
    background: green;
}

.tb-artists-search-note,
.tb-artists-empty {
    color: #555;
    font-size: 13px;
}

.tb-artists-country {
    scroll-margin-top: 70px;
}

.tb-artists-pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 7px;
    margin: 22px 0;
}

.tb-artists-pagination a,
.tb-artists-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    min-height: 34px;
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #111;
    background: #fff;
    font-size: 13px;
    text-decoration: none;
}

.tb-artists-pagination a:hover,
.tb-artists-pagination a[aria-current="page"] {
    border-color: green;
    color: #fff;
    background: green;
}

.tb-artists-page .tb-artists-page-disabled {
    color: #999;
}

@media (max-width: 768px) {
    .tb-artists-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .tb-artist-card {
        padding: 8px;
    }

    .tb-artist-card-name {
        font-size: 13px;
    }

    .tb-artists-alphabet {
        gap: 5px;
    }
}


/* Artist details page */

.tb-artist-detail {
    width: 100%;
    max-width: 980px;
    min-width: 0;
    margin: 0 auto;
    padding: 12px 24px 40px;
    color: #111;
    box-sizing: border-box;
}

.tb-artist-detail-heading {
    margin-bottom: 26px;
    text-align: center;
}

.tb-artist-detail-heading .section-heading {
    margin-bottom: 10px;
    line-height: 1.35;
}

.tb-artist-detail-profile {
    display: grid;
    grid-template-columns: 185px minmax(0, 1fr);
    align-items: start;
    gap: 22px;
    padding: 18px;
    border: 1px solid #e4e4e4;
    border-radius: 9px;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.tb-artist-detail-photo {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: 6px;
    background: #ededed;
}

.tb-artist-detail-photo img {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-artist-detail-placeholder {
    position: absolute;
    color: #777;
    font-size: 40px;
    font-weight: 900;
}

.tb-artist-detail-facts {
    min-width: 0;
    padding-top: 4px;
}

.tb-artist-detail-facts p {
    margin: 0 0 13px;
    font-size: 15px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.tb-artist-detail-facts strong {
    color: #111;
}

.tb-artist-detail-facts span {
    color: #166534;
    font-weight: 700;
}

.tb-artist-detail-bio {
    margin: 30px 0;
    padding: 0 16px;
}

.tb-artist-detail-bio p {
    margin: 0 0 16px;
    color: #242424;
    font-size: 16px;
    line-height: 1.75;
}

.tb-artist-detail-intro {
    margin: 34px 0 22px;
    padding: 12px 15px;
    border-left: 5px solid #22c55e;
    color: #111;
    background: #f4fbf5;
    font-size: 18px;
    line-height: 1.45;
}

.tb-artist-detail-section {
    margin: 28px 0 40px;
    scroll-margin-top: 70px;
}

/* Singles and video cards */

.tb-artist-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.tb-artist-detail-card {
    display: flex;
    min-width: 0;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    color: #111;
    background: #fff;
    text-decoration: none;
    box-shadow: 0 2px 9px rgba(0, 0, 0, 0.06);
}

.tb-artist-detail-card:hover {
    border-color: #16a34a;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.11);
}

.tb-artist-detail-card-image {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 140px;
    overflow: hidden;
    color: #777;
    background: #eee;
    font-size: 19px;
    font-weight: 800;
}

.tb-artist-detail-card-image img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-artist-detail-play {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #fff;
    background: rgba(0, 0, 0, 0.7);
    transform: translate(-50%, -50%);
}

.tb-artist-detail-card-content {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    padding: 12px;
}

.tb-artist-detail-card-title {
    color: #111;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.tb-artist-detail-card-featuring {
    color: #555;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.4;
}

.tb-artist-detail-card-action {
    color: #16803d;
    font-size: 12px;
    font-weight: 700;
}

/* Expandable albums */

.tb-artist-album-list {
    display: grid;
    gap: 16px;
}

.tb-artist-album {
    overflow: hidden;
    border: 1px solid #e4e4e4;
    border-radius: 9px;
    background: #fff;
    box-shadow: 0 2px 9px rgba(0, 0, 0, 0.05);
}

.tb-artist-album[open] {
    border-color: #a8d9b5;
}

.tb-artist-album-summary {
    display: flex;
    align-items: center;
    gap: 17px;
    padding: 15px;
    cursor: pointer;
    list-style: none;
}

.tb-artist-album-summary::-webkit-details-marker {
    display: none;
}

.tb-artist-album-summary::marker {
    content: "";
}

.tb-artist-album-summary:hover {
    background: #f8fcf8;
}

.tb-artist-album-cover {
    position: relative;
    display: flex;
    flex: 0 0 115px;
    align-items: center;
    justify-content: center;
    width: 115px;
    height: 115px;
    overflow: hidden;
    border-radius: 6px;
    color: #777;
    background: #eee;
    font-size: 14px;
    font-weight: 700;
}

.tb-artist-album-cover img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tb-artist-album-info {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 7px;
}

.tb-artist-album-title {
    color: #111;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.tb-artist-album-meta {
    color: #555;
    font-size: 13px;
}

.tb-artist-album-toggle {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #16803d;
    font-size: 13px;
    font-weight: 700;
}

.tb-artist-album-toggle > span:last-child {
    font-size: 19px;
    line-height: 1;
}

.tb-artist-album[open] .tb-artist-album-toggle > span:last-child {
    transform: rotate(180deg);
}

.tb-artist-album-hide,
.tb-artist-album[open] .tb-artist-album-show {
    display: none;
}

.tb-artist-album[open] .tb-artist-album-hide {
    display: inline;
}

.tb-artist-album-body {
    padding: 0 16px 16px;
    border-top: 1px solid #ededed;
}

.tb-artist-album-tracks {
    margin: 7px 0 0;
    padding: 0;
    list-style: none;
}

.tb-artist-album-tracks li + li {
    border-top: 1px solid #ededed;
}

.tb-artist-album-tracks a {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 11px 7px;
    color: #111;
    font-size: 14px;
    line-height: 1.45;
    text-decoration: none;
}

.tb-artist-album-tracks a:hover {
    color: #16803d;
    background: #f4fbf5;
}

.tb-artist-album-track-number {
    flex: 0 0 25px;
    color: #777;
}

.tb-artist-album-track-text {
    min-width: 0;
}

.tb-artist-album-track-text strong {
    font-weight: 800;
}

.tb-artist-album-track-text small {
    margin-left: 5px;
    color: #666;
    font-size: 12px;
    font-weight: 400;
}

.tb-artist-album-track-arrow {
    margin-left: auto;
    color: #16803d;
    font-weight: 700;
}

.tb-artist-album-empty {
    margin: 15px 6px;
    color: #555;
    font-size: 13px;
}

.tb-artist-album-view {
    display: inline-block;
    margin: 14px 6px 0;
    color: #16803d;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.tb-artist-detail-back {
    margin: 32px 0 0;
    text-align: center;
}

.tb-artist-detail-back a {
    color: #16803d;
    font-weight: 700;
    text-decoration: none;
}

@media (max-width: 768px) {
    .tb-artist-detail {
        padding: 10px 14px 32px;
    }

    .tb-artist-detail-profile {
        grid-template-columns: minmax(105px, 35%) minmax(0, 1fr);
        gap: 13px;
        padding: 12px;
    }

    .tb-artist-detail-facts p {
        margin-bottom: 8px;
        font-size: 12px;
    }

    .tb-artist-detail-bio {
        padding: 0 10px;
    }

    .tb-artist-detail-bio p {
        font-size: 15px;
    }

    .tb-artist-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .tb-artist-detail-card-image {
        height: 112px;
    }

    .tb-artist-detail-card-content {
        padding: 9px;
    }

    .tb-artist-detail-card-title {
        font-size: 13px;
    }

    .tb-artist-detail-intro {
        font-size: 16px;
    }

    .tb-artist-album-summary {
        gap: 11px;
        padding: 11px;
    }

    .tb-artist-album-cover {
        flex-basis: 95px;
        width: 95px;
        height: 95px;
    }

    .tb-artist-album-title {
        font-size: 15px;
    }

    .tb-artist-album-meta {
        font-size: 12px;
    }
}

@media (max-width: 380px) {
    .tb-artist-detail-profile {
        grid-template-columns: minmax(88px, 33%) minmax(0, 1fr);
        gap: 9px;
    }

    .tb-artist-detail-facts p {
        font-size: 11px;
    }

    .tb-artist-album-cover {
        flex-basis: 78px;
        width: 78px;
        height: 78px;
    }

    .tb-artist-album-title {
        font-size: 13px;
    }
}



</style>



</head>
<body>
    <header class="site-logo-bar"><a class="site-logo" href="{{ route('home') }}">Trendy<span>Beatz</span></a></header>
    @include('partials.navigation')


    <div class="page-layout">
        <main class="page-main">
            @yield('content')
        </main>

        <aside class="page-aside" aria-label="TrendyBeatz sidebar">
            @include('layouts.aside')
        </aside>
    </div>

    @include('layouts.footer')



    
    <script>
        document.querySelectorAll('[data-nav-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const panel = document.getElementById(button.getAttribute('aria-controls'));
                const open = button.getAttribute('aria-expanded') !== 'true';
                document.querySelectorAll('[data-nav-toggle]').forEach(function (other) {
                    other.setAttribute('aria-expanded', 'false');
                    document.getElementById(other.getAttribute('aria-controls')).hidden = true;
                });
                button.setAttribute('aria-expanded', String(open));
                panel.hidden = !open;
            });
        });
        document.addEventListener('click', function (event) {
            if (event.target.closest('.mobile-dropdown')) return;
            document.querySelectorAll('[data-nav-toggle]').forEach(function (button) {
                button.setAttribute('aria-expanded', 'false');
                document.getElementById(button.getAttribute('aria-controls')).hidden = true;
            });
        });
    </script>
</body>
</html>
