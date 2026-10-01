@extends('layouts.app')

@php
    $artistSlug = \Illuminate\Support\Str::slug($artistName);

    $pageTitle = 'Download Latest ' . $artistName
        . ' Songs, Music, Albums, Biography, Profile, All Music, Videos'
        . ' - TrendyBeatz';

    $pageDescription = 'Explore ' . $artistName
        . '\'s biography, latest songs, music videos and albums'
        . ' on TrendyBeatz. Discover releases and browse the artist\'s'
        . ' music profile.';

    $canonicalUrl = route('artists.show', $artistSlug);

    $photo = trim((string) $artist->ProfilePic);

    if ($photo === '') {
        $photoUrl = '';
    } elseif (preg_match('~^https?://~i', $photo)) {
        $photoUrl = $photo;
    } else {
        $photoPath = ltrim($photo, '/');

        $photoUrl = asset(
            str_starts_with($photoPath, 'images/')
                ? $photoPath
                : 'images/' . $photoPath
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('canonical', $canonicalUrl)
@section('social_title', $artistName . ' Songs, Albums and Videos | TrendyBeatz')
@section('social_description', $pageDescription)

@if ($photoUrl !== '')
    @section('social_image', $photoUrl)
@endif

@section('content')
    <article class="tb-artist-detail">
        <header class="tb-artist-detail-heading">
            <h1 class="section-heading">
                Download Latest {{ $artistName }} Songs, Albums,
                Biography, All Music, and Videos Here on TrendyBeatz
            </h1>

            @php
                $currentDate = now();
            @endphp

            <p class="tb-day-page-date">
                <time datetime="{{ $currentDate->toDateString() }}">
                    {{ $currentDate->format('M d, Y') }}
                </time>
            </p>
        </header>

        <section
            class="tb-artist-detail-profile"
            aria-label="{{ $artistName }} profile"
        >
            <div class="tb-artist-detail-photo">
                <span class="tb-artist-detail-placeholder" aria-hidden="true">
                    TB
                </span>

                @if ($photoUrl !== '')
                    <img
                        src="{{ $photoUrl }}"
                        alt="{{ $artistName }} profile photo"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <div class="tb-artist-detail-facts">
                @if (filled($artist->Fullname))
                    <p>
                        <strong>Name:</strong>
                        <span>{{ $artist->Fullname }}</span>
                    </p>
                @endif

                <p>
                    <strong>Also Known As:</strong>
                    <span>{{ $artistName }}</span>
                </p>

                @if (filled($artist->RecordLabel))
                    <p>
                        <strong>Record Label:</strong>
                        <span>{{ $artist->RecordLabel }}</span>
                    </p>
                @endif

                @if (filled($artist->Genres))
                    <p>
                        <strong>Genre:</strong>
                        <span>{{ $artist->Genres }}</span>
                    </p>
                @endif

                @php
                    $artistCountry = strtolower(trim((string) ($artist->country_id ?? '')));
                @endphp

                @if (in_array($artistCountry, ['naija', 'ghana', 'african'], true))
                    <p>
                        <strong>Country:</strong>
                     <span>   <a
                            href="{{ route('music.' . $artistCountry) }}"
                            style="text-decoration: none;"
                        >
                            {{ ucfirst($artistCountry) }}
                        </a></span>
                    </p>
                @endif
            </div>
        </section>

        @if (filled($artist->ArtistsProfile) || filled($artist->Place_Birth))
            <section class="tb-artist-detail-bio">
                <h2 class="sub-section-heading">
                    {{ $artistName }} Biography
                </h2>

                @if (filled($artist->Place_Birth))
                    <p>{{ strip_tags($artist->Place_Birth) }}</p>
                @endif

            </section>
        @endif

        <h2 class="tb-artist-detail-intro">
            Download All {{ $artistName }} Latest Songs, Albums
            and Videos Below
        </h2>

        {{-- Albums and their tracks --}}
        @if ($albums->isNotEmpty())
            <section class="tb-artist-detail-section" id="albums">
                <h2 class="sub-section-heading">
                    {{ $artistName }} Albums and Tracklists
                </h2>

                <div class="tb-artist-album-list">
                    @foreach ($albums as $album)
                        @php
                            $cover = trim((string) $album->cover_url);

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

                            $albumSlug = \Illuminate\Support\Str::slug(
                                $artistName . ' ' . $album->title
                            );

                            $tracks = $albumTracks->get(
                                $album->id,
                                collect()
                            );
                        @endphp

                        <details
                            class="tb-artist-album"
                            @if ($loop->first) open @endif
                        >
                            <summary class="tb-artist-album-summary">
                                <span class="tb-artist-album-cover">
                                    <span aria-hidden="true">Album</span>

                                    @if ($coverUrl)
                                        <img
                                            src="{{ $coverUrl }}"
                                            alt="{{ $artistName }} - {{ $album->title }} album cover"
                                            loading="lazy"
                                            onerror="this.remove()"
                                        >
                                    @endif
                                </span>

                                <span class="tb-artist-album-info">
                                    <strong class="tb-artist-album-title">
                                        {{ $artistName }} - {{ $album->title }}
                                    </strong>

                                    @if (filled($album->released_year))
                                        <span class="tb-artist-album-meta">
                                            Released in {{ $album->released_year }}
                                        </span>
                                    @endif

                                    <span class="tb-artist-album-meta">
                                        {{ $tracks->count() }}
                                        {{ \Illuminate\Support\Str::plural(
                                            'track',
                                            $tracks->count()
                                        ) }}
                                    </span>

                                    <span class="tb-artist-album-toggle">
                                        <span class="tb-artist-album-show">
                                            Show tracklist
                                        </span>
                                        <span class="tb-artist-album-hide">
                                            Hide tracklist
                                        </span>
                                        <span aria-hidden="true">⌄</span>
                                    </span>
                                </span>
                            </summary>

                            <div class="tb-artist-album-body">
                                @if ($tracks->isNotEmpty())
                                    <ol class="tb-artist-album-tracks">
                                         @foreach ($tracks as $song)
                                            <li>
                                                <a href="{{ \App\Support\MusicUrl::detail($song) }}">
                                                    <span class="tb-artist-album-track-number">
                                                        {{ $loop->iteration }}.
                                                    </span>

                                                    <span class="tb-artist-album-track-text">
                                                        <strong>{{ $song->track_title }}</strong>

                                                        @if (filled($song->featuring))
                                                            <small>
                                                                <b>feat. {{ $song->featuring }}</b>
                                                            </small>
                                                        @endif

                                                        <small class="tb-artist-album-track-stream">
                                                            Tap to Stream
                                                        </small>
                                                    </span>

                                                    <span
                                                        class="tb-artist-album-track-arrow"
                                                        aria-hidden="true"
                                                    >
                                                        →
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p class="tb-artist-album-empty">
                                        No published tracks are available for
                                        this album yet.
                                    </p>
                                @endif

                                @if (\Illuminate\Support\Facades\Route::has('albums.show'))
                                    <a
                                        class="tb-artist-album-view"
                                        href="{{ route('albums.show', [
                                            $album->id,
                                            $albumSlug,
                                        ]) }}"
                                    >
                                        View Album →
                                    </a>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Singles --}}
        @if ($singles->isNotEmpty())
            <section class="tb-artist-detail-section" id="singles">
                <h2 class="sub-section-heading">
                    {{ $artistName }} Latest Single Songs
                </h2>

                <div class="tb-artist-detail-grid">
                    @foreach ($singles as $song)
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
                            class="tb-artist-detail-card"
                            href="{{ \App\Support\MusicUrl::detail($song) }}"
                        >
                            <span class="tb-artist-detail-card-image">
                                <span aria-hidden="true">TB</span>

                                @if ($coverUrl)
                                    <img
                                        src="{{ $coverUrl }}"
                                        alt="{{ $artistName }} - {{ $song->track_title }} cover"
                                        loading="lazy"
                                        onerror="this.remove()"
                                    >
                                @endif
                            </span>

                            <span class="tb-artist-detail-card-content">
                                <strong class="tb-artist-detail-card-title">
                                    {{ $artistName }} -
                                    {{ $song->track_title }}
                                </strong>

                                @if (filled($song->featuring))
                                    <span class="tb-artist-detail-card-featuring">
                                        feat. {{ $song->featuring }}
                                    </span>
                                @endif

                                <span class="tb-artist-detail-card-action">
                                    Discover &amp; Stream →
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Videos --}}
        @if ($videos->isNotEmpty())
            <section class="tb-artist-detail-section" id="videos">
                <h2 class="sub-section-heading">
                    {{ $artistName }} Videos
                </h2>

                <div class="tb-artist-detail-grid">
                    @foreach ($videos as $video)
                        @php
                            $cover = trim((string) $video->cover_url);

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
                            class="tb-artist-detail-card"
                            href="{{ \App\Support\VideoUrl::detail($video) }}"
                        >
                            <span class="tb-artist-detail-card-image">
                                <span aria-hidden="true">TB</span>
                                <span
                                    class="tb-artist-detail-play"
                                    aria-hidden="true"
                                >
                                    ▶
                                </span>

                                @if ($coverUrl)
                                    <img
                                        src="{{ $coverUrl }}"
                                        alt="{{ $artistName }} - {{ $video->track_title }} video cover"
                                        loading="lazy"
                                        onerror="this.remove()"
                                    >
                                @endif
                            </span>

                            <span class="tb-artist-detail-card-content">
                                <strong class="tb-artist-detail-card-title">
                                    {{ $artistName }} -
                                    {{ $video->track_title }}
                                </strong>

                                @if (filled($video->featuring))
                                    <span class="tb-artist-detail-card-featuring">
                                        feat. {{ $video->featuring }}
                                    </span>
                                @endif

                                <span class="tb-artist-detail-card-action">
                                    Watch Video →
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- The share section  --}}

    <section class="tb-share-card">
        <p class="tb-share-heading">
            🔗 Share {{ $artistName }} Profile with others on
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

        <p class="tb-home-view-all">
            <a href="{{ route('artists.index') }}">
                ← Browse All Artists
            </a>
        </p>

        
    </article>
@endsection