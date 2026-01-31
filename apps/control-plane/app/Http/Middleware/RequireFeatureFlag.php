<?php

namespace App\Http\Middleware;

use App\Services\FeatureFlags\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireFeatureFlag
{
    /**
     * Require a feature flag to be enabled.
     * Usage: ->middleware('feature:admin.users')
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        if (! FeatureFlagService::isEnabled($flag, 'global')) {
            // Return 404 to hide the route (security through obscurity)
            abort(404);
        }

        return $next($request);
    }
}

