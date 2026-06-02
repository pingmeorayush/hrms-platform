<?php

namespace App\Modules\Platform\Workflow\Controllers;

use App\Models\WorkflowInstance;
use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\Platform\Workflow\Requests\StoreWorkflowInstanceRequest;
use App\Modules\Platform\Workflow\Resources\WorkflowInstanceResource;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowInstanceController
{
    public function __construct(private readonly WorkflowService $workflowService) {}

    public function index(Request $request): JsonResponse
    {
        $instances = WorkflowInstance::query()
            ->with(['definition', 'tasks.assignee', 'starter'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString()),
            )
            ->when(
                $request->filled('reference_type'),
                fn ($query) => $query->where('reference_type', $request->string('reference_type')->toString()),
            )
            ->latest()
            ->paginate((int) min($request->integer('per_page', 25), 100));

        $payload = ApiResponse::success(
            'Workflow instances loaded successfully.',
            [
                'items' => WorkflowInstanceResource::collection($instances->getCollection()),
                'meta' => [
                    'page' => $instances->currentPage(),
                    'per_page' => $instances->perPage(),
                    'total' => $instances->total(),
                    'last_page' => $instances->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreWorkflowInstanceRequest $request): JsonResponse
    {
        $instance = $this->workflowService->startInstance($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Workflow instance started successfully.',
            new WorkflowInstanceResource($instance),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
