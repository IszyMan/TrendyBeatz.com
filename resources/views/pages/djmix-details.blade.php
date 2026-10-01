@extends('layouts.app')

@php
    /*
     * Page metadata. Visible page values are prepared inside
     * the content section below.
     */
    $metaDjName = trim((string) ($mix->dj_name ?: 'TrendyBeatz DJ'));
    $metaMixTitle = trim((string) $mix->mix_title);

    $pageTitle = 'Download Mix: '
        . $metaDjName
        . ' - '
        . $metaMixTitle
        . ' Mix';

    $descriptionText = trim(strip_tags(
        (string) ($mix->details ?: $mix->description1 ?: '')
    ));

    $pageDescription = 'Download '
        . $metaDjName
        . ' '
        . $metaMixTitle
        . ' Mix.';

    if ($descriptionText !== '') {
        $pageDescription .= ' ' . $descriptionText;
    }

    $pageDescription = \Illuminate\Support\Str::limit(
        preg_replace('/\s+/', ' ', $pageDescription),
        260,
        ''
    );

    $metaKeywords = implode(', ', [
        $metaDjName . ' ' . $metaMixTitle,
        'Download ' . $metaDjName . ' ' . $metaMixTitle,
        'Stream ' . $metaMixTitle,
        'Download ' . $metaDjName . ' ' . $metaMixTitle . ' mix',
        'download mp3 ' . $metaDjName . ' ' . $metaMixTitle,
        'Download ' . $metaDjName . ' - ' . $metaMixTitle,
        $metaDjName . ' ' . $metaMixTitle . ' free mp3',
        $metaDjName . ' mixtapes',
        'TrendyBeatz DJ mixes',
    ]);

    $canonicalUrl = route('mixes.show', [
        $mix->id,
        $canonicalSlug,
    ]);

    $metaCover = trim((string) $mix->cover_url);

    if ($metaCover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $metaCover)) {
        $socialImage = $metaCover;
    } else {
        $metaCoverPath = ltrim($metaCover, '/');

        $socialImage = asset(
            str_starts_with($metaCoverPath, 'images/')
                ? $metaCoverPath
                : 'images/dj/' . basename($metaCoverPath)
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $metaDjName . ' - ' . $metaMixTitle)
@section('social_description', $pageDescription)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif

@section('content')
    @php
        $djName = trim((string) ($mix->dj_name ?: 'TrendyBeatz DJ'));
        $mixTitle = trim((string) $mix->mix_title);
        $displayTitle = $djName . ' - ' . $mixTitle;

        $cover = trim((string) $mix->cover_url);

        if ($cover === '') {
            $coverUrl = null;
        } elseif (preg_match('~^https?://~i', $cover)) {
            $coverUrl = $cover;
        } else {
            $coverPath = ltrim($cover, '/');

            $coverUrl = asset(
                str_starts_with($coverPath, 'images/')
                    ? $coverPath
                    : 'images/dj/' . basename($coverPath)
            );
        }

        $track = trim((string) $mix->track_url);

        if ($track === '') {
            $trackUrl = null;
        } elseif (preg_match('~^https?://~i', $track)) {
            $trackUrl = $track;
        } else {
            $trackUrl = asset(ltrim($track, '/'));
        }

        $currentDate = now();
    @endphp

    {{-- First panel: details, playback and download --}}
    <article class="tb-music-detail tb-music-detail-primary tb-mix-detail">
        <header class="tb-music-detail-header">
            <h1>
                {{ $displayTitle }} | Download Mix MP3
            </h1>

            <div class="tb-music-detail-cover tb-mix-detail-cover">
                <span class="tb-music-detail-placeholder" aria-hidden="true">
                    TB
                </span>

                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $displayTitle }} mixtape cover"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <p class="tb-music-detail-posted">
                @if ($mix->posted_by_name)
                    Posted By:
                    <a
                        class="tb-music-detail-poster-link"
                        href="{{ route('mixes.posted_by', \Illuminate\Support\Str::slug($mix->posted_by_name)) }}"
                    >
                        <strong>{{ $mix->posted_by_name }}</strong>
                    </a>

                    <span aria-hidden="true">•</span>
                @endif

               
                <time datetime="{{ $currentDate->toDateString() }}">
                    {{ $currentDate->format('M d, Y') }}
                </time>
            </p>
        </header>

        <section class="tb-music-detail-section">
            

            <div class="tb-music-detail-facts">
                <p>
                    <strong>DJ:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('djs.show', \Illuminate\Support\Str::slug($mix->dj_name)) }}"
                        style="text-decoration: none;"
                    >
                         <span class="tb-music-detail-blue">{{ $djName }}</span>
                    </a>
                </p>

                <p>
                    <strong>Mixtape:</strong>
                    <span class="tb-music-detail-red">{{ $mixTitle }}</span>
                </p>

                @if (filled($mix->released_year))
                    <p>
                        <strong>Year:</strong>
                        <a
                            class="tb-music-detail-green"
                            href="{{ route('mixes.year', $mix->released_year) }}"
                            style="text-decoration: none;"
                        >
                            {{ $mix->released_year }}
                        </a>
                    </p>
                @endif

                <p>
                    <strong>Category:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('mixes.index') }}"
                        style="text-decoration: none;"
                    >
                        Latest DJ Mix
                    </a>
                </p>
            </div>
        </section>

        @if (
            filled($mix->details)
            || filled($mix->details2)
            || filled($mix->description1)
            || filled($mix->description2)
        )
            <section class="tb-music-detail-section">
                <h2 class="tb-music-detail-heading">
                    About This DJ Mix
                </h2>

                <div class="tb-music-detail-description">
                    @foreach ([
                        $mix->details,
                        $mix->details2,
                        $mix->description1,
                        $mix->description2,
                    ] as $paragraph)
                        @if (filled($paragraph))
                            <p>
                                {{ trim(strip_tags((string) $paragraph)) }}
                            </p>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if ($hasAudioFile)
            <section class="tb-music-detail-listening">
                <h2>Listen to {{ $displayTitle }}</h2>

                <div class="tb-music-detail-audio">
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
                </div>

                <a
                    class="tb-music-detail-download"
                    href="{{ $trackUrl }}"
                    download
                >
                    Download {{ $displayTitle }} Mix
                </a>
            </section>
        @endif
    </article>


    {{-- The share section  --}}

    <section class="tb-share-card">
        <p class="tb-share-heading">
            🔗 Share {{ $displayTitle }} Music with others on
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

    {{-- Other mixes and DJs --}}
    <section class="tb-music-detail tb-music-detail-related tb-mix-detail-related">
        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Download Latest &amp; Other Mixes By {{ $djName }}
            </h2>

            <div class="tb-mix-detail-list">
                @forelse ($otherMixesByDj as $otherMix)
                    @php
                        $otherSlug = trim(
                            (string) $otherMix->slug,
                            " \t\n\r\0\x0B/"
                        );

                        if ($otherSlug === '') {
                            $otherSlug = \Illuminate\Support\Str::slug(
                                $otherMix->dj_name . ' ' . $otherMix->mix_title
                            );
                        }

                        $otherCover = trim((string) $otherMix->cover_url);

                        if ($otherCover === '') {
                            $otherCoverUrl = null;
                        } elseif (preg_match('~^https?://~i', $otherCover)) {
                            $otherCoverUrl = $otherCover;
                        } else {
                            $otherPath = ltrim($otherCover, '/');

                            $otherCoverUrl = asset(
                                str_starts_with($otherPath, 'images/')
                                    ? $otherPath
                                    : 'images/dj/' . basename($otherPath)
                            );
                        }
                    @endphp

                    <a href="{{ \App\Support\DjMixUrl::detail($otherMix) }}">
                        <span class="tb-mix-detail-thumb">
                            <span aria-hidden="true">TB</span>

                            @if ($otherCoverUrl)
                                <img
                                    src="{{ $otherCoverUrl }}"
                                    alt="{{ $otherMix->dj_name }} - {{ $otherMix->mix_title }} cover"
                                    loading="lazy"
                                    onerror="this.remove()"
                                >
                            @endif
                        </span>

                        <span class="tb-mix-detail-item-text">
                            <strong>{{ $otherMix->dj_name }}</strong>
                            <span><b>{{ $otherMix->mix_title }}</b></span>

                            <small>Listen & Download</small>
                        </span>
                    </a>
                @empty
                    <p class="tb-home-empty">
                        No other mixes by {{ $djName }} yet.
                    </p>
                @endforelse
            </div>
        </section>

        <section class="tb-music-detail-discovery">
            <h2 class="tb-music-detail-heading">
                Discover Mixes From Other DJs
            </h2>

            <div class="tb-mix-detail-list">
                @forelse ($otherDjs as $otherDj)
                    @php
                        
                        $djImage = trim((string) (
                            $otherDj->dj_photo ?: $otherDj->cover_url
                        ));

                        if ($djImage === '') {
                            $djImageUrl = null;
                        } elseif (preg_match('~^https?://~i', $djImage)) {
                            $djImageUrl = $djImage;
                        } else {
                            $djImagePath = ltrim($djImage, '/');

                            $djImageUrl = asset(
                                str_starts_with($djImagePath, 'images/')
                                    ? $djImagePath
                                    : 'images/dj/' . basename($djImagePath)
                            );
                        }
                    @endphp

                    <a href="{{ \App\Support\DjMixUrl::detail($otherDj) }}">
                        <span class="tb-mix-detail-thumb">
                            <span aria-hidden="true">DJ</span>

                            @if ($djImageUrl)
                                <img
                                    src="{{ $djImageUrl }}"
                                    alt="{{ $otherDj->dj_name }} photo"
                                    loading="lazy"
                                    onerror="this.remove()"
                                >
                            @endif
                        </span>

                        <span class="tb-mix-detail-item-text">
                            <strong>{{ $otherDj->dj_name }}</strong>
                            <span><b>{{ $otherDj->mix_title }}</b></span>
                            <small>Listen & Download</small>
                        </span>
                    </a>
                @empty
                    <p class="tb-home-empty">
                        No other DJs available yet.
                    </p>
                @endforelse
            </div>
        </section>

        <p class="tb-home-view-all">
            <a href="{{ route('mixes.index') }}">
                View All Latest DJ Mix →
            </a>
        </p>
    </section>

    @include('partials.comments', [
        'postType' => 'djmix',
        'postId' => $mix->id,
        'postTitle' => $djName . ' - ' . $mix->mix_title,
    ])
@endsection