@extends('layouts.app')

@php
    $pageTitle = $query !== ''
        ? 'Search results for ' . $query . ' — TrendyBeatz'
        : 'Search Music, Videos, Albums and More — TrendyBeatz';

    $description = 'Search TrendyBeatz for songs, videos, DJ mixes, albums, blog posts, artists and DJs.';

    $types = [
        'all' => 'All Results',
        'music' => 'Music',
        'video' => 'Video',
        'mix' => 'Mix',
        'album' => 'Album',
        'blog' => 'Blog',
        'artist' => 'Artists',
        'dj' => 'DJ',
    ];
@endphp

@section('title', $pageTitle)
@section('meta_description', $description)
@section('meta_keywords', 'TrendyBeatz search, music, videos, DJ mixes, albums, artists, DJs, blogs')
@section('canonical', request()->fullUrl())
@section('social_title', $pageTitle)
@section('social_description', $description)

@section('content')
    <style>
        .tb-search-page {
            max-width: 960px;
            margin: 24px auto;
            padding: 0 16px;
            color: #25332b;
        }

        .tb-search-page * {
            box-sizing: border-box;
        }

        .tb-search-heading {
            margin: 0 0 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #198754;
            font-size: 28px;
            line-height: 1.3;
        }

        .tb-search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .tb-search-input {
            flex: 1 1 250px;
            min-width: 0;
        }

        .tb-search-input,
        .tb-search-select {
            padding: 12px 14px;
            border: 1px solid #ccd7cf;
            border-radius: 5px;
            background: #fff;
            color: #222;
            font: inherit;
        }

        .tb-search-input:focus,
        .tb-search-select:focus {
            outline: 2px solid #198754;
            outline-offset: 2px;
        }

        .tb-search-button {
            padding: 12px 20px;
            border: 0;
            border-radius: 5px;
            background: #198754;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .tb-search-button:hover {
            background: #146c43;
        }

        .tb-search-help,
        .tb-search-summary {
            font-size: 14px;
            line-height: 1.7;
            color: #647168;
        }

        .tb-search-summary {
            margin: 22px 0 14px;
        }

        .tb-search-results {
            display: grid;
            gap: 12px;
        }

        .tb-search-result {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px;
            border: 1px solid #e0e6e2;
            border-radius: 6px;
            background: #fff;
            color: #25332b;
            text-decoration: none;
        }

        .tb-search-result:hover {
            border-color: #198754;
            background: #f7faf8;
        }

        .tb-search-result:focus-visible {
            outline: 3px solid #198754;
            outline-offset: 3px;
        }

        .tb-search-cover {
            position: relative;
            display: grid;
            place-items: center;
            flex: 0 0 82px;
            width: 82px;
            height: 82px;
            overflow: hidden;
            border-radius: 5px;
            background: #edf3ef;
            color: #198754;
            font-size: 13px;
            font-weight: 800;
        }

        .tb-search-cover img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tb-search-info {
            flex: 1;
            min-width: 0;
        }

        .tb-search-badge {
            display: inline-block;
            margin-bottom: 7px;
            padding: 4px 10px;
            border-radius: 4px;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.5;
        }

        /* Music — green */
        .tb-search-badge-music {
            background: #157347;
        }

        /* Video — blue */
        .tb-search-badge-video {
            background: #2457c5;
        }

        /* Mix — purple */
        .tb-search-badge-mix {
            background: #7636ad;
        }

        /* Album — brown-orange */
        .tb-search-badge-album {
            background: #a65310;
        }

        /* Blog — teal */
        .tb-search-badge-blog {
            background: #087780;
        }

        /* Artists — pink */
        .tb-search-badge-artist {
            background: #b52968;
        }

        /* DJ — red */
        .tb-search-badge-dj {
            background: #bd3030;
        }

        .tb-search-title {
            display: block;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .tb-search-date {
            display: block;
            margin-top: 6px;
            color: #758078;
            font-size: 12px;
        }

        .tb-search-empty {
            margin-top: 22px;
            padding: 22px;
            border: 1px solid #dce8df;
            border-radius: 6px;
            background: #f3f8f5;
            font-size: 15px;
            line-height: 1.8;
        }

        .tb-search-error {
            color: #b42318;
            font-size: 14px;
        }

        .tb-search-label {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .tb-search-artist {
            display: block;
            margin-bottom: 4px;
            color: #14251b;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .tb-search-track {
            display: block;
            color: #526158;
            font-size: 14px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        @media (max-width: 600px) {
            .tb-search-artist {
                font-size: 15px;
            }

            .tb-search-track {
                font-size: 13px;
            }
        }

        @media (max-width: 600px) {
            .tb-search-page {
                padding: 0 12px;
            }

            .tb-search-heading {
                font-size: 24px;
            }

            .tb-search-input {
                flex-basis: 100%;
            }

            .tb-search-select {
                flex: 1;
                min-width: 0;
            }

            .tb-search-result {
                gap: 12px;
                padding: 12px;
            }

            .tb-search-cover {
                flex-basis: 65px;
                width: 65px;
                height: 65px;
            }

            .tb-search-title {
                font-size: 14px;
            }
        }
    </style>

    <section class="tb-search-page">
        <h1 class="tb-search-heading">Search TrendyBeatz</h1>

        <form
            class="tb-search-form"
            action="{{ route('search') }}"
            method="GET"
            role="search"
        >
            <label class="tb-search-label" for="tb-search-query">
                Search by artist, title or featured artist
            </label>

            <input
                class="tb-search-input"
                id="tb-search-query"
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="Search artist, song, album, DJ or blog..."
                maxlength="200"
                required
            >

            <label class="tb-search-label" for="tb-search-type">
                Content type
            </label>

            <select
                class="tb-search-select"
                id="tb-search-type"
                name="type"
            >
                @foreach ($types as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected($type === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button class="tb-search-button" type="submit">
                Search
            </button>
        </form>

        <p class="tb-search-help">
            Search by artist, title or featured artist.
            Try “Wizkid Essence”. Results appear newest first.
        </p>

        @error('q')
            <p class="tb-search-error">{{ $message }}</p>
        @enderror

        @error('type')
            <p class="tb-search-error">{{ $message }}</p>
        @enderror

        @if ($results)
            <p class="tb-search-summary">
                {{ number_format($results->total()) }}
                {{ $results->total() === 1 ? 'result' : 'results' }}
                for <strong>“{{ $query }}”</strong>
            </p>

            <div class="tb-search-results">
                @forelse ($results as $result)
                    @php
                        $hasArtist = in_array(
                            $result->type,
                            ['music', 'video', 'mix', 'album'],
                            true
                        );

                        $resultTitle = trim((string) $result->title);
                        $featuring = trim((string) $result->featuring);

                        if (
                            $hasArtist
                            && $featuring !== ''
                            && !preg_match(
                                '/\b(?:ft|feat|featuring)\.?\s/i',
                                $resultTitle
                            )
                        ) {
                            $resultTitle .= ' feat. ' . $featuring;
                        }
                    @endphp

                    <a class="tb-search-result" href="{{ $result->url }}">
                        <span class="tb-search-cover">
                            <span aria-hidden="true">
                                {{ $types[$result->type] }}
                            </span>

                            @if ($result->image_url)
                                <img
                                    src="{{ $result->image_url }}"
                                    alt=""
                                    loading="lazy"
                                    onerror="this.remove()"
                                >
                            @endif
                        </span>

                        <span class="tb-search-info">
                            <span
                                class="tb-search-badge tb-search-badge-{{ $result->type }}"
                            >
                                {{ $types[$result->type] }}
                            </span>

                            @if ($hasArtist)
                                <strong class="tb-search-artist">
                                    {{ $result->artist_name }}
                                </strong>

                                <span class="tb-search-track">
                                    {{ $resultTitle }}
                                </span>
                            @else
                                <strong class="tb-search-title">
                                    {{ $result->title }}
                                </strong>
                            @endif
                        </span>
                    </a>
                @empty
                    <div class="tb-search-empty">
                        No results found. Try fewer words, another
                        spelling or select All Results.
                    </div>
                @endforelse
            </div>

            @include('partials.pagination', [
                'paginator' => $results,
            ])
        @elseif ($query !== '')
            <div class="tb-search-empty">
                Enter an artist name or title containing letters or numbers.
            </div>
        @else
            <div class="tb-search-empty">
                Search music, videos, DJ mixes, albums, blog posts,
                artists and DJs using the form above.
            </div>
        @endif
    </section>
@endsection