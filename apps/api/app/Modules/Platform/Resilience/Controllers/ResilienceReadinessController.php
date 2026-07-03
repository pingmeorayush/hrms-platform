<?php

namespace App\Modules\Platform\Resilience\Controllers;

use App\Modules\Platform\Resilience\Services\ResilienceReadinessService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ResilienceReadinessController
{
    public function __construct(private readonly ResilienceReadinessService $resilienceReadinessService) {}

    public function show(): JsonResponse
    {
        $payload = ApiResponse::success(
            'Resilience readiness loaded successfully.',
            $this->resilienceReadinessService->overview(),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
