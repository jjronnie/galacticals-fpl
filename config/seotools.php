<?php

return [
    'meta' => [
        'defaults' => [
            'title' => false,
            'titleBefore' => false, // Don't append anything before
            'description' => 'Advanced Fantasy Premier League statistics and analytics',
            'separator' => ' - ',
            'keywords' => [],
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
            'images' => [],
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
            'images' => [],
        ],
    ],
];