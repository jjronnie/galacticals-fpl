<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FplPlayer extends Model
{
    use HasFactory;

    protected $table = 'fpl_players';

    protected $fillable = [
        'id',
        'team_id',
        'first_name',
        'second_name',
        'web_name',
        'element_type',
        'now_cost',
        'total_points',
        'selected_by_percent',
        'form',
        'region',
    ];

    public $incrementing = false;

    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'selected_by_percent' => 'decimal:2',
            'form' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(FplTeam::class, 'team_id');
    }

    public function picks(): HasMany
    {
        return $this->hasMany(ManagerPick::class, 'player_id');
    }
}
