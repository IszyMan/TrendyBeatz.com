@extends('layouts.app')

@php
    $pageNumber = $songs->currentPage();

    $pageTitle = 'Download Top 10 Trending Songs of The Week Here';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Discover trending Nigerian songs on TrendyBeatz. '
        . 'Browse our songs of the day, explore the latest music '
        . 'and stream or download your favourite tracks.';

    $keywords = 'top trending songs, top 10 trending songs, '
        . 'trending songs in Nigeria, songs of the day, '
        . 'latest Naija music, Nigerian music mp3 download, '
        . 'trending Afrobeats songs, TrendyBeatz';

    $canonical = $pageNumber === 1
        ? route('songs.trending')
        : route('songs.trending', ['page' => $pageNumber]);

    $dateNow = now('Africa/Lagos');
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $description)
@section('meta_keywords', $keywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <article class="tb-trending-songs-page">
        <header>
            <h1 class="section-heading">
                Top 10 Trending Songs
            </h1>

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>

           <!-- <p>
                Discover trending songs selected from our songs of
                the day. Browse the newest selections first and
                open each song to stream or download.
            </p>-->
        </header>

        @include('partials.home.songs', [
            'id' => 'top-trending-songs',
            'heading' => 'Latest Trending Songs',
            'items' => $songs,
            'badge' => 'Trending',
            'more' => null,
        ])

        @if ($songs->hasPages())
            @include('partials.pagination', [
                'paginator' => $songs,
            ])
        @endif
    </article>
@endsection