@extends('layouts.app')

@php
    $pageTitle = 'Music - Discover and Stream Latest Music | Legal Download';

    $pageDescription = 'Discover, Stream and Download All the Latest Naija Music mp3, Videos, Dj mix & Entertainment Gists';

    $pageKeywords = 'Latest Nigerian Musics and Video, trending, songs, discover music, download music, latest naija music mp3 videos download, latest naija music mp3 videos download';

    $canonicalUrl = route('music.download');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $pageTitle)
@section('social_description', $pageDescription)

@section('content')
<section class="tb-download-page">

    <nav
        class="tb-download-quick-links"
        aria-label="Browse music sections"
    >
        <a href="{{ route('music.naija') }}">Naija Music <span>→</span></a>

        <a href="{{ route('music.ghana') }}">Ghanaian Music <span>→</span></a>

        <a href="{{ route('music.african') }}">African Music <span>→</span></a>

        <a href="{{ route('music.gospel') }}">Gospel Music <span>→</span></a>

        <a href="{{ route('music.highlife') }}">HighLife Music <span>→</span></a>

        <a href="{{ route('albums.popular') }}">Popular Albums <span>→</span></a>

        <a href="{{ route('blogs.category', 'music-reviews') }}">
            Music Reviews <span>→</span>
        </a>

        <a href="{{ route('songs.top_rated') }}">
            Top Rated Songs <span>→</span>
        </a>

        <a href="{{ route('songs.trending') }}">
            Top 10 Trending Songs <span>→</span>
        </a>

        <a href="{{ route('songs.week') }}">
            Top 10 Song Of The Week <span>→</span>
        </a>

        <a href="{{ route('songs.day') }}">
            Top 10 Song Of The Day
            (Songs Released Today) <span>→</span>
        </a>
    </nav>

    <p class="home-date">
        <time datetime="{{ now()->toDateString() }}">
            {{ now()->format('M d, Y') }}
        </time>
    </p>

    @include('partials.home.songs', [
        'id' => 'song-of-the-day',
        'heading' => 'Song of the Day',
        'badge' => 'Song of the Day',
        'items' => $day,
        'more' => route('songs.day'),
    ])

    <section
        class="tb-download-albums"
        id="popular-albums"
    >
        <h2 class="sub-section-heading">
            Popular Albums
        </h2>

        <div class="tb-download-album-list">
            @forelse ($albums as $album)
                @php
                    $cover = trim(
                        (string) $album->cover_url
                    );

                    if ($cover === '') {
                        $coverUrl = null;
                    } elseif (preg_match('~^https?://~i', $cover)) {
                        $coverUrl = $cover;
                    } else {
                        $coverPath = ltrim($cover, '/');

                        $coverUrl = asset(
                            str_starts_with($coverPath, 'images/')
                                ? $coverPath
                                : 'images/' . $coverPath
                        );
                    }
                @endphp

                <a
                    class="tb-download-album"
                    href="{{ route('albums.index') }}"
                >
                    <span class="tb-download-album-cover">
                        <span
                            class="tb-download-album-placeholder"
                            aria-hidden="true"
                        >
                            TB
                        </span>

                        @if ($coverUrl)
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $album->artist_name }} - {{ $album->title }} album cover"
                                loading="lazy"
                                onerror="this.remove()"
                            >
                        @endif
                    </span>

                    <span class="tb-download-album-info">
                        <span class="tb-download-album-badge">
                            Album / EP
                        </span>

                        <strong class="tb-download-album-artist">
                            {{ $album->artist_name }}
                        </strong>

                        <span class="tb-download-album-title">
                            {{ $album->title }}
                        </span>

                        @if ($album->released_year)
                            <small>
                                Year of Release:
                                {{ $album->released_year }}
                            </small>
                        @endif
                    </span>
                </a>
            @empty
                <p class="tb-home-empty">
                    No published albums available yet.
                </p>
            @endforelse
        </div>

        <div class="tb-download-section-more">
            <a
                class="tb-home-view-all"
                href="{{ route('albums.index') }}"
            >
                Explore All Popular Albums →
            </a>
        </div>
    </section>

    @include('partials.home.songs', [
        'id' => 'latest-naija',
        'heading' => 'Latest Naija Songs',
        'badge' => 'Music',
        'items' => $naija,
        'more' => route('music.naija'),
    ])

    @include('partials.home.songs', [
        'id' => 'latest-ghana',
        'heading' => 'Latest Ghana Songs',
        'badge' => 'Music',
        'items' => $ghana,
        'more' => route('music.ghana'),
    ])

    @include('partials.home.songs', [
        'id' => 'latest-african',
        'heading' => 'Latest African Songs',
        'badge' => 'Music',
        'items' => $african,
        'more' => route('music.african'),
    ])

    @include('partials.home.songs', [
        'id' => 'gospel-songs',
        'heading' => 'Gospel Songs',
        'badge' => 'Gospel Music',
        'items' => $gospel,
        'more' => route('music.gospel'),
    ])

    @include('partials.home.songs', [
        'id' => 'highlife-songs',
        'heading' => 'HighLife Songs',
        'badge' => 'HighLife Music',
        'items' => $highlife,
        'more' => route('music.highlife'),
    ])

    @include('partials.home.songs', [
        'id' => 'songs-of-the-week',
        'heading' => 'Top 10 Songs of the Week',
        'badge' => 'Song of the Week',
        'items' => $week,
        'more' => url('/songs-of-the-week'),
    ])

</section>
@endsection