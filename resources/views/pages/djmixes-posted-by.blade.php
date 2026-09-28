@extends('layouts.app')

@php
    $posterName = $poster->name;

    $pageTitle = 'Dj Mix - Download Latest DJ Mix Posted By '
        . $posterName
        . ' | TrendyBeatz';

    $pageDescription = 'Discover DJ mixes posted by '
        . $posterName
        . ' on TrendyBeatz. Listen to the latest Naija mixtapes, '
        . 'Afrobeats DJ mixes and new releases, or find a mix to download.';

    $baseUrl = route('mixes.posted_by', $posterSlug);

    $canonicalUrl = $mixes->currentPage() === 1
        ? $baseUrl
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
            ? $baseUrl
            : $mixes->previousPageUrl()
    )
@endif

@if ($mixes->hasMorePages())
    @section('next_url', $mixes->nextPageUrl())
@endif

@section('content')
    <section class="tb-day-page">
        <h1 class="section-heading">
            DjMix - Download Latest Dj Mixtapes Posted by
            {{ $posterName }}
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
            'id' => 'dj-mixes-posted-by',
            'heading' => 'Latest DJ Mix Posted by ' . $posterName,
            'items' => $mixes,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $mixes,
        ])
    </section>
@endsection