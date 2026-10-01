@php
    $commentKey = 'comments-' . $postType . '-' . $postId;

    $commentToken = \App\Support\CommentTarget::token(
        $postType,
        (int) $postId,
        $postTitle
    );

    $approvedComments = \Illuminate\Support\Facades\DB::table('comments')
        ->where('post_type', $postType)
        ->where('post_id', $postId)
        ->where('is_allowed', 1)
        ->select('id', 'name', 'coments')
        ->orderByDesc('id')
        ->paginate(10, ['*'], 'comments_page');
@endphp

<section
    class="tb-comments"
    id="{{ $commentKey }}"
    aria-label="Comments"
>
    <div class="tb-comments-list">
        <h3>
            Comments
            <span>({{ $approvedComments->total() }})</span>
        </h3>

        @forelse ($approvedComments as $comment)
            <article class="tb-comment">
                <span
                    class="tb-comment-avatar"
                    aria-hidden="true"
                >
                    {{ mb_strtoupper(
                        mb_substr($comment->name ?: 'Guest', 0, 1)
                    ) }}
                </span>

                <div>
                    <strong>{{ $comment->name ?: 'Guest' }}</strong>

                    <p>{{ $comment->coments }}</p>
                </div>
            </article>
        @empty
            <p class="tb-comments-empty">
                No comments yet. Share your thoughts below.
            </p>
        @endforelse

       @if ($approvedComments->hasPages())
            @include('partials.pagination', [
                'paginator' => $approvedComments
                    ->withQueryString()
                    ->fragment($commentKey),
            ])
        @endif
    </div>

    <div class="tb-comments-heading">
        <h2>Leave a comment</h2>
    </div>

    <form
        class="tb-comment-form"
        action="{{ route('comments.store') }}"
        method="POST"
    >
        @csrf

        <input
            type="hidden"
            name="target"
            value="{{ $commentToken }}"
        >

        <div class="tb-comment-fields">
            <label for="{{ $commentKey }}-name">
                Your name

                <input
                    id="{{ $commentKey }}-name"
                    name="name"
                    type="text"
                    required
                    maxlength="191"
                    autocomplete="name"
                    placeholder="Enter your name"
                >
            </label>

            <label for="{{ $commentKey }}-email">
                Email <span class="tb-comment-optional">(optional)</span>

                <input
                    id="{{ $commentKey }}-email"
                    name="email"
                    type="email"
                    maxlength="191"
                    autocomplete="email"
                    placeholder="you@example.com"
                >
            </label>
        </div>

        <label for="{{ $commentKey }}-message">
            Your comment

            <textarea
                id="{{ $commentKey }}-message"
                name="message"
                required
                minlength="2"
                maxlength="3000"
                rows="3"
                placeholder="Write your comment…"
            ></textarea>
        </label>

        <div class="tb-comment-footer">
            <p>
                Comments require approval. No links, please.
                Your email stays private.
            </p>

            <button type="submit">
                Submit comment
                <span aria-hidden="true">→</span>
            </button>
        </div>

        <div
            class="tb-comment-feedback"
            role="status"
            aria-live="polite"
            tabindex="-1"
            hidden
        ></div>
    </form>
</section>