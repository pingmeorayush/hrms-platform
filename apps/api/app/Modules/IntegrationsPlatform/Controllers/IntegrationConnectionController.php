<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Requests\ListIntegrationConnectionsRequest;
use App\Modules\IntegrationsPlatform\Requests\StoreIntegrationConnectionRequest;
use App\Modules\IntegrationsPlatform\Requests\UpdateIntegrationConnectionRequest;
use App\Modules\IntegrationsPlatform\Resources\IntegrationConnectionResource;
use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class IntegrationConnectionController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function index(ListIntegrationConnectionsRequest $request): JsonResponse
    {
        $connections = $this->integrationPlatformService->listConnections($request->user(), $request->validated());

        $payload = ApiResponse::success('Integration connections loaded successfully.', [
            'items' => IntegrationConnectionResource::collection($connections),
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreIntegrationConnectionRequest $request): JsonResponse
    {
        $connection = $this->integrationPlatformService->createConnection($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Integration connection created successfully.',
            new IntegrationConnectionResource($connection),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateIntegrationConnectionRequest $request, int $integrationConnectionId): JsonResponse
    {
        $connection = $this->integrationPlatformService->updateConnection(
            $request->user(),
            $integrationConnectionId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Integration connection updated successfully.',
            new IntegrationConnectionResource($connection),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
