@extends('layouts.admin')

@section('title', 'Edit Listing')

@section('content')
    <div class="admin-page-head">
        <h1>Edit Listing #{{ $listing->id }}</h1>

        <a href="{{ route('admin.listings.index') }}">
            ← All Listings
        </a>
    </div>

    @include('admin.listings.form')
@endsection