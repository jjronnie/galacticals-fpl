<?php

namespace App\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class League extends Model
{

         use HasFactory;
    protected $fillable = [
    'user_id',
    'league_id',
    'name',
    'admin_name',
    'current_gameweek',
    'season',

    'sync_status',
    'sync_message',
    'total_managers',
    'synced_managers',
    'last_synced_at',
];

protected $casts = [
    'last_synced_at' => 'datetime',
];

public function isProcessing()
{
    return $this->sync_status === 'processing';
}

public function hasFailed()
{
    return $this->sync_status === 'failed';
}

public function getSyncProgress()
{
    if ($this->total_managers === 0) return 0;
    return round(($this->synced_managers / $this->total_managers) * 100);
}

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

              // Generate shortcode if not set
        if (empty($league->shortcode)) {
            $league->shortcode = self::generateUniqueShortcode();
        }
        
        });

       

      

        // --- Update Hook: Re-generate unique slug if the name changes ---
        // static::updating(function ($league) {
        //     // Only update the slug if the name has been changed
        //     if ($league->isDirty('name')) {
        //         $league->slug = self::generateUniqueSlug($league->name);
        //     }
        // });
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

    protected static function generateUniqueShortcode($length = 5)
{
    do {
        // uppercase + numbers gives cleaner short links
        $code = Str::upper(Str::random($length));
    } while (self::where('shortcode', $code)->exists());

    return $code;
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
