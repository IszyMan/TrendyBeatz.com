@extends('layouts.app')

@php
    $pageNumber = $songs->currentPage();

    $pageTitle = 'Download Latest Top Rated Songs Here';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Discover top rated songs on TrendyBeatz. '
        . 'Browse popular Nigerian music and Afrobeats tracks, '
        . 'and open each song to stream or download.';

    $keywords = 'top rated songs, latest top rated songs, '
        . 'top rated Nigerian songs, best Naija music, '
        . 'latest Naija music mp3 download, Nigerian music mp3, '
        . 'popular Afrobeats songs, download Nigerian songs, '
        . 'TrendyBeatz';

    $canonical = $pageNumber === 1
        ? route('songs.top_rated')
        : route('songs.top_rated', ['page' => $pageNumber]);

    $dateNow = now('Africa/Lagos');
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $description)
@section('meta_keywords', $keywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <article class="tb-top-rated-songs-page">
        <header>
            <h1 class="section-heading">
                Download Latest Top Rated Songs
            </h1>

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>

          
        </header>

        @include('partials.home.songs', [
            'id' => 'top-rated-songs',
            'heading' => 'Explore top rated songs on TrendyBeatz. ',
            'items' => $songs,
            'badge' => 'Top Rated',
            'more' => null,
        ])

        @if ($songs->hasPages())
            @include('partials.pagination', [
                'paginator' => $songs,
            ])
        @endif
    </article>
@endsection