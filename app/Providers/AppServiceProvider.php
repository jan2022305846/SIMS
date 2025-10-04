<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        // Fix for older MySQL versions (< 5.7.7) and MariaDB (< 10.2.2)
        // Set default string length to 191 to avoid key too long errors
        Schema::defaultStringLength(191);
        
        // Force HTTPS in production to prevent mixed content warnings
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Trust proxy headers for proper HTTPS detection behind reverse proxy (Render)
        if (config('app.env') === 'production') {
            request()->server->set('HTTPS', 'on');
            request()->server->set('HTTP_X_FORWARDED_PROTO', 'https');
        }

        // Register morph map for polymorphic relationships
        Relation::morphMap([
            'consumable' => \App\Models\Consumable::class,
            'non_consumable' => \App\Models\NonConsumable::class,
        ]);
    }
}
