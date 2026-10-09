@extends('layouts.admin')

@section('title', 'Edit Artist')

@section('content')
    <div class="admin-page-head">
        <h1>Edit {{ $artist->Stage_Name ?: $artist->ArtistsName }}</h1>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            
            <a class="admin-button" href="{{ route('admin.artists.create') }}">
                + Add Artist
            </a>
            
            <a class="admin-button" href="{{ route('admin.artists.index') }}">
                ← All Artists
            </a>

            
        </div>
    </div>

    @include('admin.artists.form', ['artist' => $artist])
@endsection