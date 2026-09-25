<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;

/**
 * Redirect user ke dashboard sesuai role-nya.
 *
 * Dipakai pada dua titik:
 *  - middleware `guest` (user sudah login membuka halaman login/register)
 *  - setelah login sukses (lihat AuthenticatedSessionController::store)
 */
class RedirectToRoleDashboard extends RedirectIfAuthenticated
{
    /**
     * Get the path the user should be redirected to when they are authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        $user = $request->user();

        return $user instanceof User ? $user->dashboardRoute() : '/';
    }
}
