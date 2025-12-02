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
    

   protected static function boot()
    {
        parent::boot();
        
        // --- Create Hook: Generate unique slug if not present ---
        static::creating(function ($league) {
            if (empty($league->slug)) {
                $league->slug = self::generateUniqueSlug($league->name);
            }
        });

        // --- Update Hook: Re-generate unique slug if the name changes ---
        static::updating(function ($league) {
            // Only update the slug if the name has been changed
            if ($league->isDirty('name')) {
                $league->slug = self::generateUniqueSlug($league->name);
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Check if a record with the generated slug already exists
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }


     public function user()
    {
        return $this->belongsTo(User::class);
    }



    

   

  



    public function gameweekScores()
{
    return $this->hasManyThrough(
        GameweekScore::class,
        Manager::class,
        'league_id',
        'manager_id',
        'id',
        'id'
    );
}



}
