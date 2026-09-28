@php
    $blogCategories = [
        1 => ['name' => 'Sports News', 'class' => 'tb-cat-sports-news'],
        2 => ['name' => 'Celebrity News', 'class' => 'tb-cat-celebrity-news'],
        6 => ['name' => 'Hot Topics', 'class' => 'tb-cat-hot-topics'],
        7 => ['name' => 'Net Worth', 'class' => 'tb-cat-net-worth'],
        8 => ['name' => 'Music Reviews', 'class' => 'tb-cat-music-reviews'],
    ];
@endphp

<section class="tb-home-blog-section" id="{{ $id ?? 'latest-blog-news' }}">
    <h2 class="section-heading">📰 {{ $heading ?? 'Latest Blog & News' }}</h2>
    

    <div class="tb-home-blog-grid">
        @forelse ($items as $blog)
            @php
                $category = $blogCategories[(int) $blog->category_id]
                    ?? ['name' => 'News', 'class' => 'tb-cat-news'];

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
                href="{{ route('blogs.show', [
                    $blog->id,
                    \Illuminate\Support\Str::slug($blog->title)
                ]) }}"
            >
                <div class="tb-home-blog-thumb">
                    <span class="tb-home-blog-placeholder" aria-hidden="true">
                        TrendyBeatz
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
                    <span class="tb-home-blog-category {{ $category['class'] }}">
                        {{ $category['name'] }}
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
                        <time datetime="{{ date('Y-m-d', strtotime($blog->created_at)) }}">
                            {{ date('d M Y', strtotime($blog->created_at)) }}
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