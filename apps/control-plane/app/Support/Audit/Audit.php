<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class Audit
{
    /**
     * Record an audit event. Best-effort: will never throw to avoid breaking auth flows.
     *
     * @param array<string, mixed> $meta
     */
    public static function record(string $event, array $meta = [], ?Request $request = null, ?int $statusCode = null): void
    {
        try {
            $req = $request;

            /** @var Request|null $current */
            $current = app()->bound('request') ? app('request') : null;
            if (! $req && $current instanceof Request) {
                $req = $current;
            }

            $user = Auth::user();

            AuditLog::query()->create([
                'event' => $event,
                'occurred_at' => now(),
                'actor_id' => $user?->id,
                'actor_email' => $user?->email,
                'ip' => $req?->ip(),
                'user_agent' => (string) ($req?->userAgent() ?: ''),
                'method' => $req?->method(),
                'route' => $req?->route()?->getName(),
                'path' => $req?->path(),
                'status_code' => $statusCode,
                'meta' => $meta ?: null,
            ]);
        } catch (\Throwable) {
            // Best-effort: never throw.
        }
    }

    /**
     * Log role assignment.
     */
    public static function roleAssigned(User $user, string $role, ?Request $request = null): void
    {
        self::record('rbac.role.assigned', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role' => $role,
        ], $request);
    }

    /**
     * Log role removal.
     */
    public static function roleRemoved(User $user, string $role, ?Request $request = null): void
    {
        self::record('rbac.role.removed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'role' => $role,
        ], $request);
    }

    /**
     * Log site membership creation.
     */
    public static function membershipCreated(User $user, Site $site, string $role, ?Request $request = null): void
    {
        self::record('site.membership.created', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'site_id' => $site->id,
            'site_key' => $site->key,
            'role' => $role,
        ], $request);
    }

    /**
     * Log site membership update.
     */
    public static function membershipUpdated(User $user, Site $site, string $oldRole, string $newRole, ?Request $request = null): void
    {
        self::record('site.membership.updated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'site_id' => $site->id,
            'site_key' => $site->key,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ], $request);
    }

    /**
     * Log site membership removal.
     */
    public static function membershipRemoved(User $user, Site $site, string $role, ?Request $request = null): void
    {
        self::record('site.membership.removed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'site_id' => $site->id,
            'site_key' => $site->key,
            'role' => $role,
        ], $request);
    }

    /**
     * Log workflow transition.
     */
    public static function workflowTransition(string $contentType, int $contentId, string $fromState, string $toState, ?Request $request = null): void
    {
        self::record('workflow.transition', [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'from_state' => $fromState,
            'to_state' => $toState,
        ], $request);
    }

    /**
     * Log feature flag toggle.
     */
    public static function featureFlagToggled(string $flagKey, bool $enabled, ?Request $request = null): void
    {
        self::record('feature_flag.toggled', [
            'flag_key' => $flagKey,
            'enabled' => $enabled,
        ], $request);
    }
}


