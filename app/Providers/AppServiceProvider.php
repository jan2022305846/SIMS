<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

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
            
            // Force Vite to use HTTPS for assets in production
            if (class_exists(\Illuminate\Foundation\Vite::class)) {
                \Illuminate\Foundation\Vite::useScriptTagAttributes([
                    'crossorigin' => 'anonymous',
                ]);
            }
        }
        
        // Trust proxy headers for proper HTTPS detection behind reverse proxy (Render)
        if (config('app.env') === 'production') {
            request()->server->set('HTTPS', 'on');
            request()->server->set('HTTP_X_FORWARDED_PROTO', 'https');
        }
    }
}
