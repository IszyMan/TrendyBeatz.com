@extends('layouts.admin')

@php
    $editing = $listing !== null;

    $selectedArtist = old(
        'Artists_Id',
        $listing->Artists_Id ?? ''
    );

    $selectedAlbum = old(
        'album_id',
        $listing->album_id ?? 0
    );
@endphp

@section('title', $editing ? 'Edit Listing' : 'Add Listing')

@section('content')
    <div class="admin-page-head">
        <h1>{{ $editing ? 'Edit Listing #' . $listing->id : 'Add Listing' }}</h1>

        <a href="{{ route('admin.listings.index') }}">
            ← All Listings
        </a>
    </div>

    <form
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
                        value="{{ old('listing_id') }}"
                        placeholder="Leave blank for automatic ID"
                    >
                </label>
            @endunless

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
                            value="{{ $artist->Artists_Id }}"
                            @selected($selectedArtist === $artist->Artists_Id)
                        >
                            {{ $artist->Stage_Name ?: $artist->ArtistsName }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="admin-field">
                Album — optional
                <select
                    name="album_id"
                    id="listing-album"
                    data-selected="{{ $selectedAlbum }}"
                >
                    <option value="0">No album</option>
                </select>
            </label>

            <label class="admin-field">
                Track title
                <input
                    type="text"
                    name="TrackTitle"
                    maxlength="1000"
                    value="{{ old('TrackTitle', $listing->TrackTitle ?? '') }}"
                    required
                >
            </label>

            <label class="admin-field">
                Featuring
                <input
                    type="text"
                    name="Featuring"
                    value="{{ old('Featuring', $listing->Featuring ?? '') }}"
                >
            </label>

            @php
                $selectedListingType = strtolower(trim((string) old(
                    'ListingType',
                    $listing->ListingType ?? ''
                )));
            @endphp

            <label class="admin-field">
                Listing type

                <select name="ListingType" required>
                    <option value="">Choose type</option>

                    <option value="Audio" @selected($selectedListingType === 'audio')>
                        Audio
                    </option>

                    <option value="video" @selected($selectedListingType === 'video')>
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
                            @selected(old(
                                'country_id',
                                $listing->country_id ?? 'naija'
                            ) === $value)
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
                    value="{{ old('YearOfRelease', $listing->YearOfRelease ?? '') }}"
                    placeholder="2026"
                >
            </label>

            <label class="admin-field">
                Produced by
                <input
                    type="text"
                    name="producedby"
                    value="{{ old('producedby', $listing->producedby ?? '') }}"
                >
            </label>

            <label class="admin-field">
                Directed by
                <input
                    type="text"
                    name="directedby"
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
                    value="{{ old('buy_song', $listing->buy_song ?? '') }}"
                >
            </label>


            <label class="admin-field admin-field-wide">
                YouTube embed iframe

                <textarea
                    name="youtube_embed_url"
                    id="youtube-embed-url"
                    rows="3"
                    placeholder='<iframe src="https://www.youtube.com/embed/VIDEO_ID"></iframe>'
                >{{ old('youtube_embed_url', $listing->youtube_embed_url ?? '') }}</textarea>

                <span id="youtube-embed-preview" class="admin-embed-preview" hidden></span>
            </label>

            <label class="admin-field admin-field-wide">
                Audiomack embed iframe

                <textarea
                    name="audiomack_embed_url"
                    id="audiomack-embed-url"
                    rows="3"
                    placeholder='<iframe src="https://audiomack.com/embed/song/artist/title"></iframe>'
                >{{ old('audiomack_embed_url', $listing->audiomack_embed_url ?? '') }}</textarea>

                <span id="audiomack-embed-preview" class="admin-embed-preview" hidden></span>
            </label>
                        

            <label class="admin-field admin-field-wide">
                Introduction
                <textarea name="introduction">{{ old(
                    'introduction',
                    $listing->introduction ?? ''
                ) }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 1
                <textarea name="track_info_1">{{ old(
                    'track_info_1',
                    $listing->TrackInfo ?? ''
                ) }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 2
                <textarea name="track_info_2">{{ old(
                    'track_info_2',
                    $listing->trackinfo1 ?? ''
                ) }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Track Info 3
                <textarea name="track_info_3">{{ old(
                    'track_info_3',
                    $listing->trackinfo2 ?? ''
                ) }}</textarea>
            </label>

            <label class="admin-field admin-field-wide">
                Embed/script URL
                <textarea name="scriptUrl">{{ old(
                    'scriptUrl',
                    $listing->scriptUrl ?? ''
                ) }}</textarea>
            </label>
        </div>

        <div class="admin-checks">

            <label>
                    <input
                        type="checkbox"
                        name="IsPublished"
                        value="1"
                        @checked(old(
                            'IsPublished',
                            ($listing->IsPublished ?? 'NO') === 'YES' ? 1 : 0
                        ))
                    >
                    Published
            </label>
            <label>
                <input
                    type="checkbox"
                    name="isgospel"
                    value="1"
                    @checked(old('isgospel', $listing->isgospel ?? 0))
                >
                Gospel
            </label>

            <label>
                <input
                    type="checkbox"
                    name="ishighlife"
                    value="1"
                    @checked(old('ishighlife', $listing->ishighlife ?? 0))
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

                if (value.startsWith('<')) {
                    const documentFragment = new DOMParser().parseFromString(
                        value,
                        'text/html'
                    );

                    value = documentFragment.querySelector('iframe')?.getAttribute('src')
                        || '';
                }

                try {
                    return new URL(value);
                } catch {
                    return null;
                }
            }

            function previewUrl(value, provider) {
                const url = sourceFromInput(value);

                if (!url || url.protocol !== 'https:') {
                    return null;
                }

                const host = url.hostname.toLowerCase();
                const path = url.pathname;

                if (provider === 'youtube') {
                    if (
                        ![
                            'youtube.com',
                            'www.youtube.com',
                            'www.youtube-nocookie.com'
                        ].includes(host)
                    ) {
                        return null;
                    }

                    const match = path.match(/^\/embed\/([A-Za-z0-9_-]{11})$/);

                    return match
                        ? `https://www.youtube-nocookie.com/embed/${match[1]}`
                        : null;
                }

                if (
                    !['audiomack.com', 'www.audiomack.com'].includes(host)
                    || !/^\/embed\/[A-Za-z0-9_-]+\/(song|album|playlist)\/[A-Za-z0-9_-]+\/?$/.test(path)
                ) {
                    return null;
                }

                return `https://audiomack.com${path.replace(/\/$/, '')}`;
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
        });
    </script>

    <script>
        const artistSelect = document.getElementById('listing-artist');
        const albumSelect = document.getElementById('listing-album');
        const selectedAlbum = String(albumSelect.dataset.selected || '0');

        async function loadArtistAlbums(keepSelection = false) {
            const artistId = artistSelect.value;

            albumSelect.replaceChildren(new Option('No album', '0'));
            albumSelect.disabled = !artistId;

            if (!artistId) {
                return;
            }

            try {
                const url = artistSelect.dataset.albumsUrl
                    + '/'
                    + encodeURIComponent(artistId)
                    + '/albums';

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Could not load albums');
                }

                const albums = await response.json();

                for (const album of albums) {
                    albumSelect.add(
                        new Option(album.title, String(album.id))
                    );
                }

                if (keepSelection) {
                    albumSelect.value = selectedAlbum;
                }
            } catch (error) {
                albumSelect.replaceChildren(
                    new Option('Could not load albums', '0')
                );
            }
        }

        artistSelect.addEventListener('change', () => {
            loadArtistAlbums(false);
        });

        loadArtistAlbums(true);
    </script>
@endsection