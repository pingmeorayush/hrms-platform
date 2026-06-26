<?php

namespace App\Modules\PerformanceManagement\Controllers;

use App\Modules\PerformanceManagement\Requests\ListPerformanceGoalsRequest;
use App\Modules\PerformanceManagement\Requests\StorePerformanceGoalRequest;
use App\Modules\PerformanceManagement\Requests\UpdatePerformanceGoalRequest;
use App\Modules\PerformanceManagement\Resources\PerformanceGoalResource;
use App\Modules\PerformanceManagement\Services\PerformanceConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PerformanceGoalController
{
    public function __construct(private readonly PerformanceConfigurationService $performanceConfigurationService) {}

    public function index(ListPerformanceGoalsRequest $request): JsonResponse
    {
        $goals = $this->performanceConfigurationService->searchGoals($request->user(), $request->validated());

        $payload = ApiResponse::success('Performance goals loaded successfully.', [
            'items' => PerformanceGoalResource::collection($goals->items()),
            'meta' => [
                'page' => $goals->currentPage(),
                'per_page' => $goals->perPage(),
                'total' => $goals->total(),
                'last_page' => $goals->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StorePerformanceGoalRequest $request): JsonResponse
    {
        $goal = $this->performanceConfigurationService->createGoal($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Performance goal created successfully.',
            new PerformanceGoalResource($goal),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $performanceGoalId): JsonResponse
    {
        $goal = $this->performanceConfigurationService->findGoalForView(request()->user(), $performanceGoalId);

        $payload = ApiResponse::success(
            'Performance goal loaded successfully.',
            new PerformanceGoalResource($goal),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdatePerformanceGoalRequest $request, int $performanceGoalId): JsonResponse
    {
        $goal = $this->performanceConfigurationService->findGoalForView($request->user(), $performanceGoalId);
        $goal = $this->performanceConfigurationService->updateGoal($request->user(), $goal, $request->validated());

        $payload = ApiResponse::success(
            'Performance goal updated successfully.',
            new PerformanceGoalResource($goal),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
