<?php

namespace App\Services\FeatureFlags;

use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

final class FeatureFlagService
{
    private const CACHE_KEY_PREFIX = 'feature_flags:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Check if a feature flag is enabled.
     */
    public static function isEnabled(string $key, ?string $scope = 'global'): bool
    {
        $cacheKey = self::CACHE_KEY_PREFIX.$scope.':'.$key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $scope) {
            return FeatureFlag::isEnabled($key, $scope);
        });
    }

    /**
     * Get all enabled feature flag keys for a scope.
     */
    public static function allEnabledKeys(?string $scope = 'global'): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX.'all:'.$scope;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($scope) {
            return FeatureFlag::enabledKeys($scope);
        });
    }

    /**
     * Clear cache for a specific flag or all flags.
     */
    public static function clearCache(?string $key = null, ?string $scope = 'global'): void
    {
        if ($key) {
            Cache::forget(self::CACHE_KEY_PREFIX.$scope.':'.$key);
        }
        Cache::forget(self::CACHE_KEY_PREFIX.'all:'.$scope);
    }

    /**
     * Warm cache for all flags in a scope.
     */
    public static function warmCache(?string $scope = 'global'): void
    {
        self::allEnabledKeys($scope);
    }
}

