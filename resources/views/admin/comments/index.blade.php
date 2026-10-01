@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
    <h1>Comments</h1>

    <nav
        class="admin-comment-tabs"
        aria-label="Comment filters"
    >
        @foreach ([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'all' => 'All',
        ] as $value => $label)
            <a
                @class([
                    'active' => $status === $value,
                ])
                href="{{ route('admin.comments.index', [
                    'status' => $value,
                ]) }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @forelse ($comments as $comment)
        <article class="admin-comment-card">
            <strong>
                {{ $comment->name ?: 'Guest' }}
            </strong>

            <p>
                {{ $comment->email }}
                · {{ $comment->created_at }}
            </p>

            <p>
                <strong>Post:</strong>

                @if (
                    $comment->post_path
                    && str_starts_with($comment->post_path, '/')
                    && !str_starts_with($comment->post_path, '//')
                )
                    <a
                        href="{{ url($comment->post_path) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ $comment->post_title ?: 'View post' }}
                    </a>

                    <small>
                        ({{ $comment->post_type }}
                        #{{ $comment->post_id }})
                    </small>
                @else
                    Legacy comment — source not mapped

                    <small>
                        (blog_id: {{ $comment->blog_id }},
                        category_id: {{ $comment->category_id }})
                    </small>
                @endif
            </p>

            <p class="admin-comment-message">{{ $comment->coments }}</p>

            <strong>
                {{ (int) $comment->is_allowed === 1
                    ? 'Approved'
                    : 'Pending approval' }}
            </strong>

            <div class="admin-comment-actions">
                @if ((int) $comment->is_allowed !== 1)
                    <form
                        method="POST"
                        action="{{ route(
                            'admin.comments.approve',
                            $comment->id
                        ) }}"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit">
                            Approve
                        </button>
                    </form>
                @endif

                <form
                    method="POST"
                    action="{{ route(
                        'admin.comments.destroy',
                        $comment->id
                    ) }}"
                    onsubmit="return confirm('Delete this comment?')"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-comment-delete"
                    >
                        Delete
                    </button>
                </form>
            </div>
        </article>
    @empty
        <p>No comments in this section.</p>
    @endforelse

    @include('partials.pagination', [
            'paginator' => $comments,
        ])
@endsection