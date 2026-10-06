<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:191'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $selectedUser = $filters['user_id'] ?? null;

        $logs = DB::table('activity_logs as log')
            ->leftJoin('users as user', 'user.id', '=', 'log.user_id')
            ->select(
                'log.*',
                'user.name as actor_name',
                'user.username as actor_username'
            )
            ->when($selectedUser, function ($query) use ($selectedUser) {
                $query->where('log.user_id', $selectedUser);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('log.activity', 'like', "%{$search}%")
                        ->orWhere('log.description', 'like', "%{$search}%")
                        ->orWhere('user.name', 'like', "%{$search}%")
                        ->orWhere('user.username', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('log.id')
            ->paginate(30)
            ->withQueryString();

        $users = DB::table('users')
            ->select('id', 'name', 'username')
            ->orderBy('name')
            ->get();

        return view('admin.activity-logs.index', compact(
            'logs',
            'users',
            'search',
            'selectedUser'
        ));
    }
}