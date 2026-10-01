@extends('layouts.admin')

@section('title', 'All Listings')

@section('content')
    <div class="admin-page-head">
        <h1>All Listings</h1>

        <a
            class="admin-button"
            href="{{ route('admin.listings.create') }}"
        >
            + Add Listing
        </a>
    </div>

    <div class="admin-panel">
        <form
            class="admin-filters"
            method="GET"
            action="{{ route('admin.listings.index') }}"
        >
            <label class="admin-field">
                Search

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="ID, title or artist"
                >
            </label>

            <label class="admin-field">
                Type

                <select name="type">
                    <option value="">All types</option>

                    <option value="Audio" @selected($type === 'Audio')>
                        Audio
                    </option>

                    <option value="video" @selected($type === 'video')>
                        Video
                    </option>
                </select>
            </label>

            <button class="admin-button" type="submit">
                Filter
            </button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title / Artist</th>
                        <th>Type</th>
                        <th>Country</th>
                        <th>Published</th>
                        <th>Posted by</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($listings as $listing)
                        <tr>
                            <td>{{ $listing->id }}</td>

                            <td>
                                <strong>{{ $listing->artist_name }}</strong>
                                <br>
                                {{ $listing->TrackTitle }}
                            </td>

                            <td>{{ $listing->ListingType }}</td>
                            <td>{{ ucfirst($listing->country_id) }}</td>
                            <td>{{ $listing->IsPublished }}</td>
                            <td>{{ $listing->posted_by_name ?: '—' }}</td>

                            <td>
                                {{ $listing->created_at
                                    ? \Illuminate\Support\Carbon::parse(
                                        $listing->created_at
                                    )->format('M d, Y')
                                    : '—' }}
                            </td>

                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route(
                                            'admin.listings.edit',
                                            $listing->id
                                        ) }}"
                                    >
                                        Edit
                                    </a>

                                    @if (
                                        (int) auth()->user()->roleid
                                            === (int) config('admin.administrator')
                                    )
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.listings.destroy',
                                                $listing->id
                                            ) }}"
                                            onsubmit="return confirm('Delete this listing?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="admin-button admin-button-danger"
                                                type="submit"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                No listings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', [
                'paginator' => $listings,
            ])
        </div>
    </div>
@endsection