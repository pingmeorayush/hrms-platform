<?php

namespace App\Modules\Platform\Observability\Controllers;

use App\Modules\Platform\Observability\Services\ObservabilityOverviewService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ObservabilityOverviewController
{
    public function __construct(private readonly ObservabilityOverviewService $observabilityOverviewService) {}

    public function show(): JsonResponse
    {
        $payload = ApiResponse::success(
            'Observability overview loaded successfully.',
            $this->observabilityOverviewService->overview(),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
