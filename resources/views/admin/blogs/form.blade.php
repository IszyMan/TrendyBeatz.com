@php
    $editing = $blog !== null;
@endphp

<form
    method="POST"
    action="{{ $editing
        ? route('admin.blogs.update', $blog->id)
        : route('admin.blogs.store') }}"
    enctype="multipart/form-data"
    id="blog-form"
>
    @csrf

    @if ($editing)
        @method('PUT')
    @endif

    <input
        type="hidden"
        name="description"
        id="blog-body-input"
        value="{{ old('description', $blog->description ?? '') }}"
    >

    <section class="blog-section">
        <div class="blog-section-heading">Post Details</div>

        <div class="blog-section-body">
            <label class="admin-field">
                Post title
                <input
                    type="text"
                    name="title"
                    id="blog-title-input"
                    value="{{ old('title', $blog->title ?? '') }}"
                    required
                >
            </label>
            
            @if (!$editing)
                <label class="admin-field">
                    Blog slug
                    <input
                        type="text"
                        id="blog-slug-preview"
                        value="{{ \Illuminate\Support\Str::slug(old('title', '')) }}"
                        placeholder="Slug will appear as you type the title"
                        readonly
                        style="background: #f3f4f6; color: #555;"
                    >
                    <small>Automatically generated from the post title.</small>
                </label>
            @endif

            <label class="admin-field">
                Category
                <select name="category_id" required>
                    <option value="">Select category</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) old('category_id', $blog->category_id ?? '') === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="admin-field">
                Intro
                <textarea
                    name="intro"
                    rows="3"
                    required
                >{{ old('intro', $blog->intro ?? '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="blog-section">
        <div class="blog-section-heading">Featured Image</div>

        <div class="blog-section-body">
            @if ($editing && $blog->photo)
                <div class="blog-current-image">
                    <img
                        src="{{ asset('images/blog/' . basename($blog->photo)) }}"
                        alt="Current featured image"
                    >
                    <small>{{ $blog->photo }}</small>
                </div>
            @endif

            <label class="admin-field">
                Upload image (JPG, PNG, GIF or WebP; maximum 4 MB)
                <input
                    type="file"
                    name="photo_upload"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    id="blog-photo-input"
                >
            </label>

            <img
                class="blog-image-preview"
                id="blog-photo-preview"
                alt="New image preview"
            >
        </div>
    </section>

    <section class="blog-section">
        <div class="blog-section-heading">Body / Full Content</div>

        <div class="blog-section-body">
            <div id="blog-editor" class="blog-editor"></div>
            <small>Use the toolbar to add headings, formatting, links and lists.</small>
        </div>
    </section>

    <section class="blog-section">
        <div class="blog-section-heading">Embed Code (optional)</div>
    
        <div class="blog-section-body">
            <label class="admin-field">
                YouTube, Audiomack or SoundCloud iframe
    
                <textarea
                    name="scriptUrl"
                    rows="6"
                    data-blog-embed
                    data-preview="blog-embed-preview"
                    placeholder='<iframe src="https://..."></iframe>'
                >{{ old('scriptUrl', $blog->scriptUrl ?? $blog->script_url ?? '') }}</textarea>
            </label>
    
            <div
                id="blog-embed-preview"
                class="admin-blog-embed-preview"
                hidden
            ></div>
        </div>
    </section>
    
    <section class="blog-section">
        <div class="blog-section-heading">Second Embed Code (optional)</div>
    
        <div class="blog-section-body">
            <label class="admin-field">
                YouTube, Audiomack or SoundCloud iframe
    
                <textarea
                    name="script_url2"
                    rows="6"
                    data-blog-embed
                    data-preview="blog-second-embed-preview"
                    placeholder='<iframe src="https://..."></iframe>'
                >{{ old('script_url2', $blog->script_url2 ?? '') }}</textarea>
    
                @error('script_url2')
                    <small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </label>
    
            <div
                id="blog-second-embed-preview"
                class="admin-blog-embed-preview"
                hidden
            ></div>
        </div>
    </section>

    

    <section class="blog-section">
        <div class="blog-section-heading">
            Publishing options
        </div>

        <div class="blog-section-body">
            <input type="hidden" name="IsPublished" value="NO">
            <input type="hidden" name="hide_from_source" value="0">

            <div
                class="blog-publish"
                style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;"
            >
                <label>
                    <input
                        type="checkbox"
                        name="IsPublished"
                        value="YES"
                        @checked(
                            old('IsPublished', $blog->IsPublished ?? 'NO') === 'YES'
                            )
                        >

                        Publish immediately
                    </label>

                    <label>
                        <input
                            type="checkbox"
                            name="hide_from_source"
                            value="1"
                            role="switch"
                            @checked(
                                (int) old(
                                    'hide_from_source',
                                    $blog->hide_from_source ?? 0
                                ) === 1
                            )
                        >

                        Hide from Source
                    </label>
                </div>

                <small style="display: block; margin-top: 10px; color: #666;">
                    When the hide from source is enabled, the description loads separately for visitors.
                    The initial page source shows “Loading…” instead.
                </small>

                @error('hide_from_source')
                    <small style="color: #b91c1c;">
                        {{ $message }}
                    </small>
                @enderror
            </div>
        </section>

    <div class="blog-actions">
        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create Post' }}
        </button>

        <a href="{{ route('admin.blogs.index') }}">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.getElementById('blog-title-input');
    const slugPreview = document.getElementById('blog-slug-preview');
    
    if (titleInput && slugPreview) {
        const generateSlug = (title) => {
            return title
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/@/g, ' at ')
                .toLowerCase()
                .replace(/[^a-z0-9\s_-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        };
    
        titleInput.addEventListener('input', () => {
            slugPreview.value = generateSlug(titleInput.value);
        });
    }
    
    document.querySelectorAll('[data-blog-embed]').forEach(input => {
        const preview = document.getElementById(input.dataset.preview);
        let timer;

        const renderPreview = () => {
            preview.replaceChildren();
            preview.hidden = true;

            const documentHtml = new DOMParser().parseFromString(
                input.value,
                'text/html'
            );

            const original = documentHtml.querySelector('iframe');

            if (!original) {
                return;
            }

            let url;

            try {
                url = new URL(original.getAttribute('src'));
            } catch {
                return;
            }

            const allowedHosts = [
                'youtube.com',
                'youtube-nocookie.com',
                'audiomack.com',
                'soundcloud.com'
            ];

            const allowed = url.protocol === 'https:' &&
                allowedHosts.some(host =>
                    url.hostname === host ||
                    url.hostname.endsWith('.' + host)
                );

            if (!allowed) {
                return;
            }

            const iframe = document.createElement('iframe');

            iframe.src = url.href;
            iframe.title = original.getAttribute('title') || 'Embed preview';

            const height = parseInt(original.getAttribute('height'), 10);
            iframe.height = Number.isFinite(height) && height > 0
                ? Math.min(height, 1000)
                : 315;

            iframe.setAttribute(
                'allow',
                'autoplay; encrypted-media; fullscreen; picture-in-picture'
            );

            iframe.setAttribute('allowfullscreen', '');
            iframe.setAttribute(
                'sandbox',
                'allow-scripts allow-same-origin allow-popups allow-presentation'
            );

            preview.appendChild(iframe);
            preview.hidden = false;
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(renderPreview, 500);
        });

        renderPreview();
    });
});
</script>