<section
    class="tb-artists-country"
    id="artists-{{ $country }}"
    data-artist-section
    aria-label="{{ $heading }}"
>
    <h2 class="sub-section-heading">
        {{ $heading }}
    </h2>

    <div class="tb-artists-grid">
        @forelse ($artists as $artist)
            @include('partials.artists.card', [
                'artist' => $artist,
            ])
        @empty
            <p class="tb-artists-empty">
                No {{ strtolower($heading) }} available yet.
            </p>
        @endforelse
    </div>

    @if ($artists->hasPages())
        <nav
            class="tb-artists-pagination"
            aria-label="{{ $heading }} pages"
        >
            @if ($artists->onFirstPage())
                <span class="tb-artists-page-disabled">← Previous</span>
            @else
                <a
                    href="{{ route('artists.section', [
                        'country' => $country,
                        'page' => $artists->currentPage() - 1,
                    ]) }}"
                >
                    ← Previous
                </a>
            @endif

            @foreach ($artists->getUrlRange(
                max(1, $artists->currentPage() - 2),
                min($artists->lastPage(), $artists->currentPage() + 2)
            ) as $pageNumber => $unusedUrl)
                <a
                    href="{{ route('artists.section', [
                        'country' => $country,
                        'page' => $pageNumber,
                    ]) }}"
                    @if ($pageNumber === $artists->currentPage())
                        aria-current="page"
                    @endif
                >
                    {{ $pageNumber }}
                </a>
            @endforeach

            @if ($artists->hasMorePages())
                <a
                    href="{{ route('artists.section', [
                        'country' => $country,
                        'page' => $artists->currentPage() + 1,
                    ]) }}"
                >
                    Next →
                </a>
            @else
                <span class="tb-artists-page-disabled">Next →</span>
            @endif
        </nav>
    @endif
</section>