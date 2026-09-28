@extends('layouts.app')

@php
    $pageTitle = 'Download Latest Nigerian Highlife Music / Ghana Highlife Songs/Music Mp3 Here | Trendybeatz';

    $pageDescription = 'Download Latest and Popular Highlife Music in Nigeria, Ghana and Africa. HighLife Music and Songs can be gotten here first-hand, Download only the latest';

    $pageKeywords = 'download highlife songs, download highlife music, highlife music, highlife songs, download naija highlife songs, download ghana highlife songs, download african highlife songs';

    $canonicalUrl = $songs->currentPage() === 1
        ? route('music.highlife')
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
            ? route('music.highlife')
            : $songs->previousPageUrl()
    )
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
<section class="tb-highlife-page">

    <h1 class="section-heading">
        Download Latest Nigerian Highlife Music,
        Ghana Highlife and African High Life Songs Mp3
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
        'id' => 'highlife-song-list',
        'heading' => 'Latest Highlife Songs',
        'badge' => 'Highlife Music',
        'items' => $songs,
        'more' => null,
    ])

    @include('partials.pagination', [
        'paginator' => $songs,
    ])

</section>
@endsection