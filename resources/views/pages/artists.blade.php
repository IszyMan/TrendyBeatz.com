@extends('layouts.app')

@section(
    'title',
    'Artists Profiles & Full Album Downloads | TrendyBeatz'
)

@section(
    'meta_description',
    'Explore Nigerian, Ghanaian and African artists on TrendyBeatz. Browse artist profiles alphabetically and discover their music, videos and albums.'
)

@section('canonical', route('artists.index'))

@section(
    'social_title',
    'Artists Profiles & Full Album Downloads | TrendyBeatz'
)

@section(
    'social_description',
    'Discover Nigerian, Ghanaian and African artists, their profiles, music, videos and albums on TrendyBeatz.'
)

@section('content')
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

        <section class="tb-artists-find" aria-labelledby="find-artists-heading">
            <h2 class="sub-section-heading" id="find-artists-heading">
                Search for Artists Alphabetically
            </h2>

            <label class="tb-artists-search-label" for="artistSearch">
                Search artists shown on this page
            </label>

            <input
                id="artistSearch"
                class="tb-artists-search"
                type="search"
                placeholder="Type an artist name..."
                autocomplete="off"
                data-artist-search
            >

            <nav class="tb-artists-alphabet" aria-label="Artist alphabet">
                @foreach (range('A', 'Z') as $letter)
                    <button
                        type="button"
                        data-artist-letter="{{ $letter }}"
                        aria-label="Show artists beginning with {{ $letter }}"
                    >
                        {{ $letter }}
                    </button>
                @endforeach

                <button type="button" data-artist-letter="">
                    All
                </button>
            </nav>

            <p class="tb-artists-search-note">
                Search and letter filters apply to the artists currently
                displayed in each country section.
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

            const search = page.querySelector('[data-artist-search]');
            const letterButtons = page.querySelectorAll('[data-artist-letter]');
            let activeLetter = '';

            function filterCards() {
                const term = search.value.trim().toLocaleLowerCase();

                page.querySelectorAll('.tb-artist-card').forEach((card) => {
                    const name = card
                        .querySelector('.tb-artist-card-name')
                        .textContent
                        .trim()
                        .toLocaleLowerCase();

                    const matchesTerm = !term || name.includes(term);
                    const matchesLetter = !activeLetter
                        || name.startsWith(activeLetter.toLocaleLowerCase());

                    card.hidden = !(matchesTerm && matchesLetter);
                });
            }

            search.addEventListener('input', filterCards);

            letterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activeLetter = button.dataset.artistLetter;

                    letterButtons.forEach((item) => {
                        item.setAttribute(
                            'aria-pressed',
                            item === button ? 'true' : 'false'
                        );
                    });

                    filterCards();
                });
            });

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

                try {
                    section.setAttribute('aria-busy', 'true');

                    const response = await fetch(link.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Artist page could not be loaded.');
                    }

                    const markup = await response.text();
                    const template = document.createElement('template');
                    template.innerHTML = markup.trim();

                    const replacement = template.content.querySelector(
                        '[data-artist-section]'
                    );

                    if (!replacement) {
                        throw new Error('Artist section was missing.');
                    }

                    section.replaceWith(replacement);
                    filterCards();
                    replacement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                } catch (error) {
                    window.location.href = link.href;
                } finally {
                    section.removeAttribute('aria-busy');
                }
            });
        });
    </script>
@endsection