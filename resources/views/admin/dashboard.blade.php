@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-page-head">
        <h1>Dashboard</h1>
    </div>

    <div class="admin-stats">
        @if ($listingCount !== null)
            <div class="admin-stat">
                Listings
                <strong>{{ number_format($listingCount) }}</strong>
            </div>
        @endif

        @if ($mixCount !== null)
            <div class="admin-stat">
                DJ Mixes
                <strong>{{ number_format($mixCount) }}</strong>
            </div>
        @endif

        @if ($blogCount !== null)
            <div class="admin-stat">
                Blogs
                <strong>{{ number_format($blogCount) }}</strong>
            </div>
        @endif
    </div>
@endsection