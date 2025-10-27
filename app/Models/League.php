<?php

namespace App\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'description'];

      protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($league) {
            if (empty($league->slug)) {
                $league->slug = Str::slug($league->name . '-' . Str::random(6));
            }
        });
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function activeSeason()
    {
        return $this->hasOne(Season::class)->where('is_active', true);
    }


}
