<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $this->seed();

        $user = User::where('email', 'super_admin@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/bsadm')
            ->assertOk();
    }

    public function test_viewer_can_access_dashboard_but_cannot_access_users(): void
    {
        $this->seed();

        $viewer = User::where('email', 'viewer@example.com')->firstOrFail();

        $this->actingAs($viewer)
            ->get('/bsadm')
            ->assertOk();

        $this->actingAs($viewer)
            ->get('/bsadm/users')
            ->assertStatus(403);
    }
}


