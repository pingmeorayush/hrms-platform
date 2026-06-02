<?php

namespace App\Modules\Platform\Workflow\Controllers;

use App\Models\WorkflowTask;
use App\Modules\Platform\Shared\Http\ApiResponse;
use App\Modules\Platform\Workflow\Requests\UpdateWorkflowTaskRequest;
use App\Modules\Platform\Workflow\Resources\WorkflowTaskResource;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowTaskController
{
    public function __construct(private readonly WorkflowService $workflowService) {}

    public function index(Request $request): JsonResponse
    {
        $query = WorkflowTask::query()
            ->with(['instance.definition', 'assignee', 'actor'])
            ->when(
                $request->filled('status'),
                fn ($builder) => $builder->where('status', $request->string('status')->toString()),
            );

        if (! $request->user()->can('workflow.monitor')) {
            $query->where('assigned_to_user_id', $request->user()->id);
        }

        $tasks = $query->orderBy('status')->orderBy('due_at')->orderBy('id')->get();

        $payload = ApiResponse::success(
            'Workflow tasks loaded successfully.',
            WorkflowTaskResource::collection($tasks),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateWorkflowTaskRequest $request, WorkflowTask $task): JsonResponse
    {
        $task = $this->workflowService->decideTask($task, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Workflow task updated successfully.',
            new WorkflowTaskResource($task),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
