<?php

namespace App\Modules\Platform\Workflow\Controllers;

use App\Models\WorkflowDefinition;
use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\Platform\Workflow\Requests\StoreWorkflowDefinitionRequest;
use App\Modules\Platform\Workflow\Requests\UpdateWorkflowDefinitionRequest;
use App\Modules\Platform\Workflow\Resources\WorkflowDefinitionResource;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Http\JsonResponse;

class WorkflowDefinitionController
{
    public function __construct(private readonly WorkflowService $workflowService) {}

    public function index(): JsonResponse
    {
        $definitions = WorkflowDefinition::query()
            ->with(['activeVersion.stages', 'versions.stages'])
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Workflow definitions loaded successfully.',
            WorkflowDefinitionResource::collection($definitions),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreWorkflowDefinitionRequest $request): JsonResponse
    {
        $definition = $this->workflowService->createDefinition($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Workflow definition created successfully.',
            new WorkflowDefinitionResource($definition),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateWorkflowDefinitionRequest $request, WorkflowDefinition $workflow): JsonResponse
    {
        $definition = $this->workflowService->updateDefinition($workflow, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Workflow definition updated successfully.',
            new WorkflowDefinitionResource($definition),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
