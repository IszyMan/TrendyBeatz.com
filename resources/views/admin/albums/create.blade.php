@extends('layouts.admin')

@section('title', 'Add Album')

@section('content')
    <div class="admin-page-head">
        <h1>Add Album</h1>
        <a href="{{ route('admin.albums.index') }}">← All Albums</a>
    </div>

    @include('admin.albums.form')
@endsection