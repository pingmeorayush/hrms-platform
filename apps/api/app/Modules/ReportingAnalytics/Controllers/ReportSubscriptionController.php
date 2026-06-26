<?php

namespace App\Modules\ReportingAnalytics\Controllers;

use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\ReportingAnalytics\Requests\ListReportSubscriptionsRequest;
use App\Modules\ReportingAnalytics\Requests\StoreReportSubscriptionRequest;
use App\Modules\ReportingAnalytics\Requests\UpdateReportSubscriptionRequest;
use App\Modules\ReportingAnalytics\Resources\ReportSubscriptionResource;
use App\Modules\ReportingAnalytics\Services\ReportingSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportSubscriptionController
{
    public function __construct(private readonly ReportingSubscriptionService $reportingSubscriptionService) {}

    public function index(ListReportSubscriptionsRequest $request): JsonResponse
    {
        $subscriptions = $this->reportingSubscriptionService->searchSubscriptions($request->user(), $request->validated());

        $payload = ApiResponse::success('Report subscriptions loaded successfully.', [
            'items' => ReportSubscriptionResource::collection($subscriptions->items()),
            'meta' => [
                'page' => $subscriptions->currentPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
                'last_page' => $subscriptions->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreReportSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->reportingSubscriptionService->createSubscription($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Report subscription created successfully.',
            new ReportSubscriptionResource($subscription),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $reportSubscriptionId): JsonResponse
    {
        $subscription = $this->reportingSubscriptionService->showSubscription(request()->user(), $reportSubscriptionId);

        $payload = ApiResponse::success(
            'Report subscription loaded successfully.',
            new ReportSubscriptionResource($subscription),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateReportSubscriptionRequest $request, int $reportSubscriptionId): JsonResponse
    {
        $subscription = $this->reportingSubscriptionService->updateSubscription($request->user(), $reportSubscriptionId, $request->validated());

        $payload = ApiResponse::success(
            'Report subscription updated successfully.',
            new ReportSubscriptionResource($subscription),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function destroy(int $reportSubscriptionId): JsonResponse
    {
        $subscription = $this->reportingSubscriptionService->revokeSubscription(request()->user(), $reportSubscriptionId);

        $payload = ApiResponse::success(
            'Report subscription revoked successfully.',
            new ReportSubscriptionResource($subscription),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function deliver(Request $request, int $reportSubscriptionId): JsonResponse
    {
        $subscription = $this->reportingSubscriptionService->deliverSubscription($request->user(), $reportSubscriptionId);

        $message = $subscription->last_delivery_status === 'completed'
            ? 'Report subscription delivered successfully.'
            : 'Report subscription is currently blocked.';

        $payload = ApiResponse::success(
            $message,
            new ReportSubscriptionResource($subscription),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
