<?php

namespace App\Modules\Platform\Release\Controllers;

use App\Modules\Platform\Release\Requests\StoreReleaseReadinessDecisionRequest;
use App\Modules\Platform\Release\Services\ReleaseReadinessService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReleaseReadinessDecisionController
{
    public function __construct(private readonly ReleaseReadinessService $releaseReadinessService) {}

    public function store(StoreReleaseReadinessDecisionRequest $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Release readiness decision recorded successfully.',
            $this->releaseReadinessService->recordDecision($request->user(), $request->validated()),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
