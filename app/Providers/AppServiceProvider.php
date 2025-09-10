<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
    }
}
