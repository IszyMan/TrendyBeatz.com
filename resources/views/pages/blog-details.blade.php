@extends('layouts.app')

@php
    $pageTitle = $post->title . ' | TrendyBeatz';

    $descriptionText = trim(preg_replace(
        '/\s+/',
        ' ',
        strip_tags((string) ($post->intro ?: $post->description))
    ));

    $pageDescription = \Illuminate\Support\Str::limit(
        $descriptionText !== ''
            ? $descriptionText
            : 'Read ' . $post->title . ' on TrendyBeatz.',
        160,
        ''
    );

    $canonicalUrl = route('blogs.show', $post->slug);

    $cover = trim((string) $post->photo);

    if ($cover === '') {
        $socialImage = '';
    } elseif (preg_match('~^https?://~i', $cover)) {
        $socialImage = $cover;
    } else {
        $coverPath = ltrim($cover, '/');

        $socialImage = asset(
            str_starts_with($coverPath, 'images/')
                ? $coverPath
                : 'images/blog/' . basename($coverPath)
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

@section('content')
    @php
        $postedAt = $post->created_at
            ? \Illuminate\Support\Carbon::parse($post->created_at)
            : null;

        /*
         * These fields follow the order of the legacy blog table.
         * Keep this mapping when adding the admin editor later.
         */
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
    @endphp

    <article class="tb-blog-detail">
        <header class="tb-blog-detail-header">
            @if ($post->category_slug)
                <a
                    class="tb-blog-detail-category"
                    href="{{ route('blogs.category', $post->category_slug) }}"
                >
                    {{ $post->category_name }}
                </a>
            @endif

            <h1>{{ $post->title }}</h1>

            <p class="tb-blog-detail-meta">
                @if (filled($post->posted_by))
                    <span>Posted by {{ $post->posted_by }}</span>
                @endif

                @if ($postedAt)
                    <time datetime="{{ $postedAt->toDateString() }}">
                        {{ $postedAt->format('M d, Y') }}
                    </time>
                @endif
            </p>
        </header>

        <div class="tb-blog-detail-cover">
            <span aria-hidden="true">TrendyBeatz</span>

            @if ($socialImage !== '')
                <img
                    src="{{ $socialImage }}"
                    alt="{{ $post->title }} cover"
                    onerror="this.remove()"
                >
            @endif
        </div>

        @if (filled($post->intro))
            <p class="tb-blog-detail-intro">
                {{ trim(strip_tags((string) $post->intro)) }}
            </p>
        @endif

        <div class="tb-blog-detail-body">
            @foreach ($contentBlocks as [$type, $value])
                @if (filled($value))
                    @if ($type === 'image')
                        @php
                            $image = trim((string) $value);

                            if (preg_match('~^https?://~i', $image)) {
                                $imageUrl = $image;
                            } else {
                                $imagePath = ltrim($image, '/');

                                $imageUrl = asset(
                                    str_starts_with($imagePath, 'images/')
                                        ? $imagePath
                                        : 'images/blog/' . basename($imagePath)
                                );
                            }
                        @endphp

                        <figure class="tb-blog-detail-inline-image">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $post->title }}"
                                loading="lazy"
                                onerror="this.closest('figure').remove()"
                            >
                        </figure>
                    @else
                        <p>{{ trim(strip_tags((string) $value)) }}</p>
                    @endif
                @endif
            @endforeach
        </div>

        @if ($relatedPosts->isNotEmpty())
            <section class="tb-blog-detail-related">
                <h2 class="sub-section-heading">
                    More Stories
                </h2>

                @foreach ($relatedPosts as $related)
                    <a href="{{ route('blogs.show', $related->slug) }}">
                        {{ $related->title }} →
                    </a>
                @endforeach
            </section>
        @endif
    </article>
@endsection