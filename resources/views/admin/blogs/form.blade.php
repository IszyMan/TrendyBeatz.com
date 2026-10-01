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
                    value="{{ old('title', $blog->title ?? '') }}"
                    required
                >
            </label>

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
                    placeholder='<iframe src="https://..."></iframe>'
                >{{ old('scriptUrl', $blog->scriptUrl ?? '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="blog-section">
        <div class="blog-section-heading">Publish now or later</div>

        <div class="blog-section-body">
            

            <input type="hidden" name="IsPublished" value="NO">

            <div class="blog-publish">
                <label>
                    <input
                        type="checkbox"
                        name="IsPublished"
                        value="YES"
                        @checked(old('IsPublished', $blog->IsPublished ?? 'NO') === 'YES')
                    >
                    Publish immediately
                </label>
            </div>
        </div>
    </section>

    <div class="blog-actions">
        <button class="admin-button" type="submit">
            {{ $editing ? 'Save Changes' : 'Create Post' }}
        </button>

        <a href="{{ route('admin.blogs.index') }}">Cancel</a>
    </div>
</form>