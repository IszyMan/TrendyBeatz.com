@extends('layouts.app')

@php
    $pageTitle = 'Album Download - Download Latest Music Albums Mp3'
        . ' » TrendyBeatz';

    $pageDescription = 'Discover the latest Nigerian, Ghanaian and'
        . ' African music albums and EPs on TrendyBeatz. Browse album'
        . ' covers, release dates and tracklists, then explore songs'
        . ' from your favorite artists.';

    $baseUrl = route('albums.index');

    $canonicalUrl = $albums->currentPage() === 1
        ? $baseUrl
        : $albums->url($albums->currentPage());

    $firstCover = trim((string) (
        $albums->first()->cover_url ?? ''
    ));

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
        <h1 class="section-heading">
            Download Latest Music Albums, Nigerian Music Albums,
            Ghana Music Albums, and African Albums
        </h1>

        @php
            $currentDate = now();
        @endphp

        <p class="tb-day-page-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.albums', [
            'id' => 'all-artist-albums',
            'heading' => 'Latest Albums and EPs',
            'items' => $albums,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $albums,
        ])
    </section>
@endsection