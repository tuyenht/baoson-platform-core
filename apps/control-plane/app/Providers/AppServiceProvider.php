<?php

namespace App\Providers;

use App\Auth\PepperedUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

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
        Auth::provider('peppered', function ($app, array $config) {
            return new PepperedUserProvider(
                $app['hash'],
                $config['model'],
                (string) ($app['config']->get('security.password_pepper') ?: ''),
                (string) ($app['config']->get('security.password_pepper_previous') ?: '')
            );
        });

        // Optional fail-fast if pepper is required for this environment.
        if ((bool) config('security.require_password_pepper', false) && empty(config('security.password_pepper'))) {
            throw new RuntimeException('Missing AUTH_PASSWORD_PEPPER in production.');
        }
    }
}
