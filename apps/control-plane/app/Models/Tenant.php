<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'key',
        'name',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'status' => 'string',
        ];
    }

    /**
     * Get all sites for this tenant.
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return ($this->status ?? ($this->is_active ? 'active' : 'suspended')) === 'active';
    }
}


