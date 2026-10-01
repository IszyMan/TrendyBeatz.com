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

    $keywordTitle = $artistName . ' ' . $trackTitle;

    if (
        $featuring !== ''
        && !preg_match('/\b(?:ft|feat|featuring)\.?\s/i', $trackTitle)
    ) {
        $keywordTitle .= ' feat. ' . $featuring;
    }

    $metaKeywords = implode(', ', [
        $keywordTitle,
        'Download ' . $keywordTitle,
        'Stream ' . $trackTitle,
        'Download ' . $keywordTitle . ' song',
        'download music mp3 ' . $keywordTitle,
        'Download ' . $fullTitle,
        $keywordTitle . ' free mp3',
    ]);

    $metaDescription = collect([
        $song->introduction ?? '',
        $song->TrackInfo ?? '',
    ])
        ->map(fn ($text) => trim(strip_tags((string) $text)))
        ->filter(fn ($text) => $text !== '')
        ->implode(' ');

    if ($metaDescription === '') {
        $metaDescription = 'Discover and stream '
            . $fullTitle
            . ' on TrendyBeatz.';
    }

    $metaDescription = \Illuminate\Support\Str::limit(
        preg_replace('/\s+/u', ' ', $metaDescription),
        260,
        ''
    );

    $metaCover = trim((string) $song->CoverUrl);

    $artistSlug = \Illuminate\Support\Str::slug($artistName);

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
@section('meta_keywords', $metaKeywords)
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
                <a
                    class="tb-music-detail-blue"
                    href="{{ route('artists.show', $artistSlug) }}"
                    style="text-decoration: none;"
                >
                    {{ $artistName }}
                </a>
            </p>

            <p>
                <strong>Track Title:</strong>
                <span class="tb-music-detail-red">
                    {{ $trackTitle }}
                </span>
            </p>

            @if ($featuredArtists->isNotEmpty())
                <p>
                    <strong>Featuring:</strong>

                    @foreach ($featuredArtists as $featuredArtist)
                        @if (!$loop->first)
                            <span aria-hidden="true">, </span>
                        @endif

                        <a
                            class="tb-music-detail-red"
                            href="{{ !empty($featuredArtist['slug'])
                                ? route('artists.show', $featuredArtist['slug'])
                                : route('artists.index') }}"
                            style="text-decoration: none;"
                        >
                            {{ $featuredArtist['name'] }}
                        </a>
                    @endforeach
                </p>
            @endif

            @if (trim((string) $song->YearOfRelease) !== '')
                <p>
                    <strong>Recorded:</strong>
                    <a
                        class="tb-music-detail-green tb-music-detail-year-link"
                        href="{{ route('music.year', $song->YearOfRelease) }}"
                    >
                        {{ $song->YearOfRelease }} Music
                    </a>
                </p>
            @endif

            @if (trim((string) $song->country_id) !== '')
                <p>
                    <strong>Country:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('music.' . $song->country_id) }}"
                        style="text-decoration: none;"
                    >
                        {{ ucfirst($song->country_id) }} Music
                    </a>
                </p>
            @endif

            @if (!empty($song->album_id) && trim((string) $song->album_name) !== '')
                <p>
                    <strong>Album Name:</strong>
                    <a
                        class="tb-music-detail-red"
                        href="{{ \App\Support\AlbumUrl::detail($song) }}"
                        style="text-decoration: none;"
                    >
                        {{ $song->album_name }}
                    </a>
                </p>
            @endif

          <p>
                <strong>Category:</strong>

                @if ((int) ($song->isgospel ?? 0) === 1)
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('music.gospel') }}"
                        style="text-decoration: none;"
                    >
                        Gospel Songs
                    </a>
                @elseif ((int) ($song->ishighlife ?? 0) === 1)
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('music.highlife') }}"
                        style="text-decoration: none;"
                    >
                        Highlife Music
                    </a>
                @else
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('music.all') }}"
                        style="text-decoration: none;"
                    >
                        Latest Music
                    </a>
                @endif
            </p>
        </div>
    </section>

    <!--<div class="tb-music-detail-ad-label">
        Advertisement
    </div>-->

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

        $youtubeEmbed = trim((string) ($song->youtube_embed_url ?? ''));
        $audiomackEmbed = trim((string) ($song->audiomack_embed_url ?? ''));
    @endphp

  
        @if ($youtubeEmbed || $audiomackEmbed || $digitalStoreUrl || $hasAudioFile)
            <section class="tb-music-detail-listening">

                {{-- YouTube and Audiomack --}}
                @if ($youtubeEmbed || $audiomackEmbed)
                    <h2>
                        Stream {{ $fullTitle }} legally on TrendyBeatz.com
                    </h2>

                    @if ($youtubeEmbed)
                        <div class="tb-music-detail-youtube">
                            <iframe
                                src="{{ $youtubeEmbed }}"
                                title="{{ $fullTitle }} on YouTube"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @endif

                      <br>
                    <h2>Stream {{ $fullTitle }} on Audiomack</h2>

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
                @endif

                {{-- Digital store --}}
                @if ($digitalStoreUrl)
                    <div class="tb-music-detail-digital">
                        <h2>
                            Stream {{ $fullTitle }} on Digital Store
                        </h2>

                        <a
                            class="tb-music-detail-store"
                            href="{{ $digitalStoreUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Stream {{ $fullTitle }}
                        </a>
                    </div>
                @endif

                {{-- MP3 playback and download --}}
                @if ($hasAudioFile)
                    <div class="tb-music-detail-audio">
                        <h2>Listen and Download {{ $fullTitle }} Mp3</h2>

                        <p class="tb-music-copyright-notice">
                            <strong>Copyright Notice:</strong>
                            This song and its audio materials were published upon
                            express request and direct permission from the
                            copyright holder.
                        </p>

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
            </section>
        @endif
  

</article>

{{-- The share section  --}}

<section class="tb-share-card">
    <p class="tb-share-heading">
        🔗 Share {{ $fullTitle }} Music with others on
    </p>

    <div class="tb-share-row">
        <button
            type="button"
            class="tb-share-copy"
            onclick="tbCopyPageLink(this)"
            aria-live="polite"
        >Copy Link</button>
        <div class="a2a_kit a2a_kit_size_24 a2a_default_style">
            <a class="a2a_button_facebook"></a>
            <a class="a2a_button_x"></a>
            <a class="a2a_button_email"></a>
            <a class="a2a_button_pinterest"></a>
            <a class="a2a_button_linkedin"></a>
            <a class="a2a_button_whatsapp"></a>
        </div>

        
    </div>
</section>

{{-- PANEL 2: Discovery sections --}}
<aside class="tb-music-detail tb-music-detail-related">

    @if ($artistSongs->isNotEmpty())
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover {{ $artistName }} Other Songs
            </h2>

            <div class="tb-music-detail-discovery-list">
                @foreach ($artistSongs as $related)
                @php
                    $featuring = trim((string) (
                        $related->featuring ?? $related->Featuring ?? ''
                    ));
                @endphp
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

                            <span>
                                 <b>   @if (filled($related->featuring))
                                        feat. {{ $related->featuring }}
                                    @endif
                                </b>
                            </span>

                            <small>Tap to Stream</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
                <a
                    class="tb-home-view-all"
                    href="{{ route('artists.show', $artistSlug) }}"
                >
                    View All {{ $artistName }} Songs →
                </a>

    @if ($artistVideos->isNotEmpty())
    <section class="tb-music-detail-discovery">
        <h2 class="tb-music-detail-heading">
            Discover &amp; Watch {{ $artistName }} Videos
        </h2>

        <div class="tb-music-detail-discovery-list">
            @foreach ($artistVideos as $video)
                @php
                    $featuring = trim((string) (
                        $video->featuring
                        ?? $video->Featuring
                        ?? ''
                    ));
                @endphp

                <a
                    class="tb-home-song tb-home-video"
                    href="{{ \App\Support\VideoUrl::detail($video) }}"
                >
                    <span
                        class="tb-music-detail-icon"
                        aria-hidden="true"
                    >▶</span>

                    <span class="tb-music-detail-discovery-text">
                        <strong>
                            {{ $artistName }}
                            - {{ $video->track_title }}
                        </strong>

                       <b> @if ($featuring !== '')
                            <span>feat. {{ $featuring }}</span>
                            @endif
                        </b>

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
                    <a href="{{ \App\Support\AlbumUrl::detail($album) }}">
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
                    @php
                        $featuring = trim((string) (
                            $related->featuring
                            ?? $related->Featuring
                            ?? ''
                        ));
                    @endphp

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

                           <b> @if ($featuring !== '')
                                <span>feat. {{ $featuring }}</span>
                            @endif
                            </b>

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

                        $featuring = trim((string) (
                            $related->featuring
                            ?? $related->Featuring
                            ?? ''
                        ));
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

                           <b> @if ($featuring !== '')
                                <span>feat. {{ $featuring }}</span>
                            @endif</b>

                            <small>
                                {{ $isVideo
                                    ? 'Watch Video'
                                    : 'Tap to Stream' }}
                            </small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

   
        <a class="tb-home-view-all" href="{{ route('music.all') }}">
            Click Here for more Music on TrendyBeatz.com →
        </a>
       

</aside>


{{-- Comments can be inserted here later. --}}

    @include('partials.comments', [
        'postType' => 'music',
        'postId' => $song->id,
        'postTitle' => $fullTitle,
    ])
@endsection