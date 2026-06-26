<?php

return [
    'resume_disk' => env('RECRUITMENT_RESUME_DISK', 'local'),
    'resume_allowed_extensions' => ['pdf', 'doc', 'docx'],
    'resume_max_file_size_kb' => 10240,
    'candidate_stages' => [
        'applied',
        'screening',
        'shortlisted',
        'interview',
        'offer',
        'hired',
        'rejected',
        'withdrawn',
    ],
];
