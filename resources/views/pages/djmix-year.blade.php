@extends('layouts.app')

@php
    $pageNumber = $mixes->currentPage();

    $pageTitle = 'DJ Mix - Download Latest '
        . $year . ' Naija DJ Mixtape';

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = 'Discover ' . $year
        . ' DJ mixes and Naija mixtapes on TrendyBeatz. '
        . 'Browse releases from your favourite DJs, listen online '
        . 'and find available downloads.';

    $keywords = 'download DJ mix, latest DJ mix, '
        . $year . ' DJ mix, '
        . $year . ' Naija mixtapes, '
        . $year . ' Nigerian DJ mixes, '
        . $year . ' Afrobeats mixes, '
        . $year . ' Amapiano mixtapes, '
        . 'DJ Kaywise mix, DJ Baddo mix, DJ Spinall mix, '
        . 'DJ Neptune mix, DJ Sose mix, DJ Big N mix, '
        . 'DJ mixtape downloads, listen to DJ mixes';

    $canonical = $pageNumber === 1
        ? route('mixes.year', ['year' => $year])
        : route('mixes.year', [
            'year' => $year,
            'page' => $pageNumber,
        ]);

    $dateNow = now('Africa/Lagos');
@endphp

@section('title', $pageTitle . ' | TrendyBeatz')
@section('meta_description', $description)
@section('meta_keywords', $keywords)
@section('canonical', $canonical)
@section('social_title', $pageTitle . ' | TrendyBeatz')
@section('social_description', $description)

@section('content')
    <section class="tb-djmix-year">
        <header class="tb-djmix-year-header">
            <h1 class="section-heading">
                Download {{ $year }} Naija DJ Mixes and Mixtapes
            </h1>

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>

            <!--<p class="tb-djmix-year-description">
                Explore DJ mixes and mixtapes released in {{ $year }}.
                Discover releases from your favourite DJs, open each
                mixtape to listen and find download options where available.
            </p>-->
        </header>

        @include('partials.home.mixes', [
            'items' => $mixes,
            'id' => 'djmix-' . $year,
            'heading' => $year . ' DJ Mixes',
            'more' => null,
        ])

        @if ($mixes->hasPages())
            @include('partials.pagination', [
                'paginator' => $mixes,
            ])
        @endif
    </section>
@endsection