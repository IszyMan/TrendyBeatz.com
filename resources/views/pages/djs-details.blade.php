@extends('layouts.app')

@php
    $djName = trim((string) $dj->dj_name);

    $pageNumber = $mixes->currentPage();

    $pageTitle = 'Download Latest ' . $djName
        . ' Mix, DJ Mixtape, and Read Biography';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Explore ' . $djName
        . ' mixtapes on TrendyBeatz. Browse recent DJ mixes, '
        . 'discover releases and open each mixtape for listening '
        . 'and available downloads.';

    $keywords = $djName . ' mixes, '
        . $djName . ' mixtapes, '
        . 'download ' . $djName . ' DJ mix, '
        . $djName . ' biography';

    $canonical = $pageNumber === 1
        ? route('djs.show', $slug)
        : route('djs.show', [
            'slug' => $slug,
            'page' => $pageNumber,
        ]);

    $imageUrl = static function ($value) {
        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        return asset(
            str_starts_with($path, 'images/')
                ? $path
                : 'images/' . $path
        );
    };

    $photoUrl = $imageUrl($dj->photo ?? '');

    $biography = trim(strip_tags((string) ($dj->place_of_birth ?? '')));

    $awards = trim(strip_tags((string) ($dj->awards ?? '')));
    $endorsements = trim(strip_tags((string) ($dj->endorsements ?? '')));
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_keywords', $keywords)
@section('meta_description', $description)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@if ($photoUrl)
    @section('social_image', $photoUrl)
@endif

@section('content')
    <article class="tb-dj-detail">
        <header class="tb-dj-detail-heading">
            <h1 class="section-heading">
                {{ $djName }} Mixtapes and DJ Profile
            </h1>
        </header>

        <section class="tb-dj-detail-profile" aria-label="DJ profile">
            <div class="tb-dj-detail-photo">
                <span class="tb-dj-detail-placeholder" aria-hidden="true">
                    DJ
                </span>

                @if ($photoUrl)
                    <img
                        src="{{ $photoUrl }}"
                        alt="{{ $djName }} profile"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <div class="tb-dj-detail-facts">
                <p>
                    <strong>Names:</strong>
                    <span>{{ $dj->fullname }}</span>
                </p>

                <p>
                    <strong>Also Known As:</strong>
                    <span>{{ $djName }}</span>
                </p>

                
            </div>
        </section>

        @if ($biography !== '')
            <section class="tb-dj-detail-bio">
                <h2 class="sub-section-heading">
                    About {{ $djName }}
                </h2>

                @foreach (preg_split('/\R\s*\R/', $biography) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{{ $paragraph }}</p>
                    @endif
                @endforeach
            </section>
        @endif

        <section class="tb-dj-detail-section">
            <h2 class="tb-dj-detail-intro">
                Discover {{ $djName }} Mixtapes
            </h2>

            <div class="tb-dj-mixtape-list">
                @forelse ($mixes as $mixtape)
                    @php
                        $coverUrl = $imageUrl($mixtape->cover_url);

                        $mixtape->dj_name = $djName;

                        
                    @endphp

                    <a
                        class="tb-dj-mixtape"
                        href="{{ \App\Support\DjMixUrl::detail($mixtape) }}"
                    >
                        <span class="tb-dj-mixtape-cover">
                            <span aria-hidden="true">DJ Mix</span>

                            @if ($coverUrl)
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="{{ $djName }} - {{ $mixtape->mix_title }} cover"
                                    loading="lazy"
                                    onerror="this.remove()"
                                >
                            @endif
                        </span>

                        <span class="tb-dj-mixtape-info">
                            <span class="tb-dj-mixtape-name">
                                {{ $djName }}
                            </span>

                            <strong class="tb-dj-mixtape-title">
                                {{ $mixtape->mix_title }}
                            </strong>

                            <span class="tb-dj-mixtape-action">
                                Listen / Download →
                            </span>
                        </span>
                    </a>
                @empty
                    <p class="tb-dj-detail-empty">
                        No published mixtapes available yet.
                    </p>
                @endforelse
            </div>

            

        <p class="tb-home-view-all">
            <a href="{{ route('djs.index') }}">
                ← Browse All DJs
            </a>
        </p>
    </article>
@endsection