<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartAfterLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private static bool $loginListenerRegistered = false;

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
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        if (self::$loginListenerRegistered) {
            return;
        }

        Event::listen(Login::class, MergeGuestCartAfterLogin::class);
        self::$loginListenerRegistered = true;
    }
}
