@extends('layouts.app')

@php
    $artistName = trim((string) $album->artist_name);
    $albumTitle = trim((string) $album->title);
    $displayTitle = $artistName . ' - ' . $albumTitle;

    $pageTitle = 'DOWNLOAD: ' . $displayTitle
        . ' » TrendyBeatz';

    $descriptionStart = 'Download '
        . $artistName . ' ' . $albumTitle . ':';

    $albumWriteUp = trim(
        preg_replace(
            '/\s+/',
            ' ',
            strip_tags((string) $album->description)
        )
    );

    $pageDescription = $descriptionStart . ' '
        . ($albumWriteUp !== ''
            ? $albumWriteUp
            : 'Explore the tracklist, release details and songs on TrendyBeatz.');

    $pageDescription = \Illuminate\Support\Str::limit(
        $pageDescription,
        160,
        ''
    );

    $metaKeywords = implode(', ', [
        $artistName . ' ' . $albumTitle,
        'Download ' . $artistName . ' ' . $albumTitle,
        'Stream ' . $albumTitle,
        'Download ' . $artistName . ' ' . $albumTitle . ' album',
        'download album mp3 ' . $artistName . ' ' . $albumTitle,
        'Download ' . $displayTitle,
        $artistName . ' ' . $albumTitle . ' free mp3',
        $albumTitle . ' tracklist',
        $artistName . ' albums',
        'TrendyBeatz album downloads',
    ]);

    $canonicalUrl = route('albums.show', [
        $album->id,
        $correctSlug,
    ]);

    $cover = trim((string) $album->cover_url);

    if ($cover === '') {
        $coverUrl = '';
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

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $displayTitle . ' Album')
@section('social_description', $pageDescription)

@if ($coverUrl !== '')
    @section('social_image', $coverUrl)
@endif

@section('content')
    @php
        $releaseDate = $album->released_date
            ? \Illuminate\Support\Carbon::parse($album->released_date)
            : null;

        $currentDate = now();
    @endphp

    <article class="tb-music-detail tb-music-detail-primary tb-album-detail">
        <header class="tb-music-detail-header">
            <h1>
                {{ $displayTitle }} 
            </h1>

            <div class="tb-music-detail-cover">
                <span
                    class="tb-music-detail-placeholder"
                    aria-hidden="true"
                >
                    TB
                </span>

                @if ($coverUrl !== '')
                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $displayTitle }} album cover"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <p class="tb-music-detail-posted">
                @if ($album->posted_by_name)
                Posted By:

                <a
                    class="tb-music-detail-poster-link"
                    href="{{ route(
                        'albums.posted_by',
                        \Illuminate\Support\Str::slug($album->posted_by_name)
                    ) }}"
                >
                    <strong>{{ $album->posted_by_name }}</strong>
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
                    <strong>Album Artist:</strong>
                    <span class="tb-music-detail-blue">
                        <a
                            href="{{ route('artists.show', \Illuminate\Support\Str::slug($artistName)) }}"
                            style="text-decoration: none;"
                        >
                            {{ $artistName }}
                        </a>
                    </span>
                </p>

                <p>
                    <strong>Title:</strong>
                  <span class="tb-music-detail-green">  {{ $albumTitle }}</span>
                </p>

                @if ($releaseDate)
                    <p>
                        <strong>Released On:</strong>
                        {{ $releaseDate->format('M d, Y') }}
                    </p>
                @endif

                <p>
                    <strong>Category:</strong>
                    <a
                        class="tb-music-detail-blue"
                        href="{{ route('albums.index') }}"
                        style="text-decoration: none;"
                    >
                        Music Albums
                    </a>
                </p>                

                @if (filled($album->released_year))
                    @if (filled($album->released_year))
                        <p>
                            <strong>Released Year:</strong>
                            <a
                                class="tb-music-detail-red"
                                href="{{ route('albums.year', $album->released_year) }}"
                                style="text-decoration: none;"
                            >
                                {{ $album->released_year }} Music Albums
                            </a>
                        </p>
                    @endif
                @endif


                <p>
                    <strong>Track List:</strong>
                    {{ $tracks->count() }}
                </p>
            </div>
        </section>

        <section class="tb-music-detail-section">
            <h2 class="tb-music-detail-heading">
                About This Album
            </h2>

            <div class="tb-music-detail-description">
                <p class="tb-music-detail-introduction">
                    {{ $descriptionStart }}
                </p>

                @if ($albumWriteUp !== '')
                    @foreach (
                        preg_split('/\R{2,}/', trim((string) $album->description))
                        as $paragraph
                    )
                        @if (filled(strip_tags($paragraph)))
                            <p>{{ strip_tags($paragraph) }}</p>
                        @endif
                    @endforeach
                @else
                    <p>
                        Explore {{ $displayTitle }} and discover its
                        songs below.
                    </p>
                @endif
            </div>
        </section>

        <section class="tb-music-detail-section" id="album-tracklist">
            <h2 class="tb-music-detail-heading">
                {{ $displayTitle }} Tracklist
            </h2>

            @forelse ($tracks as $song)
                <a
                    class="tb-album-detail-track"
                    href="{{ \App\Support\MusicUrl::detail($song) }}"
                >
                    <span class="tb-album-detail-track-number">
                        {{ $loop->iteration }}.
                    </span>

                    <span class="tb-album-detail-track-info">
                        <strong>
                            {{ $song->track_title }}
                        </strong>

                        @if (filled($song->featuring))
                            <small>
                                <b>feat. {{ $song->featuring }}</b>
                            </small>
                        @endif

                        <small>Tap to Stream</small>
                    </span>

                    

                    <span
                        class="tb-album-detail-track-arrow"
                        aria-hidden="true"
                    >
                        →
                    </span>
                </a>
            @empty
                <p class="tb-album-detail-empty">
                    No published songs are available for this album yet.
                </p>
            @endforelse
        </section>
    </article>


    {{-- The share section  --}}

    <section class="tb-share-card">
        <p class="tb-share-heading">
            🔗 Share {{ $displayTitle }} with others on
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

    <div class="tb-music-detail tb-music-detail-related tb-album-detail">
        @if ($otherAlbums->isNotEmpty())
            <section class="tb-music-detail-discovery">
                <h2 class="tb-music-detail-heading">
                    More Albums by {{ $artistName }}
                </h2>

                <div class="tb-music-detail-discovery-list">
                    @foreach ($otherAlbums as $otherAlbum)
                        @php
                            $otherSlug = \Illuminate\Support\Str::slug(
                                $artistName . ' ' . $otherAlbum->title
                            );
                        @endphp

                        <a
                            href="{{ route('albums.show', [
                                $otherAlbum->id,
                                $otherSlug,
                            ]) }}"
                        >
                            <span
                                class="tb-music-detail-icon"
                                aria-hidden="true"
                            >
                                ♫
                            </span>

                            <span class="tb-music-detail-discovery-text">
                                <strong>
                                    {{ $artistName }} -
                                    {{ $otherAlbum->title }}
                                </strong>

                                @if (filled($otherAlbum->released_year))
                                    <small>
                                        Released in
                                        {{ $otherAlbum->released_year }}
                                    </small>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <p class="tb-home-view-all">
            <a href="{{ route('albums.index') }}">
                Browse All Albums →
            </a>
        </p>
    </div>


    @include('partials.comments', [
        'postType' => 'album',
        'postId' => $album->id,
        'postTitle' => $displayTitle,
    ])
@endsection