<section class="tb-home-albums" id="{{ $id ?? 'latest-albums' }}">
    <h2 class="section-heading">
        {{ $heading ?? 'Latest Albums' }}
    </h2>

    <div class="tb-home-album-list">
        @forelse ($items as $album)
            @php
                $cover = trim((string) $album->cover_url);

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

                $displayTitle = trim(
                    ($album->artist_name ? $album->artist_name . ' - ' : '')
                    . $album->title
                );

                $slug = \Illuminate\Support\Str::slug($displayTitle);
            @endphp

            <a
                class="tb-home-album-card"
                href="{{ route('albums.show', [$album->id, $slug]) }}"
            >
                <span class="tb-home-album-cover">
                    <span class="tb-home-album-placeholder" aria-hidden="true">
                        Album jpg
                    </span>

                    @if ($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $displayTitle }} cover"
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif
                </span>

                <span class="tb-home-album-info">
                    <span class="tb-home-album-badge">Album</span>

                    <strong class="tb-home-album-title">
                        {{ $displayTitle }}
                    </strong>

                    <span class="tb-home-album-meta">
                        Tracklists: {{ $album->track_count }}
                    </span>

                    <span class="tb-home-album-meta">
                        Year of Release: {{ $album->released_year }}
                    </span>

                    @if ($album->released_date)
                        <span class="tb-home-album-meta">
                            Date Released:
                            {{ \Illuminate\Support\Carbon::parse(
                                $album->released_date
                            )->format('M d, Y') }}
                        </span>
                    @endif
                </span>
            </a>
        @empty
            <p class="tb-home-album-empty">
                No albums available yet.
            </p>
        @endforelse
    </div>

    @if (!empty($more))
        <div class="tb-home-album-more">
            <a class="tb-home-view-all" href="{{ $more }}">
                View All Latest Albums →
            </a>
        </div>
    @endif
</section>