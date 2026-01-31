<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'enabled',
        'scope',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    /**
     * Check if a feature flag is enabled.
     */
    public static function isEnabled(string $key, ?string $scope = 'global'): bool
    {
        return static::query()
            ->where('key', $key)
            ->where('scope', $scope)
            ->where('enabled', true)
            ->exists();
    }

    /**
     * Get all enabled feature flag keys.
     */
    public static function enabledKeys(?string $scope = 'global'): array
    {
        return static::query()
            ->where('enabled', true)
            ->where('scope', $scope)
            ->pluck('key')
            ->toArray();
    }
}

