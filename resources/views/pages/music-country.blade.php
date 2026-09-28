@extends('layouts.app')


    @php
        $countryUrl = route('music.' . $country);

        $canonicalUrl = $songs->currentPage() === 1
            ? $countryUrl
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

@section('title', $page['title'])
@section('meta_description', $page['description'])
@section('canonical', $canonicalUrl)
@section('social_title', $page['title'])
@section('social_description', $page['description'])

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif

@if ($songs->currentPage() > 1)
    @section(
        'previous_url',
        $songs->currentPage() === 2
            ? $countryUrl
            : $songs->previousPageUrl()
    )
@endif

@if ($songs->hasMorePages())
    @section('next_url', $songs->nextPageUrl())
@endif

@section('content')
<section class="tb-day-page">

    <h1 class="section-heading">
        {{ $page['heading'] }}
    </h1>

    @php
        $currentDate = now();
    @endphp

    <p class="home-date">
        <time datetime="{{ $currentDate->toDateString() }}">
            {{ $currentDate->format('M d, Y') }}
        </time>
    </p>

    @include('partials.home.songs', [
        'id' => 'latest-' . $country . '-songs',
        'heading' => $page['section_heading'],
        'badge' => $page['badge'],
        'items' => $songs,
        'more' => null,
    ])

    @include('partials.pagination', [
        'paginator' => $songs,
    ])

</section>
@endsection