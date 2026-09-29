@extends('layouts.admin')

@section('title', 'Add DJ')

@section('content')
    <div class="admin-page-head">
        <h1>Add DJ</h1>
        <a href="{{ route('admin.djs.index') }}">← All DJs</a>
    </div>

    @include('admin.djs.form')
@endsection