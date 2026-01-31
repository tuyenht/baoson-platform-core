<?php

namespace App\Policies;

use App\Enums\Permission as P;
use App\Models\User;
use App\Policies\Concerns\EnforcesSiteScope;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use EnforcesSiteScope;

    public function viewAny(User $user): bool
    {
        $permission = P::PlatformAdminRolesView->value;
        $legacyPermission = P::AdminRolesView->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function view(User $user, Role $role): bool
    {
        $permission = P::PlatformAdminRolesView->value;
        $legacyPermission = P::AdminRolesView->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function create(User $user): bool
    {
        $permission = P::PlatformAdminRolesCreate->value;
        $legacyPermission = P::AdminRolesCreate->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function update(User $user, Role $role): bool
    {
        $permission = P::PlatformAdminRolesUpdate->value;
        $legacyPermission = P::AdminRolesUpdate->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function delete(User $user, Role $role): bool
    {
        $permission = P::PlatformAdminRolesDelete->value;
        $legacyPermission = P::AdminRolesDelete->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }
}


