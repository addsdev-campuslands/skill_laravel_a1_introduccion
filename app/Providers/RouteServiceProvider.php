<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
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
        Log::debug('Boot de Route Limiter');

        RateLimiter::for('api', function (Request $request) {
            Log::debug('Limit by ip' . $request->ip());
            if ($userId = optional($request->user())->getAuthIdentifier()) {
                return Limit::perMinute(100)->by('uid:' . $userId);
            }

            Log::debug('Limit by ip' . $request->ip());
            return Limit::perMinute(30)->by('ip:' . $request->ip());
        });
    }
}
