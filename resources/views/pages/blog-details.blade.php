@extends('layouts.app')

@php
    $pageTitle = $post->title . ' — TrendyBeatz';

    $cleanMetaText = static function ($text): string {
        return trim(preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode(
                strip_tags((string) $text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        ));
    };

    $pageDescription = collect([
        $post->intro ?? '',
        $post->description ?? '',
    ])
        ->map($cleanMetaText)
        ->filter(fn ($text) => $text !== '')
        ->implode(' ');

    if ($pageDescription === '') {
        $pageDescription = 'Read ' . $post->title
            . ' and more news on TrendyBeatz.';
    }

    $pageDescription = \Illuminate\Support\Str::limit(
        $pageDescription,
        260,
        ''
    );

    $metaKeywords = collect([
        $cleanMetaText($post->title),
        $cleanMetaText($post->keywords ?? ''),
        $cleanMetaText($post->category_name ?? ''),
        'TrendyBeatz blog',
        'TrendyBeatz news',
    ])
        ->filter(fn ($value) => $value !== '')
        ->unique()
        ->implode(', ');

    $canonicalUrl = route('blogs.show', $post->slug);

    $cover = trim((string) ($post->photo ?? ''));

    if ($cover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $cover)) {
        $socialImage = $cover;
    } else {
        $coverPath = ltrim($cover, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/blog/')
                ? $coverPath
                : 'images/blog/' . basename($coverPath)
        );
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $metaKeywords)
@section('canonical', $canonicalUrl)
@section('social_title', $post->title)
@section('social_description', $pageDescription)

@if ($socialImage !== '')
    @section('social_image', $socialImage)
@endif



@section('content')
    @php
        $postedAt = $post->updated_at
            ? \Illuminate\Support\Carbon::parse($post->updated_at)
            : null;

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

        $categoryClass = $categoryClasses[$post->category_slug]
            ?? 'tb-blog-news';
    
        

        $lastUpdated = filled($post->created_at)
            ? \Illuminate\Support\Carbon::parse($post->created_at)
            : null;    

        $imageUrl = function ($value) {
            $image = trim((string) $value);

            if ($image === '') {
                return null;
            }

            if (preg_match('~^https?://~i', $image)) {
                return $image;
            }

            $path = ltrim($image, '/');

            return asset(
                str_starts_with($path, 'images/blog/')
                    ? $path
                    : 'images/blog/' . basename($path)
            );
        };

        $contentBlocks = [
            ['text', $post->description],
            ['text', $post->desc2],
            ['text', $post->desc3],
            ['image', $post->photo2],
            ['text', $post->desc4],
            ['text', $post->desc5],
            ['text', $post->desc6],
            ['image', $post->photo3],
            ['text', $post->desc7],
            ['text', $post->desc8],
            ['text', $post->desc9],
            ['image', $post->photo4],
            ['text', $post->desc10],
            ['text', $post->Desc11],
            ['text', $post->Desc12],
            ['text', $post->Desc13],
            ['image', $post->photo5],
            ['text', $post->Desc14],
            ['text', $post->Desc15],
            ['text', $post->Desc16],
            ['image', $post->photo6],
            ['text', $post->Desc17],
            ['text', $post->Desc18],
            ['text', $post->Desc19],
            ['image', $post->photo7],
            ['text', $post->Desc20],
            ['text', $post->Desc21],
            ['text', $post->Desc22],
            ['text', $post->Desc23],
            ['image', $post->photo8],
            ['text', $post->Desc24],
            ['text', $post->Desc25],
            ['text', $post->Desc26],
            ['image', $post->photo9],
            ['text', $post->Desc27],
            ['text', $post->Desc28],
            ['text', $post->Desc29],
            ['image', $post->photo10],
            ['text', $post->Desc30],
        ];

        $readingParts = [(string) ($post->intro ?? '')];

        foreach ($contentBlocks as [$type, $value]) {
            if ($type === 'text' && filled($value)) {
                $readingParts[] = (string) $value;
            }
        }

        $readingText = implode(' ', $readingParts);

        // Remove markup while preserving spaces between paragraphs.
        $readingText = preg_replace('/<[^>]*>/u', ' ', $readingText);

        $readingText = html_entity_decode(
            $readingText,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $words = preg_split(
            '/[\s\p{Z}]+/u',
            trim($readingText),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $wordCount = count($words ?: []);

        $readingMinutes = max(1, (int) ceil($wordCount / 200));
    @endphp

    <div class="tb-article-page">
        <nav class="tb-article-breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span aria-hidden="true">→</span>

            <a href="{{ route('blogs.index') }}">Blog</a>
            <span aria-hidden="true">→</span>

            @if ($post->category_slug)
                <a href="{{ route('blogs.category', $post->category_slug) }}">
                    {{ $post->category_name }}
                </a>
                <span aria-hidden="true">→</span>
            @endif

            <span aria-current="page">
                {{ $post->title }}
            </span>
        </nav>

        <nav class="tb-article-categories" aria-label="Blog categories">
            <a
                class="tb-article-category-link"
                href="{{ route('blogs.index') }}"
            >
                All
            </a>

            @foreach ($categories as $category)
                <a
                    href="{{ route('blogs.category', $category->slug) }}"
                    @class([
                        'tb-article-category-link',
                        'active' => $category->id === $post->category_id,
                    ])
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <article class="tb-article-main">
            <div class="tb-article-cover">
                <span class="tb-article-placeholder" aria-hidden="true">
                    TrendyBlog
                </span>

                @if ($socialImage !== '')
                    <img
                        src="{{ $socialImage }}"
                        alt="{{ $post->title }}"
                        onerror="this.remove()"
                    >
                @endif
            </div>

            <div class="tb-article-meta">
                <a
                    class="tb-article-badge {{ $categoryClass }}"
                    href="{{ route('blogs.category', [
                        'category' => $post->category_slug,
                    ]) }}"
                    style="text-decoration: none;"
                >
                    {{ $post->category_name ?: 'News' }}
                </a>

                @if (filled($post->posted_by_name))
                    <span class="tb-article-meta-item">
                        <span aria-hidden="true">✍</span>
                        <a
                            href="{{ route('blogs.published_by', [
                                'slug' => \Illuminate\Support\Str::slug($post->posted_by_name),
                            ]) }}"
                        >
                            <strong>{{ $post->posted_by_name }}</strong>
                        </a>
                    </span>
                @endif

                @if ($postedAt)
                    <span class="tb-article-meta-item">
                        <span aria-hidden="true">🗓</span>
                        <time datetime="{{ $postedAt->toDateString() }}">
                            {{ $postedAt->format('d M Y') }}
                        </time>
                    </span>
                @endif

                <span class="tb-article-meta-item">
                    <span aria-hidden="true">⏱</span>
                    {{ $readingMinutes }} min read
                </span>

                <span class="tb-article-meta-item">
                    <span aria-hidden="true">💬</span>

                    {{ $commentCount }}
                    {{ $commentCount === 1 ? 'comment' : 'comments' }}
                </span>

                @if ($lastUpdated)
                    <span class="tb-article-meta-item tb-article-meta-updated">
                        Last updated:
                        <time datetime="{{ $lastUpdated->toIso8601String() }}">
                            {{ $lastUpdated->format('d M Y') }}
                        </time>
                    </span>
                @endif
            </div>

            <h1>{{ $post->title }}</h1>

            @if (filled($post->intro))
                <p class="tb-article-intro">
                    {{ trim(strip_tags((string) $post->intro)) }}
                </p>
            @endif

            <div class="tb-article-body">
                @foreach ($contentBlocks as [$type, $value])
                    @if (filled($value))
                        @if ($type === 'image')
                            @php
                                $inlineImage = $imageUrl($value);
                            @endphp

                            @if ($inlineImage)
                                <figure class="tb-article-body-image">
                                    <img
                                        src="{{ $inlineImage }}"
                                        alt="{{ $post->title }}"
                                        loading="lazy"
                                        onerror="this.closest('figure').remove()"
                                    >
                                </figure>
                            @endif
                        @else
                            @php
                                $paragraphs = preg_split(
                                    '/\R\s*\R/',
                                    trim(strip_tags((string) $value))
                                );
                            @endphp

                            @foreach ($paragraphs as $paragraph)
                                @if (trim($paragraph) !== '')
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        @endif
                    @endif
                @endforeach


            </div>

            <p class="tb-article-source">
                <strong>Source:</strong>
                <a href="{{ route('home') }}">TrendyBeatz</a>
            </p>
        </article>


        {{-- The share section  --}}

    <section class="tb-share-card">
        <p class="tb-share-heading">
            🔗 Share {{ $post->title }} Article with others on
        </p>

        <div class="tb-share-row">
            <button
                type="button"
                class="tb-share-copy"
                onclick="tbCopyPageLink(this)"
                aria-live="polite"
            >Copy Link</button>
            <div class="a2a_kit a2a_kit_size_24 a2a_default_style">
                <a class="a2a_button_facebook"></a>
                <a class="a2a_button_x"></a>
                <a class="a2a_button_email"></a>
                <a class="a2a_button_pinterest"></a>
                <a class="a2a_button_linkedin"></a>
                <a class="a2a_button_whatsapp"></a>
            </div>

            
        </div>
    </section>

        @if ($relatedPosts->isNotEmpty())
            <section class="tb-article-panel">
                <h2 class="tb-article-panel-heading">
                    📌 Related Posts
                </h2>

                <div class="tb-article-related-list">
                    @foreach ($relatedPosts as $related)
                        @php
                            $relatedPhoto = $imageUrl($related->photo);

                            $relatedDate = $related->updated_at
                                ? \Illuminate\Support\Carbon::parse(
                                    $related->updated_at
                                )
                                : null;

                            $relatedCategoryClass = $categoryClasses[
                                $related->category_slug
                            ] ?? 'tb-blog-news';
                        @endphp

                        <a
                            class="tb-article-related-card"
                            href="{{ route('blogs.show', $related->slug) }}"
                        >
                            <span class="tb-article-related-image">
                                <span aria-hidden="true">TrendyBlog</span>

                                @if ($relatedPhoto)
                                    <img
                                        src="{{ $relatedPhoto }}"
                                        alt="{{ $related->title }}"
                                        loading="lazy"
                                        onerror="this.remove()"
                                    >
                                @endif
                            </span>

                            <span class="tb-article-related-content">
                                <span
                                    class="tb-article-badge {{ $relatedCategoryClass }}"
                                >
                                    {{ $related->category_name ?: 'News' }}
                                </span>

                                <strong>{{ $related->title }}</strong>

                                <small>
                                    @if ($relatedDate)
                                        {{ $relatedDate->format('d M Y') }}
                                    @endif

                                    @if (filled($related->posted_by_name))
                                        ✍ {{ $related->posted_by_name }}
                                    @endif
                                </small>

                                <span class="tb-article-related-read">
                                    Continue Reading →
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>

                @if ($post->category_slug)
                    <a
                        class="tb-article-panel-more"
                        href="{{ route('blogs.category', $post->category_slug) }}"
                    >
                        View All {{ $post->category_name }} Posts →
                    </a>
                @endif
            </section>
        @endif

        @if ($latestPosts->isNotEmpty())
            <section class="tb-article-panel">
                <h2 class="tb-article-panel-heading">
                    📰 Latest Posts
                </h2>

                <div class="tb-article-related-list">
                    @foreach ($latestPosts as $latest)
                        @php
                            $latestPhoto = $imageUrl($latest->photo);

                            $latestCategoryClass = $categoryClasses[
                                $latest->category_slug
                            ] ?? 'tb-blog-news';

                            $latestDate = filled($latest->updated_at)
                            ? \Illuminate\Support\Carbon::parse($latest->updated_at)
                            : null;
                        @endphp

                        <a
                            class="tb-article-related-card"
                            href="{{ route('blogs.show', $latest->slug) }}"
                        >
                            <span class="tb-article-related-image">
                                <span aria-hidden="true">TrendyBlog</span>

                                @if ($latestPhoto)
                                    <img
                                        src="{{ $latestPhoto }}"
                                        alt="{{ $latest->title }}"
                                        loading="lazy"
                                        onerror="this.remove()"
                                    >
                                @endif
                            </span>

                            <span class="tb-article-related-content">
                                <span
                                    class="tb-article-badge {{ $latestCategoryClass }}"
                                >
                                    {{ $latest->category_name ?: 'News' }}
                                </span>

                                <strong>{{ $latest->title }}</strong>
                                <small>
                                    @if ($latestDate)
                                        <time datetime="{{ $latestDate->toDateString() }}">
                                            {{ $latestDate->format('d M Y') }}
                                        </time>
                                    @endif

                                    @if (filled($latest->posted_by_name))
                                        ✍ {{ $latest->posted_by_name }}
                                    @endif
                                </small>

                                <span class="tb-article-related-read">
                                    Continue Reading →
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>

                <a
                    class="tb-article-panel-more"
                    href="{{ route('blogs.index') }}"
                >
                    View More News &amp; Gists →
                </a>
            </section>
        @endif

        <section class="tb-article-panel">
            <h2 class="tb-article-panel-heading">
                📁 Browse Categories
            </h2>

            <div class="tb-article-category-footer">
                @foreach ($categories as $category)
                    <a href="{{ route('blogs.category', $category->slug) }}">
                        {{ $category->name }} →
                    </a>
                @endforeach
            </div>
        </section>

        <a class="tb-article-back" href="{{ route('blogs.index') }}">
            <strong>More News &amp; Gists</strong>
            <span>Catch up on all the latest entertainment</span>
            <em>View All Posts →</em>
        </a>
    </div>


    @include('partials.comments', [
        'postType' => 'blog',
        'postId' => $post->id,
        'postTitle' => $post->title,
    ])
@endsection