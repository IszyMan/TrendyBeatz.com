@if ($paginator->hasPages())
    <nav class="tb-pagination" aria-label="Page navigation">
        <ul class="tb-pagination-list">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span
                        class="tb-pagination-link is-disabled"
                        aria-disabled="true"
                        aria-label="Previous page"
                    >
                        ‹
                    </span>
                </li>
            @else
                <li>
                    <a
                        class="tb-pagination-link"
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        aria-label="Previous page"
                    >
                        ‹
                    </a>
                </li>
            @endif

            {{-- Numbered pages around the current page --}}
            @php
                $startPage = max(1, $paginator->currentPage() - 3);
                $endPage = min($paginator->lastPage(), $paginator->currentPage() + 3);
            @endphp

            @for ($page = $startPage; $page <= $endPage; $page++)
                <li>
                    @if ($page === $paginator->currentPage())
                        <span
                            class="tb-pagination-link is-current"
                            aria-current="page"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                            class="tb-pagination-link"
                            href="{{ $paginator->url($page) }}"
                            aria-label="Page {{ $page }}"
                        >
                            {{ $page }}
                        </a>
                    @endif
                </li>
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a
                        class="tb-pagination-link"
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        aria-label="Next page"
                    >
                        ›
                    </a>
                </li>
            @else
                <li>
                    <span
                        class="tb-pagination-link is-disabled"
                        aria-disabled="true"
                        aria-label="Next page"
                    >
                        ›
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif