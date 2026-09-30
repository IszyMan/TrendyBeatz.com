@php
    $articleUrl = route('blogs.show', $post->slug);

    $photo = trim((string) $post->photo);

    if ($photo === '') {
        $photoUrl = null;
    } elseif (preg_match('~^https?://~i', $photo)) {
        $photoUrl = $photo;
    } else {
        $photoPath = ltrim($photo, '/');

        $photoUrl = asset(
            str_starts_with($photoPath, 'images/')
                ? $photoPath
                : 'images/blog/' . basename($photoPath)
        );
    }

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

    $postedAt = $post->updated_at
        ? \Illuminate\Support\Carbon::parse($post->updated_at)
        : null;

    $intro = \Illuminate\Support\Str::limit(
        trim(strip_tags((string) $post->intro)),
        180,
        '...'
    );
@endphp

<a
    class="tb-news-card"
    href="{{ $articleUrl }}"
    aria-label="Read {{ $post->title }}"
>
    <span class="tb-news-card-image">
        <span class="tb-news-card-placeholder" aria-hidden="true">
            TrendyBlog
        </span>

        @if ($photoUrl)
            <img
                src="{{ $photoUrl }}"
                alt="{{ $post->title }} cover"
                loading="lazy"
                onerror="this.remove()"
            >
        @endif
    </span>

    <span class="tb-news-card-content">
        <span class="tb-blog-badge {{ $categoryClass }}">
            {{ $post->category_name ?: 'Music News' }}
        </span>

        <span class="tb-news-card-title">
            {{ $post->title }}
        </span>

        @if ($intro !== '')
            <span class="tb-news-card-intro">
                {{ $intro }}
            </span>
        @endif

        <span class="tb-news-card-bottom">
            <span class="tb-news-card-meta">
                @if (filled($post->posted_by_name))
                    <span>
                        Posted by:
                        <strong>{{ $post->posted_by_name }}</strong>
                    </span>
                @endif

                @if ($postedAt)
                    <time datetime="{{ $postedAt->toDateString() }}">
                        {{ $postedAt->format('M d, Y') }}
                    </time>
                @endif
            </span>

            <span class="tb-news-card-read">
                Continue Reading →
            </span>
        </span>
    </span>
</a>