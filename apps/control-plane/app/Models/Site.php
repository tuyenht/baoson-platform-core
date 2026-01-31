<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'domain',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Get the tenant that owns the site.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the users that are members of this site.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'site_memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all memberships for this site.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(SiteMembership::class);
    }

    /**
     * Check if site is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

