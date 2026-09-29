@php
    $editing = $mix !== null;
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
    @endif

    <label class="admin-field">
        DJ
        <select name="dj_id" required>
            <option value="">Choose DJ</option>

            @foreach ($djs as $dj)
                <option
                    value="{{ $dj->id }}"
                    @selected((string) old('dj_id', $mix->dj_id ?? '') === (string) $dj->id)
                >
                    {{ $dj->dj_name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="admin-field">
        Mix title
        <input
            type="text"
            name="mix_title"
            maxlength="150"
            value="{{ old('mix_title', $mix->mix_title ?? '') }}"
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
            value="{{ old('released_year', $mix->released_year ?? '') }}"
        >
    </label>

    <label class="admin-field">
        Audio filename
        <input
            type="text"
            name="track_url"
            maxlength="100"
            value="{{ old('track_url', $mix->track_url ?? '') }}"
            placeholder="my-dj-mix.mp3"
        >
        <small>Add Mix Filename only</small>
    </label>

    <label class="admin-field admin-field-wide">
        Details
        <textarea
            name="details"
            rows="4"
            maxlength="1000"
        >{{ old('details', $mix->details ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Additional details
        <textarea
            name="details2"
            rows="4"
            maxlength="1000"
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
        @if ($editing && $mix->cover_url)
            <small>Current: {{ $mix->cover_url }}</small>
        @endif
    </label>

    <label class="admin-field">
        Back cover
        <input
            type="file"
            name="back_cover_image"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >
        @if ($editing && $mix->back_cover)
            <small>Current: {{ $mix->back_cover }}</small>
        @endif
    </label>

    <input type="hidden" name="IsPublished" value="NO">

    <div class="admin-checks admin-field-wide">
        <label>
            <input
                type="checkbox"
                name="IsPublished"
                value="YES"
                @checked(old('IsPublished', $mix->IsPublished ?? 'NO') === 'YES')
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