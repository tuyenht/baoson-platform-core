<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\Eloquent\Model;

final class PepperedUserProvider extends EloquentUserProvider
{
    public function __construct(
        HasherContract $hasher,
        string $model,
        private readonly ?string $pepper,
        private readonly ?string $previousPepper
    ) {
        parent::__construct($hasher, $model);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = (string) ($credentials['password'] ?? '');

        if ($plain === '') {
            return false;
        }

        $globalPepper = $this->pepper ?: '';
        $globalPreviousPepper = $this->previousPepper ?: '';

        // Get user-specific pepper (based on user ID + email)
        $userPepper = $this->getUserSpecificPepper($user, $globalPepper);
        $userPreviousPepper = $globalPreviousPepper !== ''
            ? $this->getUserSpecificPepper($user, $globalPreviousPepper)
            : '';

        // 1) Try current user-specific pepper (or no pepper).
        $currentValue = $userPepper !== ''
            ? hash_hmac('sha256', $plain, $userPepper)
            : $plain;

        if ($this->hasher->check($currentValue, $user->getAuthPassword())) {
            return true;
        }

        // 2) Try previous user-specific pepper (rotation support).
        if ($userPreviousPepper === '' || $userPreviousPepper === $userPepper) {
            return false;
        }

        $previousValue = hash_hmac('sha256', $plain, $userPreviousPepper);
        $ok = $this->hasher->check($previousValue, $user->getAuthPassword());

        // 3) If previous pepper matched, opportunistically re-hash using current pepper.
        if ($ok && $userPepper !== '' && $user instanceof Model) {
            try {
                $user->forceFill(['password' => $plain])->save();
            } catch (\Throwable) {
                // Best-effort. Login should still succeed even if rehash fails.
            }
        }

        return $ok;
    }

    /**
     * Get user-specific pepper (same logic as User model).
     * This ensures each user has a unique pepper, preventing hash copying attacks.
     */
    private function getUserSpecificPepper(Authenticatable $user, string $globalPepper): string
    {
        if ($globalPepper === '') {
            return '';
        }

        // Get user email (primary identifier - always unique)
        $email = '';
        if ($user instanceof \App\Models\User) {
            $email = (string) ($user->email ?? '');
        } elseif (method_exists($user, 'getEmail')) {
            $email = (string) ($user->getEmail() ?? '');
        } elseif (isset($user->email)) {
            $email = (string) $user->email;
        }

        // Get user ID (secondary identifier)
        $userId = $user->getAuthIdentifier() ?? 0;

        // User-specific pepper: global pepper + email (primary) + ID (secondary)
        // Must match exactly with User model logic
        $userIdentifier = $email . '|' . $userId;
        
        return hash_hmac('sha256', $userIdentifier, $globalPepper);
    }
}


