@extends('layouts.app')

@php
    $pageNumber = $videos->currentPage();

    $pageTitle = 'Download Latest ' . $countryName . ' Music Video Here';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $keywords = 'latest ' . $countryName . ' music videos, '
        . 'watch ' . $countryName . ' videos, '
        . 'download ' . $countryName . ' music videos';

    $canonical = $pageNumber === 1
        ? route('videos.' . $country)
        : route('videos.' . $country, ['page' => $pageNumber]);
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_keywords', $keywords)
@section('meta_description', $description)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-country-videos">
        <header>
            <h1 class="section-heading">
                Download Latest {{ $countryName }} Music Videos
            </h1>

            <p>{{ $description }}</p>
        </header>

        @include('partials.home.videos', [
            'items' => $videos,
            'id' => 'videos-' . $country,
            'heading' => 'Latest ' . $countryName . ' Music Videos',
            'more' => null,
        ])

        @if ($videos->hasPages())
            <div class="ts-list-pagination">
                {{ $videos->links() }}
            </div>
        @endif
    </section>
@endsection