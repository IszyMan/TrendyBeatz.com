@php
    $editing = $mix !== null;

    $selectedDj = (string) old(
        'dj_id',
        $mix->dj_id ?? ''
    );
@endphp

<form
    class="admin-panel admin-form-grid"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $editing
        ? route('admin.dj-mixes.update', $mix->id)
        : route('admin.dj-mixes.store') }}"
>
    @csrf

    @if ($editing)
        @method('PUT')

        <p class="admin-field-wide">
            <strong>Mix ID:</strong> {{ $mix->id }}
        </p>
    @endif

    <label class="admin-field">
        DJ

        <select name="dj_id" required>
            <option value="">Choose DJ</option>

            @foreach ($djs as $dj)
                <option
                    value="{{ $dj->id }}"
                    @selected($selectedDj === (string) $dj->id)
                >
                    {{ $dj->name ?: $dj->full_name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="admin-field">
        Mix title

        <input
            type="text"
            name="mix_title"
            maxlength="191"
            value="{{ old('mix_title', $mix->title ?? '') }}"
            required
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
            value="{{ old('released_year', $mix->released_year ?? '') }}"
        >
    </label>

    <label class="admin-field">
        Audio filename

        <input
            type="text"
            name="track_url"
            maxlength="191"
            value="{{ old('track_url', $mix->track_url ?? '') }}"
            placeholder="my-dj-mix.mp3"
        >

        <small>
            Enter the filename only, without a URL or folder.
            @if ($editing)
                Leave blank to keep the existing filename.
            @endif
        </small>
    </label>

    <label class="admin-field admin-field-wide">
        Introduction

        <textarea
            name="introduction"
            rows="4"
        >{{ old('introduction', $mix->introduction ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Details

        <textarea
            name="details"
            rows="4"
        >{{ old('details', $mix->details ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Additional details

        <textarea
            name="details2"
            rows="4"
        >{{ old('details2', $mix->details2 ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Description 1

        <textarea
            name="description1"
            rows="6"
        >{{ old('description1', $mix->description1 ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Description 2

        <textarea
            name="description2"
            rows="6"
        >{{ old('description2', $mix->description2 ?? '') }}</textarea>
    </label>

    <label class="admin-field">
        Front cover

        <input
            type="file"
            name="cover_image"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        @if ($editing && filled($mix->cover_url))
            <small>
                Current image: {{ $mix->cover_url }}
            </small>
        @endif
    </label>

    <label class="admin-field">
        Back cover

        <input
            type="file"
            name="back_cover_image"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        @if ($editing && filled($mix->back_cover))
            <small>
                Current image: {{ $mix->back_cover }}
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
                    old(
                        'IsPublished',
                        (int) ($mix->is_published ?? 0) === 1 ? 'YES' : 'NO'
                    ) === 'YES'
                )
            >

            Published
        </label>
    </div>

    <div class="admin-field-wide">
        <button
            class="admin-button"
            type="submit"
            @disabled($djs->isEmpty())
        >
            {{ $editing ? 'Save Changes' : 'Create DJ Mix' }}
        </button>

        @if ($djs->isEmpty())
            <p>Create a DJ profile before adding a mix.</p>
        @endif
    </div>
</form>