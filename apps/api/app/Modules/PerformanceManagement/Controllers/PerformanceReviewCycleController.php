<?php

namespace App\Modules\PerformanceManagement\Controllers;

use App\Modules\PerformanceManagement\Requests\ListPerformanceReviewCyclesRequest;
use App\Modules\PerformanceManagement\Requests\StorePerformanceReviewCycleRequest;
use App\Modules\PerformanceManagement\Requests\UpdatePerformanceReviewCycleRequest;
use App\Modules\PerformanceManagement\Resources\PerformanceReviewCycleResource;
use App\Modules\PerformanceManagement\Services\PerformanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PerformanceReviewCycleController
{
    public function __construct(private readonly PerformanceConfigurationService $performanceConfigurationService) {}

    public function index(ListPerformanceReviewCyclesRequest $request): JsonResponse
    {
        $cycles = $this->performanceConfigurationService->searchReviewCycles($request->user(), $request->validated());

        $payload = ApiResponse::success('Performance review cycles loaded successfully.', [
            'items' => PerformanceReviewCycleResource::collection($cycles->items()),
            'meta' => [
                'page' => $cycles->currentPage(),
                'per_page' => $cycles->perPage(),
                'total' => $cycles->total(),
                'last_page' => $cycles->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePerformanceReviewCycleRequest $request): JsonResponse
    {
        $cycle = $this->performanceConfigurationService->createReviewCycle($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Performance review cycle created successfully.',
            new PerformanceReviewCycleResource($cycle),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $performanceReviewCycleId): JsonResponse
    {
        $cycle = $this->performanceConfigurationService->findReviewCycleForView(request()->user(), $performanceReviewCycleId);

        $payload = ApiResponse::success(
            'Performance review cycle loaded successfully.',
            new PerformanceReviewCycleResource($cycle),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePerformanceReviewCycleRequest $request, int $performanceReviewCycleId): JsonResponse
    {
        $cycle = $this->performanceConfigurationService->findReviewCycleForView($request->user(), $performanceReviewCycleId);
        $cycle = $this->performanceConfigurationService->updateReviewCycle($request->user(), $cycle, $request->validated());

        $payload = ApiResponse::success(
            'Performance review cycle updated successfully.',
            new PerformanceReviewCycleResource($cycle),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
