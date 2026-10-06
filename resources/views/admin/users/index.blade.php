@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="admin-page-head">
        <h1>Users</h1>

        <a class="admin-button" href="{{ route('admin.users.create') }}">
            + Add User
        </a>
    </div>

    <form
        class="admin-artist-search"
        method="GET"
        action="{{ route('admin.users.index') }}"
    >
        <label class="admin-field">
            Search users

            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Name, username, or email..."
            >
        </label>

        <button class="admin-button" type="submit">Search</button>

        @if ($search !== '')
            <a class="admin-button" href="{{ route('admin.users.index') }}">
                Clear
            </a>
        @endif
    </form>

    <div class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>

                            <td>
                                <strong>{{ $user->name }}</strong>
                                <br>
                                {{ $user->username }}
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                {{ $roles[(int) $user->roleid] ?? 'Other' }}
                            </td>

                            <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>

                            <td>
                                <div class="admin-actions">
                                    <a
                                        class="admin-button"
                                        href="{{ route('admin.users.edit', $user->id) }}"
                                    >
                                        Edit
                                    </a>

                                    @if (
                                        (int) $user->id !== (int) auth()->id()
                                        && !in_array((int) $user->id, [4, 5], true)
                                    )
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $user->id) }}"
                                            onsubmit="return confirm('Delete this user permanently?')"
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
                            <td colspan="6">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $users])
        </div>
    </div>
@endsection