@extends('layouts.app')

@php
    $pageTitle = 'Download Latest Music Video Posted by '
        . $poster->name
        . ' | TrendyBeatz';

    $pageDescription = 'Watch and discover the latest music videos posted by '
        . $poster->name
        . ' on TrendyBeatz. Browse Nigerian and African video releases, '
        . 'explore artists, and find videos available to download.';

    $baseUrl = route('videos.posted_by', [
        \Illuminate\Support\Str::slug($poster->name),
    ]);

    $canonicalUrl = $videos->currentPage() === 1
        ? $baseUrl
        : $videos->url($videos->currentPage());

    $firstCover = trim((string) ($videos->first()->cover_url ?? ''));

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

@if ($videos->currentPage() > 1)
    @section(
        'previous_url',
        $videos->currentPage() === 2
            ? $baseUrl
            : $videos->previousPageUrl()
    )
@endif

@if ($videos->hasMorePages())
    @section('next_url', $videos->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            Download Latest Music Video Posted By {{ $poster->name }}
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
            'id' => 'videos-posted-by-' . \Illuminate\Support\Str::slug($poster->name),
            'heading' => 'Music Videos Posted By ' . $poster->name,
            'items' => $videos,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $videos,
        ])
    </section>
@endsection