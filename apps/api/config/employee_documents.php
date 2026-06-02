<?php

return [
    'disk' => env('EMPLOYEE_DOCUMENTS_DISK', 'local'),
    'allowed_extensions' => ['pdf', 'docx', 'png', 'jpg', 'jpeg'],
    'max_file_size_kb' => 10 * 1024,
];
