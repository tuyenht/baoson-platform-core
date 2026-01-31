<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Security\TwoFactorController;
use App\Enums\Permission as P;

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
        // Setup route (must be accessible without auth)
        Route::middleware([\App\Http\Middleware\RequireSetup::class])->group(function () {
            Route::get('/setup', [\App\Http\Controllers\Setup\SetupController::class, 'create'])
                ->name('setup');
            Route::post('/setup', [\App\Http\Controllers\Setup\SetupController::class, 'store'])
                ->name('setup.store');
        });

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->name('login.store');

            Route::get('/two-factor/challenge', [TwoFactorChallengeController::class, 'create'])
                ->name('two-factor.challenge');
            Route::post('/two-factor/challenge', [TwoFactorChallengeController::class, 'store'])
                ->name('two-factor.challenge.store');

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
            })
                ->middleware('can:'.P::AdminDashboardView->value)
                ->name('dashboard');

            Route::get('/users', [UserController::class, 'index'])
                ->middleware('can:'.P::AdminUsersView->value)
                ->name('users.index');

            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware('can:'.P::AdminAuditView->value)
                ->name('audit-logs.index');

            // Example: role-based guard (in addition to permission-based "can:" guards)
            Route::get('/rbac', function () {
                return Inertia::render('Admin/Rbac');
            })
                ->middleware('role:super_admin')
                ->name('rbac');

            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');

            // Security: 2-step verification (available for all authenticated users)
            Route::get('/security/two-factor', [TwoFactorController::class, 'show'])
                ->name('security.two-factor');
            Route::post('/security/two-factor/enable', [TwoFactorController::class, 'enable'])
                ->name('security.two-factor.enable');
            Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirm'])
                ->name('security.two-factor.confirm');
            Route::post('/security/two-factor/disable', [TwoFactorController::class, 'disable'])
                ->name('security.two-factor.disable');
            Route::post('/security/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
                ->name('security.two-factor.recovery');
        });
});
