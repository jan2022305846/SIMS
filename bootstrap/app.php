<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust proxies for proper HTTPS detection (important for Render deployment)
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'faculty' => \App\Http\Middleware\FacultyMiddleware::class,
            'office_head' => \App\Http\Middleware\OfficeHeadMiddleware::class,
            'log_activity' => \App\Http\Middleware\LogActivity::class,
        ]);
        
        // Add activity logging to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\LogActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
