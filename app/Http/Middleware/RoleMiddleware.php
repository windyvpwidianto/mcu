<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Ubah semua role di parameter middleware menjadi huruf kecil
        $allowedRoles = array_map('strtolower', $roles);

        // 2. Ambil semua nama role user, lalu ubah ke huruf kecil
        $userRoles = Auth::user()->roles->pluck('name')->map('strtolower')->toArray();

        // 3. Cek apakah ada role user yang beririsan dengan role yang diizinkan
        if (empty(array_intersect($userRoles, $allowedRoles))) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
