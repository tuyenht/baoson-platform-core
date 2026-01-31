<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'locale' => ['sometimes', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $remember = $request->boolean('remember');
        $credentials = $request->only('email', 'password');

        // PERF: Avoid double user lookup. We retrieve once, then validate credentials.
        $provider = Auth::getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($this->throttleKey($request), $this->loginDecaySeconds());

            Audit::record('auth.login_failed', [
                'email' => (string) $request->input('email'),
                'reason' => 'invalid_credentials',
            ], $request, 422);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // If user has 2FA enabled, redirect to challenge step.
        if (method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled()) {
            $request->session()->put('2fa.user_id', $user->getAuthIdentifier());
            $request->session()->put('2fa.remember', $remember);
            $request->session()->regenerate();

            Audit::record('auth.two_factor_required', [
                'user_id' => $user->getAuthIdentifier(),
            ], $request, 302);

            return redirect()->route(config('admin.route_name', 'bsadm.').'two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        Audit::record('auth.login_success', [
            'user_id' => $user->getAuthIdentifier(),
            'remember' => $remember,
        ], $request, 302);

        return redirect()->intended(
            route(config('admin.route_name', 'bsadm.').'dashboard')
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        Audit::record('auth.logout', [], $request, 302);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(config('admin.route_name', 'bsadm.').'login');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, $this->maxLoginAttempts())) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($key);

        Audit::record('auth.login_throttled', [
            'email' => (string) $request->input('email'),
            'seconds' => $seconds,
        ], $request, 429);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }

    protected function maxLoginAttempts(): int
    {
        return (int) config('security.login.max_attempts', 5);
    }

    protected function loginDecaySeconds(): int
    {
        return (int) config('security.login.decay_seconds', 60);
    }
}

