<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Site;
use App\Models\User;
use App\Support\Audit\Audit;
use App\Support\Rbac\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    /**
     * Show the setup page (only if setup is required).
     */
    public function create(Request $request): Response|RedirectResponse
    {
        // If setup is not required, redirect to dashboard
        if (! \App\Http\Middleware\RequireSetup::isSetupRequired()) {
            return redirect()->route('bsadm.dashboard');
        }

        return Inertia::render('Setup/Index');
    }

    /**
     * Process the setup form.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rate limit setup attempts
        $key = 'setup:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'email' => 'Too many setup attempts. Please try again later.',
            ]);
        }

        RateLimiter::hit($key, 3600); // 1 hour decay

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'create_demo_data' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Ensure RBAC is synced
            RbacService::sync(assignAllToSuperAdmin: true);

            // Create first saasOwner user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'), // Will be hashed by User model
                'email_verified_at' => now(),
            ]);

            $user->assignRole('saasOwner');

            Audit::record('setup.completed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ], $request, 302);

            // Optionally create demo data
            if ($request->boolean('create_demo_data')) {
                $this->createDemoData($user);
            }

            DB::commit();

            RateLimiter::clear($key);

            // Auto-login the user
            auth()->login($user);

            return redirect()->route('bsadm.dashboard')
                ->with('status', 'Setup completed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            Audit::record('setup.failed', [
                'error' => $e->getMessage(),
            ], $request, 500);

            return back()->withErrors([
                'email' => 'Setup failed. Please try again or contact support.',
            ])->withInput();
        }
    }

    /**
     * Create demo tenant, site, and users for testing.
     */
    private function createDemoData(User $saasOwner): void
    {
        // Create demo tenant
        $tenant = Tenant::create([
            'key' => 'demo',
            'name' => 'Demo Tenant',
            'status' => 'active',
            'is_active' => true,
        ]);

        // Create demo site
        $site = Site::create([
            'tenant_id' => $tenant->id,
            'key' => 'demo-site',
            'name' => 'Demo Site',
            'domain' => null,
            'status' => 'active',
        ]);

        // Create demo users with different roles
        $demoUsers = [
            ['name' => 'Site Owner', 'email' => 'siteowner@demo.com', 'role' => 'siteOwner'],
            ['name' => 'Site Admin', 'email' => 'siteadmin@demo.com', 'role' => 'siteAdmin'],
            ['name' => 'Site Editor', 'email' => 'siteeditor@demo.com', 'role' => 'siteEditor'],
            ['name' => 'Site Translator', 'email' => 'sitetranslator@demo.com', 'role' => 'siteTranslator'],
            ['name' => 'Site Viewer', 'email' => 'siteviewer@demo.com', 'role' => 'siteViewer'],
        ];

        foreach ($demoUsers as $demoUser) {
            $user = User::create([
                'name' => $demoUser['name'],
                'email' => $demoUser['email'],
                'password' => 'password', // Will be hashed by User model
                'email_verified_at' => now(),
            ]);

            // Assign platform role (if applicable)
            if (in_array($demoUser['role'], ['siteOwner', 'siteAdmin'], true)) {
                // These can also have platform roles if needed
            }

            // Create site membership
            $site->members()->attach($user->id, [
                'role' => $demoUser['role'],
                'status' => 'active',
            ]);
        }
    }
}

