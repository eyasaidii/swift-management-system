<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\MessageSwift;
use App\Observers\MessageSwiftObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Pail\PailServiceProvider::class)) {
            $this->app->register(\Laravel\Pail\PailServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Pagination Bootstrap 5 ──
        Paginator::useBootstrapFive();

        // ── Enregistrement de l'Observer MessageSwift ──
        MessageSwift::observe(MessageSwiftObserver::class);
    }
}