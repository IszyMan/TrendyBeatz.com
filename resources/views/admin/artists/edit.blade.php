@extends('layouts.admin')

@section('title', 'Edit Artist')

@section('content')
    <div class="admin-page-head">
        <h1>Edit {{ $artist->Stage_Name ?: $artist->ArtistsName }}</h1>
        <a href="{{ route('admin.artists.index') }}">← All Artists</a>
    </div>

    @include('admin.artists.form', ['artist' => $artist])
@endsection