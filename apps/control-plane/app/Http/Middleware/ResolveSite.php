<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\SiteContext;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSite
{
    /**
     * Resolve the current site context for the request.
     * This runs after ResolveTenant middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only resolve site if we're in /bsadm and user is authenticated
        if ($request->is('bsadm*') || $request->is('*/bsadm*')) {
            $user = $request->user();
            $tenantId = TenantContext::id();

            if ($user && $tenantId) {
                // Try to get from session first
                $siteId = SiteContext::id();

                // If no site in context, try to resolve from user's first active membership
                if ($siteId === null) {
                    $membership = $user->siteMemberships()
                        ->whereHas('site', function ($query) use ($tenantId) {
                            $query->where('tenant_id', $tenantId)
                                ->where('status', 'active');
                        })
                        ->where('status', 'active')
                        ->first();

                    if ($membership) {
                        SiteContext::set((int) $membership->site_id, $membership->site);
                    }
                }
            }
        }

        return $next($request);
    }
}

