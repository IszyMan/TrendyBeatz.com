@extends('layouts.admin')

@section('title', 'All DJ Mixes')

@section('content')
    <div class="admin-page-head">
        <h1>All DJ Mixes</h1>

        <a class="admin-button" href="{{ route('admin.dj-mixes.create') }}">
            + Add DJ Mix
        </a>
    </div>

    <div class="admin-panel">
        <form
            class="admin-filters admin-dj-mix-filters"
            method="GET"
            action="{{ route('admin.dj-mixes.index') }}"
        >
            <label class="admin-field">
                Search
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Mix title or DJ name"
                >
            </label>

            <button class="admin-button" type="submit">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mix / DJ</th>
                        <th>Year</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($mixes as $mix)
                        <tr>
                            <td>{{ $mix->id }}</td>
                            <td>
                                <strong>{{ $mix->mix_title }}</strong>
                                <br>
                                {{ $mix->dj_name ?: '—' }}
                            </td>
                            <td>{{ $mix->released_year ?: '—' }}</td>
                            <td>{{ $mix->IsPublished }}</td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.dj-mixes.edit', $mix->id) }}"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.dj-mixes.destroy', $mix->id) }}"
                                        onsubmit="return confirm('Delete this DJ mix?')"
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
                            <td colspan="5">No DJ mixes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $mixes])
        </div>
    </div>
@endsection