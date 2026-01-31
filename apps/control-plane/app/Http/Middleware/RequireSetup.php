<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RequireSetup
{
    /**
     * Redirect to setup if no saasOwner exists.
     * Only applies to /bsadm routes (except /bsadm/setup itself).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if already on setup route
        if ($request->is('bsadm/setup*') || $request->is('*/bsadm/setup*')) {
            return $next($request);
        }

        // Skip if not in admin area
        if (! $request->is('bsadm*') && ! $request->is('*/bsadm*')) {
            return $next($request);
        }

        // Check if setup is required
        if (self::isSetupRequired()) {
            return redirect()->route('bsadm.setup');
        }

        return $next($request);
    }

    /**
     * Check if setup is required (no saasOwner exists).
     */
    public static function isSetupRequired(): bool
    {
        // If users table doesn't exist, setup is required
        if (! Schema::hasTable('users')) {
            return true;
        }

        // If no users exist, setup is required
        if (User::count() === 0) {
            return true;
        }

        // If no saasOwner role exists, setup is required
        $guard = (string) config('auth.defaults.guard', 'web');
        $saasOwnerRole = Role::where('name', 'saasOwner')->where('guard_name', $guard)->first();

        if (! $saasOwnerRole) {
            return true;
        }

        // If no user has saasOwner role, setup is required
        $saasOwnerCount = DB::table('model_has_roles')
            ->where('role_id', $saasOwnerRole->id)
            ->where('model_type', 'App\Models\User')
            ->count();

        return $saasOwnerCount === 0;
    }
}

