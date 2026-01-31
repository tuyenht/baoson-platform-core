<?php

namespace App\Services\Workflow;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Generic content workflow service.
 * Handles state transitions with role-based authorization.
 *
 * States: draft, pending, published, archived
 */
final class ContentWorkflow
{
    /**
     * Valid states.
     */
    public const STATE_DRAFT = 'draft';
    public const STATE_PENDING = 'pending';
    public const STATE_PUBLISHED = 'published';
    public const STATE_ARCHIVED = 'archived';

    /**
     * Get all valid states.
     */
    public static function validStates(): array
    {
        return [
            self::STATE_DRAFT,
            self::STATE_PENDING,
            self::STATE_PUBLISHED,
            self::STATE_ARCHIVED,
        ];
    }

    /**
     * Check if a user can transition content from one state to another.
     *
     * @param User $user The user attempting the transition
     * @param Site|null $site The site context (required for site-scoped roles)
     * @param string $fromState Current state
     * @param string $toState Target state
     * @return bool
     */
    public static function canTransition(User $user, ?Site $site, string $fromState, string $toState): bool
    {
        // Validate states
        if (! in_array($fromState, self::validStates(), true) ||
            ! in_array($toState, self::validStates(), true)) {
            return false;
        }

        // Same state is always allowed (no-op)
        if ($fromState === $toState) {
            return true;
        }

        // Check transition rules
        return match ("{$fromState}->{$toState}") {
            // draft -> pending: any editor (siteEditor or higher at site scope)
            'draft->pending' => self::hasSiteRole($user, $site, ['siteEditor', 'siteAdmin', 'siteOwner']) ||
                               self::hasPlatformRole($user, ['saasAdmin', 'saasOwner']),

            // pending -> published: siteAdmin or higher
            'pending->published' => self::hasSiteRole($user, $site, ['siteAdmin', 'siteOwner']) ||
                                    self::hasPlatformRole($user, ['saasAdmin', 'saasOwner']),

            // published -> archived: siteOwner or saasAdmin
            'published->archived' => self::hasSiteRole($user, $site, ['siteOwner']) ||
                                     self::hasPlatformRole($user, ['saasAdmin', 'saasOwner']),

            // archived -> draft: siteAdmin
            'archived->draft' => self::hasSiteRole($user, $site, ['siteAdmin', 'siteOwner']) ||
                                 self::hasPlatformRole($user, ['saasAdmin', 'saasOwner']),

            // All other transitions are not allowed
            default => false,
        };
    }

    /**
     * Get allowed transitions for a user from a given state.
     *
     * @param User $user
     * @param Site|null $site
     * @param string $fromState
     * @return array<string> List of allowed target states
     */
    public static function getAllowedTransitions(User $user, ?Site $site, string $fromState): array
    {
        $allowed = [];

        foreach (self::validStates() as $toState) {
            if (self::canTransition($user, $site, $fromState, $toState)) {
                $allowed[] = $toState;
            }
        }

        return $allowed;
    }

    /**
     * Check if user has a site role (from site membership).
     */
    private static function hasSiteRole(User $user, ?Site $site, array $requiredRoles): bool
    {
        if (! $site) {
            return false;
        }

        $userRole = $user->getSiteRole($site);
        if (! $userRole) {
            return false;
        }

        return in_array($userRole, $requiredRoles, true);
    }

    /**
     * Check if user has a platform role (from Spatie roles).
     */
    private static function hasPlatformRole(User $user, array $requiredRoles): bool
    {
        $userRoles = $user->getRoleNames()->toArray();

        foreach ($requiredRoles as $requiredRole) {
            if (in_array($requiredRole, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate a state transition and throw exception if not allowed.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public static function authorizeTransition(User $user, ?Site $site, string $fromState, string $toState): void
    {
        if (! self::canTransition($user, $site, $fromState, $toState)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                "User cannot transition content from '{$fromState}' to '{$toState}'."
            );
        }
    }
}

