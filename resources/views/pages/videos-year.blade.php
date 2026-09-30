@extends('layouts.app')

@php
    $pageNumber = $videos->currentPage();

    $pageTitle = 'Download ' . $year
        . ' Music Videos - Nigerian & African Videos';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Watch ' . $year
        . ' Nigerian, Ghanaian and African music videos on TrendyBeatz. '
        . 'Explore top notch Mp4, discover artists and find available video.';

    $keywords = $year . ' music videos, '
        . $year . ' Nigerian music videos, '
        . $year . ' Naija videos, '
        . $year . ' African music videos, '
        . 'download ' . $year . ' videos';

    $canonical = $pageNumber === 1
        ? route('videos.year', ['year' => $year])
        : route('videos.year', [
            'year' => $year,
            'page' => $pageNumber,
        ]);
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $description)
@section('meta_keywords', $keywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-videos-year">
        <header class="tb-videos-year-header">
            <h1 class="section-heading">
                Download {{ $year }} Music Videos
            </h1>

            <p class="tb-videos-year-description">
                Explore Nigerian, Ghanaian and African music videos released in
                {{ $year }}.
            </p>
        </header>

        @include('partials.home.videos', [
            'items' => $videos,
            'id' => 'videos-' . $year,
            'heading' => $year . ' Music Videos',
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $videos,
        ])
    </section>
@endsection