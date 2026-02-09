<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueGameweekStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'gameweek',
        'manager_id',
        'rank',
        'points',
        'total_points',
        'difference_to_average',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
