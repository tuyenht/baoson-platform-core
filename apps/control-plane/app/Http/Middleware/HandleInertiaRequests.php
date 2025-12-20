<?php

namespace App\Http\Middleware;

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
                    'login' => $adminRouteName.'login',
                    'loginStore' => $adminRouteName.'login.store',
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
                ] : null,
            ],
            'flash' => [
                'status' => session('status'),
                'error' => session('error'),
            ],
            'social' => [
                'googleEnabled' => (bool) config('services.google.enabled', false),
                'facebookEnabled' => (bool) config('services.facebook.enabled', false),
                'socialiteInstalled' => class_exists(\Laravel\Socialite\Facades\Socialite::class),
            ],
        ];
    }
}
