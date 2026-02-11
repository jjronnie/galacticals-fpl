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
    private const PROFILE_SECTIONS = [
        'overview',
        'contributions',
        'chips',
        'captaincy',
        'transfers',
        'value',
        'awards',
        'history',
    ];

    public function __construct(private readonly LeagueStatsService $leagueStatsService) {}

    public function getProfileStats(Manager $manager, string $section = 'overview'): array
    {
        $section = in_array($section, self::PROFILE_SECTIONS, true) ? $section : 'overview';
        $entryId = (int) $manager->entry_id;
        $cacheKey = "profile_stats_entry_v3_{$entryId}_{$section}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($manager, $entryId, $section): array {
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

            $summary = $this->summaryStats($referenceManager, $scores);
            $autoSubImpact = (int) $scores->sum('autop_sub_points');

            $requiresPicks = in_array($section, ['contributions', 'captaincy', 'awards'], true);
            $requiresChips = $section === 'chips';
            $requiresCaptaincy = in_array($section, ['overview', 'captaincy', 'awards'], true);
            $requiresTransfers = in_array($section, ['overview', 'transfers', 'awards'], true);

            $picks = collect();
            $picksByGameweek = collect();

            if ($requiresPicks) {
                $picks = ManagerPick::query()
                    ->whereIn('manager_id', $relatedManagerIds)
                    ->with('player.team')
                    ->get()
                    ->unique(fn ($pick): string => $pick->gameweek.'-'.$pick->player_id)
                    ->values();

                $picksByGameweek = $picks->groupBy('gameweek');
            }

            $chips = collect();

            if ($requiresChips) {
                $chips = ManagerChip::query()
                    ->whereIn('manager_id', $relatedManagerIds)
                    ->orderBy('gameweek')
                    ->get()
                    ->unique(fn ($chip): string => $chip->gameweek.'-'.$chip->chip_name)
                    ->values();
            }

            $captaincy = $requiresCaptaincy
                ? $this->captaincyPerformance($picksByGameweek, $scores)
                : ['rows' => [], 'total_captain_points' => 0, 'total_what_if_points' => 0, 'missed_points' => 0];
            $transfers = $requiresTransfers
                ? $this->transferEfficiency($scores)
                : ['rows' => [], 'transfers_total' => 0, 'hits_total' => 0, 'net_points_after_hits' => 0];
            $valueEvolution = $section === 'value'
                ? $this->squadValueEvolution($scores)
                : ['labels' => [], 'values' => [], 'rows' => []];
            $playerContribution = $section === 'contributions'
                ? $this->playerContribution($picks)
                : [];
            $favouriteClubBias = $section === 'contributions'
                ? $this->favouriteClubBias($referenceManager, $picks)
                : ['team' => null, 'points' => 0, 'percent' => 0];
            $chipUsage = $section === 'chips'
                ? $this->chipUsage($chips, $scores)
                : ['rows' => [], 'most_effective' => null, 'least_used' => null, 'by_chip_points' => []];
            $awards = in_array($section, ['overview', 'awards'], true)
                ? $this->buildAwards($referenceManager, $scores, $captaincy, $transfers)
                : [];
            $historyRows = $section === 'history'
                ? $scores
                    ->sortByDesc('gameweek')
                    ->values()
                    ->map(fn ($score): array => [
                        'gameweek' => (int) $score->gameweek,
                        'points' => (int) $score->points,
                        'total_points' => (int) ($score->total_points ?? 0),
                        'overall_rank' => (int) ($score->overall_rank ?? 0),
                    ])
                    ->all()
                : [];
            $insightPreviews = $section === 'overview'
                ? $this->buildInsightPreviews($scores, $relatedManagerIds)
                : [];
            $insightCards = $section === 'overview'
                ? $this->buildInsightCards($scores, $relatedManagerIds)
                : [];

            return [
                'manager' => $referenceManager,
                'summary' => $summary,
                'captaincy' => $captaincy,
                'transfers' => $transfers,
                'value_evolution' => $valueEvolution,
                'player_contribution' => $playerContribution,
                'favourite_club_bias' => $favouriteClubBias,
                'chip_usage' => $chipUsage,
                'auto_sub_impact' => $autoSubImpact,
                'awards' => $awards,
                'history_rows' => $historyRows,
                'insight_previews' => $insightPreviews,
                'insight_cards' => $insightCards,
                'active_section' => $section,
            ];
        });
    }

    private function summaryStats(Manager $manager, Collection $scores): array
    {
        $latestScore = $scores->sortBy('gameweek')->last();

        return [
            'total_points' => (int) ($manager->total_points ?? ($latestScore?->total_points ?? 0)),
            'overall_rank' => (int) ($latestScore?->overall_rank ?? 0),
            'favourite_club' => $manager->favouriteTeam?->name ?? 'No data',
            'transfers_made' => (int) $scores->sum('event_transfers'),
            'transfer_hits' => (int) $scores->sum('event_transfers_cost'),
            'bench_points' => (int) $scores->sum('points_on_bench'),
            'auto_sub_points' => (int) $scores->sum('autop_sub_points'),
            'country' => $manager->nationality,
            'current_squad_value' => round(((int) ($latestScore?->value ?? 0)) / 10, 1),
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
                ->sortByDesc('gameweek')
                ->values()
                ->all();

            $totalCaptainPoints = collect($rows)->sum('captain_points');
            $totalWhatIfPoints = collect($rows)->sum('what_if_points');
        }

        return [
            'rows' => collect($rows)->sortByDesc('gameweek')->values()->all(),
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
        })->sortByDesc('gameweek')->values();

        return [
            'rows' => $rows->all(),
            'transfers_total' => (int) $scores->sum('event_transfers'),
            'hits_total' => (int) $scores->sum('event_transfers_cost'),
            'net_points_after_hits' => (int) $rows->sum('net_points'),
        ];
    }

    private function squadValueEvolution(Collection $scores): array
    {
        $rows = $scores->map(function ($score): array {
            return [
                'gameweek' => (int) $score->gameweek,
                'value' => round(((int) ($score->value ?? 0)) / 10, 1),
            ];
        })->sortByDesc('gameweek')->values();

        return [
            'labels' => $scores->pluck('gameweek')->map(fn ($gameweek): int => (int) $gameweek)->values()->all(),
            'values' => $scores->pluck('value')->map(function ($value): float {
                return round(((int) $value) / 10, 1);
            })->values()->all(),
            'rows' => $rows->all(),
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
        })->sortByDesc('gameweek')->values();

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

    private function buildInsightPreviews(Collection $scores, Collection $relatedManagerIds): array
    {
        $defaultPreviews = [
            'contributions' => 'No data yet',
            'chips' => 'No data yet',
            'captaincy' => 'No data yet',
            'transfers' => 'No data yet',
            'value' => 'No data yet',
            'awards' => 'No data yet',
            'history' => 'No data yet',
        ];

        if ($scores->isEmpty()) {
            return $defaultPreviews;
        }

        $allPicks = ManagerPick::query()
            ->whereIn('manager_id', $relatedManagerIds)
            ->with('player.team')
            ->get();

        $topContribution = $allPicks
            ->groupBy('player_id')
            ->map(function (Collection $rows): array {
                $first = $rows->first();
                $points = $rows->sum(fn ($pick): int => (int) (($pick->event_points ?? 0) * max((int) ($pick->multiplier ?? 1), 1)));

                return [
                    'player' => (string) ($first?->player?->web_name ?? 'Unknown'),
                    'points' => $points,
                ];
            })
            ->sortByDesc('points')
            ->first();

        $captainNamesByGameweek = $allPicks
            ->where('is_captain', true)
            ->sortByDesc('updated_at')
            ->groupBy('gameweek')
            ->map(fn (Collection $rows): string => (string) ($rows->first()?->player?->web_name ?? 'Captain'));

        $chipRows = ManagerChip::query()
            ->whereIn('manager_id', $relatedManagerIds)
            ->orderBy('gameweek')
            ->get()
            ->unique(fn ($chip): string => $chip->gameweek.'-'.$chip->chip_name)
            ->values()
            ->map(function ($chip) use ($scores): array {
                $pointsAfter = $chip->points_after;
                $pointsBefore = $chip->points_before;

                if ($pointsAfter === null || $pointsBefore === null) {
                    $scoreAtChipGw = $scores->firstWhere('gameweek', $chip->gameweek);
                    $scoreBeforeChip = $scores
                        ->where('gameweek', '<', $chip->gameweek)
                        ->sortByDesc('gameweek')
                        ->first();

                    $pointsAfter = $scoreAtChipGw?->total_points;
                    $pointsBefore = $scoreBeforeChip?->total_points;
                }

                return [
                    'gameweek' => (int) $chip->gameweek,
                    'chip' => $this->formatChipName((string) $chip->chip_name),
                    'points_gained' => (int) (($pointsAfter ?? 0) - ($pointsBefore ?? 0)),
                ];
            })
            ->values();

        $bestChip = $chipRows->sortByDesc('points_gained')->first();
        $bestCaptainScore = $scores->sortByDesc('captain_points')->first();
        $bestHistoryScore = $scores->sortByDesc('points')->first();
        $bestValueScore = $scores->sortByDesc(fn ($score): int => (int) ($score->value ?? 0))->first();

        $bestTransferScore = $scores
            ->map(fn ($score): array => [
                'gameweek' => (int) $score->gameweek,
                'net_points' => (int) $score->points - (int) ($score->event_transfers_cost ?? 0),
            ])
            ->sortByDesc('net_points')
            ->first();

        $contributionPreview = $topContribution !== null
            ? 'Best · '.$topContribution['player'].' '.$topContribution['points'].' pts'
            : 'Best · No pick data';

        $chipPreview = $bestChip !== null
            ? 'Best · GW'.$bestChip['gameweek'].' · '.$bestChip['chip'].' '.($bestChip['points_gained'] >= 0 ? '+' : '').$bestChip['points_gained'].' pts'
            : 'Best · No chip data';

        $captainPreview = $bestCaptainScore !== null
            ? 'Best · GW'.(int) $bestCaptainScore->gameweek
                .' · '.$captainNamesByGameweek->get((int) $bestCaptainScore->gameweek, 'Captain')
                .' '.(int) ($bestCaptainScore->captain_points ?? 0).' pts'
            : 'Best · No captaincy data';

        $transferPreview = $bestTransferScore !== null
            ? 'Best · GW'.$bestTransferScore['gameweek'].' · Net '.$bestTransferScore['net_points']
            : 'Best · No transfer data';

        $valuePreview = $bestValueScore !== null
            ? 'Best · €'.number_format(round(((int) ($bestValueScore->value ?? 0)) / 10, 1), 1).'m (GW'.(int) $bestValueScore->gameweek.')'
            : 'Best · No value data';

        $historyPreview = $bestHistoryScore !== null
            ? 'Best · GW'.(int) $bestHistoryScore->gameweek.' · '.(int) $bestHistoryScore->points.' pts'
            : 'Best · No history data';

        $seasonMilestones = 0;

        if ($scores->contains(fn ($score): bool => (int) $score->points >= 100)) {
            $seasonMilestones++;
        }

        if ((int) $scores->sum('captain_points') >= 200) {
            $seasonMilestones++;
        }

        if (
            (int) $scores->sum('event_transfers') > 0
            && (int) $scores->sum(fn ($score): int => (int) $score->points - (int) $score->event_transfers_cost) > 0
        ) {
            $seasonMilestones++;
        }

        return [
            'contributions' => $contributionPreview,
            'chips' => $chipPreview,
            'captaincy' => $captainPreview,
            'transfers' => $transferPreview,
            'value' => $valuePreview,
            'awards' => $seasonMilestones > 0 ? $seasonMilestones.' milestones' : 'No milestones yet',
            'history' => $historyPreview,
        ];
    }

    private function buildInsightCards(Collection $scores, Collection $relatedManagerIds): array
    {
        $latestScores = $scores
            ->sortByDesc('gameweek')
            ->values()
            ->take(5)
            ->values();

        $latestGameweeks = $latestScores
            ->pluck('gameweek')
            ->map(fn ($gameweek): int => (int) $gameweek)
            ->all();

        $captainRowsByGameweek = ManagerPick::query()
            ->whereIn('manager_id', $relatedManagerIds)
            ->whereIn('gameweek', $latestGameweeks)
            ->where('is_captain', true)
            ->with('player:id,web_name')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('gameweek')
            ->map(fn (Collection $rows): string => (string) ($rows->first()?->player?->web_name ?? 'N/A'));

        $captaincyRows = $latestScores
            ->map(function ($score) use ($captainRowsByGameweek): array {
                $gameweek = (int) $score->gameweek;

                return [
                    'gameweek' => $gameweek,
                    'captain' => $captainRowsByGameweek->get($gameweek, 'N/A'),
                    'captain_points' => (int) ($score->captain_points ?? 0),
                    'what_if_points' => (int) (($score->best_pick_points ?? 0) * 2),
                ];
            })
            ->values()
            ->all();

        $transferRows = $latestScores
            ->map(fn ($score): array => [
                'gameweek' => (int) $score->gameweek,
                'transfers' => (int) ($score->event_transfers ?? 0),
                'hit_cost' => (int) ($score->event_transfers_cost ?? 0),
                'net_points' => (int) $score->points - (int) ($score->event_transfers_cost ?? 0),
            ])
            ->values()
            ->all();

        $historyRows = $latestScores
            ->map(fn ($score): array => [
                'gameweek' => (int) $score->gameweek,
                'points' => (int) $score->points,
                'total_points' => (int) ($score->total_points ?? 0),
                'overall_rank' => (int) ($score->overall_rank ?? 0),
            ])
            ->values()
            ->all();

        $valueRowsAsc = $scores
            ->sortBy('gameweek')
            ->values()
            ->map(fn ($score): array => [
                'gameweek' => (int) $score->gameweek,
                'value' => round(((int) ($score->value ?? 0)) / 10, 1),
            ])
            ->values();

        $valueRowsWithTrend = $valueRowsAsc
            ->map(function (array $row, int $index) use ($valueRowsAsc): array {
                $previousRow = $index > 0 ? $valueRowsAsc[$index - 1] : null;

                if ($previousRow === null) {
                    $trend = 'same';
                } elseif ($row['value'] > $previousRow['value']) {
                    $trend = 'up';
                } elseif ($row['value'] < $previousRow['value']) {
                    $trend = 'down';
                } else {
                    $trend = 'same';
                }

                $row['trend'] = $trend;

                return $row;
            })
            ->sortByDesc('gameweek')
            ->values()
            ->take(5)
            ->values()
            ->all();

        $chipRows = ManagerChip::query()
            ->whereIn('manager_id', $relatedManagerIds)
            ->orderByDesc('gameweek')
            ->orderByDesc('id')
            ->take(3)
            ->get()
            ->map(function ($chip) use ($scores): array {
                $pointsAfter = $chip->points_after;
                $pointsBefore = $chip->points_before;

                if ($pointsAfter === null || $pointsBefore === null) {
                    $scoreAtChipGw = $scores->firstWhere('gameweek', $chip->gameweek);
                    $scoreBeforeChip = $scores
                        ->where('gameweek', '<', $chip->gameweek)
                        ->sortByDesc('gameweek')
                        ->first();

                    $pointsAfter = $scoreAtChipGw?->total_points;
                    $pointsBefore = $scoreBeforeChip?->total_points;
                }

                $pointsGained = (int) (($pointsAfter ?? 0) - ($pointsBefore ?? 0));

                return [
                    'gameweek' => (int) $chip->gameweek,
                    'chip' => $this->formatChipName((string) $chip->chip_name),
                    'points_gained' => $pointsGained,
                ];
            })
            ->values()
            ->all();

        $contributionRows = ManagerPick::query()
            ->whereIn('manager_id', $relatedManagerIds)
            ->with('player.team')
            ->get()
            ->groupBy('player_id')
            ->map(function (Collection $rows): array {
                $first = $rows->first();
                $points = $rows->sum(fn ($pick): int => (int) (($pick->event_points ?? 0) * max((int) ($pick->multiplier ?? 1), 1)));

                return [
                    'player' => (string) ($first?->player?->web_name ?? 'Unknown'),
                    'team' => (string) ($first?->player?->team?->short_name ?? '-'),
                    'points' => $points,
                ];
            })
            ->sortByDesc('points')
            ->take(3)
            ->values()
            ->all();

        return [
            'contributions_rows' => $contributionRows,
            'captaincy_rows' => $captaincyRows,
            'transfer_rows' => $transferRows,
            'value_rows' => $valueRowsWithTrend,
            'history_rows' => $historyRows,
            'chip_rows' => $chipRows,
        ];
    }

    /**
     * @return array<int, array{title: string, reason: string, achieved_at: string, gameweek: int|null}>
     */
    private function buildAwards(Manager $manager, Collection $scores, array $captaincy, array $transfers): array
    {
        $awards = [];
        $orderedScores = $scores->sortBy('gameweek')->values();

        $firstHundredPointWeek = $orderedScores->first(fn ($score): bool => (int) $score->points >= 100);
        if ($firstHundredPointWeek !== null) {
            $awards[] = [
                'title' => '100+ King Award',
                'reason' => 'Given for scoring at least 100 points in one gameweek.',
                'achieved_at' => 'GW '.(int) $firstHundredPointWeek->gameweek.' ('.(int) $firstHundredPointWeek->points.' pts)',
                'gameweek' => (int) $firstHundredPointWeek->gameweek,
            ];
        }

        if ((int) ($captaincy['total_captain_points'] ?? 0) >= 200) {
            $runningCaptainPoints = 0;
            $captainMilestoneGameweek = null;

            foreach ($orderedScores as $score) {
                $runningCaptainPoints += (int) ($score->captain_points ?? 0);

                if ($runningCaptainPoints >= 200) {
                    $captainMilestoneGameweek = (int) $score->gameweek;
                    break;
                }
            }

            $awards[] = [
                'title' => 'Captaincy Legend Award',
                'reason' => 'Given for reaching at least 200 captain points in the season.',
                'achieved_at' => $captainMilestoneGameweek !== null
                    ? 'GW '.$captainMilestoneGameweek.' (season captain points reached '.$runningCaptainPoints.')'
                    : 'Season total',
                'gameweek' => $captainMilestoneGameweek,
            ];
        }

        if ((int) $orderedScores->sum('event_transfers') > 0 && (int) ($transfers['net_points_after_hits'] ?? 0) > 0) {
            $bestTransferWeek = $orderedScores
                ->map(fn ($score): array => [
                    'gameweek' => (int) $score->gameweek,
                    'net_points' => (int) $score->points - (int) ($score->event_transfers_cost ?? 0),
                ])
                ->sortByDesc('net_points')
                ->first();

            $awards[] = [
                'title' => 'Transfer Guru Award',
                'reason' => 'Given for finishing with positive net points after transfer hits.',
                'achieved_at' => $bestTransferWeek !== null
                    ? 'GW '.$bestTransferWeek['gameweek'].' (net '.$bestTransferWeek['net_points'].' pts)'
                    : 'Season total',
                'gameweek' => $bestTransferWeek['gameweek'] ?? null,
            ];
        }

        if ($manager->league_id !== null && $manager->league !== null) {
            $leagueStats = $this->leagueStatsService->getLeagueStats($manager->league);
            $managerName = (string) $manager->player_name;

            $gameweekPerformance = collect($leagueStats['gwPerformance'] ?? []);
            $bottomFinishGameweeks = $gameweekPerformance
                ->filter(fn (array $row): bool => in_array($managerName, $row['worst_managers'] ?? [], true))
                ->pluck('gameweek')
                ->map(fn ($gameweek): int => (int) $gameweek)
                ->sort()
                ->values();

            $hallOfShame = $leagueStats['stats']['hall_of_shame'] ?? [];
            if (array_key_exists($managerName, $hallOfShame)) {
                $thirdBottomFinishGameweek = $bottomFinishGameweeks->get(2);
                $bottomFinishCount = (int) ($hallOfShame[$managerName] ?? 0);

                $awards[] = [
                    'title' => 'Hall of Shame Award',
                    'reason' => 'Given for finishing bottom in at least 3 gameweeks in a league.',
                    'achieved_at' => $thirdBottomFinishGameweek !== null
                        ? 'GW '.$thirdBottomFinishGameweek.' (3rd bottom finish, total '.$bottomFinishCount.')'
                        : 'League season',
                    'gameweek' => $thirdBottomFinishGameweek !== null ? (int) $thirdBottomFinishGameweek : null,
                ];
            }

            if (in_array($managerName, $leagueStats['stats']['wooden_spoon_contenders'] ?? [], true)) {
                $latestLeagueGameweek = $gameweekPerformance
                    ->pluck('gameweek')
                    ->map(fn ($gameweek): int => (int) $gameweek)
                    ->max();

                if (! is_int($latestLeagueGameweek) || $latestLeagueGameweek <= 0) {
                    $latestLeagueGameweek = (int) ($orderedScores->max('gameweek') ?? 0);
                }

                $awards[] = [
                    'title' => 'Wooden Spoon Award',
                    'reason' => 'Given for being in the current bottom zone in your league.',
                    'achieved_at' => $latestLeagueGameweek > 0
                        ? 'After GW '.$latestLeagueGameweek
                        : 'Current table',
                    'gameweek' => $latestLeagueGameweek > 0 ? $latestLeagueGameweek : null,
                ];
            }

            $hundredClubRecords = collect($leagueStats['stats']['hundred_plus_league'] ?? [])
                ->filter(fn ($record): bool => is_string($record) && str_starts_with($record, $managerName.' '))
                ->values();

            if ($hundredClubRecords->isNotEmpty()) {
                $firstHundredClubRecord = $hundredClubRecords
                    ->map(function (string $record): array {
                        $gameweek = null;
                        $points = null;

                        if (preg_match('/\((\d+)\s+pts in GW\s+(\d+)\)/i', $record, $matches) === 1) {
                            $points = (int) $matches[1];
                            $gameweek = (int) $matches[2];
                        }

                        return [
                            'record' => $record,
                            'points' => $points,
                            'gameweek' => $gameweek,
                        ];
                    })
                    ->filter(fn (array $parsed): bool => $parsed['gameweek'] !== null)
                    ->sortBy('gameweek')
                    ->first();

                $awards[] = [
                    'title' => 'Hundred Club Award',
                    'reason' => 'Given for posting a 100+ point gameweek in league competition.',
                    'achieved_at' => $firstHundredClubRecord !== null
                        ? 'GW '.$firstHundredClubRecord['gameweek'].' ('.$firstHundredClubRecord['points'].' pts)'
                        : 'League season',
                    'gameweek' => $firstHundredClubRecord['gameweek'] ?? null,
                ];
            }
        }

        return collect($awards)
            ->unique('title')
            ->values()
            ->all();
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
