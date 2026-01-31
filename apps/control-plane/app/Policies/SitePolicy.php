<?php

namespace App\Policies;

use App\Enums\Permission as P;
use App\Models\Site;
use App\Models\User;
use App\Policies\Concerns\EnforcesSiteScope;
use App\Support\Tenancy\SiteContext;
use App\Support\Tenancy\TenantContext;

class SitePolicy
{
    use EnforcesSiteScope;

    /**
     * Determine if user can view any sites.
     */
    public function viewAny(User $user): bool
    {
        // Platform roles can view all sites
        if ($this->hasPlatformRole($user)) {
            return $user->can(P::PlatformAdminTenantsView->value)
                || $user->can(P::AdminUsersView->value); // Legacy
        }

        // Site roles can only view sites they're members of
        $tenantId = TenantContext::id();
        if ($tenantId === null) {
            return false;
        }

        return $user->siteMemberships()
            ->whereHas('site', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->where('status', 'active');
            })
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Determine if user can view a specific site.
     */
    public function view(User $user, Site $site): bool
    {
        // Platform roles can view any site
        if ($this->hasPlatformRole($user)) {
            return true;
        }

        // Site roles can only view sites they're members of
        return $this->hasActiveSiteMembership($user, $site->id);
    }

    /**
     * Determine if user can create sites.
     */
    public function create(User $user): bool
    {
        // Only platform roles can create sites
        if (!$this->hasPlatformRole($user)) {
            return false;
        }

        $permission = P::PlatformAdminTenantsCreate->value;
        $legacyPermission = P::AdminUsersCreate->value; // Legacy fallback

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }

    /**
     * Determine if user can update a site.
     */
    public function update(User $user, Site $site): bool
    {
        // Platform roles can update any site
        if ($this->hasPlatformRole($user)) {
            $permission = P::PlatformAdminTenantsUpdate->value;
            $legacyPermission = P::AdminUsersUpdate->value;
            
            return $this->checkPermissionWithSiteScope($user, $permission)
                || $this->checkPermissionWithSiteScope($user, $legacyPermission);
        }

        // SiteOwner can update their own site
        $siteRole = $user->getSiteRole($site);
        if ($siteRole === 'siteOwner') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete a site.
     */
    public function delete(User $user, Site $site): bool
    {
        // Only platform roles can delete sites
        if (!$this->hasPlatformRole($user)) {
            return false;
        }

        $permission = P::PlatformAdminTenantsDelete->value;
        $legacyPermission = P::AdminUsersDelete->value;

        return $this->checkPermissionWithSiteScope($user, $permission)
            || $this->checkPermissionWithSiteScope($user, $legacyPermission);
    }
}

