<?php

namespace App\Modules\Platform\Release\Controllers;

use App\Modules\Platform\Release\Services\ReleaseReadinessService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReleaseReadinessController
{
    public function __construct(private readonly ReleaseReadinessService $releaseReadinessService) {}

    public function show(): JsonResponse
    {
        $payload = ApiResponse::success(
            'Release readiness loaded successfully.',
            $this->releaseReadinessService->overview(),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
