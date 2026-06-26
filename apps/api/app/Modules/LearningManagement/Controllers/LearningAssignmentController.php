<?php

namespace App\Modules\LearningManagement\Controllers;

use App\Modules\LearningManagement\Requests\ListLearningAssignmentsRequest;
use App\Modules\LearningManagement\Requests\StoreLearningAssignmentRequest;
use App\Modules\LearningManagement\Resources\LearningAssignmentResource;
use App\Modules\LearningManagement\Services\LearningManagementService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LearningAssignmentController
{
    public function __construct(private readonly LearningManagementService $learningManagementService) {}

    public function index(ListLearningAssignmentsRequest $request): JsonResponse
    {
        $assignments = $this->learningManagementService->searchAssignments($request->user(), $request->validated());

        $payload = ApiResponse::success('Learning assignments loaded successfully.', [
            'items' => LearningAssignmentResource::collection($assignments->items()),
            'meta' => [
                'page' => $assignments->currentPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
                'last_page' => $assignments->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLearningAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->learningManagementService->createAssignment($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Learning assignment created successfully.',
            new LearningAssignmentResource($assignment),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $learningAssignmentId): JsonResponse
    {
        $assignment = $this->learningManagementService->findAssignmentForView(request()->user(), $learningAssignmentId);

        $payload = ApiResponse::success(
            'Learning assignment loaded successfully.',
            new LearningAssignmentResource($assignment),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
