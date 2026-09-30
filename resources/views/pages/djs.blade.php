@extends('layouts.app')

@php
    $pageNumber = $djs->currentPage();

    $pageTitle = 'DJ Profiles & Full Mix';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Explore Nigerian, Ghanaian and African DJ profiles '
        . 'on TrendyBeatz. Discover your favourite DJs, read their '
        . 'biographies and browse their latest mixes and mixtapes.';

    $keywords = 'DJ profiles, latest DJ mix, DJ mixtapes, '
        . 'Nigerian DJs, Ghanaian DJs, African DJs, '
        . 'DJ Spinall, DJ Neptune, DJ Cuppy';

    $canonical = $pageNumber === 1
        ? route('djs.index')
        : route('djs.index', ['page' => $pageNumber]);
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_keywords', $keywords)
@section('meta_description', $description)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-artists-page tb-djs-page">
        <header class="tb-artists-header">
            <h1 class="section-heading">
               Discover Nigerian, Ghanaian and African DJ Profiles & Mixes
            </h1>

          <!--  <p class="tb-artists-intro">
                Discover your favourite DJs and explore their mixtapes
                on TrendyBeatz. Open a DJ’s profile to read about them,
                browse their releases and find listening and download
                options.
            </p>-->

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>
        </header>

        <section class="tb-artists-popular">
            <h2 class="sub-section-heading">
                Browse DJ Profiles
            </h2>

            <div class="tb-artists-grid">
                @forelse ($djs as $dj)
                    @php
                        $djName = trim((string) $dj->dj_name);

                        $djSlug = trim((string) ($dj->slug ?? ''));

                        if ($djSlug === '') {
                            $djSlug = \Illuminate\Support\Str::slug($djName);
                        }

                        $photo = trim((string) ($dj->photo ?? ''));

                        if ($photo === '') {
                            $photoUrl = null;
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

                    <a
                        class="tb-artist-card"
                        href="{{ route('djs.show', $djSlug) }}"
                    >
                        <span class="tb-artist-card-photo">
                            <span
                                class="tb-artist-card-placeholder"
                                aria-hidden="true"
                            >
                                DJ
                            </span>

                            @if ($photoUrl)
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="{{ $djName }} profile"
                                    loading="lazy"
                                    onerror="this.remove()"
                                >
                            @endif
                        </span>

                        <strong class="tb-artist-card-name">
                            {{ $djName }}
                        </strong>

                        <span class="tb-artist-card-action">
                            View Profile & Mixtapes →
                        </span>
                    </a>
                @empty
                    <p class="tb-artists-empty">
                        No published DJ profiles available yet.
                    </p>
                @endforelse
            </div>
        </section>

        @if ($djs->hasPages())
            @include('partials.pagination', [
                'paginator' => $djs,
            ])
        @endif
    </section>
@endsection