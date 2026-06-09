<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
        // Already-authenticated users who hit a guest-only page (/login, /register, ...) should
        // land on the menu — consistent with where login/register send customers — not the
        // dashboard (the framework default when a `dashboard` route exists).
        RedirectIfAuthenticated::redirectUsing(fn () => route('menu.index'));
    }
}
