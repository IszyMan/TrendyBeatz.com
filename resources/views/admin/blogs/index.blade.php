@extends('layouts.admin')

@section('title', 'All Blogs')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-blog.css') }}">
@endpush

@section('content')
    <div class="admin-page-head">
        <h1>All Blogs</h1>

        <a class="admin-button" href="{{ route('admin.blogs.create') }}">
            + Add Blog Post
        </a>
    </div>

    <div class="admin-panel">
        <form
            class="admin-filters blog-index-filters"
            method="GET"
            action="{{ route('admin.blogs.index') }}"
        >
            <label class="admin-field">
                Search
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Post title or category"
                >
            </label>

            <button class="admin-button" type="submit">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post</th>
                        <th>Category</th>
                        <th>Public author</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($blogs as $blog)
                        <tr>
                            <td>{{ $blog->id }}</td>
                            <td><strong>{{ $blog->title }}</strong></td>
                            <td>{{ $blog->category_name ?: '—' }}</td>
                            <td>{{ $blog->public_author_name ?: '—' }}</td>
                            <td>{{ $blog->IsPublished }}</td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.blogs.edit', $blog->id) }}"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.blogs.destroy', $blog->id) }}"
                                        onsubmit="return confirm('Delete this blog post?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="admin-button admin-button-danger"
                                            type="submit"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No blog posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $blogs])
        </div>
    </div>
@endsection