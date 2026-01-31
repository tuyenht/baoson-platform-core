<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminMenuItem extends Model
{
    protected $fillable = [
        'key',
        'label',
        'icon',
        'route_name',
        'url',
        'parent_id',
        'sort_order',
        'required_permissions',
        'required_roles',
        'required_flags',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'required_permissions' => 'array',
            'required_roles' => 'array',
            'required_flags' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AdminMenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(AdminMenuItem::class, 'parent_id')->orderBy('sort_order');
    }
}

