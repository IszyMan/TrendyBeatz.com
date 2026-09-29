@extends('layouts.admin')

@section('title', 'Add DJ Mix')

@section('content')
    <div class="admin-page-head">
        <h1>Add DJ Mix</h1>
        <a href="{{ route('admin.dj-mixes.index') }}">← All DJ Mixes</a>
    </div>

    @include('admin.dj-mixes.form')
@endsection