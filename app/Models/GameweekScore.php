<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameweekScore extends Model
{
    use HasFactory;

    protected $fillable = ['manager_id', 'gameweek', 'season_year', 'points'];

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    
}
