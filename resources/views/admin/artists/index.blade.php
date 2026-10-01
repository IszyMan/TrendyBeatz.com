@extends('layouts.admin')

@section('title', 'All Artists')

@section('content')
    <div class="admin-page-head">
        <h1>All Artists</h1>

        <a class="admin-button" href="{{ route('admin.artists.create') }}">
            + Add Artist
        </a>
    </div>

    <div class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Artist</th>
                        <th>Country</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($artists as $artist)
                        <tr>
                            <td>{{ $artist->Artists_Id }}</td>
                            <td>
                                <strong>
                                    {{ $artist->Stage_Name ?: $artist->ArtistsName }}
                                </strong>
                            </td>
                            <td>{{ ucfirst($artist->country_id) }}</td>
                            <td>{{ $artist->IsPublished }}</td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.artists.edit', $artist->id) }}"
                                    >
                                        Edit
                                    </a>

                                    @if (
                                        (int) auth()->user()->roleid
                                            === (int) config('admin.administrator')
                                    )

                                        <form
                                            method="POST"
                                            action="{{ route('admin.artists.destroy', $artist->id) }}"
                                            onsubmit="return confirm('Delete this artist?')"
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
                            <td colspan="5">No artists found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $artists])
        </div>
    </div>
@endsection