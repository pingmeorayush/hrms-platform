<?php

namespace App\Modules\Platform\Shared\Http;

class ApiResponse
{
    /**
     * @return array{
     *   status: int,
     *   body: array{
     *     success: true,
     *     message: string,
     *     data: mixed
     *   }
     * }
     */
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

    /**
     * @param  array<string|int, mixed>  $errors
     * @return array{
     *   status: int,
     *   body: array{
     *     success: false,
     *     message: string,
     *     errors: object
     *   }
     * }
     */
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
