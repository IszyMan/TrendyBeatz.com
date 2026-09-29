@extends('layouts.admin')

@section('title', 'Add Artist')

@section('content')
    <div class="admin-page-head">
        <h1>Add Artist</h1>
        <a href="{{ route('admin.artists.index') }}">← All Artists</a>
    </div>

    @include('admin.artists.form', ['artist' => null])
@endsection