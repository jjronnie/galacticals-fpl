<?php

namespace App\Services;

use App\Helpers\AdminCacheHelper;
use App\Helpers\TeamColorHelper;
use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Models\ManagerPick;
use Illuminate\Support\Collection;

class DashboardStatsService
{
    /**
     * @var array<int, array{name: string, defenders: int, midfielders: int, forwards: int}>
     */
    private const FORMATIONS = [
        ['name' => '4-2-3-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
        ['name' => '4-3-3', 'defenders' => 4, 'midfielders' => 3, 'forwards' => 3],
        ['name' => '3-4-3', 'defenders' => 3, 'midfielders' => 4, 'forwards' => 3],
        ['name' => '3-5-2', 'defenders' => 3, 'midfielders' => 5, 'forwards' => 2],
        ['name' => '4-4-2', 'defenders' => 4, 'midfielders' => 4, 'forwards' => 2],
        ['name' => '4-1-4-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
        ['name' => '5-3-2', 'defenders' => 5, 'midfielders' => 3, 'forwards' => 2],
        ['name' => '4-5-1', 'defenders' => 4, 'midfielders' => 5, 'forwards' => 1],
    ];

    /**
     * @return array<string, mixed>
     */
    public function getGlobalDashboardStats(?League $league = null): array
    {
        $cacheKey = $league !== null
            ? "dashboard_global_stats_v3_league_{$league->id}"
            : 'dashboard_global_stats_v3_all';

        return AdminCacheHelper::remember($cacheKey, now()->addMinutes(15), function () use ($league): array {
            $latestGameweek = (int) (ManagerPick::query()->max('gameweek') ?? 0);

            if ($latestGameweek <= 0) {
                return [
                    'best_leagues' => $this->bestLeagues(),
                    'most_valuable_teams' => $this->mostValuableTeams($league),
                    'player_of_week_cards' => [],
                    'team_of_week_rows' => [],
                ];
            }

            $startGameweek = max(1, $latestGameweek - 11);

            $picks = ManagerPick::query()
                ->whereBetween('gameweek', [$startGameweek, $latestGameweek])
                ->whereNotNull('event_points')
                ->with(['player.team:id,name,short_name'])
                ->get();

            $picksByGameweek = $picks
                ->groupBy('gameweek')
                ->sortKeysDesc();

            return [
                'best_leagues' => $this->bestLeagues(),
                'most_valuable_teams' => $this->mostValuableTeams($league),
                'player_of_week_cards' => $this->playerOfWeekCards($picksByGameweek),
                'team_of_week_rows' => $this->teamOfWeekRows($picksByGameweek),
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPlayerOfWeekHistory(int $limit = 40): array
    {
        return AdminCacheHelper::remember("dashboard_player_of_week_history_v1_{$limit}", now()->addMinutes(15), function () use ($limit): array {
            $gameweeks = ManagerPick::query()
                ->whereNotNull('event_points')
                ->select('gameweek')
                ->distinct()
                ->orderByDesc('gameweek')
                ->limit($limit)
                ->pluck('gameweek')
                ->map(fn ($gameweek): int => (int) $gameweek);

            if ($gameweeks->isEmpty()) {
                return [];
            }

            $picksByGameweek = ManagerPick::query()
                ->whereIn('gameweek', $gameweeks->all())
                ->whereNotNull('event_points')
                ->with(['player.team:id,name,short_name'])
                ->get()
                ->groupBy('gameweek')
                ->sortKeysDesc();

            return $this->playerOfWeekCards($picksByGameweek);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTeamOfWeekHistory(int $limit = 40): array
    {
        return AdminCacheHelper::remember("dashboard_team_of_week_history_v1_{$limit}", now()->addMinutes(15), function () use ($limit): array {
            $gameweeks = ManagerPick::query()
                ->whereNotNull('event_points')
                ->select('gameweek')
                ->distinct()
                ->orderByDesc('gameweek')
                ->limit($limit)
                ->pluck('gameweek')
                ->map(fn ($gameweek): int => (int) $gameweek);

            if ($gameweeks->isEmpty()) {
                return [];
            }

            $picksByGameweek = ManagerPick::query()
                ->whereIn('gameweek', $gameweeks->all())
                ->whereNotNull('event_points')
                ->with(['player.team:id,name,short_name'])
                ->get()
                ->groupBy('gameweek')
                ->sortKeysDesc();

            return $this->teamOfWeekRows($picksByGameweek);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bestLeagues(): array
    {
        $leagues = League::query()
            ->with(['managers:id,league_id,total_points'])
            ->get(['id', 'name', 'slug']);

        return $leagues
            ->map(function (League $league): ?array {
                $managerPoints = $league->managers
                    ->map(fn (Manager $manager): int => (int) ($manager->total_points ?? 0))
                    ->values();

                if ($managerPoints->isEmpty()) {
                    return null;
                }

                return [
                    'league_name' => $league->name,
                    'league_slug' => $league->slug,
                    'average' => round((float) $managerPoints->avg(), 1),
                ];
            })
            ->filter()
            ->sortByDesc('average')
            ->take(5)
            ->values()
            ->map(function (array $row, int $index): array {
                $row['position'] = $index + 1;

                return $row;
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mostValuableTeams(?League $league = null): array
    {
        $leagueManagerIds = null;

        if ($league !== null) {
            $leagueManagerIds = $league->managers()->pluck('id')->all();

            if ($leagueManagerIds === []) {
                return [];
            }
        }

        $latestScoresQuery = GameweekScore::query()
            ->whereNotNull('value')
            ->where('value', '>', 0)
            ->orderByDesc('gameweek')
            ->orderByDesc('id');

        if ($leagueManagerIds !== null) {
            $latestScoresQuery->whereIn('manager_id', $leagueManagerIds);
        }

        $latestScoresByManager = $latestScoresQuery
            ->get(['manager_id', 'value'])
            ->groupBy('manager_id')
            ->map(fn (Collection $rows) => $rows->first());

        if ($latestScoresByManager->isEmpty()) {
            return [];
        }

        $managersQuery = Manager::query()
            ->whereIn('id', $latestScoresByManager->keys()->all())
            ->select(['id', 'entry_id', 'team_name']);

        if ($league !== null) {
            $managersQuery->where('league_id', $league->id);
        }

        $managers = $managersQuery->get();

        $bestByEntry = collect();

        foreach ($managers as $manager) {
            $score = $latestScoresByManager->get((int) $manager->id);

            if ($score === null) {
                continue;
            }

            $teamValue = round(((int) $score->value) / 10, 1);
            $entryId = (int) $manager->entry_id;

            if (! $bestByEntry->has($entryId) || $teamValue > (float) $bestByEntry->get($entryId)['value']) {
                $bestByEntry->put($entryId, [
                    'entry_id' => $entryId,
                    'team_name' => (string) $manager->team_name,
                    'value' => $teamValue,
                ]);
            }
        }

        return $bestByEntry
            ->values()
            ->sortByDesc('value')
            ->take(5)
            ->values()
            ->map(function (array $row, int $index): array {
                $row['position'] = $index + 1;

                return $row;
            })
            ->all();
    }

    /**
     * @param  Collection<int, Collection<int, ManagerPick>>  $picksByGameweek
     * @return array<int, array<string, mixed>>
     */
    private function playerOfWeekCards(Collection $picksByGameweek): array
    {
        return $picksByGameweek
            ->map(function (Collection $gameweekPicks, int $gameweek): ?array {
                $bestPick = $this->bestPlayerRowsForGameweek($gameweekPicks)
                    ->sortByDesc(fn (array $row): int => $row['points'])
                    ->first();

                if ($bestPick === null) {
                    return null;
                }

                return [
                    'gameweek' => (int) $gameweek,
                    'web_name' => $bestPick['web_name'],
                    'points' => $bestPick['points'],
                    'team_name' => $bestPick['team_name'],
                    'team_short_name' => $bestPick['team_short_name'],
                    'team_color' => $bestPick['team_color'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Collection<int, ManagerPick>>  $picksByGameweek
     * @return array<int, array<string, mixed>>
     */
    private function teamOfWeekRows(Collection $picksByGameweek): array
    {
        return $picksByGameweek
            ->map(function (Collection $gameweekPicks, int $gameweek): ?array {
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

                foreach (self::FORMATIONS as $formation) {
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

                $playerOfWeek = $bestRows->sortByDesc('points')->first();

                return [
                    'gameweek' => (int) $gameweek,
                    'formation' => $bestTeam['formation'],
                    'total_points' => $bestTeam['total_points'],
                    'goalkeeper' => $bestTeam['goalkeeper'],
                    'defenders' => $bestTeam['defenders'],
                    'midfielders' => $bestTeam['midfielders'],
                    'forwards' => $bestTeam['forwards'],
                    'player_of_week' => $playerOfWeek,
                ];
            })
            ->filter()
            ->values()
            ->all();
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
}
