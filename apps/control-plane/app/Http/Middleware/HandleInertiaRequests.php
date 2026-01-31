<?php

namespace App\Http\Middleware;

use App\Services\FeatureFlags\FeatureFlagService;
use App\Services\Menu\AdminMenuResolver;
use App\Support\Tenancy\SiteContext;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $adminRouteName = config('admin.route_name', 'bsadm.');
        $currentLocale = app()->getLocale();

        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'csrfToken' => csrf_token(),
            'locale' => $currentLocale,
            'admin' => [
                'prefix' => config('admin.prefix'),
                'routes' => [
                    'dashboard' => $adminRouteName.'dashboard',
                    'usersIndex' => $adminRouteName.'users.index',
                    'auditLogsIndex' => $adminRouteName.'audit-logs.index',
                    'login' => $adminRouteName.'login',
                    'loginStore' => $adminRouteName.'login.store',
                    'twoFactorChallenge' => $adminRouteName.'two-factor.challenge',
                    'twoFactorChallengeStore' => $adminRouteName.'two-factor.challenge.store',
                    'twoFactorEnable' => $adminRouteName.'security.two-factor.enable',
                    'twoFactorConfirm' => $adminRouteName.'security.two-factor.confirm',
                    'twoFactorDisable' => $adminRouteName.'security.two-factor.disable',
                    'twoFactorRecovery' => $adminRouteName.'security.two-factor.recovery',
                    'twoFactorSettings' => $adminRouteName.'security.two-factor',
                    'logout' => $adminRouteName.'logout',
                    'passwordRequest' => $adminRouteName.'password.request',
                    'passwordEmail' => $adminRouteName.'password.email',
                    'passwordReset' => $adminRouteName.'password.reset',
                    'passwordUpdate' => $adminRouteName.'password.update',
                    'oauthRedirect' => $adminRouteName.'oauth.redirect',
                    'localeStore' => 'locale.store',
                ],
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at?->toIso8601String(),
                    'two_factor_enabled' => method_exists($request->user(), 'hasTwoFactorEnabled')
                        ? (bool) $request->user()->hasTwoFactorEnabled()
                        : false,
                ] : null,
                'roles' => $request->user()
                    ? $request->user()->getRoleNames()->values()->all()
                    : [],
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->values()->all()
                    : [],
            ],
            'flash' => [
                'status' => session('status'),
                'error' => session('error'),
                'recoveryCodes' => session('recovery_codes'),
            ],
            'social' => [
                'googleEnabled' => (bool) config('services.google.enabled', false),
                'facebookEnabled' => (bool) config('services.facebook.enabled', false),
                'socialiteInstalled' => class_exists(\Laravel\Socialite\Facades\Socialite::class),
            ],
            'features' => [
                'enabledFlags' => FeatureFlagService::allEnabledKeys('global'),
            ],
            'ui' => [
                'menu' => $request->user()
                    ? AdminMenuResolver::resolve($request->user())
                    : [],
            ],
            'context' => [
                'tenant' => TenantContext::get() ? [
                    'id' => TenantContext::id(),
                    'key' => TenantContext::key(),
                    'name' => TenantContext::get()?->name,
                ] : null,
                'site' => SiteContext::get() ? [
                    'id' => SiteContext::id(),
                    'key' => SiteContext::get()?->key,
                    'name' => SiteContext::get()?->name,
                ] : null,
            ],
        ];
    }
}
