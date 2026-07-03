<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Requests\ListIntegrationSyncJobsRequest;
use App\Modules\IntegrationsPlatform\Resources\IntegrationSyncJobResource;
use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationSyncJobController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function index(ListIntegrationSyncJobsRequest $request): JsonResponse
    {
        $jobs = $this->integrationPlatformService->listSyncJobs($request->user(), $request->validated());

        $payload = ApiResponse::success('Integration sync jobs loaded successfully.', [
            'items' => IntegrationSyncJobResource::collection($jobs->items()),
            'meta' => [
                'page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'last_page' => $jobs->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(Request $request, int $integrationSyncJobId): JsonResponse
    {
        $job = $this->integrationPlatformService->showSyncJob($request->user(), $integrationSyncJobId);

        $payload = ApiResponse::success(
            'Integration sync job loaded successfully.',
            new IntegrationSyncJobResource($job),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function process(Request $request, int $integrationSyncJobId): JsonResponse
    {
        $job = $this->integrationPlatformService->processSyncJob($request->user(), $integrationSyncJobId);

        $payload = ApiResponse::success(
            'Integration sync job processed successfully.',
            new IntegrationSyncJobResource($job),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function retry(Request $request, int $integrationSyncJobId): JsonResponse
    {
        $job = $this->integrationPlatformService->retrySyncJob($request->user(), $integrationSyncJobId);

        $payload = ApiResponse::success(
            'Integration sync job retried successfully.',
            new IntegrationSyncJobResource($job),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
