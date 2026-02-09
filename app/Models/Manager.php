<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'user_id',
        'entry_id',
        'player_name',
        'player_first_name',
        'player_last_name',
        'region_name',
        'favourite_team_id',
        'team_name',
        'rank',
        'total_points',
        'claimed_at',
        'verified_at',
        'verified_by',
        'suspended_at',
        'notes',
        'sync_status',
        'sync_message',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function favouriteTeam(): BelongsTo
    {
        return $this->belongsTo(FplTeam::class, 'favourite_team_id');
    }

    public function gameweekScores(): HasMany
    {
        return $this->hasMany(GameweekScore::class);
    }

    public function latestGameweekScore(): HasOne
    {
        return $this->hasOne(GameweekScore::class)->latestOfMany('gameweek');
    }

    public function scores(): HasMany
    {
        return $this->gameweekScores();
    }

    public function picks(): HasMany
    {
        return $this->hasMany(ManagerPick::class);
    }

    public function chips(): HasMany
    {
        return $this->hasMany(ManagerChip::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(ClaimsComplaint::class);
    }

    public function getFullNameAttribute(): string
    {
        $fullName = trim(($this->player_first_name ?? '').' '.($this->player_last_name ?? ''));

        return $fullName !== '' ? $fullName : $this->player_name;
    }

    public function getNationalityAttribute(): string
    {
        return $this->region_name ?? 'Unknown';
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
