<?php

namespace App\Modules\LearningManagement\Controllers;

use App\Modules\LearningManagement\Requests\ListLearningItemsRequest;
use App\Modules\LearningManagement\Requests\StoreLearningItemRequest;
use App\Modules\LearningManagement\Requests\UpdateLearningItemRequest;
use App\Modules\LearningManagement\Resources\LearningItemResource;
use App\Modules\LearningManagement\Services\LearningManagementService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class LearningItemController
{
    public function __construct(private readonly LearningManagementService $learningManagementService) {}

    public function index(ListLearningItemsRequest $request): JsonResponse
    {
        $items = $this->learningManagementService->searchItems($request->user(), $request->validated());

        $payload = ApiResponse::success('Learning items loaded successfully.', [
            'items' => LearningItemResource::collection($items->items()),
            'meta' => [
                'page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreLearningItemRequest $request): JsonResponse
    {
        $item = $this->learningManagementService->createItem($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Learning item created successfully.',
            new LearningItemResource($item),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $learningItemId): JsonResponse
    {
        $item = $this->learningManagementService->findItemForView(request()->user(), $learningItemId);

        $payload = ApiResponse::success(
            'Learning item loaded successfully.',
            new LearningItemResource($item),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateLearningItemRequest $request, int $learningItemId): JsonResponse
    {
        $item = $this->learningManagementService->findItemForView($request->user(), $learningItemId);
        $item = $this->learningManagementService->updateItem($request->user(), $item, $request->validated());

        $payload = ApiResponse::success(
            'Learning item updated successfully.',
            new LearningItemResource($item),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
