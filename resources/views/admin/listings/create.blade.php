@extends('layouts.admin')

@section('title', 'Add Listing')

@section('content')
    <div class="admin-page-head">
        <h1>Add Listing</h1>

        <a href="{{ route('admin.listings.index') }}">
            ← All Listings
        </a>
    </div>

    @include('admin.listings.form')
@endsection