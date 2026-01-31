<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Support\TwoFactor\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $enabled = $user?->hasTwoFactorEnabled() ?? false;
        $pending = (bool) ($user?->two_factor_secret) && ! $enabled;

        $secret = null;
        $otpauthUrl = null;

        if ($pending) {
            $secret = Crypt::decryptString((string) $user->two_factor_secret);
            $otpauthUrl = Totp::otpAuthUrl(
                issuer: config('app.name', 'Baoson'),
                accountName: (string) $user->email,
                secret: $secret
            );
        }

        $recoveryCodes = null;
        if (session()->has('recovery_codes')) {
            $recoveryCodes = session('recovery_codes');
        }

        return Inertia::render('Security/TwoFactor', [
            'twoFactor' => [
                'enabled' => $enabled,
                'pending' => $pending,
                'secret' => $secret,
                'otpauthUrl' => $otpauthUrl,
                'recoveryCodes' => $recoveryCodes,
            ],
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $secret = Totp::generateSecret();

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => null,
        ])->save();

        Audit::record('2fa.setup_started', [
            'user_id' => $user?->id,
        ], $request, 302);

        return redirect()
            ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
            ->with('status', __('twofactor.setup_started'));
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! $user?->two_factor_secret) {
            Audit::record('2fa.confirm_failed', [
                'user_id' => $user?->id,
                'reason' => 'not_started',
            ], $request, 302);

            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
                ->with('error', __('twofactor.not_started'));
        }

        $secret = Crypt::decryptString((string) $user->two_factor_secret);

        if (! Totp::verify($secret, (string) $request->input('code'))) {
            Audit::record('2fa.confirm_failed', [
                'user_id' => $user?->id,
                'reason' => 'invalid_code',
            ], $request, 302);

            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
                ->with('error', __('twofactor.invalid_code'));
        }

        $recoveryCodes = self::generateRecoveryCodes();

        $user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)),
        ])->save();

        Audit::record('2fa.enabled', [
            'user_id' => $user?->id,
        ], $request, 302);

        return redirect()
            ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
            ->with('status', __('twofactor.enabled'))
            ->with('recovery_codes', $recoveryCodes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        Audit::record('2fa.disabled', [
            'user_id' => $user?->id,
        ], $request, 302);

        return redirect()
            ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
            ->with('status', __('twofactor.disabled'));
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if (! $user?->hasTwoFactorEnabled()) {
            Audit::record('2fa.recovery_regenerate_failed', [
                'user_id' => $user?->id,
                'reason' => 'not_enabled',
            ], $request, 302);

            return redirect()
                ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
                ->with('error', __('twofactor.not_enabled'));
        }

        $recoveryCodes = self::generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)),
        ])->save();

        Audit::record('2fa.recovery_regenerated', [
            'user_id' => $user?->id,
        ], $request, 302);

        return redirect()
            ->route(config('admin.route_name', 'bsadm.').'security.two-factor')
            ->with('status', __('twofactor.recovery_regenerated'))
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * @return list<string>
     */
    private static function generateRecoveryCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $raw = Str::upper(Str::random(10));
            $codes[] = substr($raw, 0, 5).'-'.substr($raw, 5, 5);
        }
        return $codes;
    }
}


