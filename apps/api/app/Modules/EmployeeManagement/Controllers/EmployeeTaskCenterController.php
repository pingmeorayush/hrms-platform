<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Modules\EmployeeManagement\Requests\UpdateEmployeeTaskCenterLifecycleTaskRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeOnboardingTaskResource;
use App\Modules\EmployeeManagement\Services\EmployeeTaskCenterService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeTaskCenterController
{
    public function __construct(private readonly EmployeeTaskCenterService $employeeTaskCenterService) {}

    public function index(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Employee task center loaded successfully.',
            $this->employeeTaskCenterService->overview($request->user()),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function updateLifecycleTask(UpdateEmployeeTaskCenterLifecycleTaskRequest $request, int $taskId): JsonResponse
    {
        $task = $this->employeeTaskCenterService->updateLifecycleTask(
            $request->user(),
            $taskId,
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Employee task center lifecycle task updated successfully.',
            new EmployeeOnboardingTaskResource($task),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
