<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacGrant extends Command
{
    protected $signature = 'rbac:grant
        {permission : Permission name (e.g. admin.audit.view)}
        {--role= : Role name to grant to (e.g. admin)}
        {--guard= : Guard name (default: auth.defaults.guard)}';

    protected $description = 'Grant a permission to a role (idempotent).';

    public function handle(): int
    {
        $permissionName = (string) $this->argument('permission');
        $roleName = (string) ($this->option('role') ?: '');
        $guard = (string) ($this->option('guard') ?: config('auth.defaults.guard', 'web'));

        if ($roleName === '') {
            $this->error('Missing --role option.');
            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()->firstOrCreate([
            'name' => $permissionName,
            'guard_name' => $guard,
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => $guard,
        ]);

        $role->givePermissionTo($permission);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Granted permission '{$permissionName}' to role '{$roleName}' (guard={$guard}).");

        return self::SUCCESS;
    }
}


