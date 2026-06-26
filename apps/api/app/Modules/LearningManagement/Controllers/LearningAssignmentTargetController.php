<?php

namespace App\Modules\LearningManagement\Controllers;

use App\Modules\LearningManagement\Requests\CompleteLearningAssignmentTargetRequest;
use App\Modules\LearningManagement\Requests\ListLearningTargetsRequest;
use App\Modules\LearningManagement\Resources\LearningAssignmentTargetResource;
use App\Modules\LearningManagement\Services\LearningManagementService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LearningAssignmentTargetController
{
    public function __construct(private readonly LearningManagementService $learningManagementService) {}

    public function index(ListLearningTargetsRequest $request): JsonResponse
    {
        $targets = $this->learningManagementService->searchTargets($request->user(), $request->validated());

        $payload = ApiResponse::success('Learning assignment targets loaded successfully.', [
            'items' => LearningAssignmentTargetResource::collection($targets->items()),
            'meta' => [
                'page' => $targets->currentPage(),
                'per_page' => $targets->perPage(),
                'total' => $targets->total(),
                'last_page' => $targets->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function mine(ListLearningTargetsRequest $request): JsonResponse
    {
        $targets = $this->learningManagementService->searchMyTargets($request->user(), $request->validated());

        $payload = ApiResponse::success('My learning assignments loaded successfully.', [
            'items' => LearningAssignmentTargetResource::collection($targets->items()),
            'meta' => [
                'page' => $targets->currentPage(),
                'per_page' => $targets->perPage(),
                'total' => $targets->total(),
                'last_page' => $targets->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $learningAssignmentTargetId): JsonResponse
    {
        $target = $this->learningManagementService->findTargetForView(request()->user(), $learningAssignmentTargetId);

        $payload = ApiResponse::success(
            'Learning assignment target loaded successfully.',
            new LearningAssignmentTargetResource($target),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function complete(CompleteLearningAssignmentTargetRequest $request, int $learningAssignmentTargetId): JsonResponse
    {
        $target = $this->learningManagementService->completeTarget(
            $request->user(),
            $learningAssignmentTargetId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Learning assignment completed successfully.',
            new LearningAssignmentTargetResource($target),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
