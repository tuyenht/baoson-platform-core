<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TwoFactor\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_and_confirm_two_factor(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->post(route('bsadm.security.two-factor.enable'), [
                'password' => 'password',
            ])
            ->assertRedirect(route('bsadm.security.two-factor'));

        $user->refresh();

        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_secret);

        $secret = Crypt::decryptString((string) $user->two_factor_secret);
        $code = $this->generateValidTotpCode($secret);

        $this->actingAs($user)
            ->post(route('bsadm.security.two-factor.confirm'), [
                'code' => $code,
            ])
            ->assertRedirect(route('bsadm.security.two-factor'));

        $user->refresh();

        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_two_factor_challenge_with_totp_code_logs_in_user(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        // Bat dau setup 2FA
        $this->actingAs($user)
            ->post(route('bsadm.security.two-factor.enable'), [
                'password' => 'password',
            ]);

        $user->refresh();
        $secret = Crypt::decryptString((string) $user->two_factor_secret);
        $code = $this->generateValidTotpCode($secret);

        // Xac nhan de 2FA duoc bat va tao recovery codes
        $this->actingAs($user)
            ->post(route('bsadm.security.two-factor.confirm'), [
                'code' => $code,
            ]);

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());

        // Dang xuat va bat dau flow dang nhap co 2FA
        auth()->logout();

        // Dang nhap bang email/password -> bi chuyen sang 2FA challenge (session 2fa.user_id)
        $this->post(route('bsadm.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('bsadm.two-factor.challenge'));

        $session = $this->app['session.store'];
        $userId = $session->get('2fa.user_id');
        $this->assertSame($user->id, $userId);

        $code = $this->generateValidTotpCode($secret);

        // Gui code 2FA hop le
        $this->withSession([
            '2fa.user_id' => $user->id,
            '2fa.remember' => false,
        ])->post(route('bsadm.two-factor.challenge.store'), [
            'code' => $code,
        ])->assertRedirect(route('bsadm.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    private function generateValidTotpCode(string $secret): string
    {
        // De test on dinh, su dung timestamp hien tai
        $timestamp = time();

        // Thu tim ma hop le trong cua so nho quanh timestamp
        for ($t = $timestamp - 60; $t <= $timestamp + 60; $t += 30) {
            for ($window = 0; $window <= 1; $window++) {
                // Thu sinh 000000..999999 de tim ma hop le
                for ($i = 0; $i < 1000000; $i++) {
                    $code = str_pad((string) $i, 6, '0', STR_PAD_LEFT);
                    if (Totp::verify($secret, $code, $window, $t)) {
                        return $code;
                    }
                }
            }
        }

        $this->fail('Could not generate a valid TOTP code for testing.');
    }
}


