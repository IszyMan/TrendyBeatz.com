@php
    $editing = $dj !== null;
@endphp

<form
    class="admin-panel admin-form-grid"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $editing
        ? route('admin.djs.update', $dj->id)
        : route('admin.djs.store') }}"
>
    @csrf

    @if ($editing)
        @method('PUT')
    @endif

    <label class="admin-field">
        DJ name

        <input
            type="text"
            name="dj_name"
            maxlength="191"
            value="{{ old('dj_name', $dj->name ?? '') }}"
            required
        >
    </label>

    <label class="admin-field">
        Full name

        <input
            type="text"
            name="fullname"
            maxlength="191"
            value="{{ old('fullname', $dj->full_name ?? '') }}"
        >
    </label>

    <label class="admin-field">
        Country

        <select name="country_id">
            <option
                value=""
                @selected(old('country_id', $dj->country_key ?? '') === '')
            >
                Choose country
            </option>

            @foreach ([
                'naija' => 'Naija',
                'ghana' => 'Ghana',
                'african' => 'African',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        old('country_id', $dj->country_key ?? '') === $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="admin-field admin-field-wide">
        DJ biography

        <textarea
            name="place_of_birth"
            rows="10"
            placeholder="Write the DJ biography..."
        >{{ old('place_of_birth', $dj->place_of_birth ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        DJ photo

        <input
            type="file"
            name="photo_upload"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        @if ($editing && filled($dj->profile_img))
            <small>
                Current image: {{ $dj->profile_img }}
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
                    old('IsPublished', $dj->IsPublished ?? 'NO') === 'YES'
                )
            >

            Published
        </label>
    </div>

    <div class="admin-field-wide">
        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create DJ' }}
        </button>
    </div>
</form>