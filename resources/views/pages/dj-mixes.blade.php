@extends('layouts.app')

@php
    $pageTitle = 'Dj Mix - Download Latest Naija DJ Mix - DJ Mixtape | Trendybeatz';

    $pageDescription = 'Discover and listen to the latest Naija DJ mixes and African mixtapes on TrendyBeatz. Explore new Afrobeats mixes and find DJ mixtapes to stream or download.';

    $canonicalUrl = $mixes->currentPage() === 1
        ? route('mixes.index')
        : $mixes->url($mixes->currentPage());

    $firstCover = trim((string) ($mixes->first()->cover_url ?? ''));

    if ($firstCover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $firstCover)) {
        $socialImage = $firstCover;
    } else {
        $coverPath = ltrim($firstCover, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/')
                ? $coverPath
                : 'images/dj/' . basename($coverPath)
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

@if ($mixes->currentPage() > 1)
    @section(
        'previous_url',
        $mixes->currentPage() === 2
            ? route('mixes.index')
            : $mixes->previousPageUrl()
    )
@endif

@if ($mixes->hasMorePages())
    @section('next_url', $mixes->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            Dj Mix - Download Latest Naija Dj Mix/mixtapes
        </h1>

        @php
            $currentDate = now();
        @endphp

        <p class="home-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.mixes', [
            'id' => 'latest-dj-mix',
            'heading' => 'Latest DJ Mix',
            'items' => $mixes,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $mixes,
        ])
    </section>
@endsection