<?php

namespace App\Modules\Platform\Release\Controllers;

use App\Modules\Platform\Release\Services\ReleaseQualityGateService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReleaseQualityGateController
{
    public function __construct(private readonly ReleaseQualityGateService $releaseQualityGateService) {}

    public function show(): JsonResponse
    {
        $payload = ApiResponse::success(
            'Release quality gates loaded successfully.',
            $this->releaseQualityGateService->overview(),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
