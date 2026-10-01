<section class="tb-home-section tb-home-mixes" id="{{ $id ?? 'latest-dj-mix' }}">
    <h2 class="section-heading">
        {{ $heading ?? 'Latest DJ Mix' }}
    </h2>

    <div class="tb-home-mix-grid">
        @forelse ($items as $mix)
            @php
                
                $cover = trim((string) $mix->cover_url);

                if ($cover === '') {
                    $coverUrl = null;
                } elseif (preg_match('~^https?://~i', $cover)) {
                    $coverUrl = $cover;
                } else {
                    $coverPath = ltrim($cover, '/');

                    $coverUrl = asset(
                        str_starts_with($coverPath, 'images/')
                            ? $coverPath
                            : 'images/dj/' . basename($coverPath)
                    );
                }
            @endphp

            <a
                class="tb-home-mix"
                href="{{ \App\Support\DjMixUrl::detail($mix) }}"
            >
                <span class="tb-home-mix-thumb">
                    <span class="tb-home-mix-placeholder" aria-hidden="true">
                        TrendyMix
                    </span>

                    @if ($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $mix->dj_name }} - {{ $mix->mix_title }} cover"
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif
                </span>

                <span class="tb-home-mix-info">
                    <span class="tb-home-mix-label">DJ Mix</span>

                    <strong class="tb-home-mix-dj">
                        {{ $mix->dj_name }}
                    </strong>

                    <span class="tb-home-mix-title">
                        {{ $mix->mix_title }}
                    </span>

                    <span class="tb-home-mix-actions">
                        <span class="tb-home-mix-discover">Discover</span>
                        <span class="tb-home-mix-separator">|</span>
                        <span class="tb-home-mix-listen">Listen</span>
                    </span>
                </span>
            </a>
        @empty
            <p class="tb-home-empty">
                No DJ mixes available yet.
            </p>
        @endforelse
    </div>

    @if (!empty($more))
        <div class="tb-home-mix-more">
            <a class="tb-home-view-all" href="{{ $more }}">
                View All Latest DJ Mix →
            </a>
        </div>
    @endif
</section>