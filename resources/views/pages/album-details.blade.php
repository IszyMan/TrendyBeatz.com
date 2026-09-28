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
                        {{ $artistName }}
                    </span>
                </p>

                <p>
                    <strong>Title:</strong>
                    {{ $albumTitle }}
                </p>

                @if ($releaseDate)
                    <p>
                        <strong>Released On:</strong>
                        {{ $releaseDate->format('M d, Y') }}
                    </p>
                @endif

                <p>
                    <strong>Category:</strong>
                    Music Albums
                </p>

                <p>
                    <strong>Track List:</strong>
                    {{ $tracks->count() }}
                </p>

                @if (filled($album->released_year))
                    <p>
                        <strong>Released Year:</strong>
                        {{ $album->released_year }}
                    </p>
                @endif
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
                            {{ $artistName }} - {{ $song->track_title }}
                        </strong>

                        @if (filled($song->featuring))
                            <small>
                                feat. {{ $song->featuring }}
                            </small>
                        @endif
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

        <p class="tb-music-detail-more">
            <a href="{{ route('albums.index') }}">
                Browse All Albums →
            </a>
        </p>
    </div>
@endsection