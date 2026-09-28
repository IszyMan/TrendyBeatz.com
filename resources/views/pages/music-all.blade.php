@extends('layouts.app')

@php
    $pageTitle = 'Download Latest Music Mp3 Here | TrendyBeatz';

    $pageDescription = 'Explore the latest music on TrendyBeatz.'
        . ' Discover published songs from Nigerian, Ghanaian and'
        . ' other African artists, highlife & gospel, browse new releases, and find'
        . ' tracks to stream or download.';

    $baseUrl = route('music.all');

    $canonicalUrl = $songs->currentPage() === 1
        ? $baseUrl
        : $songs->url($songs->currentPage());

    $firstCover = trim((string) (
        $songs->first()->cover_url ?? ''
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
            Download Latest Music Mp3
        </h1>

        @php
            $currentDate = now();
        @endphp

        <p class="tb-day-page-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.songs', [
            'id' => 'all-music',
            'heading' => 'All Latest Music',
            'badge' => 'Music',
            'items' => $songs,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $songs,
        ])
    </section>
@endsection