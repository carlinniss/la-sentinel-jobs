<?php

declare(strict_types=1);

return [
    'google_publisher' => [
        'enabled' => (bool) env('GOOGLE_PUBLISHER_ENABLED', true),
        'slots' => [
            'leaderboard' => env('GOOGLE_PUBLISHER_SLOT_LEADERBOARD', '/17020487/LeaderBoard'),
            'tile_4' => env('GOOGLE_PUBLISHER_SLOT_TILE_4', '/17020487/Tile_4'),
        ],
    ],
    'broadstreet' => [
        'enabled' => (bool) env('BROADSTREET_ENABLED', true),
        'network_id' => env('BROADSTREET_NETWORK_ID', 10588),
        'preview' => (bool) env('BROADSTREET_PREVIEW', false),
        'flux_enabled' => (bool) env('BROADSTREET_FLUX_ENABLED', true),
        'zones' => [
            'billboard' => env('BROADSTREET_ZONE_BILLBOARD', 187227),
            'source_body_secondary' => env('BROADSTREET_ZONE_SOURCE_BODY_SECONDARY', 187229),
            'source_header_left' => env('BROADSTREET_ZONE_SOURCE_HEADER_LEFT', 187225),
            'source_header_right' => env('BROADSTREET_ZONE_SOURCE_HEADER_RIGHT', 187226),
            'display' => env('BROADSTREET_ZONE_DISPLAY', env('BROADSTREET_ZONE_CUBE', 187228)),
            'sidebar_skyscraper' => env('BROADSTREET_ZONE_SKYSCRAPER', env('BROADSTREET_ZONE_SOURCE_HEADER_LEFT', 187225)),
            'sidebar_half_page' => env('BROADSTREET_ZONE_HALF_PAGE', env('BROADSTREET_ZONE_SOURCE_HEADER_RIGHT', 187226)),
            'inline' => env('BROADSTREET_ZONE_INLINE', env('BROADSTREET_ZONE_SOURCE_BODY_SECONDARY', 187229)),
            'cube' => env('BROADSTREET_ZONE_CUBE', 187228),
        ],
    ],
];
