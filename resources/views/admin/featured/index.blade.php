@extends('layouts.admin')

@section('title', $kind === 'song' ? 'Featured Songs' : 'Featured Albums')

@section('content')
    <div class="admin-page-head">
        <h1>
            {{ $kind === 'song' ? 'Featured Songs' : 'Featured Albums' }}
        </h1>

        <a class="admin-button" href="{{ route($prefix . '.create') }}">
            {{ $kind === 'song' ? 'Add Featured Song' : 'Add Featured Album' }}
        </a>
    </div>

    <div class="admin-panel admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ $kind === 'song' ? 'Song' : 'Album' }}</th>

                    @if ($kind === 'song')
                        <th>Featured In</th>
                    @endif

                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>

                        <td>
                            <strong>{{ $item->artist_name }}</strong>
                            <br>
                            {{ $item->title }}
                        </td>

                        @if ($kind === 'song')
                            <td>
                                @if ($item->song_day)
                                    <div>Song of the Day</div>
                                @endif

                                @if ($item->song_week)
                                    <div>Song of the Week</div>
                                @endif
                            </td>
                        @endif

                        <td>
                            {{ (int) $item->is_published === 1 ? 'YES' : 'NO' }}
                        </td>

                        <td>
                            <div class="admin-actions">
                                <a
                                    class="admin-button"
                                    href="{{ route($prefix . '.edit', ['id' => $item->id]) }}"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route($prefix . '.destroy', ['id' => $item->id]) }}"
                                    onsubmit="return confirm('Remove this item from featured content?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="admin-button admin-button-danger"
                                        type="submit"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $kind === 'song' ? 5 : 4 }}">
                            No featured items available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <div class="admin-pagination">
            @include('partials.pagination', ['paginator' => $items])
        </div>
    </div>
@endsection