<?php

return [
    'meta' => [
        'defaults' => [
            'title' => 'FPL Galaxy',
            'titleBefore' => false, 
            'description' => 'Advanced Fantasy Premier League statistics and analytics',
            'separator' => ' | ',
            'keywords' => ['FPL', 'Fantasy Premier League', 'FPL Stats', 'Mini League', 'FPL Galaxy', 'FPL Analytics'],
            'canonical' => null,
            'robots' => null,
        ],
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
            'norton' => null,
        ],
        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        'defaults' => [
            'title' => 'FPL Galaxy',
            'description' => 'Advanced Fantasy Premier League statistics',
            'url' => null,
            'type' => 'website',
            'site_name' => 'FPL Galaxy',
            'images' => ['/assets/img/logo.webp'],
        ],
    ],
    'twitter' => [
        'defaults' => [
            'card' => 'summary_large_image',
            'site' => '@fplgalaxy', // Add your Twitter handle if you have one
        ],
    ],
    'json-ld' => [
        'defaults' => [
            'title' => 'FPL Galaxy',
            'description' => 'Advanced Fantasy Premier League statistics',
            'url' => null,
            'type' => 'WebPage',
            'images' => ['/assets/img/logo.webp'],
        ],
    ],
];

