@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
    <div class="admin-page-head">
        <h1>Activity Logs</h1>
    </div>

    <form
        class="admin-filters"
        method="GET"
        action="{{ route('admin.activity-logs.index') }}"
    >
        <label class="admin-field">
            Search activities

            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search title, action, or user..."
            >
        </label>

        <label class="admin-field">
            User

            <select name="user_id">
                <option value="">All users</option>

                @foreach ($users as $user)
                    <option
                        value="{{ $user->id }}"
                        @selected(
                            (string) $selectedUser === (string) $user->id
                        )
                    >
                        {{ $user->name }} ({{ $user->username }})
                    </option>
                @endforeach
            </select>
        </label>

        <div class="admin-actions" style="align-items: end;">
            <button class="admin-button" type="submit">
                Filter
            </button>

            <a
                class="admin-button"
                href="{{ route('admin.activity-logs.index') }}"
            >
                Clear
            </a>
        </div>
    </form>

    <div class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Activity</th>
                        <th>Description</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                {{ $log->created_at
                                    ? \Illuminate\Support\Carbon::parse(
                                        $log->created_at
                                    )->format('d M Y, H:i:s')
                                    : '—' }}
                            </td>

                            <td>
                                {{ $log->actor_name
                                    ?? ($log->user_id
                                        ? 'Deleted user #' . $log->user_id
                                        : 'System') }}
                            </td>

                            <td>{{ $log->activity }}</td>

                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No activities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $logs])
        </div>
    </div>
@endsection