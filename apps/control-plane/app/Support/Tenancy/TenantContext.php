<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class TenantContext
{
    private static ?int $tenantId = null;
    private static ?string $tenantKey = null;
    private static ?Tenant $tenant = null;

    public static function set(?int $tenantId, ?string $tenantKey = null): void
    {
        self::$tenantId = $tenantId;
        self::$tenantKey = $tenantKey;
        self::$tenant = null; // Clear cached model

        if ($tenantId !== null) {
            Session::put('current_tenant_id', $tenantId);
            if ($tenantKey !== null) {
                Session::put('current_tenant_key', $tenantKey);
            }
        } else {
            Session::forget(['current_tenant_id', 'current_tenant_key']);
        }
    }

    public static function id(): ?int
    {
        if (self::$tenantId !== null) {
            return self::$tenantId;
        }

        // Try to get from session
        $sessionId = Session::get('current_tenant_id');
        if ($sessionId !== null) {
            self::$tenantId = (int) $sessionId;
            return self::$tenantId;
        }

        // If tenancy is disabled, return null
        if (! self::enabled()) {
            return null;
        }

        // Try to resolve from user's first site membership
        $user = Auth::user();
        if ($user) {
            $membership = $user->siteMemberships()
                ->where('status', 'active')
                ->whereHas('site', function ($query) {
                    $query->where('status', 'active');
                })
                ->with('site.tenant')
                ->first();

            if ($membership && $membership->site?->tenant) {
                $tenant = $membership->site->tenant;
                self::$tenantId = (int) $tenant->id;
                self::$tenantKey = $tenant->key;
                Session::put('current_tenant_id', self::$tenantId);
                Session::put('current_tenant_key', self::$tenantKey);
                return self::$tenantId;
            }
        }

        return null;
    }

    public static function key(): ?string
    {
        if (self::$tenantKey !== null) {
            return self::$tenantKey;
        }

        // Try to get from session
        $sessionKey = Session::get('current_tenant_key');
        if ($sessionKey !== null) {
            self::$tenantKey = $sessionKey;
            return self::$tenantKey;
        }

        // Try to resolve from tenant model
        $tenant = self::get();
        if ($tenant) {
            self::$tenantKey = $tenant->key;
            return self::$tenantKey;
        }

        return null;
    }

    /**
     * Get the current tenant model.
     */
    public static function get(): ?Tenant
    {
        if (self::$tenant !== null) {
            return self::$tenant;
        }

        $tenantId = self::id();
        if ($tenantId === null) {
            return null;
        }

        self::$tenant = Tenant::query()->find($tenantId);
        return self::$tenant;
    }

    public static function enabled(): bool
    {
        return (bool) config('tenancy.enabled', false);
    }

    /**
     * Clear the current tenant context.
     */
    public static function clear(): void
    {
        self::$tenantId = null;
        self::$tenantKey = null;
        self::$tenant = null;
        Session::forget(['current_tenant_id', 'current_tenant_key']);
    }
}


