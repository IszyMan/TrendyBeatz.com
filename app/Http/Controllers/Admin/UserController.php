<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLogger;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'roles' => $this->roles(),
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'managedUser' => null,
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = new User();

        $this->assignValues($user, $data);

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created.');
    }

    public function edit(int $user)
    {
        return view('admin.users.edit', [
            'managedUser' => User::findOrFail($user),
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, int $user)
    {
        $data = $this->validated($request, $user);

        DB::transaction(function () use ($request, $user, $data) {
            // Serialize changes to administrator accounts.
            $administrators = User::query()
                ->where('roleid', config('admin.administrator'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $row = User::whereKey($user)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $row->id === $request->user()->id
                && (
                    (int) $data['roleid']
                        !== (int) config('admin.administrator')
                    || (int) $data['is_active'] !== 1
                )
            ) {
                throw ValidationException::withMessages([
                    'roleid' => 'You cannot remove your own administrator access.',
                ]);
            }

            $removesActiveAdministrator =
                (int) $row->roleid === (int) config('admin.administrator')
                && $row->is_active
                && (
                    (int) $data['roleid']
                        !== (int) config('admin.administrator')
                    || (int) $data['is_active'] !== 1
                );

            if (
                $removesActiveAdministrator
                && $administrators->where('is_active', true)->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'roleid' => 'Keep at least one active administrator.',
                ]);
            }

            // Capture the existing role before assigning the new values.
            $oldRole = (int) $row->roleid;
            
            $passwordChanged = filled($data['password'] ?? null);
            
            $this->assignValues($row, $data);
            
            if ($passwordChanged) {
                $row->remember_token = null;
            }
            
            $row->save();
            
            // Record an activity only when the role actually changes.
            $newRole = (int) $row->roleid;
            
            if ($oldRole !== $newRole) {
                $roles = $this->roles();
            
                ActivityLogger::record(
                    'role_changed',
                    'user',
                    $row->id,
                    $row->name,
                    [
                        'Role' => [
                            'old' => $roles[$oldRole] ?? 'Other',
                            'new' => $roles[$newRole] ?? 'Other',
                        ],
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $user)
    {
        DB::transaction(function () use ($request, $user) {
            $administrators = User::query()
                ->where('roleid', config('admin.administrator'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $row = User::whereKey($user)
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->id === $request->user()->id) {
                $this->denyDeletion('You cannot delete your own account.');
            }

            if (in_array((int) $row->id, [4, 5], true)) {
                $this->denyDeletion(
                    'This account is used as a public author and cannot be deleted.'
                );
            }

            if (
                (int) $row->roleid === (int) config('admin.administrator')
                && $row->is_active
                && $administrators->where('is_active', true)->count() <= 1
            ) {
                $this->denyDeletion('Keep at least one active administrator.');
            }

            if ($this->hasLinkedContent($row->id)) {
                $this->denyDeletion(
                    'This user has linked content. Deactivate the account instead.'
                );
            }

            $row->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'username' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'phone_number' => ['nullable', 'string', 'max:191'],
            'password' => [
                $id === null ? 'required' : 'nullable',
                'string',
                'min:12',
                'max:72',
                'confirmed',
            ],
            'roleid' => [
                'required',
                'integer',
                Rule::in(array_keys($this->roles())),
            ],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function assignValues(User $user, array $data): void
    {
        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone_number = $data['phone_number'] ?? null;
        $user->roleid = (int) $data['roleid'];
        $user->is_active = (bool) $data['is_active'];

        $user->channel =
            (int) $data['roleid'] === (int) config('admin.editor')
                ? 'editor'
                : 'admin';

        if (filled($data['password'] ?? null)) {
            // The User model's hashed cast hashes this value.
            $user->password = $data['password'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }

    private function roles(): array
    {
        return [
            (int) config('admin.administrator') => 'Administrator',
            (int) config('admin.standard') => 'Standard',
            (int) config('admin.editor') => 'Editor',
        ];
    }

    private function hasLinkedContent(int $id): bool
    {
        // Check user references across the application's current tables.
        foreach ([
            'listings',
            'artists',
            'albums',
            'djs',
            'dj_mixes',
            'blogs',
            'comments',
            'listing_features',
            'album_populars',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (['user_id', 'posted_by'] as $column) {
                if (
                    Schema::hasColumn($table, $column)
                    && DB::table($table)->where($column, $id)->exists()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function denyDeletion(string $message): never
    {
        throw ValidationException::withMessages([
            'user' => $message,
        ]);
    }
}