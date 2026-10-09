@php
    $blogCategoryNames = \Illuminate\Support\Facades\DB::table('blog_types')
        ->pluck('name', 'id');

    $blogCategoryClasses = [
        'sports-news' => 'tb-cat-sports-news',
        'celebrity-news' => 'tb-cat-celebrity-news',
        'hot-topics' => 'tb-cat-hot-topics',
        'hot-gists' => 'tb-cat-hot-topics',
        'net-worth' => 'tb-cat-net-worth',
        'music-reviews' => 'tb-cat-music-reviews',
        'education' => 'tb-cat-education',
        'articles' => 'tb-cat-articles',
    ];
@endphp

<section class="tb-home-blog-section" id="{{ $id ?? 'latest-blog-news' }}">
    <h2 class="section-heading">📰 {{ $heading ?? 'Latest Blog & News' }}</h2>
    

    <div class="tb-home-blog-grid">
        @forelse ($items as $blog)
            @php
                $categoryId = (int) (
                    $blog->blog_type ?? $blog->category_id ?? 0
                );
                
                $categoryName = $blogCategoryNames->get($categoryId) ?? 'News';
                
                $categoryClass = $blogCategoryClasses[
                    \Illuminate\Support\Str::slug($categoryName)
                ] ?? 'tb-cat-news';

                $photo = trim((string) $blog->photo);

                if ($photo === '') {
                    $imageUrl = null;
                } elseif (preg_match('~^https?://~i', $photo)) {
                    $imageUrl = $photo;
                } else {
                    $photoPath = ltrim($photo, '/');

                    if (!str_starts_with($photoPath, 'images/')) {
                        $photoPath = 'images/blog/' . basename($photoPath);
                    }

                    $imageUrl = asset($photoPath);
                }
            @endphp

            <a
                class="tb-home-blog-post"
                href="{{ route('blogs.show', $blog->slug) }}"
            >
                <div class="tb-home-blog-thumb">
                    <span class="tb-home-blog-placeholder" aria-hidden="true">
                        {{ $placeholder ?? 'TrendyNews' }}
                    </span>

                    @if ($imageUrl)
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $blog->title }}"
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif
                </div>

                <div class="tb-home-blog-info">
                    <span class="tb-home-blog-category {{ $categoryClass }}">
                        {{ $categoryName }}
                    </span>

                    <h3 class="tb-home-blog-title">
                        {{ $blog->title }}
                    </h3>

                    <p class="tb-home-blog-intro">
                        {{ \Illuminate\Support\Str::limit(
                            trim(strip_tags($blog->intro)),
                            160
                        ) }}
                    </p>

                    <div class="tb-home-blog-meta">
                        <time datetime="{{ date('Y-m-d', strtotime($blog->updated_at)) }}">
                            {{ date('d M Y', strtotime($blog->updated_at)) }}
                        </time>

                        <span class="tb-home-blog-read">
                            Continue Reading →
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <p class="tb-home-blog-empty">No blog posts available yet.</p>
        @endforelse
    </div>

    @if (!empty($more))
        <div class="tb-home-blog-more">
            <a class="tb-home-view-all" href="{{ $more }}">
                View All {{ $heading ?? 'Blog & News' }} →
            </a>
        </div>
    @endif
</section>