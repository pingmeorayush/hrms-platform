<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Resources\IntegrationSyncJobResource;
use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicWebhookController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function store(Request $request, string $subscriptionKey): JsonResponse
    {
        $job = $this->integrationPlatformService->receiveInboundWebhook(
            $subscriptionKey,
            is_array($request->all()) ? $request->all() : [],
            (string) $request->getContent(),
            $request->headers->all(),
        );

        $payload = ApiResponse::success(
            'Webhook received successfully.',
            new IntegrationSyncJobResource($job),
            202,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
