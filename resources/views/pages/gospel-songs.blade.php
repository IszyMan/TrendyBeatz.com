@extends('layouts.app')

@php
    $pageTitle = 'Discover Latest Gospel / Naija Gospel Worship Songs/Music Here | Legal Download | Trendybeatz';

    $pageDescription = 'Discover, Stream and Download Latest and Popular Gospel Songs in Nigeria, Ghana and Africa. Gospel Music and Songs can be gotten here first-hand, Download only the latest';

    $pageKeywords = 'download gospel songs, download gospel music, gospel music, gospel songs, download naija gospel songs, download ghana gospel songs, download african gospel songs';

    $canonicalUrl = $songs->currentPage() === 1
        ? route('music.gospel')
        : $songs->url($songs->currentPage());

    $firstCover = trim(
        (string) ($songs->first()->cover_url ?? '')
    );

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
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $pageTitle)
@section('social_description', $pageDescription)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif

@if ($songs->currentPage() > 1)
    @section(
        'previous_url',
        $songs->currentPage() === 2
            ? route('music.gospel')
            : $songs->previousPageUrl()
    )
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
<section class="tb-gospel-page">

    <h1 class="section-heading">
        Discover Latest Nigerian Gospel Songs
        and African Gospel Music
    </h1>

    @php
        $currentDate = now();
    @endphp

    <p class="home-date">
        <time datetime="{{ $currentDate->toDateString() }}">
            {{ $currentDate->format('M d, Y') }}
        </time>
    </p>

    @include('partials.home.songs', [
        'id' => 'gospel-song-list',
        'heading' => 'Latest Gospel Songs',
        'badge' => 'Gospel Music',
        'items' => $songs,
        'more' => null,
    ])

    @include('partials.pagination', [
        'paginator' => $songs,
    ])

</section>
@endsection