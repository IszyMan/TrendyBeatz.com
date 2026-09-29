@extends('layouts.admin')

@section('title', 'Edit DJ Mix')

@section('content')
    <div class="admin-page-head">
        <h1>Edit DJ Mix #{{ $mix->id }}</h1>
        <a href="{{ route('admin.dj-mixes.index') }}">← All DJ Mixes</a>
    </div>

    @include('admin.dj-mixes.form')
@endsection