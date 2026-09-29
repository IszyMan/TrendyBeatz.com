@extends('layouts.admin')

@section('title', 'All DJs')

@section('content')
    <div class="admin-page-head">
        <h1>All DJs</h1>

        <a class="admin-button" href="{{ route('admin.djs.create') }}">
            + Add DJ
        </a>
    </div>

    <div class="admin-panel">
        <form
            class="admin-filters admin-dj-filters"
            method="GET"
            action="{{ route('admin.djs.index') }}"
        >
            <label class="admin-field">
                Search
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="DJ name or full name"
                >
            </label>

            <button class="admin-button" type="submit">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DJ</th>
                        <th>Country</th>
                        <th>Published</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($djs as $dj)
                        <tr>
                            <td>{{ $dj->id }}</td>
                            <td>
                                <strong>{{ $dj->dj_name }}</strong>
                                @if ($dj->fullname)
                                    <br>{{ $dj->fullname }}
                                @endif
                            </td>
                            <td>{{ ucfirst($dj->country_id ?: '—') }}</td>
                            <td>{{ $dj->IsPublished }}</td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.djs.edit', $dj->id) }}"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.djs.destroy', $dj->id) }}"
                                        onsubmit="return confirm('Delete this DJ?')"
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
                            <td colspan="5">No DJs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $djs])
        </div>
    </div>
@endsection