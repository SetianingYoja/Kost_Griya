<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();

        if ($user->status !== 'Aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi pengelola.');
        }

        // Super Admin has universal access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $userRoleSlug = $user->role ? $user->role->slug : '';
        $normalizedUserRole = $user->normalizeRoleSlug($userRoleSlug);
        $normalizedAllowedRoles = array_map(fn ($role) => $user->normalizeRoleSlug($role), $roles);

        if (!in_array($normalizedUserRole, $normalizedAllowedRoles, true)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
