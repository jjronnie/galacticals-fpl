<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

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

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (League $league): void {
            if (empty($league->slug)) {
                $league->slug = self::generateUniqueSlug($league->name);
            }

            if (empty($league->shortcode)) {
                $league->shortcode = self::generateUniqueShortcode();
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    protected static function generateUniqueShortcode(int $length = 5): string
    {
        do {
            $code = Str::upper(Str::random($length));
        } while (self::where('shortcode', $code)->exists());

        return $code;
    }

    public function isProcessing(): bool
    {
        return $this->sync_status === 'processing';
    }

    public function hasFailed(): bool
    {
        return $this->sync_status === 'failed';
    }

    public function getSyncProgress(): int
    {
        if ($this->total_managers === 0) {
            return 0;
        }

        return (int) round(($this->synced_managers / $this->total_managers) * 100);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    public function gameweekScores(): HasManyThrough
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

    public function gameweekStandings(): HasMany
    {
        return $this->hasMany(LeagueGameweekStanding::class);
    }
}
