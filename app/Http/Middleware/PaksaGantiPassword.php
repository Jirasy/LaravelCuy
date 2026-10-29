<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// User dengan password sementara hanya boleh membuka halaman Pengaturan (untuk mengganti password), Notifikasi (info akunnya), dan logout.
class PaksaGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password
            && !$request->routeIs('pengaturan.edit', 'pengaturan.password', 'notifikasi.*', 'logout')) {
            return redirect()->route('pengaturan.edit')
                ->with('error', 'Demi keamanan, ganti dulu password sementaramu.');
        }

        return $next($request);
    }
}