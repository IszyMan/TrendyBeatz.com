@extends('layouts.app')

@php
    $artistName = $video->artist_name ?: 'TrendyBeatz';
    $trackTitle = trim((string) $video->TrackTitle);
    $featuring = trim((string) ($video->Featuring ?? ''));

    $displayTitle = $artistName . ' - ' . $trackTitle;
    $keywordTitle = $artistName . ' ' . $trackTitle;

    if (
        $featuring !== ''
        && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $trackTitle)
    ) {
        $displayTitle .= ' feat. ' . $featuring;
        $keywordTitle .= ' feat. ' . $featuring;
    }

    $pageTitle = $displayTitle . ' | Download Video MP4 » TrendyBeatz';

    $metaKeywords = implode(', ', [
        $keywordTitle . ' video',
        'Download ' . $keywordTitle . ' video',
        'Watch ' . $trackTitle . ' video',
        'Stream ' . $keywordTitle . ' video',
        'download video mp4 ' . $keywordTitle,
        'Download ' . $displayTitle . ' MP4',
        $keywordTitle . ' music video',
    ]);

    $description = collect([
        $video->introduction ?? '',
        $video->TrackInfo ?? '',
    ])
        ->map(fn ($text) => trim(strip_tags((string) $text)))
        ->filter(fn ($text) => $text !== '')
        ->implode(' ');

    if ($description === '') {
        $description = 'Watch and download ' . $displayTitle
            . ' music video on TrendyBeatz.';
    }

    $description = \Illuminate\Support\Str::limit(
        preg_replace('/\s+/u', ' ', $description),
        280,
        ''
    );

    $canonicalUrl = \App\Support\VideoUrl::detail($video);

    $coverPath = trim((string) ($video->CoverUrl ?? ''));

    if ($coverPath === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $coverPath)) {
        $socialImage = $coverPath;
    } else {
        $coverPath = ltrim($coverPath, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/')
                ? $coverPath
                : 'images/' . $coverPath
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $description)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $displayTitle)
@section('social_description', $description)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif


@section('content')
    @php

        $artistSlug = \Illuminate\Support\Str::slug($artistName);
        
        $videoFile = trim((string) $video->TrackUrl);

        if ($videoFile === '') {
            $videoUrl = null;
        } elseif (preg_match('~^https?://~i', $videoFile)) {
            $videoUrl = $videoFile;
        } else {
            $videoUrl = asset(ltrim($videoFile, '/'));
        }

        /*
         * The old scriptUrl can contain HTML or JavaScript.
         * Extract a YouTube URL only; do not render scriptUrl as HTML.
         */
        $youtubeEmbed = null;

        if (preg_match(
            '~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})~i',
            (string) $video->scriptUrl,
            $youtubeMatch
        )) {
            $youtubeEmbed = 'https://www.youtube-nocookie.com/embed/'
                . $youtubeMatch[1];
        }

        $videoLink = function ($item) use ($video): string {
            $itemSlug = trim((string) $item->slug, " \t\n\r\0\x0B/");

            if ($itemSlug === '') {
                $itemSlug = \Illuminate\Support\Str::slug(
                    $video->artist_name . ' '
                    . $item->track_title . ' '
                    . ($item->featuring ? 'ft ' . $item->featuring : '')
                );
            }

            return route('video_details', [$item->id, $itemSlug]);
        };
    @endphp

    {{-- Main video panel --}}
    <article class="tb-music-detail tb-music-detail-primary tb-video-detail">
        <header class="tb-music-detail-header">
            <h1>{{ $displayTitle }} </h1>

            <div class="tb-music-detail-cover">
                <span class="tb-music-detail-placeholder" aria-hidden="true">
                    TB
                </span>

                @if ($socialImage !== '')
                    <img
                        src="{{ $socialImage }}"
                        alt="{{ $displayTitle }} video cover"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <p class="tb-music-detail-posted">
                @if ($video->posted_by_name)
                    Posted By:
                    <a
                        class="tb-music-detail-poster-link"
                        href="{{ route(
                            'videos.posted_by',
                            \Illuminate\Support\Str::slug($video->posted_by_name)
                        ) }}"
                    >
                        <strong>{{ $video->posted_by_name }}</strong>
                    </a>

                    <span aria-hidden="true">•</span>
                @endif

                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>
        </header>

        <section class="tb-music-detail-section">
            <h2 class="tb-music-detail-heading">
                Download and Watch {{ $displayTitle }}
            </h2>

            <div class="tb-music-detail-facts">
                <p>
                    <strong>Artist Name:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('artists.show', $artistSlug) }}"
                        style="text-decoration: none;"
                    >
                        {{ $artistName }}
                    </a>
                </p>

                @if ($featuredArtists->isNotEmpty())
                    <p>
                        <strong>Featuring:</strong>

                        @foreach ($featuredArtists as $featuredArtist)
                            @if (!$loop->first)
                                <span aria-hidden="true">, </span>
                            @endif

                            @if ($featuredArtist['slug'])
                                <a
                                    class="tb-music-detail-red"
                                    href="{{ route('artists.show', $featuredArtist['slug']) }}"
                                    style="text-decoration: none;"
                                >
                                    {{ $featuredArtist['name'] }}
                                </a>
                            @else
                                <span class="tb-music-detail-red">
                                    {{ $featuredArtist['name'] }}
                                </span>
                            @endif
                        @endforeach
                    </p>
                @endif

                <p class="tb-music-detail-green">
                    <strong>Track Title:</strong>
                    {{ $trackTitle }}
                </p>

                @if (filled($video->YearOfRelease))
                    <p>
                        <strong>Year of Release:</strong>
                        <a
                            class="tb-music-detail-red"
                            href="{{ route('videos.year', $video->YearOfRelease) }}"
                            style="text-decoration: none;"
                        >
                            {{ $video->YearOfRelease }} Videos
                        </a>
                    </p>
                @endif

                @if (filled($video->directedby))
                    <p>
                        <strong>Video Directed By:</strong>
                        {{ $video->directedby }}
                    </p>
                @endif

                <p>
                    <strong>Category:</strong>
                    <a
                      
                        href="{{ route('videos.index') }}"
                        style="text-decoration: none;"
                    >
                        Latest Video
                    </a>
                </p>

                @if (in_array($video->country_id, ['naija', 'ghana', 'african'], true))
                    <p>
                        <strong>Country:</strong>
                        <a
                            class="tb-music-detail-blue"
                            href="{{ route('videos.' . $video->country_id) }}"
                            style="text-decoration: none;"
                        >
                            {{ ucfirst($video->country_id) }} Music Video
                        </a>
                    </p>
                @endif
            </div>
        </section>

        @if (
            filled($video->introduction)
            || filled($video->TrackInfo)
            || filled($video->trackinfo1)
            || filled($video->trackinfo2)
        )
            <section class="tb-music-detail-section">
                <h2 class="tb-music-detail-heading">
                    About This Video
                </h2>

                <div class="tb-music-detail-description">
                    @if (filled($video->introduction))
                        <p class="tb-music-detail-introduction">
                            {{ strip_tags($video->introduction) }}
                        </p>
                    @endif

                    @foreach ([$video->TrackInfo, $video->trackinfo1, $video->trackinfo2] as $paragraph)
                        @if (filled($paragraph))
                            <p>{{ strip_tags($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($youtubeEmbed || $videoUrl)
            <section class="tb-music-detail-listening">
                <h2>Watch {{ $displayTitle }}</h2>

                @if ($youtubeEmbed)
                    <div class="tb-music-detail-youtube">
                        <iframe
                            src="{{ $youtubeEmbed }}"
                            title="{{ $displayTitle }} video"
                            loading="lazy"
                            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                @elseif ($videoUrl)
                    <video
                        class="tb-video-detail-player"
                        controls
                        preload="metadata"
                        @if ($socialImage !== '') poster="{{ $socialImage }}" @endif
                    >
                        <source src="{{ $videoUrl }}">
                        Your browser does not support video playback.
                    </video>
                @endif

                @if ($videoUrl)
                    <a
                        class="tb-music-detail-download"
                        href="{{ $videoUrl }}"
                    >
                        Download {{ $displayTitle }} MP4
                    </a>
                @endif
            </section>
        @endif
    </article>

    
    <div class="tb-music-detail tb-music-detail-related tb-video-detail">
        @if ($otherVideos->isNotEmpty())
            <section class="tb-music-detail-discovery">
                <h2 class="tb-music-detail-heading">
                    Discover &amp; Watch {{ $artistName }} Other Videos
                </h2>

                <div class="tb-music-detail-discovery-list">
                    @foreach ($otherVideos as $item)
                        <a href="{{ \App\Support\VideoUrl::detail($item) }}">
                            <span class="tb-music-detail-icon" aria-hidden="true">
                                ▶
                            </span>

                            <span class="tb-music-detail-discovery-text">
                                <strong>
                                    {{ $artistName }} - {{ $item->track_title }}
                                </strong>

                                <small>
                                    Watch video
                                    @if (filled($item->featuring))
                                        · Ft {{ $item->featuring }}
                                    @endif
                                </small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($artistSongs->isNotEmpty())
            <section class="tb-music-detail-discovery">
                <h2 class="tb-music-detail-heading">
                    Discover &amp; Stream {{ $artistName }} Songs
                </h2>

                <div class="tb-music-detail-discovery-list">
                    @foreach ($artistSongs as $song)

                    
                        <a href="{{ \App\Support\MusicUrl::detail($song) }}">
                            <span class="tb-music-detail-icon" aria-hidden="true">
                                ♫
                            </span>

                            <span class="tb-music-detail-discovery-text">
                                <strong>
                                    {{ $artistName }} - {{ $song->TrackTitle }}
                                </strong>

                                <small>
                                    Listen to song
                                    @if (filled($song->Featuring))
                                        · Ft {{ $song->Featuring }}
                                    @endif
                                </small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover Latest Music MP3 &amp; Videos
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($latestSongs as $song)
                    <a href="{{ \App\Support\MusicUrl::detail($song) }}">
                        <span class="tb-music-detail-icon" aria-hidden="true">
                            ♫
                        </span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $song->artist_name ?: 'TrendyBeatz' }}
                                - {{ $song->TrackTitle }}
                            </strong>
                            <small>Latest music MP3</small>
                        </span>
                    </a>
                @endforeach

                @foreach ($latestVideos as $item)
                    <a href="{{ \App\Support\VideoUrl::detail($item) }}">
                        <span class="tb-music-detail-icon" aria-hidden="true">
                            ▶
                        </span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>{{ $item->artist_name }} - {{ $item->track_title }}</strong>
                            <small>Latest video</small>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="tb-music-detail-more">
                <a href="{{ route('videos.index') }}">
                    View All Latest Videos →
                </a>
            </p>
        </section>
    </div>
@endsection