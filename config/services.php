<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    'fpl' => [
        'base_url' => env('FPL_BASE_URL', 'https://fantasy.premierleague.com/api'),
        'connect_timeout_seconds' => (int) env('FPL_CONNECT_TIMEOUT_SECONDS', 10),
        'request_timeout_seconds' => (int) env('FPL_REQUEST_TIMEOUT_SECONDS', 45),
        'retry_attempts' => (int) env('FPL_RETRY_ATTEMPTS', 4),
        'retry_initial_delay_ms' => (int) env('FPL_RETRY_INITIAL_DELAY_MS', 750),
        'force_ipv4' => env('FPL_FORCE_IPV4', false),
        'catalog_min_teams' => (int) env('FPL_CATALOG_MIN_TEAMS', 20),
        'catalog_min_players' => (int) env('FPL_CATALOG_MIN_PLAYERS', 700),
        'manager_request_interval_ms' => (int) env('FPL_MANAGER_REQUEST_INTERVAL_MS', 300),
        'page_request_interval_ms' => (int) env('FPL_PAGE_REQUEST_INTERVAL_MS', 200),
        'profile_batch_size' => (int) env('FPL_PROFILE_BATCH_SIZE', 50),
        'profile_cooldown_seconds' => (int) env('FPL_PROFILE_COOLDOWN_SECONDS', 60),
    ],

];
