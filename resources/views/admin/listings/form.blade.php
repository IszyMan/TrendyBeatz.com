@extends('layouts.admin')

@php
    $editing = $listing !== null;

    $selectedArtist = (string) old(
        'Artists_Id',
        $listing->Artists_Id ?? ''
    );

    $selectedAlbum = (string) (
        old('album_id', $listing->album_id ?? 0) ?: 0
    );

    $selectedListingType = strtolower(trim((string) old(
        'ListingType',
        $listing->ListingType ?? ''
    )));
@endphp

@section('title', $editing ? 'Edit Listing' : 'Add Listing')

@section('content')
    <div class="admin-page-head">
        <h1>
            {{ $editing ? 'Edit Listing #' . $listing->id : 'Add Listing' }}
        </h1>

        <a href="{{ route('admin.listings.index') }}">
            ← All Listings
        </a>
    </div>

    @if ($errors->any())
        <div class="admin-panel" role="alert">
            <strong>Please correct these fields:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="listing-form"
        class="admin-panel"
        method="POST"
        enctype="multipart/form-data"
        action="{{ $editing
            ? route('admin.listings.update', $listing->id)
            : route('admin.listings.store') }}"
    >
        @csrf

        @if ($editing)
            @method('PUT')
        @endif

        <div class="admin-form-grid">
            @unless ($editing)
                <label class="admin-field">
                    Listing ID — optional

                    <input
                        type="number"
                        name="listing_id"
                        min="1"
                        max="2147483647"
                        value="{{ old('listing_id') }}"
                        placeholder="Leave blank for automatic ID"
                    >
                </label>
            @endunless

            <label class="admin-field">
                Album — optional

                <select
                    name="album_id"
                    id="listing-album"
                    data-selected="{{ $selectedAlbum }}"
                    disabled
                >
                    <option value="0">No album</option>
                </select>

                <small id="listing-album-status" role="status"></small>
            </label>

            <label class="admin-field">
                Artist

                <select
                    name="Artists_Id"
                    id="listing-artist"
                    data-albums-url="{{ url('/admin/artists') }}"
                    required
                >
                    <option value="">Select artist</option>

                    @foreach ($artists as $artist)
                        <option
                            value="{{ $artist->id }}"
                            @selected($selectedArtist === (string) $artist->id)
                        >
                            {{ $artist->stage_name ?: $artist->full_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            

            <label class="admin-field">
                Track title

                <input
                    type="text"
                    name="TrackTitle"
                    maxlength="191"
                    value="{{ old('TrackTitle', $listing->TrackTitle ?? '') }}"
                    required
                >
            </label>

            <label class="admin-field">
                Featuring

                <input
                    type="text"
                    name="Featuring"
                    maxlength="191"
                    value="{{ old('Featuring', $listing->Featuring ?? '') }}"
                >
            </label>

            <label class="admin-field">
                Listing type

                <select name="ListingType" required>
                    <option value="">Choose type</option>

                    <option
                        value="Audio"
                        @selected($selectedListingType === 'audio')
                    >
                        Audio
                    </option>

                    <option
                        value="video"
                        @selected($selectedListingType === 'video')
                    >
                        Video
                    </option>
                </select>
            </label>

            <label class="admin-field">
                Country

                <select name="country_id" required>
                    @foreach ([
                        'naija' => 'Naija',
                        'ghana' => 'Ghana',
                        'african' => 'African',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(
                                old('country_id', $listing->country_id ?? 'naija')
                                    === $value
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="admin-field">
                Year of release

                <input
                    type="text"
                    name="YearOfRelease"
                    maxlength="191"
                    value="{{ old(
                        'YearOfRelease',
                        $listing->YearOfRelease ?? ''
                    ) }}"
                    placeholder="2026"
                >
            </label>

            <label class="admin-field">
                Produced by

                <input
                    type="text"
                    name="producedby"
                    maxlength="191"
                    value="{{ old('producedby', $listing->producedby ?? '') }}"
                >
            </label>

            <label class="admin-field">
                Directed by

                <input
                    type="text"
                    name="directedby"
                    maxlength="191"
                    value="{{ old('directedby', $listing->directedby ?? '') }}"
                >
            </label>

            <label class="admin-field">
                Album track number

                <input
                    type="number"
                    name="track_number"
                    min="1"
                    value="{{ old('track_number', $listing->track_number ?? 1) }}"
                >
            </label>

            <label class="admin-field admin-field-wide">
                Cover image

                <input
                    type="file"
                    name="cover_image"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                >

                @if ($editing && filled($listing->CoverUrl))
                    <small>
                        Current image: {{ $listing->CoverUrl }}
                    </small>
                @endif
            </label>

            <label class="admin-field admin-field-wide">
                Media filename or full file URL

                <input
                    type="text"
                    name="media_url"
                    maxlength="2000"
                    value="{{ old('media_url') }}"
                    placeholder="2Baba-Intro-Skit-[TrendyBeatz.com].mp3"
                >

                @if ($editing && filled($listing->TrackUrl))
                    <small>
                        Stored filename: {{ $listing->TrackUrl }}
                        <br>
                        Leave blank to keep it.
                    </small>
                @endif
            </label>

            <label class="admin-field admin-field-wide">
                Digital store URL

                <input
                    type="text"
                    name="buy_song"
                    maxlength="191"
                    value="{{ old('buy_song', $listing->buy_song ?? '') }}"
                >
            </label>

            <label class="admin-field admin-field-wide">
                YouTube embed iframe

                <textarea
                    name="youtube_embed_url"
                    id="youtube-embed-url"
                    rows="3"
                    maxlength="5000"
                    placeholder='<iframe src="https://www.youtube.com/embed/VIDEO_ID"></iframe>'
                >{{ old('youtube_embed_url', $listing->youtube_embed_url ?? '') }}</textarea>

                <span
                    id="youtube-embed-preview"
                    class="admin-embed-preview"
                    hidden
                ></span>
            </label>

            <label class="admin-field admin-field-wide">
                Audiomack embed iframe

                <textarea
                    name="audiomack_embed_url"
                    id="audiomack-embed-url"
                    rows="3"
                    maxlength="5000"
                    placeholder='<iframe src="https://audiomack.com/embed/artist/song/title"></iframe>'
                >{{ old('audiomack_embed_url', $listing->audiomack_embed_url ?? '') }}</textarea>

                <span
                    id="audiomack-embed-preview"
                    class="admin-embed-preview"
                    hidden
                ></span>
            </label>

            <label class="admin-field admin-field-wide">
                Introduction

                <textarea
                    name="introduction"
                    rows="4"
                >{{ old('introduction', $listing->introduction ?? '') }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 1

                <textarea
                    name="track_info_1"
                    rows="6"
                    maxlength="5000"
                >{{ old('track_info_1', $listing->TrackInfo ?? '') }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 2

                <textarea
                    name="track_info_2"
                    rows="6"
                    maxlength="5000"
                >{{ old('track_info_2', $listing->trackinfo1 ?? '') }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 3

                <textarea
                    name="track_info_3"
                    rows="6"
                    maxlength="5000"
                >{{ old('track_info_3', $listing->trackinfo2 ?? '') }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Embed/script URL

                <textarea
                    name="scriptUrl"
                    rows="4"
                >{{ old('scriptUrl', $listing->scriptUrl ?? '') }}</textarea>
            </label>
        </div>

        <input type="hidden" name="IsPublished" value="0">
        <input type="hidden" name="isgospel" value="0">
        <input type="hidden" name="ishighlife" value="0">

        <div class="admin-checks">
            <label>
                <input
                    type="checkbox"
                    name="IsPublished"
                    value="1"
                    @checked(
                        (string) old(
                            'IsPublished',
                            ($listing->IsPublished ?? 'NO') === 'YES' ? 1 : 0
                        ) === '1'
                    )
                >

                Published
            </label>

            <label>
                <input
                    type="checkbox"
                    name="isgospel"
                    value="1"
                    @checked(
                        (string) old('isgospel', $listing->isgospel ?? 0) === '1'
                    )
                >

                Gospel
            </label>

            <label>
                <input
                    type="checkbox"
                    name="ishighlife"
                    value="1"
                    @checked(
                        (string) old('ishighlife', $listing->ishighlife ?? 0) === '1'
                    )
                >

                Highlife
            </label>
        </div>

        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create Listing' }}
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /*
             * YouTube and Audiomack previews.
             * Parse the source and create a fresh iframe rather than
             * inserting the pasted HTML into the page.
             */
            const previews = [
                {
                    input: document.getElementById('youtube-embed-url'),
                    container: document.getElementById('youtube-embed-preview'),
                    provider: 'youtube',
                    title: 'YouTube preview'
                },
                {
                    input: document.getElementById('audiomack-embed-url'),
                    container: document.getElementById('audiomack-embed-preview'),
                    provider: 'audiomack',
                    title: 'Audiomack preview'
                }
            ];

            function sourceFromInput(value) {
                value = value.trim();

                if (!value) {
                    return null;
                }

                if (value.includes('<')) {
                    const parsed = new DOMParser().parseFromString(
                        value,
                        'text/html'
                    );

                    value = parsed.querySelector('iframe')
                        ?.getAttribute('src') || '';
                }

                try {
                    return new URL(value.trim());
                } catch {
                    return null;
                }
            }

            function previewUrl(value, provider) {
                const url = sourceFromInput(value);

                if (
                    !url
                    || url.protocol !== 'https:'
                    || url.username
                    || url.password
                    || (url.port && url.port !== '443')
                ) {
                    return null;
                }

                const host = url.hostname.toLowerCase();
                const path = url.pathname;

                if (provider === 'youtube') {
                    if (![
                        'youtube.com',
                        'www.youtube.com',
                        'youtube-nocookie.com',
                        'www.youtube-nocookie.com'
                    ].includes(host)) {
                        return null;
                    }

                    const match = path.match(
                        /^\/embed\/([A-Za-z0-9_-]{11})\/?$/
                    );

                    return match
                        ? 'https://www.youtube-nocookie.com/embed/' + match[1]
                        : null;
                }

                if (
                    !['audiomack.com', 'www.audiomack.com'].includes(host)
                    || !/^\/embed\/[A-Za-z0-9_-]+\/(song|album|playlist)\/[A-Za-z0-9_-]+\/?$/.test(path)
                ) {
                    return null;
                }

                return 'https://audiomack.com' + path.replace(/\/$/, '');
            }

            function updatePreview(item) {
                const src = previewUrl(item.input.value, item.provider);

                item.container.replaceChildren();
                item.container.hidden = !src;

                if (!src) {
                    return;
                }

                const iframe = document.createElement('iframe');

                iframe.src = src;
                iframe.title = item.title;
                iframe.loading = 'lazy';
                iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
                iframe.allowFullscreen = true;
                iframe.style.width = '100%';
                iframe.style.height = item.provider === 'youtube'
                    ? '315px'
                    : '252px';
                iframe.style.border = '0';

                item.container.appendChild(iframe);
            }

            for (const item of previews) {
                if (!item.input || !item.container) {
                    continue;
                }

                item.input.addEventListener(
                    'input',
                    () => updatePreview(item)
                );

                updatePreview(item);
            }

            /*
             * Load albums using the numeric artist ID.
             * Block saving while loading or after an error, so an
             * existing album is not accidentally cleared.
             */
            const form = document.getElementById('listing-form');
            const artistSelect = document.getElementById('listing-artist');
            const albumSelect = document.getElementById('listing-album');
            const albumStatus = document.getElementById('listing-album-status');

            if (!form || !artistSelect || !albumSelect || !albumStatus) {
                return;
            }

            const initialAlbum = String(
                albumSelect.dataset.selected || '0'
            );

            let activeRequest = null;
            let albumsReady = false;
            let albumError = '';

            async function loadArtistAlbums(keepSelection = false) {
                activeRequest?.abort();

                const controller = new AbortController();
                activeRequest = controller;

                const artistId = artistSelect.value;

                albumsReady = false;
                albumError = '';
                albumSelect.disabled = true;
                albumStatus.textContent = '';

                albumSelect.replaceChildren(
                    new Option('No album', '0')
                );

                if (!artistId) {
                    return;
                }

                albumStatus.textContent = 'Loading albums…';

                try {
                    const url = artistSelect.dataset.albumsUrl
                        + '/'
                        + encodeURIComponent(artistId)
                        + '/albums';

                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json'
                        },
                        signal: controller.signal
                    });

                    if (!response.ok) {
                        throw new Error('Could not load albums');
                    }

                    const albums = await response.json();

                    if (!Array.isArray(albums)) {
                        throw new Error('Invalid album response');
                    }

                    if (controller !== activeRequest) {
                        return;
                    }

                    for (const album of albums) {
                        albumSelect.add(
                            new Option(album.title, String(album.id))
                        );
                    }

                    if (keepSelection && initialAlbum !== '0') {
                        const exists = Array.from(
                            albumSelect.options
                        ).some(option => option.value === initialAlbum);

                        if (!exists) {
                            albumError =
                                'The current album was not returned. '
                                + 'Check its artist and publication status before saving.';

                            albumStatus.textContent = albumError;
                            return;
                        }

                        albumSelect.value = initialAlbum;
                    }

                    albumSelect.disabled = false;
                    albumsReady = true;
                    albumStatus.textContent = '';
                } catch (error) {
                    if (
                        error.name === 'AbortError'
                        || controller !== activeRequest
                    ) {
                        return;
                    }

                    albumError =
                        'Could not load albums. Reload the page before saving.';

                    albumStatus.textContent = albumError;
                }
            }

            artistSelect.addEventListener('change', () => {
                loadArtistAlbums(false);
            });

            form.addEventListener('submit', event => {
                if (!albumsReady) {
                    event.preventDefault();

                    albumStatus.textContent = albumError
                        || 'Select an artist and wait for the albums to load before saving.';
                }
            });

            loadArtistAlbums(true);
        });
    </script>
@endsection