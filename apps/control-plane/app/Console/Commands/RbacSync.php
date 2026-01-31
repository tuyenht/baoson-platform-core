<?php

namespace App\Console\Commands;

use App\Enums\Permission as P;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RbacSync extends Command
{
    protected $signature = 'rbac:sync
        {--guard= : Guard name (default: auth.defaults.guard)}
        {--assign-super-admin : Sync all permissions to role super_admin}';

    protected $description = 'Upsert canonical RBAC permissions into DB (and optionally assign to super_admin).';

    public function handle(): int
    {
        $guard = (string) ($this->option('guard') ?: config('auth.defaults.guard', 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $created = 0;
        foreach (P::values() as $permissionName) {
            $permission = SpatiePermission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);

            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("RBAC: synced ".count(P::values())." permissions (created {$created}). Guard={$guard}");

        if ((bool) $this->option('assign-super-admin')) {
            $role = SpatieRole::query()->firstOrCreate([
                'name' => 'super_admin',
                'guard_name' => $guard,
            ]);

            $all = SpatiePermission::query()->where('guard_name', $guard)->get();
            $role->syncPermissions($all);

            $this->info('RBAC: synced all permissions to role super_admin.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return self::SUCCESS;
    }
}


