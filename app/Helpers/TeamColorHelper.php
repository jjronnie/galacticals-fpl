<?php

namespace App\Helpers;

class TeamColorHelper
{
    /**
     * @var array<string, string>
     */
    private const PRIMARY_COLORS = [
        'ARS' => '#EF0107',
        'AVL' => '#670E36',
        'BOU' => '#DA291C',
        'BRE' => '#E30613',
        'BHA' => '#0057B8',
        'CHE' => '#034694',
        'CRY' => '#1B458F',
        'EVE' => '#003399',
        'FUL' => '#111111',
        'IPS' => '#0064A8',
        'LEI' => '#003090',
        'LIV' => '#C8102E',
        'MCI' => '#6CABDD',
        'MUN' => '#DA291C',
        'NEW' => '#241F20',
        'NFO' => '#DD0000',
        'SOU' => '#D71920',
        'TOT' => '#132257',
        'WHU' => '#7A263A',
        'WOL' => '#FDB913',
    ];

    public static function primary(?string $teamShortName): string
    {
        if ($teamShortName === null) {
            return '#6b7280';
        }

        $shortName = strtoupper(trim($teamShortName));

        return self::PRIMARY_COLORS[$shortName] ?? '#6b7280';
    }
}
