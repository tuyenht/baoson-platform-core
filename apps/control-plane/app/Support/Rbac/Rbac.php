<?php

namespace App\Support\Rbac;

use App\Enums\Permission as CanonicalPermission;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class Rbac
{
    /**
     * Sync canonical permissions into DB (upsert by name+guard).
     *
     * @return Collection<int, Permission>
     */
    public static function syncPermissions(string $guardName = 'web'): Collection
    {
        $permissions = collect(CanonicalPermission::values())
            ->map(fn (string $name) => Permission::findOrCreate($name, $guardName));

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $permissions;
    }

    public static function findOrCreateRole(string $name, string $guardName = 'web'): Role
    {
        return Role::findOrCreate($name, $guardName);
    }
}


