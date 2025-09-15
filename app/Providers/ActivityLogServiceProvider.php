<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Item;
use App\Models\Request;
use App\Models\Category;
use App\Observers\UserObserver;
use App\Observers\ItemObserver;
use App\Observers\RequestObserver;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model observers for automatic activity logging
        User::observe(UserObserver::class);
        Item::observe(ItemObserver::class);
        Request::observe(RequestObserver::class);
        
        // Register auth event listeners for login/logout tracking
        $this->registerAuthEventListeners();
    }

    /**
     * Register authentication event listeners
     */
    private function registerAuthEventListeners(): void
    {
        // Listen for successful login events
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\ActivityLog::log('User logged into the system')
                    ->inLog('authentication')
                    ->causedBy($event->user)
                    ->withEvent('login')
                    ->withProperties([
                        'user_id' => $event->user->id,
                        'user_name' => $event->user->name,
                        'email' => $event->user->email,
                        'role' => $event->user->role,
                        'login_time' => now()->toDateTimeString(),
                        'guard' => $event->guard
                    ])
                    ->save();
            }
        );

        // Listen for logout events
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function ($event) {
                if ($event->user) {
                    \App\Models\ActivityLog::log('User logged out of the system')
                        ->inLog('authentication')
                        ->causedBy($event->user)
                        ->withEvent('logout')
                        ->withProperties([
                            'user_id' => $event->user->id,
                            'user_name' => $event->user->name,
                            'logout_time' => now()->toDateTimeString(),
                            'guard' => $event->guard
                        ])
                        ->save();
                }
            }
        );

        // Listen for failed login attempts
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                \App\Models\ActivityLog::log('Failed login attempt')
                    ->inLog('authentication')
                    ->withEvent('login_failed')
                    ->withProperties([
                        'email' => $event->credentials['email'] ?? 'unknown',
                        'attempt_time' => now()->toDateTimeString(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'guard' => $event->guard
                    ])
                    ->save();
            }
        );

        // Listen for password reset events
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\PasswordReset::class,
            function ($event) {
                \App\Models\ActivityLog::log('User password was reset')
                    ->inLog('authentication')
                    ->causedBy($event->user)
                    ->withEvent('password_reset')
                    ->withProperties([
                        'user_id' => $event->user->id,
                        'user_name' => $event->user->name,
                        'reset_time' => now()->toDateTimeString()
                    ])
                    ->save();
            }
        );

        // Listen for lockout events
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Lockout::class,
            function ($event) {
                \App\Models\ActivityLog::log('Account locked due to too many failed attempts')
                    ->inLog('authentication')
                    ->withEvent('account_locked')
                    ->withProperties([
                        'email' => $event->request->input('email'),
                        'lockout_time' => now()->toDateTimeString(),
                        'ip_address' => $event->request->ip(),
                        'user_agent' => $event->request->userAgent()
                    ])
                    ->save();
            }
        );
    }
}
