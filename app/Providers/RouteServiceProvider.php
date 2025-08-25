<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Stricter limits for authentication-related endpoints
        RateLimiter::for('login', function (Request $request) {
            $key = sprintf('login:%s', strtolower((string) $request->input('email')) ?: $request->ip());
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('register', function (Request $request) {
            $key = sprintf('register:%s', $request->ip());
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('resend-email', function (Request $request) {
            $key = sprintf('resend:%s', strtolower((string) $request->input('email')) ?: $request->ip());
            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('verify-email', function (Request $request) {
            $key = sprintf('verify:%s', strtolower((string) $request->input('email')) ?: $request->ip());
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('refresh', function (Request $request) {
            $key = sprintf('refresh:%s', $request->user()?->id ?: $request->ip());
            return Limit::perMinute(30)->by($key);
        });
    }
}
