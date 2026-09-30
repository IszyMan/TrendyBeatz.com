@extends('layouts.app')

@php
    $pageTitle = 'Popular Albums Download - Download Latest Music Albums Mp3 | TrendyBeatz';

    $pageDescription = 'Discover popular Nigerian and African albums and EPs on TrendyBeatz. Browse tracklists, check release dates, and stream or download your favourite music.';

    $pageKeywords = 'popular albums, popular albums download, '
        . 'download music albums mp3, popular Nigerian albums, '
        . 'popular African albums, Naija music albums, '
        . 'latest music albums, Nigerian EPs, African EPs, '
        . 'Afrobeats albums, Afropop albums, hip hop albums, '
        . 'full album downloads, album tracklists, album release dates, '
        . 'Burna Boy albums, Davido albums, Wizkid albums, '
        . 'Rema albums, Asake albums, Olamide albums, '
        . 'Kizz Daniel albums, TrendyBeatz albums';

    $baseUrl = route('albums.popular');

    $canonicalUrl = $albums->currentPage() === 1
        ? $baseUrl
        : $albums->url($albums->currentPage());

    $firstCover = trim((string) ($albums->first()->cover_url ?? ''));

    if ($firstCover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $firstCover)) {
        $socialImage = $firstCover;
    } else {
        $coverPath = ltrim($firstCover, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/')
                ? $coverPath
                : 'images/' . $coverPath
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_keywords', $pageKeywords)
@section('meta_description', $pageDescription)
@section('canonical', $canonicalUrl)
@section('social_title', $pageTitle)
@section('social_description', $pageDescription)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif

@if ($albums->currentPage() > 1)
    @section(
        'previous_url',
        $albums->currentPage() === 2
            ? $baseUrl
            : $albums->previousPageUrl()
    )
@endif

@if ($albums->hasMorePages())
    @section('next_url', $albums->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
       
        @php
            $currentDate = now();
        @endphp

        <p class="home-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.albums', [
            'id' => 'popular-albums',
            'heading' => 'Popular Albums / EPs',
            'items' => $albums,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $albums,
        ])
    </section>
@endsection