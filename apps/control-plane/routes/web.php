<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\LocaleController;

$adminPrefix = config('admin.prefix', 'bsadm');
$adminRouteName = config('admin.route_name', 'bsadm.');

// Locale switching (available for all users)
Route::post('/locale', [LocaleController::class, 'store'])->name('locale.store');

// Laravel's built-in `auth` middleware redirects unauthenticated users to route('login').
// Our actual login screen lives under the admin prefix, so we provide a small alias.
Route::get('/login', function () use ($adminPrefix) {
    return redirect("/{$adminPrefix}/login");
})->name('login');

Route::get('/', function () use ($adminPrefix) {
    return redirect("/{$adminPrefix}");
});

Route::prefix($adminPrefix)
    ->name($adminRouteName)
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->name('login.store');

            Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

            Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
            Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');

            Route::get('/oauth/{provider}/redirect', [OAuthController::class, 'redirect'])
                ->whereIn('provider', ['google', 'facebook'])
                ->name('oauth.redirect');
            Route::get('/oauth/{provider}/callback', [OAuthController::class, 'callback'])
                ->whereIn('provider', ['google', 'facebook'])
                ->name('oauth.callback');
        });

        Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
            })->name('dashboard');

            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
        });
});
