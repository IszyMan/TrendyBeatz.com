@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="admin-page-head">
        <h1>Edit User: {{ $managedUser->name }}</h1>
    </div>

    @include('admin.users.form')
@endsection