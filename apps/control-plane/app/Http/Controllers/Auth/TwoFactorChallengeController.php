<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit\Audit;
use App\Support\TwoFactor\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $userId = $request->session()->get('2fa.user_id');
        if (! $userId) {
            return redirect()->route(config('admin.route_name', 'bsadm.').'login');
        }

        $user = User::query()->select(['id', 'email'])->find($userId);
        if (! $user) {
            $request->session()->forget(['2fa.user_id', '2fa.remember']);
            return redirect()->route(config('admin.route_name', 'bsadm.').'login');
        }

        $maskedEmail = preg_replace('/(^.).*(@.*$)/', '$1***$2', (string) $user->email);

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => $maskedEmail,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa.user_id');
        if (! $userId) {
            Audit::record('2fa.challenge_failed', [
                'reason' => 'missing_session_user',
            ], $request, 302);

            return redirect()->route(config('admin.route_name', 'bsadm.').'login');
        }

        $this->ensureIsNotRateLimited($request, (int) $userId);

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $code = trim((string) $request->input('code', ''));
        $recoveryCode = strtoupper(trim((string) $request->input('recovery_code', '')));

        if ($code === '' && $recoveryCode === '') {
            Audit::record('2fa.challenge_failed', [
                'user_id' => $userId,
                'reason' => 'code_required',
            ], $request, 422);

            RateLimiter::hit($this->throttleKey($request, (int) $userId), $this->decaySeconds());

            throw ValidationException::withMessages([
                'code' => __('twofactor.code_required'),
            ]);
        }

        $user = User::query()->findOrFail($userId);
        if (! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['2fa.user_id', '2fa.remember']);
            Audit::record('2fa.challenge_failed', [
                'user_id' => $userId,
                'reason' => 'not_enabled',
            ], $request, 302);

            return redirect()->route(config('admin.route_name', 'bsadm.').'login');
        }

        $passed = false;

        if ($code !== '') {
            $secret = Crypt::decryptString((string) $user->two_factor_secret);
            $passed = Totp::verify($secret, $code);
        } else {
            $passed = $this->verifyAndConsumeRecoveryCode($user, $recoveryCode);
        }

        if (! $passed) {
            Audit::record('2fa.challenge_failed', [
                'user_id' => $userId,
                'reason' => 'invalid_code',
                'mode' => $code !== '' ? 'totp' : 'recovery',
            ], $request, 422);

            RateLimiter::hit($this->throttleKey($request, (int) $userId), $this->decaySeconds());

            throw ValidationException::withMessages([
                'code' => __('twofactor.invalid_code'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, (int) $userId));

        $remember = (bool) $request->session()->get('2fa.remember', false);
        Auth::login($user, $remember);
        $request->session()->forget(['2fa.user_id', '2fa.remember']);
        $request->session()->regenerate();

        Audit::record('2fa.challenge_success', [
            'user_id' => $userId,
            'remember' => $remember,
        ], $request, 302);

        return redirect()->intended(route(config('admin.route_name', 'bsadm.').'dashboard'));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function ensureIsNotRateLimited(Request $request, int $userId): void
    {
        $key = $this->throttleKey($request, $userId);
        $maxAttempts = (int) config('security.two_factor.challenge_max_attempts', 5);

        if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        Audit::record('2fa.challenge_throttled', [
            'user_id' => $userId,
            'seconds' => $seconds,
        ], $request, 429);

        throw ValidationException::withMessages([
            'code' => __('twofactor.throttle', ['seconds' => $seconds]),
        ]);
    }

    private function throttleKey(Request $request, int $userId): string
    {
        return Str::transliterate('2fa|'.$userId.'|'.$request->ip());
    }

    private function decaySeconds(): int
    {
        return (int) config('security.two_factor.challenge_decay_seconds', 60);
    }

    private function verifyAndConsumeRecoveryCode(User $user, string $recoveryCode): bool
    {
        if ($recoveryCode === '' || ! $user->two_factor_recovery_codes) {
            return false;
        }

        $codes = json_decode(
            Crypt::decryptString((string) $user->two_factor_recovery_codes),
            true
        );

        if (! is_array($codes)) {
            return false;
        }

        $index = array_search($recoveryCode, $codes, true);
        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $codes = array_values($codes);

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes, JSON_THROW_ON_ERROR)),
        ])->save();

        return true;
    }
}


