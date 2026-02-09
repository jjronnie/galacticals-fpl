<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameweekScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'gameweek',
        'season_year',
        'points',
        'total_points',
        'overall_rank',
        'bank',
        'value',
        'event_transfers',
        'event_transfers_cost',
        'points_on_bench',
        'autop_sub_points',
        'captain_points',
        'vice_captain_points',
        'best_pick_points',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
