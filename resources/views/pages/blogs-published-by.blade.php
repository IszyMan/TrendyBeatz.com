@extends('layouts.app')

@php
    $pageNumber = $posts->currentPage();

    $pageTitle = 'Posts Published By ' . $publisherName;

    if ($pageNumber > 1) {
        $pageTitle .= ' - Page ' . $pageNumber;
    }

    $description = \Illuminate\Support\Str::limit(
        'Read posts published by ' . $publisherName
            . ' on TrendyBeatz. Explore music reviews, entertainment '
            . 'updates, celebrity news and articles from this publisher.',
        160,
        ''
    );

    $keywords = $publisherName . ', '
        . 'posts by ' . $publisherName . ', '
        . $publisherName . ' articles, '
        . 'TrendyBeatz blog, music reviews, entertainment updates, '
        . 'celebrity news, Nigerian entertainment news, '
        . 'African music news, latest blog posts';

    $canonical = $pageNumber === 1
        ? route('blogs.published_by', ['slug' => $slug])
        : route('blogs.published_by', [
            'slug' => $slug,
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
    <section class="tb-publisher-page">
        <header class="tb-publisher-header">
            <h1 class="section-heading">
                Posts Published By {{ $publisherName }}
            </h1>

            <p class="home-date">
                <time datetime="{{ now()->toDateString() }}">
                    {{ now()->format('M d, Y') }}
                </time>
            </p>

            <!--<p class="tb-publisher-description">
                Browse posts published by {{ $publisherName }}
                on TrendyBeatz. Discover articles, reviews and news,
                then open any post to continue reading.
            </p>-->
        </header>

        <div class="tb-publisher-grid">
            @forelse ($posts as $post)
                @include('partials.blogs.card', [
                    'post' => $post,
                ])
            @empty
                <p>No published posts available yet.</p>
            @endforelse
        </div>

        @if ($posts->hasPages())
            @include('partials.pagination', [
                'paginator' => $posts,
            ])
        @endif
    </section>
@endsection