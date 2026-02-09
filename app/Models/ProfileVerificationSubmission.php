<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVerificationSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'entry_id',
        'team_name',
        'player_name',
        'screenshot_path',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
