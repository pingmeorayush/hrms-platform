<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationCatalogController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function index(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Integration catalog loaded successfully.',
            $this->integrationPlatformService->catalog($request->user()),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
