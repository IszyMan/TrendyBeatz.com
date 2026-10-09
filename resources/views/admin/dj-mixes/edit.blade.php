@extends('layouts.admin')

@section('title', 'Edit DJ Mix')

@section('content')
    <div class="admin-page-head">
        <h1>Edit DJ Mix #{{ $mix->id }}</h1>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <a class="admin-button" href="{{ route('admin.dj-mixes.create') }}">
                + Add DJ Mix
            </a>

            <a class="admin-button" href="{{ route('admin.dj-mixes.index') }}">
                ← All DJ Mixes
            </a>
        </div>
    </div>

    @include('admin.dj-mixes.form')
@endsection