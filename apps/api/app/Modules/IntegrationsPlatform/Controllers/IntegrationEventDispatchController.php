<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Requests\DispatchIntegrationEventRequest;
use App\Modules\IntegrationsPlatform\Resources\IntegrationSyncJobResource;
use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class IntegrationEventDispatchController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function store(DispatchIntegrationEventRequest $request): JsonResponse
    {
        $jobs = $this->integrationPlatformService->dispatchEvent($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Integration event dispatched successfully.',
            ['items' => IntegrationSyncJobResource::collection($jobs)],
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
