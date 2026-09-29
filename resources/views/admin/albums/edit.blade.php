@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="admin-page-head">
        <h1>Edit Album #{{ $album->id }}</h1>
        <a href="{{ route('admin.albums.index') }}">← All Albums</a>
    </div>

    @include('admin.albums.form')
@endsection