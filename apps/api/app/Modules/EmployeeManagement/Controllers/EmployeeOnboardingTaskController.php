<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeOnboardingTaskRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeOnboardingTaskRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeOnboardingTaskResource;
use App\Modules\EmployeeManagement\Services\EmployeeOnboardingService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeOnboardingTaskController
{
    public function __construct(private readonly EmployeeOnboardingService $employeeOnboardingService) {}

    public function index(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        [$tasks, $summary] = $this->employeeOnboardingService->listForEmployee(
            $employee,
            $request->user(),
            EmployeeOnboardingService::DEFAULT_LIFECYCLE_TYPE,
        );

        $payload = ApiResponse::success(
            'Employee onboarding tasks loaded successfully.',
            [
                'items' => EmployeeOnboardingTaskResource::collection($tasks),
                'summary' => $summary,
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeOnboardingTaskRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $task = $this->employeeOnboardingService->create(
            $employee,
            $request->user(),
            $request->validated(),
            EmployeeOnboardingService::DEFAULT_LIFECYCLE_TYPE,
        );

        $payload = ApiResponse::success(
            'Employee onboarding task created successfully.',
            new EmployeeOnboardingTaskResource($task),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeOnboardingTaskRequest $request, int $employeeId, int $taskId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $task = $employee->onboardingTasks()->findOrFail($taskId);
        $task = $this->employeeOnboardingService->update(
            $employee,
            $task,
            $request->user(),
            $request->validated(),
            EmployeeOnboardingService::DEFAULT_LIFECYCLE_TYPE,
        );

        $payload = ApiResponse::success(
            'Employee onboarding task updated successfully.',
            new EmployeeOnboardingTaskResource($task),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function incompleteStatus(Request $request): JsonResponse
    {
        $employees = $this->employeeOnboardingService->listIncompleteStatuses(
            $request->user(),
            EmployeeOnboardingService::DEFAULT_LIFECYCLE_TYPE,
        );
        $data = $employees
            ->map(fn (Employee $employee): array => $this->employeeOnboardingService->summarizeEmployee(
                $employee,
                EmployeeOnboardingService::DEFAULT_LIFECYCLE_TYPE,
            ))
            ->values()
            ->all();

        $payload = ApiResponse::success(
            'Incomplete onboarding status loaded successfully.',
            $data,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function lifecycleIndex(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $lifecycleType = $this->resolveLifecycleType($request);
        [$tasks, $summary] = $this->employeeOnboardingService->listForEmployee($employee, $request->user(), $lifecycleType);

        $payload = ApiResponse::success(
            'Employee lifecycle tasks loaded successfully.',
            [
                'items' => EmployeeOnboardingTaskResource::collection($tasks),
                'summary' => $summary,
                'lifecycle_type' => $lifecycleType,
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function lifecycleStore(StoreEmployeeOnboardingTaskRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $task = $this->employeeOnboardingService->create(
            $employee,
            $request->user(),
            $request->validated(),
            $this->resolveLifecycleType($request),
        );

        $payload = ApiResponse::success(
            'Employee lifecycle task created successfully.',
            new EmployeeOnboardingTaskResource($task),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function lifecycleUpdate(UpdateEmployeeOnboardingTaskRequest $request, int $employeeId, int $taskId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $lifecycleType = $this->resolveLifecycleType($request);
        $task = $employee->lifecycleTasks()
            ->where('lifecycle_type', $lifecycleType)
            ->findOrFail($taskId);

        $task = $this->employeeOnboardingService->update(
            $employee,
            $task,
            $request->user(),
            $request->validated(),
            $lifecycleType,
        );

        $payload = ApiResponse::success(
            'Employee lifecycle task updated successfully.',
            new EmployeeOnboardingTaskResource($task),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function lifecycleStatus(Request $request): JsonResponse
    {
        $lifecycleType = $this->resolveLifecycleType($request);
        $employees = $this->employeeOnboardingService->listIncompleteStatuses($request->user(), $lifecycleType);
        $data = $employees
            ->map(fn (Employee $employee): array => $this->employeeOnboardingService->summarizeEmployee($employee, $lifecycleType))
            ->values()
            ->all();

        $payload = ApiResponse::success(
            'Incomplete lifecycle task status loaded successfully.',
            $data,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    private function resolveLifecycleType(Request $request): string
    {
        $validated = $request->validate([
            'lifecycle_type' => ['required', 'in:onboarding,offboarding'],
        ]);

        return (string) $validated['lifecycle_type'];
    }
}
