<?php

namespace App\Support\Tenancy;

use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class SiteContext
{
    private static ?int $siteId = null;
    private static ?Site $site = null;

    /**
     * Set the current site context.
     */
    public static function set(?int $siteId, ?Site $site = null): void
    {
        self::$siteId = $siteId;
        self::$site = $site;

        if ($siteId !== null) {
            Session::put('current_site_id', $siteId);
        } else {
            Session::forget('current_site_id');
        }
    }

    /**
     * Get the current site ID.
     */
    public static function id(): ?int
    {
        if (self::$siteId !== null) {
            return self::$siteId;
        }

        // Try to get from session
        $sessionId = Session::get('current_site_id');
        if ($sessionId !== null) {
            self::$siteId = (int) $sessionId;
            return self::$siteId;
        }

        // Try to resolve from user's first active membership
        $user = Auth::user();
        if ($user) {
            $tenantId = TenantContext::id();
            if ($tenantId) {
                $membership = $user->siteMemberships()
                    ->whereHas('site', function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId)
                            ->where('status', 'active');
                    })
                    ->where('status', 'active')
                    ->first();

                if ($membership) {
                    self::$siteId = (int) $membership->site_id;
                    Session::put('current_site_id', self::$siteId);
                    return self::$siteId;
                }
            }
        }

        return null;
    }

    /**
     * Get the current site model.
     */
    public static function get(): ?Site
    {
        if (self::$site !== null) {
            return self::$site;
        }

        $siteId = self::id();
        if ($siteId === null) {
            return null;
        }

        self::$site = Site::query()->find($siteId);
        return self::$site;
    }

    /**
     * Clear the current site context.
     */
    public static function clear(): void
    {
        self::$siteId = null;
        self::$site = null;
        Session::forget('current_site_id');
    }
}

