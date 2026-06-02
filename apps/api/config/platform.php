<?php

return [
    'auth' => [
        'max_login_attempts' => env('AUTH_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_minutes' => env('AUTH_LOCKOUT_MINUTES', 15),
        'mfa_code_minutes' => env('AUTH_MFA_CODE_MINUTES', 10),
        'session_timeout_minutes' => env('AUTH_SESSION_TIMEOUT_MINUTES', 30),
        'token_name' => env('AUTH_TOKEN_NAME', 'phoenix-api'),
    ],
];
