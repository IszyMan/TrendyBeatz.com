@extends('layouts.admin')

@section('title', 'Edit DJ')

@section('content')
    <div class="admin-page-head">
        <h1>Edit {{ $dj->dj_name }}</h1>
        <a href="{{ route('admin.djs.index') }}">← All DJs</a>
    </div>

    @include('admin.djs.form')
@endsection