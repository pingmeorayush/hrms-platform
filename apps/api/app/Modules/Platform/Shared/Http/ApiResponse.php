<?php

namespace App\Modules\Platform\Shared\Http;

class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $status = 200): array
    {
        return [
            'status' => $status,
            'body' => [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
        ];
    }

    public static function error(string $message, array $errors = [], int $status = 422): array
    {
        return [
            'status' => $status,
            'body' => [
                'success' => false,
                'message' => $message,
                'errors' => (object) $errors,
            ],
        ];
    }
}
