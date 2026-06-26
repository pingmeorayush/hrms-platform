<?php

return [
    'exports' => [
        'disk' => env('REPORTING_EXPORT_DISK', 'local'),
        'sync_row_limit' => (int) env('REPORTING_EXPORT_SYNC_ROW_LIMIT', 500),
        'max_row_limit' => (int) env('REPORTING_EXPORT_MAX_ROW_LIMIT', 5000),
        'retention_hours' => (int) env('REPORTING_EXPORT_RETENTION_HOURS', 48),
        'formats' => ['csv', 'json'],
        'delivery_targets' => ['requestor_download'],
    ],
    'saved_views' => [
        'share_scopes' => ['private', 'roles', 'company'],
    ],
    'subscriptions' => [
        'channels' => ['in_app_notification'],
        'delivery_targets' => ['owner_only'],
        'frequencies' => ['daily', 'weekly', 'monthly'],
        'statuses' => ['active', 'paused', 'blocked', 'revoked'],
    ],
];
