@extends('layouts.app')

@php
    $pageTitle = 'Video - Download Latest Videos - Music Videos and Comedy Videos';

    $pageDescription = 'Download Latest Videos - Music Videos and Comedy Videos and mp4, Naija Music Videos, Latest Comedy Videos, Latest Music Videos';

    $pageKeywords = 'download latest video, latest video, latest naija music video, latest naija video, download latest naija video, Latest Comedy Videos, download comedy video';

    $canonicalUrl = $videos->currentPage() === 1
        ? route('videos.index')
        : $videos->url($videos->currentPage());

    $firstCover = trim((string) ($videos->first()->cover_url ?? ''));

    if ($firstCover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $firstCover)) {
        $socialImage = $firstCover;
    } else {
        $path = ltrim($firstCover, '/');

        $socialImage = asset(
            str_starts_with($path, 'images/')
                ? $path
                : 'images/' . $path
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

@if ($videos->currentPage() > 1)
    @section(
        'previous_url',
        $videos->currentPage() === 2
            ? route('videos.index')
            : $videos->previousPageUrl()
    )
@endif

@if ($videos->hasMorePages())
    @section('next_url', $videos->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            Video - Download Latest Videos -
            Music Videos and Comedy Videos Here
        </h1>

        @php
            $currentDate = now();
        @endphp

        <p class="tb-day-page-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.videos', [
            'id' => 'latest-videos',
            'heading' => 'All Latest Videos',
            'items' => $videos,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $videos,
        ])
    </section>
@endsection