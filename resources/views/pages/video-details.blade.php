@extends('layouts.app')

@php
    $artistName = $video->artist_name ?: 'TrendyBeatz';
    $trackTitle = trim((string) $video->TrackTitle);
    $featuring = trim((string) $video->Featuring);

    $displayTitle = $artistName . ' - ' . $trackTitle;

    if (
        $featuring !== ''
        && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $trackTitle)
    ) {
        $displayTitle .= ' feat. ' . $featuring;
    }

    $pageTitle = $displayTitle . ' | Download Video MP4 » TrendyBeatz';

    $descriptionSource = trim(strip_tags(
        (string) (
            $video->introduction
            ?: $video->TrackInfo
            ?: $video->trackinfo1
            ?: ''
        )
    ));

    $description = $descriptionSource !== ''
        ? \Illuminate\Support\Str::limit(
            preg_replace('/\s+/', ' ', $descriptionSource),
            160,
            ''
        )
        : 'Watch and download ' . $displayTitle
            . ' music video on TrendyBeatz.';

    $canonicalUrl = \App\Support\VideoUrl::detail($video);

    $coverPath = trim((string) $video->CoverUrl);

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
@section('canonical', $canonicalUrl)
@section('social_title', $displayTitle)
@section('social_description', $description)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif

@section('content')
    @php
        
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
                    <span class="tb-music-detail-blue">
                        {{ $artistName }}
                    </span>
                </p>

                @if ($featuring !== '')
                    <p>
                        <strong>Featuring:</strong>
                        <span class="tb-music-detail-red">
                            {{ $featuring }}
                        </span>
                    </p>
                @endif

                <p>
                    <strong>Track Title:</strong>
                    {{ $trackTitle }}
                </p>

                @if (filled($video->YearOfRelease))
                    <p>
                        <strong>Year of Release:</strong>
                        {{ $video->YearOfRelease }}
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
                    Latest Video
                </p>

                @if (filled($video->country_id))
                    <p>
                        <strong>Country:</strong>
                        {{ ucfirst($video->country_id) }} Music Video
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

    {{-- Separate panel leaves space for the share section later --}}
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
                            <strong>{{ $item->track_title }}</strong>
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