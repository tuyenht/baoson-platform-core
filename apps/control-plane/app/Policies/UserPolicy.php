<?php

namespace App\Policies;

use App\Enums\Permission as P;
use App\Models\User;
use App\Policies\Concerns\EnforcesSiteScope;

class UserPolicy
{
    use EnforcesSiteScope;

    public function viewAny(User $user): bool
    {
        // Users management is platform-level, but we check both legacy and new permissions
        $permission = P::PlatformAdminUsersView->value;
        $legacyPermission = P::AdminUsersView->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function view(User $user, User $model): bool
    {
        $permission = P::PlatformAdminUsersView->value;
        $legacyPermission = P::AdminUsersView->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function create(User $user): bool
    {
        $permission = P::PlatformAdminUsersCreate->value;
        $legacyPermission = P::AdminUsersCreate->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function update(User $user, User $model): bool
    {
        $permission = P::PlatformAdminUsersUpdate->value;
        $legacyPermission = P::AdminUsersUpdate->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    public function delete(User $user, User $model): bool
    {
        $permission = P::PlatformAdminUsersDelete->value;
        $legacyPermission = P::AdminUsersDelete->value;
        
        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }
}


