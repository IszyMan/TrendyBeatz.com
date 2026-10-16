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

    @if (!$editing)
        <label class="admin-field">
            Mix ID (optional)

            <input
                type="number"
                name="mix_id"
                min="1"
                step="1"
                value="{{ old('mix_id') }}"
                placeholder="Leave blank for automatic ID"
            >

            <small>
                Enter an unused ID, or leave blank to assign one automatically.
            </small>

            @error('mix_id')
                <small style="color: #b91c1c;">{{ $message }}</small>
            @enderror
        </label>
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
            placeholder="e.g Dj-Mix-File-Name-(TrendyBeatz.com).mp3"
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
        Description 1

        <textarea
            name="details"
            rows="4"
        >{{ old('details', $mix->details ?? '') }}</textarea>
    </label>

    <label class="admin-field admin-field-wide">
        Description 2

        <textarea
            name="details2"
            rows="4"
        >{{ old('details2', $mix->details2 ?? '') }}</textarea>
    </label>

    <!--
    To be removed
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
    </label>-->
    
     <label class="admin-field admin-field-wide">
        Audiomack embed iframe
    
        <textarea
            name="audiomack_embed_url"
            id="audiomack-embed-url"
            rows="3"
            maxlength="5000"
            placeholder='<iframe src="https://audiomack.com/embed/artist/song/title"></iframe>'
        >{{ old(
            'audiomack_embed_url',
            filled($mix->audiomack_embed_url ?? null)
                ? '<iframe src="' . e($mix->audiomack_embed_url) . '"></iframe>'
                : ''
        ) }}</textarea>
    
        <small>Paste the Audiomack iframe. Clear this field to remove it.</small>
    
        @error('audiomack_embed_url')
            <small style="color: #b91c1c;">{{ $message }}</small>
        @enderror
    </label>
    
    <div
        id="audiomack-embed-preview"
        class="admin-embed-preview admin-field-wide"
        hidden
    ></div>

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



    <label class="admin-field admin-field-wide">
       <span style="color:red"> COPYRIGHT PERMISSION IMAGE </span>

        <input
            type="file"
            name="copyright_image"
            accept="image/jpeg,image/png,image/webp"
        >

        <small>
            Optional. JPG, PNG or WebP.
            Without a permission image, the Fair Use notice is displayed.
        </small>

        @error('copyright_image')
            <span style="color: #b91c1c;">{{ $message }}</span>
        @enderror
    </label>

    @if (filled($mix->copyright_image ?? null))
        <div class="admin-field admin-field-wide">
            <img
                src="{{ asset('images/copyright/' . basename($mix->copyright_image)) }}"
                alt="Current copyright permission"
                style="display: block; max-width: 220px; max-height: 220px; object-fit: contain;"
            >
        </div>

        <label class="admin-field admin-field-wide">
            <input
                type="checkbox"
                name="remove_copyright_image"
                value="1"
                @checked(old('remove_copyright_image'))
            >

            Remove this permission image and use the Fair Use notice
        </label>
    @endif
        
    
   
   

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

<script>
(() => {
    const input = document.getElementById('audiomack-embed-url');
    const preview = document.getElementById('audiomack-embed-preview');

    if (!input || !preview) return;

    let timer;

    function updatePreview() {
        preview.replaceChildren();
        preview.hidden = true;

        const value = input.value.trim();
        if (!value) return;

        const documentFragment = new DOMParser().parseFromString(
            value,
            'text/html'
        );

        const pastedIframe = documentFragment.querySelector('iframe');
        if (!pastedIframe) return;

        let url;

        try {
            url = new URL(pastedIframe.getAttribute('src') || '');
        } catch {
            return;
        }

        if (
            url.protocol !== 'https:' ||
            !['audiomack.com', 'www.audiomack.com'].includes(url.hostname) ||
            !url.pathname.startsWith('/embed/') ||
            url.username ||
            url.password ||
            url.port
        ) {
            return;
        }

        const iframe = document.createElement('iframe');
        iframe.src = url.href;
        iframe.title = 'Audiomack preview';
        iframe.width = '100%';
        iframe.height = '252';
        iframe.setAttribute('scrolling', 'no');
        iframe.style.border = '0';

        preview.appendChild(iframe);
        preview.hidden = false;
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(updatePreview, 400);
    });

    updatePreview();
})();
</script>