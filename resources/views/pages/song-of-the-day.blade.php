@extends('layouts.app')

@php
    $pageTitle = 'Popular Songs Of The Day - Latest Naija Songs Trending Today | TrendyBeatz';

    $pageDescription = 'Download latest Naija, Ghana and African Songs and Music Trending Today, popular song of day, popular naija music, popular songs of the day,download latest popular songs, music of the day, Musics of the Day mp3, song of day mp3, Latest Naija Music Trending Today';

    $pageKeywords = 'song of day, songs of the day, music of the day, popular music, popular songs, Musics of the Day mp3, song of day mp3, latest naija music mp3 download ,naija music mp3 audio,naija latest music mp3 audio,download latest naija music mp3 audio,naija afro music mp3,all naija music mp3 download,all latest naija music mp3,naija music mp3 bullet,new naija music mp3 bullet,latest naija music mp3,best naija music mp3,best naija music mp3 download,best naija music mp3 free download';

    $canonicalUrl = $songs->currentPage() === 1
        ? route('songs.day')
        : $songs->url($songs->currentPage());

    $firstCover = trim((string) ($songs->first()->cover_url ?? ''));

    if ($firstCover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $firstCover)) {
        $socialImage = $firstCover;
    } else {
        $firstCoverPath = ltrim($firstCover, '/');

        $socialImage = asset(
            str_starts_with($firstCoverPath, 'images/')
                ? $firstCoverPath
                : 'images/' . $firstCoverPath
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)
@section('canonical', $canonicalUrl)
@section('social_image', $socialImage)

@if ($songs->currentPage() > 1)
    @section('previous_url', $songs->currentPage() === 2
        ? route('songs.day')
        : $songs->previousPageUrl())
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            Popular and Latest Naija Songs Trending Today
        </h1>

        <p class="tb-day-page-date">
            <time datetime="{{ now('Africa/Lagos')->toDateString() }}">
                {{ now('Africa/Lagos')->format('M d, Y') }}
            </time>
        </p>

        <div class="tb-day-page-related">
            <a class="tb-home-view-all" href="{{ url('/songs-of-the-week') }}">
                Click Here To also Discover the Top 10 Popular Songs Of The Week →
            </a>
        </div>

        <div class="tb-home-song-list">
            @forelse ($songs as $song)
                @php
                    $cover = trim((string) $song->cover_url);

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
                    class="tb-home-song"
                    href="{{ route('music_details', [$song->id, $song->slug]) }}"
                >
                    <span class="tb-home-song-thumb">
                        <span class="tb-home-song-placeholder" aria-hidden="true">
                            TB
                        </span>

                        @if ($coverUrl)
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $song->artist_name }} - {{ $song->track_title }} cover"
                                loading="lazy"
                                onerror="this.remove()"
                            >
                        @endif
                    </span>

                    <span class="tb-home-song-info">
                        <span class="tb-home-song-badge">Music</span>

                        <strong class="tb-home-song-artist">
                            {{ $song->artist_name }}
                        </strong>

                        <span class="tb-home-song-title">
                            {{ $song->track_title }}
                        </span>

                        @if (filled($song->featuring))
                            <span class="tb-home-song-featuring">
                                Featuring: {{ $song->featuring }}
                            </span>
                        @endif

                        <span class="tb-home-song-actions">
                            <span class="tb-home-discover">Discover</span>
                            <span>|</span>
                            <span class="tb-home-stream">Stream</span>
                        </span>
                    </span>
                </a>
            @empty
                <p class="tb-home-empty">
                    No Song of the Day entries available yet.
                </p>
            @endforelse
        </div>

        @include('partials.pagination', ['paginator' => $songs])
    </section>
@endsection