<?php

namespace App\Modules\Platform\Resilience\Controllers;

use App\Modules\Platform\Resilience\Requests\StoreResilienceValidationRunRequest;
use App\Modules\Platform\Resilience\Services\ResilienceReadinessService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ResilienceValidationRunController
{
    public function __construct(private readonly ResilienceReadinessService $resilienceReadinessService) {}

    public function store(StoreResilienceValidationRunRequest $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Resilience validation run recorded successfully.',
            $this->resilienceReadinessService->recordValidationRun($request->user(), $request->validated()),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
