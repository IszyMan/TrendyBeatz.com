@extends('layouts.admin')

@section('title', $kind === 'song' ? 'Feature a Song' : 'Feature an Album')

@section('content')
    <div class="admin-page-head">
        <h1>
            {{ $kind === 'song' ? 'Feature a Song' : 'Feature an Album' }}
        </h1>

        <a class="admin-button" href="{{ route($prefix . '.index') }}">
            {{ $kind === 'song' ? 'All Featured Songs' : 'All Featured Albums' }}
        </a>
    </div>

    <form
        class="admin-panel admin-form-grid"
        method="GET"
        action="{{ route($prefix . '.create') }}"
    >
        <label class="admin-field">
            {{ $kind === 'song' ? 'Listing ID' : 'Album ID' }}

            <input
                type="number"
                name="id"
                min="1"
                value="{{ old('id', $item->id ?? request('id')) }}"
                required
            >
        </label>

        <div>
            <button class="admin-button" type="submit">
                {{ $kind === 'song' ? 'Fetch Song' : 'Fetch Album' }}
            </button>
        </div>
    </form>

    @if ($item)
        <div class="admin-panel" style="margin-top: 20px;">
            <h2>{{ $item->artist_name }}</h2>

            <p>
                <strong>
                    {{ $kind === 'song' ? $item->track_title : $item->title }}
                </strong>
            </p>

            @if ($kind === 'song' && filled($item->featuring))
                <p>Featuring: {{ $item->featuring }}</p>
            @endif

            <p>
                {{ $kind === 'song' ? 'Listing ID' : 'Album ID' }}:
                {{ $item->id }}
            </p>

            @if ((int) $item->is_published !== 1)
                <p class="admin-errors">
                    Publish this item before featuring it.
                </p>
            @else
                <form method="POST" action="{{ route($prefix . '.store') }}">
                    @csrf

                    <input type="hidden" name="id" value="{{ $item->id }}">

                    @if ($kind === 'song')
                        <input type="hidden" name="song_day" value="0">
                        <input type="hidden" name="song_week" value="0">

                        <div class="admin-checks">
                            <label>
                                <input
                                    type="checkbox"
                                    name="song_day"
                                    value="1"
                                    @checked(
                                        (string) old('song_day', (int) $songDay) === '1'
                                    )
                                >

                                Song of the Day
                            </label>

                            <label>
                                <input
                                    type="checkbox"
                                    name="song_week"
                                    value="1"
                                    @checked(
                                        (string) old('song_week', (int) $songWeek) === '1'
                                    )
                                >

                                Song of the Week
                            </label>
                        </div>
                    @else
                        <p>Add this album to Popular Albums.</p>
                    @endif

                    <button class="admin-button" type="submit">
                        Save Featured {{ $kind === 'song' ? 'Song' : 'Album' }}
                    </button>
                </form>
            @endif
        </div>
    @endif
@endsection