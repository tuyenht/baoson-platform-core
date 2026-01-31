<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::enabled()) {
            $this->applySpatieTeam(null);
            return $next($request);
        }

        $strategy = (string) config('tenancy.strategy', 'subdomain');
        $tenantKey = null;

        if ($strategy === 'header') {
            $header = (string) config('tenancy.header_name', 'X-Tenant');
            $tenantKey = trim((string) $request->headers->get($header, ''));
        } else {
            // subdomain strategy
            $host = (string) $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $candidate = (string) $parts[0];
                $reserved = (array) config('tenancy.reserved_subdomains', []);
                if ($candidate !== '' && ! in_array($candidate, $reserved, true)) {
                    $tenantKey = $candidate;
                }
            }
        }

        $tenantId = null;

        // Best-effort: if tenants table doesn't exist yet, keep single-tenant behavior.
        if ($tenantKey && Schema::hasTable('tenants')) {
            $tenant = Tenant::query()
                ->select(['id', 'key', 'is_active', 'status'])
                ->where('key', $tenantKey)
                ->first();

            // Check both is_active (legacy) and status (new)
            $isActive = $tenant && (
                ($tenant->is_active ?? false) ||
                ($tenant->status === 'active')
            );

            if ($isActive) {
                $tenantId = (int) $tenant->id;
            } else {
                Log::warning('Tenancy enabled but tenant not found or inactive.', [
                    'tenant_key' => $tenantKey,
                ]);
            }
        }

        // If no tenant from request, try to resolve from user session/membership
        if ($tenantId === null && $request->user()) {
            $tenantId = TenantContext::id(); // This will try session/user membership
        }

        TenantContext::set($tenantId, $tenantKey);
        $this->applySpatieTeam($tenantId);

        return $next($request);
    }

    private function applySpatieTeam(?int $tenantId): void
    {
        // Only meaningful when Spatie teams is enabled.
        if (! (bool) config('permission.teams', false)) {
            return;
        }

        try {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        } catch (\Throwable) {
            // Best-effort; don't break requests if spatie internals change.
        }
    }
}


