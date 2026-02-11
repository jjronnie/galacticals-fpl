<?php

namespace App\Services;

use App\Helpers\TeamColorHelper;
use App\Models\FplPlayer;
use App\Models\GameweekScore;
use App\Models\League;
use App\Models\LeagueGameweekStanding;
use App\Models\ManagerChip;
use App\Models\ManagerPick;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LeagueStatsService
{
    /**
     * @var array<int, array{name: string, defenders: int, midfielders: int, forwards: int}>
     */
    private const TEAM_OF_WEEK_FORMATIONS = [
        ['name' => '4-2-3-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
        ['name' => '4-3-3', 'defenders' => 4, 'midfielders' => 3, 'forwards' => 3],
        ['name' => '3-4-3', 'defenders' => 3, 'midfielders' => 4, 'forwards' => 3],
        ['name' => '3-5-2', 'defenders' => 3, 'midfielders' => 5, 'forwards' => 2],
        ['name' => '4-4-2', 'defenders' => 4, 'midfielders' => 4, 'forwards' => 2],
        ['name' => '4-1-4-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
        ['name' => '5-3-2', 'defenders' => 5, 'midfielders' => 3, 'forwards' => 2],
        ['name' => '4-5-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
    ];

    public function getLeagueStats(League $league): array
    {
        $cacheKey = $this->getCacheKey($league->id);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($league): array {
            $managers = $league->managers()->with('favouriteTeam')->get();

            if ($managers->isEmpty()) {
                return [
                    'isEmpty' => true,
                    'standings' => collect(),
                    'gwPerformance' => collect(),
                    'teamOfWeekRows' => [],
                    'stats' => $this->getEmptyStats(),
                ];
            }

            $managerIds = $managers->pluck('id')->all();
            $seasonYear = (int) ($league->season ?? now()->year);

            $allScores = GameweekScore::query()
                ->whereIn('manager_id', $managerIds)
                ->where('season_year', $seasonYear)
                ->with('manager')
                ->orderBy('gameweek')
                ->get();

            if ($allScores->isEmpty()) {
                return [
                    'isEmpty' => true,
                    'standings' => $managers->sortByDesc('total_points')->values(),
                    'gwPerformance' => collect(),
                    'teamOfWeekRows' => [],
                    'stats' => $this->getEmptyStats(),
                ];
            }

            $standings = $managers->map(function ($manager): array {
                return [
                    'name' => $manager->player_name,
                    'team' => $manager->team_name,
                    'total_points' => $manager->total_points,
                ];
            })->sortByDesc('total_points')->values();

            $topManagers = $standings->take(5);
            $relegationManagers = $standings->slice(-3)->values();

            $leagueZones = [
                'champions_league' => $topManagers->take(4)->pluck('name')->all(),
                'europa_league' => $topManagers->slice(4, 1)->pluck('name')->all(),
                'relegation_zone' => $relegationManagers->pluck('name')->all(),
            ];

            $gameweeks = $allScores->groupBy('gameweek');
            $managerTotalPoints = $managers->pluck('id', 'player_name')->map(fn () => 0)->toArray();

            $currentTopStreak = 0;
            $currentTopManager = null;
            $longestTopStreak = 0;
            $longestStreakManager = null;
            $longestStreakStartGW = null;
            $streakStartGW = null;

            $theBlowout = ['difference' => 0, 'gw' => null];
            $gwPerformance = [];
            $managerLeads = [];
            $managerLasts = [];
            $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
            $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

            foreach ($gameweeks as $gw => $scores) {
                if ($scores->isEmpty() || (int) $gw === 0) {
                    continue;
                }

                $bestScore = (int) $scores->max('points');
                $worstScore = (int) $scores->min('points');
                $scoreDifference = $bestScore - $worstScore;

                $bestManagers = $scores->where('points', $bestScore)->pluck('manager.player_name')->unique()->values()->all();
                $worstManagers = $scores->where('points', $worstScore)->pluck('manager.player_name')->unique()->values()->all();
                $bestManagersMeta = $scores
                    ->where('points', $bestScore)
                    ->filter(fn ($score): bool => $score->manager !== null)
                    ->map(fn ($score): array => [
                        'name' => $score->manager->player_name,
                        'team_name' => $score->manager->team_name,
                        'entry_id' => $score->manager->entry_id,
                    ])
                    ->unique('entry_id')
                    ->values()
                    ->all();

                $worstManagersMeta = $scores
                    ->where('points', $worstScore)
                    ->filter(fn ($score): bool => $score->manager !== null)
                    ->map(fn ($score): array => [
                        'name' => $score->manager->player_name,
                        'team_name' => $score->manager->team_name,
                        'entry_id' => $score->manager->entry_id,
                    ])
                    ->unique('entry_id')
                    ->values()
                    ->all();

                if ($scoreDifference > $theBlowout['difference']) {
                    $theBlowout = [
                        'difference' => $scoreDifference,
                        'gw' => (int) $gw,
                        'highest_scorer' => $bestManagers[0] ?? null,
                        'lowest_scorer' => $worstManagers[0] ?? null,
                        'highest_points' => $bestScore,
                        'lowest_points' => $worstScore,
                    ];
                }

                $gwPerformance[] = [
                    'gameweek' => (int) $gw,
                    'best_managers' => $bestManagers,
                    'best_managers_meta' => $bestManagersMeta,
                    'best_points' => $bestScore,
                    'worst_managers' => $worstManagers,
                    'worst_managers_meta' => $worstManagersMeta,
                    'worst_points' => $worstScore,
                ];

                foreach ($bestManagers as $managerName) {
                    $managerLeads[$managerName] = ($managerLeads[$managerName] ?? 0) + 1;
                }

                foreach ($worstManagers as $managerName) {
                    $managerLasts[$managerName] = ($managerLasts[$managerName] ?? 0) + 1;
                }

                if ($bestScore > $highestGwScore['points']) {
                    $highestGwScore = [
                        'points' => $bestScore,
                        'manager' => $bestManagers[0] ?? null,
                        'gw' => (int) $gw,
                    ];
                }

                if ($worstScore < $lowestGwScore['points']) {
                    $lowestGwScore = [
                        'points' => $worstScore,
                        'manager' => $worstManagers[0] ?? null,
                        'gw' => (int) $gw,
                    ];
                }

                $scoresByManager = $scores->keyBy('manager_id');
                $currentStandings = [];

                foreach ($managers as $manager) {
                    $score = $scoresByManager->get($manager->id);

                    if ($score !== null) {
                        $managerTotalPoints[$manager->player_name] += (int) $score->points;
                    }

                    $currentStandings[] = [
                        'name' => $manager->player_name,
                        'total_points' => $managerTotalPoints[$manager->player_name],
                    ];
                }

                usort($currentStandings, fn (array $a, array $b): int => $b['total_points'] <=> $a['total_points']);

                $leaderPoints = $currentStandings[0]['total_points'];
                $leaderNames = array_column(
                    array_filter($currentStandings, fn (array $manager): bool => $manager['total_points'] === $leaderPoints),
                    'name'
                );

                if (count($leaderNames) === 1 && $leaderNames[0] === $currentTopManager) {
                    $currentTopStreak++;
                } else {
                    if ($currentTopStreak > $longestTopStreak) {
                        $longestTopStreak = $currentTopStreak;
                        $longestStreakManager = $currentTopManager;
                        $longestStreakStartGW = $streakStartGW;
                    }

                    if (count($leaderNames) === 1) {
                        $currentTopManager = $leaderNames[0];
                        $currentTopStreak = 1;
                        $streakStartGW = (int) $gw;
                    } else {
                        $currentTopManager = null;
                        $currentTopStreak = 0;
                        $streakStartGW = null;
                    }
                }
            }

            if ($currentTopStreak > $longestTopStreak) {
                $longestTopStreak = $currentTopStreak;
                $longestStreakManager = $currentTopManager;
                $longestStreakStartGW = $streakStartGW;
            }

            $maxLeadCount = collect($managerLeads)->max();
            $mostGWLeads = collect($managerLeads)
                ->filter(fn (int $count): bool => $count === $maxLeadCount && $maxLeadCount > 0)
                ->sortDesc()
                ->toArray();

            $maxLastCount = collect($managerLasts)->max();
            $mostGWLasts = collect($managerLasts)
                ->filter(fn (int $count): bool => $count === $maxLastCount && $maxLastCount > 0)
                ->sortDesc()
                ->toArray();

            $allManagerNames = $managers->pluck('player_name')->all();
            $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

            $countryDistribution = $managers
                ->groupBy(fn ($manager) => $manager->region_name ?: 'Unknown')
                ->map(fn (Collection $group): int => $group->count())
                ->sortDesc()
                ->take(8)
                ->toArray();

            $favouriteTeamTotals = $managers
                ->filter(fn ($manager): bool => $manager->favourite_team_id !== null)
                ->groupBy(fn ($manager) => $manager->favouriteTeam?->name ?: 'Unknown')
                ->map(fn (Collection $group): int => $group->count())
                ->filter(fn (int $count): bool => $count > 0)
                ->sortDesc()
                ->toArray();

            $chipStats = ManagerChip::query()
                ->whereHas('manager', fn ($query) => $query->where('league_id', $league->id))
                ->get();

            $chipUsage = $chipStats->groupBy('chip_name')->map(fn (Collection $rows): int => $rows->count());
            $chipEffectiveness = $chipStats
                ->groupBy('chip_name')
                ->map(function (Collection $rows): float {
                    return round($rows->avg(fn ($chip): int => (int) (($chip->points_after ?? 0) - ($chip->points_before ?? 0))), 2);
                });

            $teamOfWeekRows = $this->buildTeamOfWeekRows($managerIds, $gameweeks->keys()->map(fn ($gw): int => (int) $gw)->all());

            $leastUsedChip = $chipUsage->isNotEmpty()
                ? $this->formatChipName((string) $chipUsage->sort()->keys()->first())
                : null;
            $mostUsedChip = $chipUsage->isNotEmpty()
                ? $this->formatChipName((string) $chipUsage->sortDesc()->keys()->first())
                : null;
            $mostEffectiveChip = $chipEffectiveness->isNotEmpty()
                ? $this->formatChipName((string) $chipEffectiveness->sortDesc()->keys()->first())
                : null;
            $pointsGainedByChip = $chipEffectiveness
                ->mapWithKeys(fn (float $value, string $chip): array => [$this->formatChipName($chip) => $value])
                ->toArray();

            return [
                'isEmpty' => false,
                'standings' => $standings,
                'gwPerformance' => collect($gwPerformance),
                'teamOfWeekRows' => $teamOfWeekRows,
                'stats' => [
                    'most_gw_leads' => $mostGWLeads,
                    'most_gw_last' => $mostGWLasts,
                    'highest_gw_score' => $highestGwScore,
                    'lowest_gw_score' => $lowestGwScore,
                    'longest_top_streak' => [
                        'manager' => $longestStreakManager,
                        'length' => $longestTopStreak,
                        'start_gw' => $longestStreakStartGW,
                        'end_gw' => $longestTopStreak > 0 ? ($longestStreakStartGW + $longestTopStreak - 1) : null,
                    ],
                    'the_blowout' => $theBlowout,
                    'wooden_spoon_contenders' => $relegationManagers->pluck('name')->all(),
                    'league_zones' => $leagueZones,
                    'mediocres' => array_values(array_diff($allManagerNames, $bestOrWorstNames)),
                    'men_standing' => array_values(array_diff($allManagerNames, array_keys($managerLasts))),
                    'never_best_in_gw' => array_values(array_diff($allManagerNames, array_keys($managerLeads))),
                    'hall_of_shame' => collect($managerLasts)->filter(fn (int $count): bool => $count >= 3)->sortDesc()->toArray(),
                    'hundred_plus_league' => $allScores
                        ->where('points', '>=', 100)
                        ->map(fn ($score): string => $score->manager->player_name.' ('.$score->points.' pts in GW '.$score->gameweek.')')
                        ->unique()
                        ->values()
                        ->all(),
                    'country_distribution' => $countryDistribution,
                    'least_used_chip' => $leastUsedChip,
                    'most_used_chip' => $mostUsedChip,
                    'most_effective_chip' => $mostEffectiveChip,
                    'points_gained_by_chip' => $pointsGainedByChip,
                    'favourite_team_totals' => $favouriteTeamTotals,
                ],
            ];
        });
    }

    public function getGameweekStandings(League $league, int $gameweek): Collection
    {
        $cacheKey = "league_gameweek_standings_{$league->id}_{$gameweek}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($league, $gameweek): Collection {
            return LeagueGameweekStanding::query()
                ->where('league_id', $league->id)
                ->where('gameweek', $gameweek)
                ->with('manager')
                ->orderBy('rank')
                ->get();
        });
    }

    /**
     * @return array<int>
     */
    public function getAvailableGameweeks(League $league): array
    {
        $cacheKey = 'league_available_gameweeks_'.$league->id;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($league): array {
            $gameweeks = LeagueGameweekStanding::query()
                ->where('league_id', $league->id)
                ->select('gameweek')
                ->distinct()
                ->orderBy('gameweek')
                ->pluck('gameweek')
                ->map(fn ($gameweek): int => (int) $gameweek)
                ->all();

            if ($gameweeks !== []) {
                return $gameweeks;
            }

            return GameweekScore::query()
                ->whereHas('manager', fn ($query) => $query->where('league_id', $league->id))
                ->select('gameweek')
                ->distinct()
                ->orderBy('gameweek')
                ->pluck('gameweek')
                ->map(fn ($gameweek): int => (int) $gameweek)
                ->all();
        });
    }

    public function getOwnershipAndCaptaincyTrends(League $league, int $gameweek): array
    {
        $cacheKey = "league_trends_{$league->id}_{$gameweek}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($league, $gameweek): array {
            $query = ManagerPick::query()
                ->where('gameweek', $gameweek)
                ->whereHas('manager', fn ($builder) => $builder->where('league_id', $league->id));

            $managerCount = max(1, $league->managers()->count());

            $mostOwned = (clone $query)
                ->selectRaw('player_id, COUNT(DISTINCT manager_id) as managers_count')
                ->with('player')
                ->groupBy('player_id')
                ->orderByDesc('managers_count')
                ->limit(5)
                ->get()
                ->map(function ($row) use ($managerCount): array {
                    return [
                        'player' => $row->player?->web_name ?? 'Unknown',
                        'count' => (int) $row->managers_count,
                        'percent' => round(((int) $row->managers_count / $managerCount) * 100, 2),
                    ];
                })
                ->all();

            $mostCaptained = (clone $query)
                ->where('is_captain', true)
                ->selectRaw('player_id, COUNT(*) as captain_count')
                ->with('player')
                ->groupBy('player_id')
                ->orderByDesc('captain_count')
                ->limit(5)
                ->get()
                ->map(fn ($row): array => [
                    'player' => $row->player?->web_name ?? 'Unknown',
                    'count' => (int) $row->captain_count,
                ])
                ->all();

            $mostViceCaptained = (clone $query)
                ->where('is_vice_captain', true)
                ->selectRaw('player_id, COUNT(*) as vice_count')
                ->with('player')
                ->groupBy('player_id')
                ->orderByDesc('vice_count')
                ->limit(5)
                ->get()
                ->map(fn ($row): array => [
                    'player' => $row->player?->web_name ?? 'Unknown',
                    'count' => (int) $row->vice_count,
                ])
                ->all();

            $differentials = (clone $query)
                ->selectRaw('player_id, COUNT(DISTINCT manager_id) as managers_count, AVG(event_points) as average_points')
                ->with('player')
                ->groupBy('player_id')
                ->havingRaw('COUNT(DISTINCT manager_id) / ? < 0.10', [$managerCount])
                ->orderByDesc('average_points')
                ->limit(5)
                ->get()
                ->map(fn ($row): array => [
                    'player' => $row->player?->web_name ?? 'Unknown',
                    'ownership_percent' => round(((int) $row->managers_count / $managerCount) * 100, 2),
                    'average_points' => round((float) $row->average_points, 2),
                ])
                ->all();

            $previousGameweek = ManagerPick::query()
                ->whereHas('manager', fn ($builder) => $builder->where('league_id', $league->id))
                ->where('gameweek', '<', $gameweek)
                ->max('gameweek');

            $mostTransferredIn = [];
            $mostTransferredOut = [];

            if ($previousGameweek !== null) {
                $managerIds = $league->managers()->pluck('id')->all();

                $currentByManager = ManagerPick::query()
                    ->whereIn('manager_id', $managerIds)
                    ->where('gameweek', $gameweek)
                    ->get(['manager_id', 'player_id'])
                    ->groupBy('manager_id')
                    ->map(fn (Collection $rows): array => $rows->pluck('player_id')->all());

                $previousByManager = ManagerPick::query()
                    ->whereIn('manager_id', $managerIds)
                    ->where('gameweek', $previousGameweek)
                    ->get(['manager_id', 'player_id'])
                    ->groupBy('manager_id')
                    ->map(fn (Collection $rows): array => $rows->pluck('player_id')->all());

                $transferInCounts = [];
                $transferOutCounts = [];

                foreach ($currentByManager as $managerId => $currentPlayers) {
                    $previousPlayers = $previousByManager[$managerId] ?? [];

                    foreach (array_diff($currentPlayers, $previousPlayers) as $playerId) {
                        $transferInCounts[$playerId] = ($transferInCounts[$playerId] ?? 0) + 1;
                    }

                    foreach (array_diff($previousPlayers, $currentPlayers) as $playerId) {
                        $transferOutCounts[$playerId] = ($transferOutCounts[$playerId] ?? 0) + 1;
                    }
                }

                $mostTransferredIn = $this->mapTransferCountsToPlayers($transferInCounts);
                $mostTransferredOut = $this->mapTransferCountsToPlayers($transferOutCounts);
            }

            $chipsPlayed = ManagerChip::query()
                ->where('gameweek', $gameweek)
                ->whereHas('manager', fn ($builder) => $builder->where('league_id', $league->id))
                ->get()
                ->groupBy('chip_name')
                ->map(fn (Collection $chips): int => $chips->count())
                ->filter(fn (int $count): bool => $count > 0)
                ->mapWithKeys(fn (int $count, string $chip): array => [
                    $this->formatChipName($chip) => $count,
                ])
                ->toArray();

            return [
                'most_owned' => $mostOwned,
                'most_captained' => $mostCaptained,
                'most_vice_captained' => $mostViceCaptained,
                'differentials' => $differentials,
                'most_transferred_in' => $mostTransferredIn,
                'most_transferred_out' => $mostTransferredOut,
                'chips_played' => $chipsPlayed,
            ];
        });
    }

    public function flushLeagueStats(League $league): void
    {
        Cache::forget($this->getCacheKey($league->id));
        Cache::forget('league_available_gameweeks_'.$league->id);

        $gameweeks = $this->getAvailableGameweeks($league);

        foreach ($gameweeks as $gameweek) {
            Cache::forget("league_gameweek_standings_{$league->id}_{$gameweek}");
            Cache::forget("league_trends_{$league->id}_{$gameweek}");
        }
    }

    private function getCacheKey(int $leagueId): string
    {
        return 'league_stats_'.$leagueId;
    }

    private function getEmptyStats(): array
    {
        return [
            'most_gw_leads' => [],
            'most_gw_last' => [],
            'highest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => null],
            'lowest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => null],
            'longest_top_streak' => ['manager' => null, 'length' => 0, 'start_gw' => null, 'end_gw' => null],
            'the_blowout' => ['difference' => 0, 'gw' => null],
            'wooden_spoon_contenders' => [],
            'league_zones' => [
                'champions_league' => [],
                'europa_league' => [],
                'relegation_zone' => [],
            ],
            'mediocres' => [],
            'men_standing' => [],
            'hall_of_shame' => [],
            'never_best_in_gw' => [],
            'hundred_plus_league' => [],
            'country_distribution' => [],
            'least_used_chip' => null,
            'most_used_chip' => null,
            'most_effective_chip' => null,
            'points_gained_by_chip' => [],
            'favourite_team_totals' => [],
        ];
    }

    /**
     * @param  array<int, int>  $counts
     * @return array<int, array{player:string,count:int}>
     */
    private function mapTransferCountsToPlayers(array $counts): array
    {
        if ($counts === []) {
            return [];
        }

        arsort($counts);
        $topCounts = array_slice($counts, 0, 5, true);

        $players = FplPlayer::query()
            ->whereIn('id', array_keys($topCounts))
            ->pluck('web_name', 'id')
            ->toArray();

        return collect($topCounts)->map(function (int $count, int $playerId) use ($players): array {
            return [
                'player' => $players[$playerId] ?? 'Unknown',
                'count' => $count,
            ];
        })->values()->all();
    }

    /**
     * @param  array<int>  $managerIds
     * @param  array<int>  $gameweeks
     * @return array<int, array<string, mixed>>
     */
    private function buildTeamOfWeekRows(array $managerIds, array $gameweeks): array
    {
        if ($managerIds === [] || $gameweeks === []) {
            return [];
        }

        $picks = ManagerPick::query()
            ->whereIn('manager_id', $managerIds)
            ->whereIn('gameweek', $gameweeks)
            ->whereNotNull('event_points')
            ->with('player.team')
            ->get()
            ->groupBy('gameweek')
            ->sortKeysDesc();

        return $picks->map(function (Collection $gameweekPicks, int $gameweek): ?array {
            $bestRows = $this->bestPlayerRowsForGameweek($gameweekPicks);

            if ($bestRows->isEmpty()) {
                return null;
            }

            $goalkeepers = $bestRows->where('element_type', 1)->sortByDesc('points')->values();
            $defenders = $bestRows->where('element_type', 2)->sortByDesc('points')->values();
            $midfielders = $bestRows->where('element_type', 3)->sortByDesc('points')->values();
            $forwards = $bestRows->where('element_type', 4)->sortByDesc('points')->values();

            if ($goalkeepers->isEmpty() || $defenders->count() < 3 || $midfielders->count() < 3 || $forwards->count() < 1) {
                return null;
            }

            $goalkeeper = $goalkeepers->first();
            $bestTeam = null;

            foreach (self::TEAM_OF_WEEK_FORMATIONS as $formation) {
                if (
                    $defenders->count() < $formation['defenders']
                    || $midfielders->count() < $formation['midfielders']
                    || $forwards->count() < $formation['forwards']
                ) {
                    continue;
                }

                $selectedDefenders = $defenders->take($formation['defenders'])->values();
                $selectedMidfielders = $midfielders->take($formation['midfielders'])->values();
                $selectedForwards = $forwards->take($formation['forwards'])->values();

                $baseTotalPoints = (int) (
                    $goalkeeper['points']
                    + $selectedDefenders->sum('points')
                    + $selectedMidfielders->sum('points')
                    + $selectedForwards->sum('points')
                );
                $captainBonus = $this->captainBonusPoints(
                    collect([$goalkeeper])
                        ->merge($selectedDefenders)
                        ->merge($selectedMidfielders)
                        ->merge($selectedForwards)
                );
                $totalPoints = $baseTotalPoints + $captainBonus;

                if ($bestTeam === null || $totalPoints > $bestTeam['total_points']) {
                    $captainizedRows = $this->captainizeRows(
                        collect([$goalkeeper])
                            ->merge($selectedDefenders)
                            ->merge($selectedMidfielders)
                            ->merge($selectedForwards)
                    );

                    $bestTeam = [
                        'formation' => $formation['name'],
                        'goalkeeper' => $captainizedRows->where('element_type', 1)->values()->all(),
                        'defenders' => $captainizedRows->where('element_type', 2)->values()->all(),
                        'midfielders' => $captainizedRows->where('element_type', 3)->values()->all(),
                        'forwards' => $captainizedRows->where('element_type', 4)->values()->all(),
                        'total_points' => $totalPoints,
                    ];
                }
            }

            if ($bestTeam === null) {
                return null;
            }

            return [
                'gameweek' => (int) $gameweek,
                'formation' => $bestTeam['formation'],
                'goalkeeper' => $bestTeam['goalkeeper'],
                'defenders' => $bestTeam['defenders'],
                'midfielders' => $bestTeam['midfielders'],
                'forwards' => $bestTeam['forwards'],
                'total_points' => $bestTeam['total_points'],
            ];
        })->filter()->values()->all();
    }

    /**
     * @param  Collection<int, ManagerPick>  $gameweekPicks
     * @return Collection<int, array<string, mixed>>
     */
    private function bestPlayerRowsForGameweek(Collection $gameweekPicks): Collection
    {
        return $gameweekPicks
            ->filter(fn (ManagerPick $pick): bool => $pick->player !== null)
            ->groupBy('player_id')
            ->map(function (Collection $rows): ?array {
                /** @var ManagerPick|null $pick */
                $pick = $rows->sortByDesc(function (ManagerPick $candidate): int {
                    return (int) ($candidate->event_points ?? 0);
                })->first();

                if ($pick === null || $pick->player === null) {
                    return null;
                }

                $teamShortName = $pick->player->team?->short_name;
                $teamName = $pick->player->team?->name;

                return [
                    'player_id' => (int) $pick->player_id,
                    'web_name' => (string) $pick->player->web_name,
                    'points' => (int) ($pick->event_points ?? 0),
                    'element_type' => (int) ($pick->player->element_type ?? 0),
                    'team_name' => (string) ($teamName ?? 'Unknown'),
                    'team_short_name' => (string) ($teamShortName ?? ''),
                    'team_color' => TeamColorHelper::primary($teamShortName),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $lineupRows
     */
    private function captainBonusPoints(Collection $lineupRows): int
    {
        $captain = $lineupRows
            ->sortByDesc(fn (array $row): int => (int) ($row['points'] ?? 0))
            ->first();

        return (int) ($captain['points'] ?? 0);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $lineupRows
     * @return Collection<int, array<string, mixed>>
     */
    private function captainizeRows(Collection $lineupRows): Collection
    {
        $captain = $lineupRows
            ->sortByDesc(fn (array $row): int => (int) ($row['points'] ?? 0))
            ->first();
        $captainPlayerId = (int) ($captain['player_id'] ?? 0);

        return $lineupRows
            ->map(function (array $row) use ($captainPlayerId): array {
                $isCaptain = (int) ($row['player_id'] ?? 0) === $captainPlayerId;

                $row['is_captain'] = $isCaptain;
                $row['points'] = $isCaptain
                    ? (int) ($row['points'] ?? 0) * 2
                    : (int) ($row['points'] ?? 0);

                return $row;
            })
            ->values();
    }

    private function formatChipName(string $chipName): string
    {
        return match (strtolower($chipName)) {
            '3xc' => 'Tripple Captain',
            'bboost' => 'Bench Boost',
            'freehit' => 'Free Hit',
            'wildcard' => 'Wildcard',
            default => strtoupper($chipName),
        };
    }
}
