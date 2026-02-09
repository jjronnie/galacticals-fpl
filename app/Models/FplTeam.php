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
        'code',
        'strength_overall',
    ];

    public $incrementing = false;

    protected $keyType = 'int';

    public function players(): HasMany
    {
        return $this->hasMany(FplPlayer::class, 'team_id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class, 'favourite_team_id');
    }
}
