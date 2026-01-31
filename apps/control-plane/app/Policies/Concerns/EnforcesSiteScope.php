<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Tenancy\SiteContext;

/**
 * Trait to enforce site scope for site-scoped permissions.
 *
 * Logic:
 * - Platform roles (saasOwner, saasAdmin) bypass all site checks
 * - Site-scoped permissions (site.*) require active site membership
 * - Platform-scoped permissions (platform.*, admin.*) don't require site membership
 */
trait EnforcesSiteScope
{
    /**
     * Check if user has a platform role (can bypass site scope checks).
     */
    protected function hasPlatformRole(User $user): bool
    {
        return $user->hasAnyRole(['saasOwner', 'saasAdmin']);
    }

    /**
     * Check if permission is site-scoped.
     */
    protected function isSiteScopedPermission(string $permission): bool
    {
        return str_starts_with($permission, 'site.');
    }

    /**
     * Check if permission is platform-scoped.
     */
    protected function isPlatformScopedPermission(string $permission): bool
    {
        return str_starts_with($permission, 'platform.') || str_starts_with($permission, 'admin.');
    }

    /**
     * Check if user has active membership in the current site.
     */
    protected function hasActiveSiteMembership(User $user, ?int $siteId = null): bool
    {
        $siteId = $siteId ?? SiteContext::id();
        
        if ($siteId === null) {
            return false;
        }

        return $user->siteMemberships()
            ->where('site_id', $siteId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Check permission with site scope enforcement.
     *
     * Rules:
     * 1. Platform roles (saasOwner, saasAdmin) → always allowed
     * 2. Platform-scoped permissions → check permission only (no site check)
     * 3. Site-scoped permissions → check permission + site membership
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    protected function checkPermissionWithSiteScope(User $user, string $permission): bool
    {
        // 1. Platform roles bypass all checks
        if ($this->hasPlatformRole($user)) {
            return $user->can($permission);
        }

        // 2. Platform-scoped permissions: check permission only
        if ($this->isPlatformScopedPermission($permission)) {
            return $user->can($permission);
        }

        // 3. Site-scoped permissions: check permission + site membership
        if ($this->isSiteScopedPermission($permission)) {
            if (!$this->hasActiveSiteMembership($user)) {
                return false;
            }
            return $user->can($permission);
        }

        // 4. Unknown scope: default to permission check only (backward compatibility)
        return $user->can($permission);
    }
}

