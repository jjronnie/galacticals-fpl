<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerPick extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'gameweek',
        'player_id',
        'position',
        'multiplier',
        'is_captain',
        'is_vice_captain',
        'event_points',
    ];

    protected function casts(): array
    {
        return [
            'is_captain' => 'boolean',
            'is_vice_captain' => 'boolean',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(FplPlayer::class, 'player_id');
    }
}
