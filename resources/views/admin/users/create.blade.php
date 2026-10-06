@extends('layouts.admin')

@section('title', 'Add User')

@section('content')
    <div class="admin-page-head">
        <h1>Add User</h1>
    </div>

    @include('admin.users.form')
@endsection