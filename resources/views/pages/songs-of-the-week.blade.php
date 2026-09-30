@extends('layouts.app')

@php
    $pageNumber = $songs->currentPage();

    $pageTitle = 'Download Latest And Trending Songs of The Week Here';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Discover songs of the week on TrendyBeatz. '
        . 'Explore featured Naija and African music, find new artists '
        . 'and stream songs or find available downloads.';

    $keywords = 'songs of the week, trending songs, '
        . 'Naija songs of the week, Nigerian music, African music, '
        . 'weekly music picks, Afrobeats songs, latest Naija songs, '
        . 'stream music, music downloads, TrendyBeatz';

    $canonical = $pageNumber === 1
        ? route('songs.week')
        : route('songs.week', ['page' => $pageNumber]);

    $dateNow = now('Africa/Lagos');
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $description)
@section('meta_keywords', $keywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-songs-week-page">
        <header>
            <h1 class="section-heading">
                Latest and Trending Songs of the Week
            </h1>

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>

            
        </header>

        @include('partials.home.songs', [
            'items' => $songs,
            'id' => 'songs-of-the-week',
            'heading' => 'Songs of the Week',
            'badge' => 'Song of the Week',
            'more' => null,
        ])

        @if ($songs->hasPages())
            @include('partials.pagination', [
                'paginator' => $songs,
            ])
        @endif
    </section>
@endsection