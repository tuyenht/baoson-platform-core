<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteMembership extends Model
{
    protected $table = 'site_memberships';

    protected $fillable = [
        'site_id',
        'user_id',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Get the site that this membership belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that this membership belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if membership is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Valid site roles.
     */
    public static function validRoles(): array
    {
        return [
            'siteOwner',
            'siteAdmin',
            'siteEditor',
            'siteTranslator',
            'siteViewer',
        ];
    }
}

