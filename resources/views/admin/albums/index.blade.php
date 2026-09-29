@extends('layouts.admin')

@section('title', 'All Albums')

@section('content')
    <div class="admin-page-head">
        <h1>All Albums</h1>

        <a class="admin-button" href="{{ route('admin.albums.create') }}">
            + Add Album
        </a>
    </div>

    <div class="admin-panel">
        <form
            class="admin-filters admin-album-filters"
            method="GET"
            action="{{ route('admin.albums.index') }}"
        >
            <label class="admin-field">
                Search
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Album title, ID or artist"
                >
            </label>

            <button class="admin-button" type="submit">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Album / Artist</th>
                        <th>Year</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($albums as $album)
                        <tr>
                            <td>{{ $album->id }}</td>

                            <td>
                                <strong>{{ $album->title }}</strong>
                                <br>
                                {{ $album->Stage_Name ?: ($album->ArtistsName ?: '—') }}
                            </td>

                            <td>{{ $album->released_year }}</td>
                            <td>{{ $album->IsPublished }}</td>

                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.albums.edit', $album->id) }}"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.albums.destroy', $album->id) }}"
                                        onsubmit="return confirm('Delete this album?')"
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No albums found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $albums])
        </div>
    </div>
@endsection