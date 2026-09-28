<section class="tb-home-section tb-home-videos" id="{{ $id ?? 'latest-videos' }}">
    <h2 class="sub-section-heading">
        {{ $heading ?? 'Latest Videos' }}
    </h2>

    <div class="tb-home-song-list">
        @forelse ($items as $video)
            @php
                $cover = trim((string) $video->cover_url);

                if ($cover === '') {
                    $coverUrl = null;
                } elseif (preg_match('~^https?://~i', $cover)) {
                    $coverUrl = $cover;
                } else {
                    $coverPath = ltrim($cover, '/');

                    $coverUrl = asset(
                        str_starts_with($coverPath, 'images/')
                            ? $coverPath
                            : 'images/' . $coverPath
                    );
                }
            @endphp

            <a
                class="tb-home-song tb-home-video"
                href="{{ \App\Support\VideoUrl::detail($video) }}"
                
            >
                <span class="tb-home-song-thumb tb-home-video-thumb">
                    <span class="tb-home-song-placeholder" aria-hidden="true">
                        TrendyViews
                    </span>

                    @if ($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $video->artist_name }} - {{ $video->track_title }} video"
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif

                    <span class="tb-home-video-play" aria-hidden="true">
                        ▶
                    </span>
                </span>

                <span class="tb-home-song-info">
                    <span class="tb-home-song-badge">Video</span>

                    <strong class="tb-home-song-artist">
                        {{ $video->artist_name ?: 'TrendyBeatz' }}
                    </strong>

                    <span class="tb-home-song-title">
                        {{ $video->track_title }}
                    </span>

                    @if (!empty($video->featuring))
                        <span class="tb-home-song-featuring">
                            Featuring: {{ $video->featuring }}
                        </span>
                    @endif

                    <span class="tb-home-song-actions">
                        <span class="tb-home-discover">Discover</span>
                        <span>|</span>
                        <span class="tb-home-stream">Watch</span>
                    </span>
                </span>
            </a>
        @empty
            <p class="tb-home-empty">
                No videos available yet.
            </p>
        @endforelse
    </div>

    @if (!empty($more))
        <div class="tb-home-video-more">
            <a class="tb-home-view-all" href="{{ $more }}">
                View All Videos →
            </a>
        </div>
    @endif
</section>