<?php

use App\Http\Middleware\RedirectToRoleDashboard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    // Daftarkan command kelas di app/Console/Commands (mis. queue:reset-harian).
    // Laravel 11 tidak mengambilnya otomatis selama withRouting() memakai
    // berkas routes/console.php.
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware) {
        // Alias role & permission (spatie) + redirect sesuai role setelah login.
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'guest' => RedirectToRoleDashboard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
