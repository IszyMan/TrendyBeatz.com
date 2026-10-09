@extends('layouts.admin')

@section('title', 'Edit DJ')

@section('content')
    <div class="admin-page-head">
        <h1>Edit {{ $dj->dj_name }}</h1>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <a class="admin-button" href="{{ route('admin.djs.create') }}">
                + Add DJ
            </a>

            <a class="admin-button" href="{{ route('admin.djs.index') }}">
                ← All DJs
            </a>
        </div>
    </div>

    @include('admin.djs.form')
@endsection