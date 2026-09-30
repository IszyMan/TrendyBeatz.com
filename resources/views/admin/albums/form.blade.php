@php
    $editing = $album !== null;

    $selectedArtist = (string) old(
        'artist_id',
        $album->artist_id ?? ''
    );
@endphp

<form
    class="admin-panel admin-form-grid"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $editing
        ? route('admin.albums.update', $album->id)
        : route('admin.albums.store') }}"
>
    @csrf

    @if ($editing)
        @method('PUT')

        <p class="admin-field-wide">
            <strong>Album ID:</strong> {{ $album->id }}
        </p>
    @endif

   <!-- <label class="admin-field admin-field-wide">
        Search artists

        <input
            type="search"
            id="album-artist-search"
            placeholder="Search by artist name..."
            autocomplete="off"
        >
    </label>-->

    <label class="admin-field admin-field-wide">
        Artist

        <select name="artist_id" id="album-artist" required>
            <option value="">Choose an existing artist</option>

            @foreach ($artists as $artist)
                <option
                    value="{{ $artist->id }}"
                    @selected($selectedArtist === (string) $artist->id)
                >
                    {{ $artist->stage_name ?: $artist->full_name }}
                </option>
            @endforeach
        </select>

        @if ($artists->isEmpty())
            <small>
                Create an artist profile before adding an album.
            </small>
        @endif
    </label>

    <label class="admin-field admin-field-wide">
        Album title

        <input
            type="text"
            name="title"
            maxlength="191"
            value="{{ old('title', $album->title ?? '') }}"
            required
        >
    </label>

    <label class="admin-field">
        Featuring

        <input
            type="text"
            name="featuring"
            maxlength="191"
            value="{{ old('featuring', $album->featuring ?? '') }}"
        >
    </label>

    <label class="admin-field">
        Release year

        <input
            type="number"
            name="released_year"
            min="1900"
            max="2099"
            step="1"
            value="{{ old('released_year', $album->released_year ?? '') }}"
            required
        >
    </label>

    <label class="admin-field">
        Release date

        <input
            type="date"
            name="released_date"
            value="{{ old('released_date', $album->released_date ?? '') }}"
        >
    </label>

    <label class="admin-field admin-field-wide">
        Description

        <textarea
            name="description"
            rows="8"
        >{{ old('description', $album->description ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Album cover

        <input
            type="file"
            name="cover_image"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        @if ($editing && filled($album->cover_url))
            <small>
                Current image: {{ $album->cover_url }}
            </small>
        @endif
    </label>

    <input type="hidden" name="IsPublished" value="NO">

    <div class="admin-checks admin-field-wide">
        <label>
            <input
                type="checkbox"
                name="IsPublished"
                value="YES"
                @checked(
                    old('IsPublished', $album->IsPublished ?? 'NO') === 'YES'
                )
            >

            Published
        </label>
    </div>

    <div class="admin-field-wide">
        <button
            class="admin-button"
            type="submit"
            @disabled($artists->isEmpty())
        >
            {{ $editing ? 'Save Changes' : 'Create Album' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const search = document.getElementById('album-artist-search');
        const select = document.getElementById('album-artist');

        if (!search || !select) {
            return;
        }

        const allOptions = Array.from(select.options).map(option => ({
            value: option.value,
            text: option.textContent.trim()
        }));

        search.addEventListener('input', () => {
            const query = search.value.trim().toLowerCase();
            const selectedValue = select.value;

            select.replaceChildren();

            for (const item of allOptions) {
                if (
                    item.value === ''
                    || item.value === selectedValue
                    || item.text.toLowerCase().includes(query)
                ) {
                    select.add(new Option(
                        item.text,
                        item.value,
                        false,
                        item.value === selectedValue
                    ));
                }
            }

            select.value = selectedValue;
        });
    });
</script>