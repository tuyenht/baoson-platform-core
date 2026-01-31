<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Rbac\Rbac;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RbacBootstrapSuperAdmin extends Command
{
    protected $signature = 'rbac:bootstrap-super-admin
        {email : Email of the super admin user}
        {--name= : Display name (optional)}
        {--password= : Plain password (optional; if omitted a random one is generated)}
        {--guard=web : Guard name (default: web)}
        {--force : Allow running in production}';

    protected $description = 'Create or update a super_admin user and assign full RBAC permissions (deploy-safe).';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force.');
            return self::FAILURE;
        }

        $email = (string) $this->argument('email');
        $guard = (string) ($this->option('guard') ?: 'web');
        $name = (string) ($this->option('name') ?: 'Super Admin');

        $plainPassword = (string) ($this->option('password') ?: Str::random(20));

        // Ensure canonical permissions + baseline roles exist
        Rbac::syncPermissions($guard);
        $role = Rbac::findOrCreateRole('super_admin', $guard);
        $role->syncPermissions(\App\Enums\Permission::values());

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                // Plain password; will be hashed by User::setPasswordAttribute (pepper-aware)
                'password' => $plainPassword,
                'email_verified_at' => now(),
            ]
        );

        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$role->name]);
        }

        $this->info("Super admin ready:");
        $this->line("  email: {$email}");
        $this->line("  name : {$user->name}");
        $this->line("  role : {$role->name}");
        $this->line("  guard: {$guard}");

        if (! $this->option('password')) {
            $this->warn('Generated password (store it securely, shown once):');
            $this->line("  password: {$plainPassword}");
        }

        return self::SUCCESS;
    }
}


