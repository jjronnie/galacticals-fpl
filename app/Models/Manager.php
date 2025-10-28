<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manager extends Model
{
    use HasFactory;
    protected $fillable = [
        'league_id',
        'entry_id',        
        'player_name',
        'team_name',
        'rank',
        'total_points',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the gameweek scores for the manager.
     */
    public function scores()
    {
        return $this->hasMany(GameweekScore::class);
    }
}
