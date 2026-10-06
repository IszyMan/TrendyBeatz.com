<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        abort_unless($user, 403);
        
        if (!(bool) $user->is_active) {
            \Illuminate\Support\Facades\Auth::logout();
        
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $allowedIds = array_map(
            fn (string $role): int => (int) config('admin.' . $role),
            $roles
        );

        abort_unless(
            in_array((int) $user->roleid, $allowedIds, true),
            403
        );

        return $next($request);
    }
}