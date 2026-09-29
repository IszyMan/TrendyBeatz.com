@extends('layouts.admin')

@section('title', 'Edit Blog Post')

@push('styles')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css"
    >
@endpush

@section('content')
    <div class="blog-page">
        <div class="admin-page-head">
            <h1>Edit Blog Post #{{ $blog->id }}</h1>
            <a href="{{ route('admin.blogs.index') }}">← All Blogs</a>
        </div>

        @include('admin.blogs.form')
    </div>
@endsection

@push('scripts')
    @include('admin.blogs.editor-script')
@endpush

