<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Role;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = array_map(fn($r) => match($r) {
            'admin' => Role::ADMIN,
            'pic'   => Role::PIC,
            'guru'  => Role::GURU,
            'waka'  => Role::WAKA,
            default => 0,
        }, $roles);

        if (!in_array((int)$user->role_id, $allowed, true)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
