<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admin & Kader boleh mengelola data (lanskap + check-in warga).
        // Di Blade dipakai lewat @can('kelola-data') ... @endcan
        Gate::define('kelola-data', fn (User $user) => $user->isStaff());

        // Hanya admin yang boleh membuat akun petugas baru.
        Gate::define('kelola-akun', fn (User $user) => $user->isActive() && $user->isAdmin());
    }
}
