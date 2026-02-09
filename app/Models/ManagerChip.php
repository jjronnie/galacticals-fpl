<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerChip extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'gameweek',
        'chip_name',
        'points_before',
        'points_after',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }
}
