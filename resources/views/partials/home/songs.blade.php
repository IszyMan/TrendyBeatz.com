<section class="tb-home-section tb-home-songs" id="{{ $id }}">
    <h2 class="sub-section-heading">{{ $heading }}</h2>

    <div class="tb-home-song-list">
        @forelse ($items as $song)
            <a
                class="tb-home-song"
                href="{{ route('songs.show', [$song->id, $song->slug]) }}"
            >
                @php
                    $cover = trim((string) $song->cover_url);

                    if ($cover === '') {
                        $coverUrl = null;
                    } elseif (preg_match('~^https?://~i', $cover)) {
                        $coverUrl = $cover;
                    } else {
                        $path = ltrim($cover, '/');

                        // Handles both "cover.jpg" and "images/cover.jpg".
                        $coverUrl = asset(
                            str_starts_with($path, 'images/')
                                ? $path
                                : 'images/' . $path
                        );
                    }
                @endphp

                <span class="tb-home-song-thumb">
                    <span class="tb-home-song-placeholder" aria-hidden="true">
                        TB
                    </span>

                    @if ($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $song->artist_name ?: 'TrendyBeatz' }} – {{ $song->track_title }} cover"
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif
                </span>

                <span class="tb-home-song-info">
                    <span class="tb-home-song-badge">
                        {{ $badge }}
                    </span>

                    <strong class="tb-home-song-artist">
                        {{ $song->artist_name ?: 'TrendyBeatz' }}
                    </strong>

                    <span class="tb-home-song-title">
                        {{ $song->track_title }}
                    </span>

                    @if ($song->featuring)
                        <span class="tb-home-song-featuring">
                            Featuring {{ $song->featuring }}
                        </span>
                    @endif

                    <span class="tb-home-song-actions">
                        <span class="tb-home-discover">Discover</span>
                        <span class="tb-home-action-divider">|</span>
                        <span class="tb-home-stream">Stream</span>
                    </span>
                </span>
            </a>
        @empty
            <p class="tb-home-empty">
                No songs in this section yet.
            </p>
        @endforelse
    </div>

    @if (!empty($more))
        <a class="tb-home-view-all" href="{{ $more }}">
            View All {{ $heading }} →
        </a>
    @endif
</section>