@extends('layouts.app')

@php
    $pageTitle = $year
        . ' Songs - Download Latest Music Released in '
        . $year
        . ' | TrendyBeatz';

    $pageDescription = 'Discover songs released in '
        . $year
        . ' on TrendyBeatz. Browse Naija, Ghanaian and African music, '
        . 'find your favourite artists, and stream or download '
        . $year
        . ' songs.';

    $pageKeywords = $year . ' songs, '
        . $year . ' music, songs released in ' . $year . ', '
        . 'latest ' . $year . ' songs, '
        . 'download ' . $year . ' music, '
        . 'Naija songs ' . $year . ', '
        . 'Ghana music ' . $year . ', '
        . 'African music ' . $year;

    $baseUrl = route('music.year', $year);

    $canonicalUrl = $songs->currentPage() === 1
        ? $baseUrl
        : $songs->url($songs->currentPage());

    $firstCover = trim((string) ($songs->first()->cover_url ?? ''));

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
            ? $baseUrl
            : $songs->previousPageUrl()
    )
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            Songs Released in {{ $year }}
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
            'id' => 'songs-released-in-' . $year,
            'heading' => 'Latest Songs From ' . $year,
            'badge' => $year . ' Music',
            'items' => $songs,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $songs,
        ])
    </section>
@endsection