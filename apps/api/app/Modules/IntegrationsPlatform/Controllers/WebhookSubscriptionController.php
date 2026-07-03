<?php

namespace App\Modules\IntegrationsPlatform\Controllers;

use App\Modules\IntegrationsPlatform\Requests\ListWebhookSubscriptionsRequest;
use App\Modules\IntegrationsPlatform\Requests\StoreWebhookSubscriptionRequest;
use App\Modules\IntegrationsPlatform\Requests\UpdateWebhookSubscriptionRequest;
use App\Modules\IntegrationsPlatform\Resources\WebhookSubscriptionResource;
use App\Modules\IntegrationsPlatform\Services\IntegrationPlatformService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class WebhookSubscriptionController
{
    public function __construct(private readonly IntegrationPlatformService $integrationPlatformService) {}

    public function index(ListWebhookSubscriptionsRequest $request): JsonResponse
    {
        $subscriptions = $this->integrationPlatformService->listWebhookSubscriptions($request->user(), $request->validated());

        $payload = ApiResponse::success('Webhook subscriptions loaded successfully.', [
            'items' => WebhookSubscriptionResource::collection($subscriptions),
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreWebhookSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->integrationPlatformService->createWebhookSubscription($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Webhook subscription created successfully.',
            new WebhookSubscriptionResource($subscription),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateWebhookSubscriptionRequest $request, int $webhookSubscriptionId): JsonResponse
    {
        $subscription = $this->integrationPlatformService->updateWebhookSubscription(
            $request->user(),
            $webhookSubscriptionId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Webhook subscription updated successfully.',
            new WebhookSubscriptionResource($subscription),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
