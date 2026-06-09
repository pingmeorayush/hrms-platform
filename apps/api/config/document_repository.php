<?php

return [
    'disk' => env('DOCUMENT_REPOSITORY_DISK', 'local'),
    'allowed_extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
    'max_file_size_kb' => 15 * 1024,
    'repository_scopes' => ['general', 'employee', 'policy', 'compliance', 'payroll', 'asset', 'onboarding', 'offboarding'],
    'visibility_scopes' => ['internal', 'restricted', 'confidential'],
    'category_statuses' => ['active', 'inactive'],
];
