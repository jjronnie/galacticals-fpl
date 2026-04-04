<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FplTeam extends Model
{
    use HasFactory;

    protected $table = 'fpl_teams';

    protected $fillable = [
        'id',
        'name',
        'short_name',
        'fpl_code',
        'code',
        'strength_overall',
    ];

    public $incrementing = false;

    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'fpl_code' => 'integer',
            'code' => 'integer',
            'strength_overall' => 'integer',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(FplPlayer::class, 'team_id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class, 'favourite_team_id');
    }

    public function badgeUrl(): ?string
    {
        if (! $this->fpl_code) {
            return null;
        }

        return "https://resources.premierleague.com/premierleague/badges/25/t{$this->fpl_code}.png";
    }
}
