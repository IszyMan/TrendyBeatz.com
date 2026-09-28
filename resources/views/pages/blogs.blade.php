@extends('layouts.app')

@php
    $isCategoryPage = $selectedCategory !== null;

    $heading = $isCategoryPage
        ? $selectedCategory->name
        : 'Latest Blog & News';

    $pageTitle = $isCategoryPage
        ? $heading . ' - Latest Stories | TrendyBeatz'
        : 'Music News - Top Naija Celebrity News, Entertainment Gists & Updates | TrendyBeatz';

    $pageDescription = $isCategoryPage
        ? 'Explore the latest ' . $selectedCategory->name
            . ' stories, updates and articles on TrendyBeatz.'
        : 'Read the latest Nigerian music news, celebrity stories, entertainment updates, music reviews and trending conversations on TrendyBeatz.';

    $baseUrl = $isCategoryPage
        ? route('blogs.category', $selectedCategory->slug)
        : route('blogs.index');

    $canonicalUrl = $posts->currentPage() === 1
        ? $baseUrl
        : $posts->url($posts->currentPage());

    $firstPhoto = trim((string) ($posts->first()->photo ?? ''));

    if ($firstPhoto === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $firstPhoto)) {
        $socialImage = $firstPhoto;
    } else {
        $firstPhotoPath = ltrim($firstPhoto, '/');

        $socialImage = asset(
            str_starts_with($firstPhotoPath, 'images/')
                ? $firstPhotoPath
                : 'images/blog/' . basename($firstPhotoPath)
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

@if ($posts->currentPage() > 1)
    @section(
        'previous_url',
        $posts->currentPage() === 2
            ? $baseUrl
            : $posts->previousPageUrl()
    )
@endif

@if ($posts->hasMorePages())
    @section('next_url', $posts->nextPageUrl())
@endif

@section('content')
    <section class="tb-news-page">
        <h1 class="section-heading">
            {{ $heading }}
        </h1>

        @php
            $currentDate = now();

            $categoryClasses = [
                'sport-news' => 'tb-blog-sport',
                'celebrity-news' => 'tb-blog-celebrity',
                'hot-gists' => 'tb-blog-gists',
                'networth' => 'tb-blog-networth',
                'music-reviews' => 'tb-blog-reviews',
                'education' => 'tb-blog-education',
                'articles' => 'tb-blog-articles',
                'news' => 'tb-blog-news',
            ];
        @endphp

        <p class="home-date">
            <time datetime="{{ $currentDate->toDateString() }}">
                {{ $currentDate->format('M d, Y') }}
            </time>
        </p>

        <nav class="tb-blog-categories" aria-label="Blog categories">
            <a
                href="{{ route('blogs.index') }}"
                @class([
                    'tb-blog-category-button',
                    'tb-blog-all',
                    'active' => !$isCategoryPage,
                ])
                @if (!$isCategoryPage) aria-current="page" @endif
            >
                All Posts
            </a>

            @foreach ($categories as $category)
                <a
                    href="{{ route('blogs.category', $category->slug) }}"
                    @class([
                        'tb-blog-category-button',
                        $categoryClasses[$category->slug] ?? 'tb-blog-news',
                        'active' => $isCategoryPage
                            && $selectedCategory->id === $category->id,
                    ])
                    @if (
                        $isCategoryPage
                        && $selectedCategory->id === $category->id
                    )
                        aria-current="page"
                    @endif
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <h2 class="sub-section-heading">
            {{ $isCategoryPage ? $heading . ' Posts' : 'All Blog Posts' }}
        </h2>

        <div class="tb-news-page-list">
            @forelse ($posts as $post)
                @include('partials.blogs.card', [
                    'post' => $post,
                ])
            @empty
                <p class="tb-home-empty">
                    No published posts in this section yet.
                </p>
            @endforelse
        </div>

        @include('partials.pagination', [
            'paginator' => $posts,
        ])
    </section>
@endsection