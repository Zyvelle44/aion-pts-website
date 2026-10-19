<?php

return [
    /* Dedicated, read-only connection to the AION game database. */
    'connection' => env('AION_DB_CONNECTION', 'aion_game'),
    'database' => env('AION_DB_DATABASE', 'AionAccounts'),
    'freshness_seconds' => (int) env('AION_STATUS_FRESHNESS_SECONDS', 300),

    /* MainServer/config.xml declares serverId=1. Change only if verified otherwise. */
    'player_server_ids' => array_values(array_filter(array_map(
        'intval',
        explode(',', (string) env('AION_PLAYER_SERVER_IDS', '1')),
    ), static fn (int $id): bool => $id >= 0 && $id <= 255)),
    'cache_seconds' => (int) env('AION_STATUS_CACHE_SECONDS', 30),

    /*
     * Server status is based on TCP reachability, not an unverified MSSQL
     * status code. Set these to the actual game-client endpoint.
     */
    'status_host' => env('AION_STATUS_HOST', '127.0.0.1'),
    'status_port' => (int) env('AION_STATUS_PORT', 7777),
    'status_timeout' => (float) env('AION_STATUS_TIMEOUT', 1.0),
];
