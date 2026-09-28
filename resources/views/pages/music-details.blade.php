@extends('layouts.app')

@php
    /*
     * Metadata only. Values needed by the visible page
     * are calculated in the body below.
     */
    $artistName = $song->artist_name ?: 'TrendyBeatz';
    $trackTitle = trim((string) $song->TrackTitle);
    $featuring = trim((string) $song->Featuring);
    $fullTitle = $artistName . ' - ' . $trackTitle;
    if (
        $featuring !== ''
        && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $trackTitle)
    ) {
        $fullTitle .= ' feat. ' . $featuring;
    }
    $pageTitle = $fullTitle . ' Music';

    $canonical = route('music_details', [
        $song->id,
        \App\Support\MusicUrl::slug($song),
    ]);

    $metaDescription = trim(strip_tags((string) (
        $song->introduction
        ?: $song->TrackInfo
        ?: ''
    )));

    if ($metaDescription === '') {
        $metaDescription = 'Discover and stream '
            . $fullTitle
            . ' on TrendyBeatz.';
    }

    $metaDescription = \Illuminate\Support\Str::limit(
        preg_replace('/\s+/', ' ', $metaDescription),
        160,
        ''
    );

    $metaCover = trim((string) $song->CoverUrl);

    $socialImage = $metaCover === ''
        ? null
        : (
            preg_match('~^https?://~i', $metaCover)
                ? $metaCover
                : asset(
                    str_starts_with(ltrim($metaCover, '/'), 'images/')
                        ? ltrim($metaCover, '/')
                        : 'images/' . ltrim($metaCover, '/')
                )
        );
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $metaDescription)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $metaDescription)

@if ($socialImage)
    @section('social_image', $socialImage)
@endif

@section('content')

{{-- PANEL 1: Song information, listening and download --}}
<article class="tb-music-detail tb-music-detail-primary">

    <header class="tb-music-detail-header">
        <h1>{{ $pageTitle }}</h1>

        <div class="tb-music-detail-cover">
            <span class="tb-music-detail-placeholder" aria-hidden="true">
                TB
            </span>

            @if ($socialImage)
                <img
                    src="{{ $socialImage }}"
                    alt="{{ $fullTitle }} cover art"
                    onerror="this.remove()"
                >
            @endif
        </div>

        <p class="tb-music-detail-posted">
            Posted By:
            <strong>
                <a
                    class="tb-music-detail-poster-link"
                    href="{{ route('songs.posted_by', [
                        'slug' => \Illuminate\Support\Str::slug($song->posted_by_name),
                    ]) }}"
                >
                    {{ $song->posted_by_name }}
                </a>
            </strong>

            @php
                $currentTime = now();
            @endphp

            <span aria-hidden="true">•</span>

            <time datetime="{{ $currentTime->toIso8601String() }}">
                {{ $currentTime->format('M d, Y ') }}
            </time>
        </p>
    </header>

    <!--<div class="tb-music-detail-ad-label">
        Advertisement
    </div>-->

    <section class="tb-music-detail-section">
        <h2 class="tb-music-detail-heading">
            Track Details
        </h2>

        <div class="tb-music-detail-facts">
            <p>
                <strong>Artist Name:</strong>
                <span class="tb-music-detail-blue">
                    {{ $artistName }}
                </span>
            </p>

            <p>
                <strong>Track Title:</strong>
                <span class="tb-music-detail-red">
                    {{ $trackTitle }}
                </span>
            </p>

            @if (trim((string) $song->Featuring) !== '')
                <p>
                    <strong>Featuring:</strong>
                    <span class="tb-music-detail-red">
                        {{ $song->Featuring }}
                    </span>
                </p>
            @endif

            @if (trim((string) $song->YearOfRelease) !== '')
                <p>
                    <strong>Recorded:</strong>
                    <a
                        class="tb-music-detail-blue tb-music-detail-year-link"
                        href="{{ route('music.year', $song->YearOfRelease) }}"
                    >
                        {{ $song->YearOfRelease }} Music
                    </a>
                </p>
            @endif

            @if (trim((string) $song->country_id) !== '')
                <p>
                    <strong>Country:</strong>
                    <span class="tb-music-detail-blue">
                        {{ ucfirst($song->country_id) }} Music
                    </span>
                </p>
            @endif

            @if (trim((string) $song->AlbumName) !== '')
                <p>
                    <strong>Album Name:</strong>
                    <span class="tb-music-detail-red">
                        {{ $song->AlbumName }}
                    </span>
                </p>
            @endif

            <p>
                <strong>Category:</strong>
                <span class="tb-music-detail-blue">
                    Latest Music
                </span>
            </p>
        </div>
    </section>

    <div class="tb-music-detail-ad-label">
        Advertisement
    </div>

    @php
        $introduction = trim(strip_tags(
            (string) $song->introduction
        ));

        $descriptionParagraphs = array_filter(
            [
                trim(strip_tags((string) $song->TrackInfo)),
                trim(strip_tags((string) $song->trackinfo1)),
                trim(strip_tags((string) $song->trackinfo2)),
            ],
            fn ($text) => $text !== ''
        );
    @endphp

    @if ($introduction !== '' || $descriptionParagraphs !== [])
        <section class="tb-music-detail-section">
            <h2 class="tb-music-detail-heading">
                About This Song
            </h2>

            <div class="tb-music-detail-description">
                @if ($introduction !== '')
                    <p class="tb-music-detail-introduction">
                        {{ $introduction }}
                    </p>
                @endif

                @foreach ($descriptionParagraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </section>
    @endif

    @php
        $trackPath = trim((string) $song->TrackUrl);

        $trackUrl = $trackPath === ''
            ? null
            : (
                preg_match('~^https?://~i', $trackPath)
                    ? $trackPath
                    : asset(ltrim($trackPath, '/'))
            );

        $digitalStore = trim((string) $song->buy_song);

        $digitalStoreUrl = filter_var(
            $digitalStore,
            FILTER_VALIDATE_URL
        ) && in_array(
            parse_url($digitalStore, PHP_URL_SCHEME),
            ['http', 'https'],
            true
        ) ? $digitalStore : null;

        /*
         * The restored listing table contains scriptUrl.
         * Extract recognized media URLs; do not print
         * scriptUrl directly as executable HTML.
         */
        $embedSource = (string) $song->scriptUrl;

        $youtubeEmbed = null;
        $audiomackEmbed = null;

        if (preg_match(
            '~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})~i',
            $embedSource,
            $youtubeMatch
        )) {
            $youtubeEmbed = 'https://www.youtube-nocookie.com/embed/'
                . $youtubeMatch[1];
        }

        if (preg_match(
            '~https?://(?:www\.)?audiomack\.com/(?:embed/)?[^\s"\'<>]+~i',
            $embedSource,
            $audiomackMatch
        )) {
            $audiomackPath = parse_url(
                html_entity_decode(rtrim($audiomackMatch[0], ');,')),
                PHP_URL_PATH
            );

            if ($audiomackPath) {
                $audiomackEmbed = 'https://audiomack.com/embed/'
                    . ltrim(
                        preg_replace(
                            '~^/embed/~',
                            '/',
                            $audiomackPath
                        ),
                        '/'
                    );
            }
        }
    @endphp

    @if ($youtubeEmbed || $audiomackEmbed || $trackUrl || $digitalStoreUrl)
        <section class="tb-music-detail-listening">
            <h2>
                Stream {{ $fullTitle }}
                Legally on TrendyBeatz Below:
            </h2>

            @if ($youtubeEmbed)
                <div class="tb-music-detail-youtube">
                    <iframe
                        src="{{ $youtubeEmbed }}"
                        title="{{ $fullTitle }} on YouTube"
                        loading="lazy"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            @endif

            @if ($audiomackEmbed)
                <div class="tb-music-detail-audiomack">
                    <iframe
                        src="{{ $audiomackEmbed }}"
                        title="{{ $fullTitle }} on Audiomack"
                        loading="lazy"
                        allow="autoplay"
                    ></iframe>
                </div>
            @endif

            @if ($trackUrl)
                <div class="tb-music-detail-audio">
                    <audio controls preload="none">
                        <source src="{{ $trackUrl }}">
                        Your browser does not support audio playback.
                    </audio>

                    <a
                        class="tb-music-detail-download"
                        href="{{ $trackUrl }}"
                        download
                    >
                        Download {{ $fullTitle }} Mp3
                    </a>
                </div>
            @endif

            @if ($digitalStoreUrl)
                <a
                    class="tb-music-detail-store"
                    href="{{ $digitalStoreUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Stream {{ $fullTitle }} on Digital Stores
                </a>
            @endif
        </section>
    @endif

</article>

{{-- The share section can be inserted here later. --}}

{{-- PANEL 2: Discovery sections --}}
<aside class="tb-music-detail tb-music-detail-related">

    @if ($artistSongs->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover {{ $artistName }} Other Songs
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($artistSongs as $related)
                    <a href="{{ \App\Support\MusicUrl::detail($related) }}">
                        <span
                            class="tb-music-detail-icon"
                            aria-hidden="true"
                        >♫</span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $related->artist_name }}
                                - {{ $related->track_title }}
                            </strong>

                            <small>Tap to Stream</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($artistVideos->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover &amp; Watch {{ $artistName }} Videos
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($artistVideos as $video)
                    <a href="{{ route('videos.index') }}">
                        <span
                            class="tb-music-detail-icon"
                            aria-hidden="true"
                        >▶</span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $artistName }}
                                - {{ $video->track_title }}
                            </strong>

                            <small>Watch video</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($artistAlbums->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                {{ $artistName }} Music Albums
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($artistAlbums as $album)
                    <a href="{{ route('albums.index') }}">
                        <span
                            class="tb-music-detail-icon"
                            aria-hidden="true"
                        >♫</span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                → {{ $album->title }}

                                @if ($album->released_year)
                                    Album Released in
                                    {{ $album->released_year }}
                                @endif
                            </strong>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($collaborations->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover and Stream
                {{ $artistName }} Various Collaborations
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($collaborations as $related)
                    <a href="{{ \App\Support\MusicUrl::detail($related) }}">
                        <span
                            class="tb-music-detail-icon"
                            aria-hidden="true"
                        >♫</span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $related->artist_name }}
                                - {{ $related->track_title }}
                            </strong>

                            <small>Tap to Stream</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($latestMusic->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover and Stream Latest Music
                &amp; Videos Below
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($latestMusic as $related)
                    @php
                        $isVideo = strtolower(
                            (string) $related->listing_type
                        ) === 'video';
                    @endphp

                    <a href="{{ $isVideo
                        ? route('videos.index')
                        : \App\Support\MusicUrl::detail($related) }}">
                        <span
                            class="tb-music-detail-icon"
                            aria-hidden="true"
                        >
                            {{ $isVideo ? '▶' : '♫' }}
                        </span>

                        <span class="tb-music-detail-discovery-text">
                            <strong>
                                {{ $related->artist_name }}
                                - {{ $related->track_title }}
                            </strong>

                            <small>
                                {{ $isVideo
                                    ? 'Watch Video'
                                    : 'Stream Audio' }}
                            </small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <p class="tb-music-detail-more">
        <a href="{{ route('music.all') }}">
            Click Here for more Music on TrendyBeatz.com →
        </a>
    </p>

    {{-- Comments can be inserted here later. --}}

</aside>
@endsection