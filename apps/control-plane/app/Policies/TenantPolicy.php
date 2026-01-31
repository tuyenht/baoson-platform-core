<?php

namespace App\Policies;

use App\Enums\Permission as P;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\EnforcesSiteScope;

class TenantPolicy
{
    use EnforcesSiteScope;

    /**
     * Determine if user can view any tenants.
     */
    public function viewAny(User $user): bool
    {
        // Only platform roles can view tenants
        if (!$this->hasPlatformRole($user)) {
            return false;
        }

        $permission = P::PlatformAdminTenantsView->value;
        $legacyPermission = P::AdminUsersView->value; // Legacy fallback

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    /**
     * Determine if user can view a specific tenant.
     */
    public function view(User $user, Tenant $tenant): bool
    {
        // Only platform roles can view tenants
        if (!$this->hasPlatformRole($user)) {
            return false;
        }

        $permission = P::PlatformAdminTenantsView->value;
        $legacyPermission = P::AdminUsersView->value;

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    /**
     * Determine if user can create tenants.
     */
    public function create(User $user): bool
    {
        // Only saasOwner can create tenants
        if (!$user->hasRole('saasOwner')) {
            return false;
        }

        $permission = P::PlatformAdminTenantsCreate->value;
        $legacyPermission = P::AdminUsersCreate->value;

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    /**
     * Determine if user can update a tenant.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        // Only platform roles can update tenants
        if (!$this->hasPlatformRole($user)) {
            return false;
        }

        $permission = P::PlatformAdminTenantsUpdate->value;
        $legacyPermission = P::AdminUsersUpdate->value;

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    /**
     * Determine if user can delete a tenant.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        // Only saasOwner can delete tenants
        if (!$user->hasRole('saasOwner')) {
            return false;
        }

        $permission = P::PlatformAdminTenantsDelete->value;
        $legacyPermission = P::AdminUsersDelete->value;

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }
}

