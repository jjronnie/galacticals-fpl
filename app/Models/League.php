<?php

namespace App\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{

     protected $fillable = [
        'user_id',
        'league_id',       // FPL league ID
        'name',
        'admin_name',
        'current_gameweek',
        'season',
    ];

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }
    



     public function user()
    {
        return $this->belongsTo(User::class);
    }



    

   

  

    public function gameweekScores()
    {
        return $this->hasManyThrough(GameweekScore::class, Manager::class);
    }


}
