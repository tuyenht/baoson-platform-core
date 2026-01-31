<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\Permission as P;
use App\Policies\Concerns\EnforcesSiteScope;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    use EnforcesSiteScope;

    public function viewAny(User $actor): bool
    {
        $permission = P::PlatformAdminPermissionsView->value;
        $legacyPermission = P::AdminPermissionsView->value;
        
        return $this->checkPermissionWithSiteScope($actor, $permission)
            || $this->checkPermissionWithSiteScope($actor, $legacyPermission);
    }

    public function view(User $actor, Permission $permission): bool
    {
        $permission = P::PlatformAdminPermissionsView->value;
        $legacyPermission = P::AdminPermissionsView->value;
        
        return $this->checkPermissionWithSiteScope($actor, $permission)
            || $this->checkPermissionWithSiteScope($actor, $legacyPermission);
    }

    public function create(User $actor): bool
    {
        $permission = P::PlatformAdminPermissionsCreate->value;
        $legacyPermission = P::AdminPermissionsCreate->value;
        
        return $this->checkPermissionWithSiteScope($actor, $permission)
            || $this->checkPermissionWithSiteScope($actor, $legacyPermission);
    }

    public function update(User $actor, Permission $permission): bool
    {
        $permission = P::PlatformAdminPermissionsUpdate->value;
        $legacyPermission = P::AdminPermissionsUpdate->value;
        
        return $this->checkPermissionWithSiteScope($actor, $permission)
            || $this->checkPermissionWithSiteScope($actor, $legacyPermission);
    }

    public function delete(User $actor, Permission $permission): bool
    {
        $permission = P::PlatformAdminPermissionsDelete->value;
        $legacyPermission = P::AdminPermissionsDelete->value;
        
        return $this->checkPermissionWithSiteScope($actor, $permission)
            || $this->checkPermissionWithSiteScope($actor, $legacyPermission);
    }
}


