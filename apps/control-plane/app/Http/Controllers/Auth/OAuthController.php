<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    /**
     * @return array<int, string>
     */
    protected function allowedProviders(): array
    {
        return ['google', 'facebook'];
    }

    protected function isProviderEnabled(string $provider): bool
    {
        return (bool) config("services.{$provider}.enabled", false);
    }

    protected function isSocialiteInstalled(): bool
    {
        return class_exists(\Laravel\Socialite\Facades\Socialite::class);
    }

    public function redirect(Request $request, string $provider)
    {
        if (! in_array($provider, $this->allowedProviders(), true)) {
            abort(404);
        }

        if (! $this->isProviderEnabled($provider)) {
            abort(404);
        }

        if (! $this->isSocialiteInstalled()) {
            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'login')
                ->with('error', 'Social login chưa được cài (thiếu laravel/socialite).');
        }

        $driver = \Laravel\Socialite\Facades\Socialite::driver($provider);
        $stateless = (bool) config("services.{$provider}.stateless", false);

        if ($stateless) {
            $driver = $driver->stateless();
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders(), true)) {
            abort(404);
        }

        if (! $this->isProviderEnabled($provider)) {
            abort(404);
        }

        if (! $this->isSocialiteInstalled()) {
            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'login')
                ->with('error', 'Social login chưa được cài (thiếu laravel/socialite).');
        }

        try {
            $driver = \Laravel\Socialite\Facades\Socialite::driver($provider);
            $stateless = (bool) config("services.{$provider}.stateless", false);
            if ($stateless) {
                $driver = $driver->stateless();
            }

            $socialUser = $driver->user();
            $email = $socialUser->getEmail();

            if (! is_string($email) || $email === '') {
                return redirect()
                    ->route(config('admin.route_name', 'bsadm.').'login')
                    ->with('error', 'Không lấy được email từ nhà cung cấp OAuth.');
            }

            $name = $socialUser->getName() ?: ($socialUser->getNickname() ?: $email);

            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Str::random(32),
                ]);
            } else {
                if (is_string($name) && $name !== '' && $user->name !== $name) {
                    $user->forceFill(['name' => $name])->save();
                }
            }

            if (is_null($user->email_verified_at)) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route(config('admin.route_name', 'bsadm.').'dashboard'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'login')
                ->with('error', 'Đăng nhập OAuth thất bại. Vui lòng thử lại.');
        }
    }
}

