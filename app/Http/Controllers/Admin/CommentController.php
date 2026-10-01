<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    private function authorizeAdministrator(
        Request $request
    ): void {
        $administratorRole = config('admin.administrator');

        abort_unless(
            $request->user()
            && $administratorRole !== null
            && (int) $request->user()->roleid
                === (int) $administratorRole,
            403
        );
    }

    public function index(Request $request): View
    {
        $this->authorizeAdministrator($request);

        $status = $request->query('status', 'pending');

        abort_unless(
            in_array(
                $status,
                ['pending', 'approved', 'all'],
                true
            ),
            404
        );

        $query = DB::table('comments');

        if ($status === 'pending') {
            $query->where(function ($query) {
                $query
                    ->where('is_allowed', 0)
                    ->orWhereNull('is_allowed');
            });
        } elseif ($status === 'approved') {
            $query->where('is_allowed', 1);
        }

        $comments = $query
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'status' => $status,
        ]);
    }

    public function approve(
        Request $request,
        int $comment
    ): RedirectResponse {
        $this->authorizeAdministrator($request);

        abort_unless(
            DB::table('comments')
                ->where('id', $comment)
                ->exists(),
            404
        );

        DB::table('comments')
            ->where('id', $comment)
            ->update([
                'is_allowed' => 1,
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Comment approved.'
        );
    }

    public function destroy(
        Request $request,
        int $comment
    ): RedirectResponse {
        $this->authorizeAdministrator($request);

        $deleted = DB::table('comments')
            ->where('id', $comment)
            ->delete();

        abort_unless($deleted, 404);

        return back()->with(
            'success',
            'Comment deleted.'
        );
    }
}