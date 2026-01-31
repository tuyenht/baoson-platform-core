<?php

return [
    'scheduler' => [
        // Heartbeat stored in cache by core:scheduler-heartbeat
        'heartbeat_ttl_seconds' => (int) env('CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS', 3600),
        // core:doctor will warn if last heartbeat is older than this
        'heartbeat_max_age_seconds' => (int) env('CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS', 300),
    ],
];


