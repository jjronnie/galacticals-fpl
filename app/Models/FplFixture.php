<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FplFixture extends Model
{
    protected $fillable = [
        'fpl_fixture_id',
        'event',
        'team_h',
        'team_a',
        'team_h_difficulty',
        'team_a_difficulty',
        'kickoff_time',
        'started',
        'finished',
        'finished_provisional',
        'team_h_score',
        'team_a_score',
        'minutes',
        'pulse_id',
        'stats',
    ];

    protected function casts(): array
    {
        return [
            'fpl_fixture_id' => 'integer',
            'event' => 'integer',
            'team_h' => 'integer',
            'team_a' => 'integer',
            'team_h_difficulty' => 'integer',
            'team_a_difficulty' => 'integer',
            'kickoff_time' => 'datetime',
            'started' => 'boolean',
            'finished' => 'boolean',
            'finished_provisional' => 'boolean',
            'team_h_score' => 'integer',
            'team_a_score' => 'integer',
            'minutes' => 'integer',
            'pulse_id' => 'integer',
            'stats' => 'array',
        ];
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(FplTeam::class, 'team_h');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(FplTeam::class, 'team_a');
    }

    public function isLive(): bool
    {
        return $this->started && ! $this->finished;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function isUpcoming(): bool
    {
        return ! $this->started;
    }

    public function status(): string
    {
        if ($this->finished) {
            return 'FT';
        }

        if ($this->started) {
            return 'LIVE';
        }

        return 'Upcoming';
    }

    public function homeDifficultyBadge(): string
    {
        if ($this->isFinished()) {
            return 'bg-gray-700 text-gray-300';
        }

        $h = $this->team_h_difficulty ?? 3;
        $a = $this->team_a_difficulty ?? 3;

        if ($h < $a) {
            return 'bg-green-600 text-white';
        }

        return 'bg-gray-600 text-gray-300';
    }

    public function awayDifficultyBadge(): string
    {
        if ($this->isFinished()) {
            return 'bg-gray-700 text-gray-300';
        }

        $h = $this->team_h_difficulty ?? 3;
        $a = $this->team_a_difficulty ?? 3;

        if ($a < $h) {
            return 'bg-green-600 text-white';
        }

        return 'bg-gray-600 text-gray-300';
    }
}
