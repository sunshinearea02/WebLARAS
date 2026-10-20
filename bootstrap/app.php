<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Mendaftarkan alias middleware untuk memeriksa role
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Guest yang membuka halaman khusus petugas diarahkan ke login,
        // petugas yang sudah login dan membuka /login diarahkan ke Data Lanskap.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/');
        $middleware->validateCsrfTokens(except: ['api/n8n/*']);

        // Di Vercel (dan hosting lain di balik proxy) request asli memakai HTTPS.
        // Tanpa ini Laravel membuat URL http:// sehingga form & link bisa gagal.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();