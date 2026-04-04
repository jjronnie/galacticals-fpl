<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FplSyncRun extends Model
{
    protected $fillable = [
        'event',
        'status',
        'meta',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'event' => 'integer',
            'meta' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
