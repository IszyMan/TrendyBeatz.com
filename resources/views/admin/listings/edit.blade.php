@extends('layouts.admin')

@section('title', 'Edit Listing')

@section('content')
    <div class="admin-page-head">
        <h1>Edit Listing #{{ $listing->id }}</h1>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <a class="admin-button" href="{{ route('admin.listings.create') }}">
                + Add Listing
            </a>
            
            <a class="admin-button" href="{{ route('admin.listings.index') }}">
                ← All Listings
            </a>

            
        </div>
    </div>

    @include('admin.listings.form')
@endsection