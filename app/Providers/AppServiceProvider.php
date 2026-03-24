<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\MessageSwift;
use App\Observers\MessageSwiftObserver;

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
        // ── Enregistrement de l'Observer MessageSwift ──
        // Placé ici car AppServiceProvider est TOUJOURS chargé dans Laravel 12
        // contrairement à AuthServiceProvider qui doit être déclaré manuellement
        MessageSwift::observe(MessageSwiftObserver::class);
    }
}