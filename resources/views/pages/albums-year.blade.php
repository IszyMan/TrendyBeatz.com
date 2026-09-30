@extends('layouts.app')

@php
    $pageNumber = $albums->currentPage();

    $pageTitle = 'Download Latest ' . $year . ' Albums Here';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Explore ' . $year
        . ' Nigerian, Ghanaian and African music albums on TrendyBeatz. '
        . 'Browse tracklists, discover artists and find available album downloads.';

    $keywords = $year . ' albums, '
        . $year . ' Naija albums, '
        . $year . ' Nigerian music albums, '
        . $year . ' Ghanaian albums, '
        . $year . ' African music albums';

    $canonical = $pageNumber === 1
        ? route('albums.year', ['year' => $year])
        : route('albums.year', [
            'year' => $year,
            'page' => $pageNumber,
        ]);
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_keywords', $keywords)
@section('meta_description', $description)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-albums-year">
        <header>
            <h1 class="section-heading">
                Download {{ $year }} Music Albums
            </h1>

            <p>
                Discover Nigerian, Ghanaian and African music albums
                released in {{ $year }}. Browse album tracklists from your favourite artists .
            </p>
        </header>

        @include('partials.home-albums', [
            'items' => $albums,
            'id' => 'albums-' . $year,
            'heading' => $year . ' Albums',
            'more' => null,
        ])

        @if ($albums->hasPages())
            <div class="ts-list-pagination">
                {{ $albums->links() }}
            </div>
        @endif
    </section>
@endsection