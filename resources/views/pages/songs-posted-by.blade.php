@extends('layouts.app')

@php
    $pageTitle = 'Download Latest Songs Posted by '
        . $poster->name
        . ' | Trendybeatz';

    $pageDescription = 'Discover the latest songs posted by '. $poster->name . ' on TrendyBeatz. Explore new music, stream tracks, and find available downloads from your favourite artists.';

    $pageKeywords = 'download music mp3, latests songs, 2Baba, tekno, tiwa savage, burna boy, zlatan, mi, ycee, flavour,phyno, rema, olamide, d prince, akon, timaya, spinall, neptune, cuppy, kizz daniel, spinall, neptune, kizz daniel naija musics, nigerian songs, dj cuppy, new music, ycee, tekno, download music mp3, akon';

    $canonicalUrl = $songs->currentPage() === 1
        ? route('songs.posted_by', [
            'slug' => \Illuminate\Support\Str::slug($poster->name),
        ])
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
            ? route('songs.posted_by', [
                'slug' => \Illuminate\Support\Str::slug(
                    $poster->name
                ),
            ])
            : $songs->previousPageUrl()
    )
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
    <section class="tb-posted-songs-page">
        <h1 class="section-heading">
            Download Latest Songs Posted By {{ $poster->name }}
        </h1>

        <p class="tb-posted-songs-date">
            <time datetime="{{ now()->toDateString() }}">
                {{ now()->format('M d, Y') }}
            </time>
        </p>

        @include('partials.home.songs', [
            'id' => 'songs-posted-by-' . \Illuminate\Support\Str::slug(
                $poster->name
            ),
            'heading' => 'Latest Songs Posted By ' . $poster->name,
            'badge' => 'Music',
            'items' => $songs,
            'more' => null,
        ])

        @include('partials.pagination', [
            'paginator' => $songs,
        ])
    </section>
@endsection