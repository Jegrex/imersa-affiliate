<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            // Reload fresh from DB to ensure no stale session flag
            $freshAdmin = \App\Models\Admin::find($admin->id);

            if (!$freshAdmin || !$freshAdmin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Akun admin tidak aktif atau telah dinonaktifkan.',
                ]);
            }
        }

        return $next($request);
    }
}
