<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Menu;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil segmen pertama dari URL (misal: 'wpi')
        $rootSegment = $request->segment(1);

        // 2. Cari di database menu yang memiliki request_route atau route mirip segmen tersebut
        $menu = Menu::where(function($query) use ($rootSegment) {
            $query->where('request_route', $rootSegment)
                  ->orWhere('request_route', $rootSegment . '/*')
                  ->orWhere('request_route', $rootSegment . '*');
        })->first();

        // 3. Jika menu ditemukan dan statusnya NOT enabled, blokir akses
        if ($menu && $menu->status !== 'enabled') {
            abort(403, 'Menu ini sedang dinonaktifkan.');
        }

        return $next($request);
    }
}
