@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="admin-page-head">
        <h1>Edit Album #{{ $album->id }}</h1>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            
            <a class="admin-button" href="{{ route('admin.albums.create') }}">
                + Add Album
            </a>
            
            <a class="admin-button" href="{{ route('admin.albums.index') }}">
                ← All Albums
            </a>

            
        </div>
    </div>

    @include('admin.albums.form')
@endsection