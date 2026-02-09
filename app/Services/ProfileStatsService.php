<?php

namespace App\Services;

use App\Models\GameweekScore;
use App\Models\LeagueGameweekStanding;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Models\ManagerPick;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProfileStatsService
{
    public function __construct(private readonly LeagueStatsService $leagueStatsService) {}

    public function getProfileStats(Manager $manager): array
    {
        $entryId = (int) $manager->entry_id;
        $cacheKey = 'profile_stats_entry_'.$entryId;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($manager, $entryId): array {
            $relatedManagerIds = Manager::query()
                ->where('entry_id', $entryId)
                ->pluck('id');

            $referenceManager = Manager::query()
                ->whereIn('id', $relatedManagerIds)
                ->whereNotNull('favourite_team_id')
                ->with('favouriteTeam')
                ->first() ?? $manager->load('favouriteTeam');

            $scores = GameweekScore::query()
                ->whereIn('manager_id', $relatedManagerIds)
                ->orderBy('gameweek')
                ->get()
                ->sortBy([['gameweek', 'asc'], ['updated_at', 'desc'], ['id', 'desc']])
                ->groupBy('gameweek')
                ->map(fn (Collection $rows) => $rows->first())
                ->values();

            $historyRows = $scores->map(fn ($score): array => [
                'gameweek' => (int) $score->gameweek,
                'points' => (int) $score->points,
                'total_points' => (int) ($score->total_points ?? 0),
                'overall_rank' => (int) ($score->overall_rank ?? 0),
            ])->values()->all();

            $picks = ManagerPick::query()
                ->whereIn('manager_id', $relatedManagerIds)
                ->with('player.team')
                ->get()
                ->unique(fn ($pick): string => $pick->gameweek.'-'.$pick->player_id)
                ->values();

            $chips = ManagerChip::query()
                ->whereIn('manager_id', $relatedManagerIds)
                ->orderBy('gameweek')
                ->get()
                ->unique(fn ($chip): string => $chip->gameweek.'-'.$chip->chip_name)
                ->values();

            $picksByGameweek = $picks->groupBy('gameweek');

            $summary = $this->summaryStats($referenceManager, $scores);
            $trajectory = $this->pointsTrajectory($referenceManager, $scores);
            $captaincy = $this->captaincyPerformance($picksByGameweek, $scores);
            $transfers = $this->transferEfficiency($scores);
            $valueEvolution = $this->squadValueEvolution($scores);
            $playerContribution = $this->playerContribution($picks);
            $favouriteClubBias = $this->favouriteClubBias($referenceManager, $picks);
            $chipUsage = $this->chipUsage($chips, $scores);
            $autoSubImpact = (int) $scores->sum('autop_sub_points');

            $awards = $this->buildAwards($referenceManager, $scores, $captaincy, $transfers);

            return [
                'manager' => $referenceManager,
                'summary' => $summary,
                'trajectory' => $trajectory,
                'captaincy' => $captaincy,
                'transfers' => $transfers,
                'value_evolution' => $valueEvolution,
                'player_contribution' => $playerContribution,
                'favourite_club_bias' => $favouriteClubBias,
                'chip_usage' => $chipUsage,
                'auto_sub_impact' => $autoSubImpact,
                'awards' => $awards,
                'history_rows' => $historyRows,
            ];
        });
    }

    private function summaryStats(Manager $manager, Collection $scores): array
    {
        $latestScore = $scores->sortBy('gameweek')->last();

        return [
            'total_points' => (int) ($manager->total_points ?? ($latestScore?->total_points ?? 0)),
            'overall_rank' => (int) ($latestScore?->overall_rank ?? 0),
            'favourite_club' => $manager->favouriteTeam?->name ?? 'Not set',
            'transfers_made' => (int) $scores->sum('event_transfers'),
            'transfer_hits' => (int) $scores->sum('event_transfers_cost'),
            'bench_points' => (int) $scores->sum('points_on_bench'),
            'auto_sub_points' => (int) $scores->sum('autop_sub_points'),
            'country' => $manager->nationality,
        ];
    }

    private function pointsTrajectory(Manager $manager, Collection $scores): array
    {
        $labels = $scores->pluck('gameweek')->map(fn ($gameweek): int => (int) $gameweek)->values();
        $managerPoints = $scores->pluck('points')->map(fn ($points): int => (int) $points)->values();

        $bootstrap = Cache::get('fpl.bootstrap-static.latest', []);

        $averageByGameweek = collect($bootstrap['events'] ?? [])->mapWithKeys(function (array $event): array {
            return [(int) $event['id'] => (int) ($event['average_entry_score'] ?? 0)];
        });

        $averageLine = $labels->map(fn (int $gameweek): int => (int) ($averageByGameweek[$gameweek] ?? 0))->values();

        $bestLine = collect();

        if ($manager->league_id !== null) {
            $bestByGameweek = LeagueGameweekStanding::query()
                ->where('league_id', $manager->league_id)
                ->whereIn('gameweek', $labels->all())
                ->selectRaw('gameweek, MAX(points) as best_points')
                ->groupBy('gameweek')
                ->pluck('best_points', 'gameweek');

            $bestLine = $labels->map(fn (int $gameweek): int => (int) ($bestByGameweek[$gameweek] ?? 0))->values();
        }

        return [
            'labels' => $labels->all(),
            'manager_points' => $managerPoints->all(),
            'league_average' => $averageLine->all(),
            'league_best' => $bestLine->all(),
        ];
    }

    private function captaincyPerformance(Collection $picksByGameweek, Collection $scores): array
    {
        $rows = [];
        $totalCaptainPoints = 0;
        $totalWhatIfPoints = 0;

        foreach ($picksByGameweek as $gameweek => $picks) {
            $captain = $picks->firstWhere('is_captain', true);

            $captainPoints = $captain !== null
                ? (int) (($captain->event_points ?? 0) * max((int) ($captain->multiplier ?? 1), 1))
                : 0;

            $highestScorerPoints = (int) $picks->max('event_points');
            $whatIfPoints = $highestScorerPoints * 2;

            $rows[] = [
                'gameweek' => (int) $gameweek,
                'captain' => $captain?->player?->web_name ?? 'N/A',
                'captain_points' => $captainPoints,
                'what_if_points' => $whatIfPoints,
                'difference' => $whatIfPoints - $captainPoints,
            ];

            $totalCaptainPoints += $captainPoints;
            $totalWhatIfPoints += $whatIfPoints;
        }

        if ($rows === []) {
            $rows = $scores
                ->map(function ($score): array {
                    $captainPoints = (int) ($score->captain_points ?? 0);
                    $whatIfPoints = (int) (($score->best_pick_points ?? 0) * 2);

                    return [
                        'gameweek' => (int) $score->gameweek,
                        'captain' => 'N/A',
                        'captain_points' => $captainPoints,
                        'what_if_points' => $whatIfPoints,
                        'difference' => $whatIfPoints - $captainPoints,
                    ];
                })
                ->sortBy('gameweek')
                ->values()
                ->all();

            $totalCaptainPoints = collect($rows)->sum('captain_points');
            $totalWhatIfPoints = collect($rows)->sum('what_if_points');
        }

        return [
            'rows' => collect($rows)->sortBy('gameweek')->values()->all(),
            'total_captain_points' => $totalCaptainPoints,
            'total_what_if_points' => $totalWhatIfPoints,
            'missed_points' => $totalWhatIfPoints - $totalCaptainPoints,
        ];
    }

    private function transferEfficiency(Collection $scores): array
    {
        $rows = $scores->map(function ($score): array {
            return [
                'gameweek' => (int) $score->gameweek,
                'transfers' => (int) $score->event_transfers,
                'hit_cost' => (int) $score->event_transfers_cost,
                'net_points' => (int) $score->points - (int) $score->event_transfers_cost,
            ];
        })->values();

        return [
            'rows' => $rows->all(),
            'transfers_total' => (int) $scores->sum('event_transfers'),
            'hits_total' => (int) $scores->sum('event_transfers_cost'),
            'net_points_after_hits' => (int) $rows->sum('net_points'),
        ];
    }

    private function squadValueEvolution(Collection $scores): array
    {
        return [
            'labels' => $scores->pluck('gameweek')->map(fn ($gameweek): int => (int) $gameweek)->values()->all(),
            'values' => $scores->pluck('value')->map(function ($value): float {
                return round(((int) $value) / 10, 1);
            })->values()->all(),
        ];
    }

    private function playerContribution(Collection $picks): array
    {
        return $picks
            ->groupBy('player_id')
            ->map(function (Collection $rows): array {
                $first = $rows->first();
                $points = $rows->sum(fn ($pick): int => (int) (($pick->event_points ?? 0) * max((int) $pick->multiplier, 1)));

                return [
                    'player' => $first?->player?->web_name ?? 'Unknown',
                    'team' => $first?->player?->team?->short_name ?? '-',
                    'points' => $points,
                ];
            })
            ->sortByDesc('points')
            ->take(10)
            ->values()
            ->all();
    }

    private function favouriteClubBias(Manager $manager, Collection $picks): array
    {
        if ($manager->favourite_team_id === null) {
            return [
                'team' => null,
                'points' => 0,
                'percent' => 0,
            ];
        }

        $totalContributed = (int) $picks->sum(fn ($pick): int => (int) (($pick->event_points ?? 0) * max((int) $pick->multiplier, 1)));

        $favouritePoints = (int) $picks
            ->filter(fn ($pick): bool => (int) ($pick->player?->team_id ?? 0) === (int) $manager->favourite_team_id)
            ->sum(fn ($pick): int => (int) (($pick->event_points ?? 0) * max((int) $pick->multiplier, 1)));

        return [
            'team' => $manager->favouriteTeam?->name,
            'points' => $favouritePoints,
            'percent' => $totalContributed > 0
                ? round(($favouritePoints / $totalContributed) * 100, 2)
                : 0,
        ];
    }

    private function chipUsage(Collection $chips, Collection $scores): array
    {
        $rows = $chips->map(function ($chip) use ($scores): array {
            $pointsAfter = $chip->points_after;
            $pointsBefore = $chip->points_before;

            if ($pointsAfter === null || $pointsBefore === null) {
                $score = $scores->firstWhere('gameweek', $chip->gameweek);
                $pointsAfter = $score?->total_points;
                $pointsBefore = $scores
                    ->where('gameweek', '<', $chip->gameweek)
                    ->sortByDesc('gameweek')
                    ->first()?->total_points;
            }

            return [
                'gameweek' => (int) $chip->gameweek,
                'chip' => $this->formatChipName((string) $chip->chip_name),
                'points_before' => (int) ($pointsBefore ?? 0),
                'points_after' => (int) ($pointsAfter ?? 0),
                'points_gained' => (int) (($pointsAfter ?? 0) - ($pointsBefore ?? 0)),
            ];
        })->values();

        $effectiveness = $rows->groupBy('chip')->map(fn (Collection $chipRows): float => round($chipRows->avg('points_gained'), 2));

        return [
            'rows' => $rows->all(),
            'most_effective' => $effectiveness->isNotEmpty() ? $effectiveness->sortDesc()->keys()->first() : null,
            'least_used' => $rows->isNotEmpty()
                ? $rows->groupBy('chip')->sortBy(fn (Collection $chipRows): int => $chipRows->count())->keys()->first()
                : null,
            'by_chip_points' => $effectiveness->toArray(),
        ];
    }

    private function buildAwards(Manager $manager, Collection $scores, array $captaincy, array $transfers): array
    {
        $awards = [];

        if ($scores->contains(fn ($score): bool => $score->points >= 100)) {
            $awards[] = '100 King Award';
        }

        if ($captaincy['total_captain_points'] >= 200) {
            $awards[] = 'Captaincy Legend Award';
        }

        if ($scores->sum('event_transfers') > 0 && $transfers['net_points_after_hits'] > 0) {
            $awards[] = 'Transfer Guru Award';
        }

        if ($manager->league_id !== null) {
            $leagueStats = $this->leagueStatsService->getLeagueStats($manager->league);
            $managerName = $manager->player_name;

            if (array_key_exists($managerName, $leagueStats['stats']['hall_of_shame'] ?? [])) {
                $awards[] = 'Hall of Shame Award';
            }

            if (in_array($managerName, $leagueStats['stats']['wooden_spoon_contenders'] ?? [], true)) {
                $awards[] = 'Wooden Spoon Award';
            }

            $hundredPlusRecords = $leagueStats['stats']['hundred_plus_league'] ?? [];
            if (collect($hundredPlusRecords)->contains(fn ($record): bool => str_contains($record, $managerName))) {
                $awards[] = 'Hundred Club Award';
            }
        }

        return array_values(array_unique($awards));
    }

    private function formatChipName(string $chipName): string
    {
        $upper = strtoupper($chipName);

        return $upper === '3XC' ? 'Tripple Captain' : $upper;
    }
}
