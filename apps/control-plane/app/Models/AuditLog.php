<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event',
        'occurred_at',
        'actor_id',
        'actor_email',
        'ip',
        'user_agent',
        'method',
        'route',
        'path',
        'status_code',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}


