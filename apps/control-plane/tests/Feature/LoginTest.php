<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            // Plain password; will be hashed by the mutator with pepper
            'password' => 'password',
        ]);

        $response = $this->post(route('bsadm.login.store'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('bsadm.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('bsadm.login'))
            ->post(route('bsadm.login.store'), [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect(route('bsadm.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        // 6 lan thu voi mat khau sai (nguong la 5 trong controller)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->from(route('bsadm.login'))
                ->post(route('bsadm.login.store'), [
                    'email' => 'user@example.com',
                    'password' => 'wrong-password',
                ]);
        }

        $response->assertRedirect(route('bsadm.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}


