@php
    $editing = $artist !== null;
@endphp

<form
    class="admin-panel admin-form-grid"
    method="POST"
    enctype="multipart/form-data"
    action="{{ $editing
        ? route('admin.artists.update', $artist->id)
        : route('admin.artists.store') }}"
>
    @csrf

    @if ($editing)
        @method('PUT')
        <p class="admin-field-wide">
            <strong>Artist ID:</strong> {{ $artist->Artists_Id }}
        </p>
    @endif

    @foreach ([
        'Stage_Name' => 'Stage name',
        'Fullname' => 'Full name',
        'RecordLabel' => 'Record label',
        'Genres' => 'Genres',
    ] as $field => $label)
        <label class="admin-field">
            {{ $label }}
            <input
                type="text"
                name="{{ $field }}"
                value="{{ old($field, $artist->{$field} ?? '') }}"
                @if ($field === 'Stage_Name') required @endif
            >
        </label>
    @endforeach

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
                    @selected(old('country_id', $artist->country_id ?? 'naija') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="admin-field">
        Net worth ID
        <input
            type="number"
            name="networth_id"
            min="0"
            value="{{ old('networth_id', $artist->networth_id ?? '') }}"
        >
    </label>

    <label class="admin-field">
        Also comedian?
        <select name="Is_also_comedian" required>
            <option
                value="0"
                @selected((string) old('Is_also_comedian', $artist->Is_also_comedian ?? 0) === '0')
            >No</option>
            <option
                value="1"
                @selected((string) old('Is_also_comedian', $artist->Is_also_comedian ?? 0) === '1')
            >Yes</option>
        </select>
    </label>

    <label class="admin-field admin-field-wide">
        Artist biography
        <textarea
            name="Place_Birth"
            rows="10"
            placeholder="Write the artist biography..."
        >{{ old('Place_Birth', $artist->Place_Birth ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Profile image
        <input
            type="file"
            name="profile_image"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        @if ($editing && $artist->ProfilePic)
            <small>Current image: {{ $artist->ProfilePic }}</small>
        @endif
    </label>

    <input type="hidden" name="IsPublished" value="NO">

    <div class="admin-checks admin-field-wide">
        <label>
            <input
                type="checkbox"
                name="IsPublished"
                value="YES"
                @checked(old('IsPublished', $artist->IsPublished ?? 'NO') === 'YES')
            >
            Published
        </label>
    </div>

    <div class="admin-field-wide">
        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create Artist' }}
        </button>
    </div>
</form>