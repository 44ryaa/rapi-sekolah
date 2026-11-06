<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = [];
        foreach ($roles as $r) {
            match ($r) {
                'admin' => $allowed[] = Role::ADMIN,
                'pic' => $allowed[] = Role::PIC,
                'guru' => array_push($allowed, Role::GURU, Role::ORGANISASI),
                'organisasi' => $allowed[] = Role::ORGANISASI,
                'waka' => $allowed[] = Role::WAKA,
                default => null,
            };
        }

        if (! in_array((int) $user->role_id, $allowed, true)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
