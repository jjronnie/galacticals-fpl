<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manager extends Model
{
    use HasFactory;
    protected $fillable = ['league_id', 'name', 'team_name', 'is_active'];

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
