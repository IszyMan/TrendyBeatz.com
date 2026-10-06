@extends('layouts.app')

@section(
    'title',
    'Discover Artists Profiles & Full Album Legal Downloads | TrendyBeatz'
)

@section(
    'meta_description',
    'Explore Nigerian, Ghanaian and African artists on TrendyBeatz. Browse artist profiles alphabetically and discover their music, videos and albums.'
)

@section('canonical', route('artists.index'))

@section(
    'social_title',
    'Discover Artists Profiles & Full Album Legal Downloads | TrendyBeatz'
)

@section(
    'social_description',
    'Discover Nigerian, Ghanaian and African musical artists, their profiles, music, videos and albums on TrendyBeatz.'
)

@section('content')
    @php
        $searchTerm = trim((string) request('search', ''));
        $activeLetter = strtoupper(trim((string) request('letter', '')));
    
        $hasArtistFilter = $searchTerm !== '' || $activeLetter !== '';
    @endphp
    <main class="tb-artists-page">
        <header class="tb-artists-header">
            <h1 class="section-heading">
                TrendyBeatz Hall Of Fame
            </h1>

            @php
                $currentDate = now();
            @endphp

            <p class="tb-day-page-date">
                <time datetime="{{ $currentDate->toDateString() }}">
                    {{ $currentDate->format('M d, Y') }}
                </time>
            </p>

            <p class="tb-artists-intro">
                Explore popular artists and discover musicians from
                Nigeria, Ghana and across Africa.
            </p>
        </header>

        @if (! $hasArtistFilter)
            <section class="tb-artists-popular" aria-labelledby="popular-artists-heading">
                <h2 class="sub-section-heading" id="popular-artists-heading">
                    Popular Artists
                </h2>
        
                <div class="tb-artists-grid">
                    @foreach ($popularArtists as $artist)
                        @include('partials.artists.card', [
                            'artist' => $artist,
                        ])
                    @endforeach
                </div>
            </section>
        @endif

        <section class="tb-artists-find" aria-labelledby="find-artists-heading">
            <h2 class="sub-section-heading" id="find-artists-heading">
                Search for Artists Alphabetically
            </h2>
        
            <form method="GET" action="{{ route('artists.index') }}">
                <label class="tb-artists-search-label" for="artistSearch">
                    Search all published artists
                </label>
        
                <input
                    id="artistSearch"
                    class="tb-artists-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Type an artist name..."
                    autocomplete="off"
                >
        
                <button type="submit">Search</button>
            </form>
        
            <nav class="tb-artists-alphabet" aria-label="Artist alphabet">
                @foreach (range('A', 'Z') as $letter)
                    <a
                        href="{{ route('artists.index', [
                            'letter' => $letter,
                            'search' => $searchTerm,
                        ]) }}"
                        class="{{ $activeLetter === $letter ? 'is-active' : '' }}"
                        @if ($activeLetter === $letter)
                            aria-current="page"
                        @endif
                        aria-label="Show artists beginning with {{ $letter }}"
                    >
                        {{ $letter }}
                    </a>
                @endforeach
            
                <a
                    href="{{ route('artists.index') }}"
                    class="{{ ! $hasArtistFilter ? 'is-active' : '' }}"
                    @if (! $hasArtistFilter)
                        aria-current="page"
                    @endif
                >
                    All
                </a>
            </nav>
        
            <p class="tb-artists-search-note">
                Search and alphabet filters cover all published artists,
                including artists on other pages.
            </p>
        </section>

        @foreach ($countries as $country => $section)
            @include('partials.artists.country-section', [
                'country' => $country,
                'heading' => $section['heading'],
                'artists' => $section['artists'],
            ])
        @endforeach
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const page = document.querySelector('.tb-artists-page');
    
            if (!page) {
                return;
            }
    
            page.addEventListener('click', async (event) => {
                const link = event.target.closest(
                    '[data-artist-section] .tb-artists-pagination a'
                );
    
                if (!link) {
                    return;
                }
    
                const section = link.closest('[data-artist-section]');
    
                if (!section) {
                    return;
                }
    
                event.preventDefault();
    
                const url = new URL(link.href);
                const filters = new URLSearchParams(window.location.search);
    
                ['search', 'letter'].forEach((key) => {
                    const value = filters.get(key);
    
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                });
    
                try {
                    section.setAttribute('aria-busy', 'true');
    
                    const response = await fetch(url.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
    
                    if (!response.ok) {
                        throw new Error('Artist page could not be loaded.');
                    }
    
                    const template = document.createElement('template');
                    template.innerHTML = (await response.text()).trim();
    
                    const replacement = template.content.querySelector(
                        '[data-artist-section]'
                    );
    
                    if (!replacement) {
                        throw new Error('Artist section was missing.');
                    }
    
                    section.replaceWith(replacement);
    
                    replacement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                } catch (error) {
                    window.location.href = url.href;
                } finally {
                    section.removeAttribute('aria-busy');
                }
            });
        });
    </script>
@endsection