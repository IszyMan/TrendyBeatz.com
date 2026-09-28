@extends('layouts.app')

@php
    $posterSlug = \Illuminate\Support\Str::slug($poster->name);

    $pageTitle = 'Albums - Download Latest Music Albums Posted by '
        . $poster->name
        . ' | TrendyBeatz';

    $pageDescription = 'Explore music albums and EPs posted by '
        . $poster->name
        . ' on TrendyBeatz. Browse album covers, release dates'
        . ' and tracklists from Nigerian, Ghanaian and African artists.';

    $baseUrl = route('albums.posted_by', $posterSlug);

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
            Download Latest Albums Posted By: {{ $poster->name }}
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
            'id' => 'albums-posted-by-' . $posterSlug,
            'heading' => 'Music Albums Posted By ' . $poster->name,
            'items' => $albums,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $albums,
        ])
    </section>
@endsection