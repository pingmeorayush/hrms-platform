<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Modules\EmployeeManagement\Requests\ApplyEmployeeLifecycleTaskTemplatesRequest;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeLifecycleTaskTemplateRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeLifecycleTaskTemplateRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeLifecycleTaskTemplateResource;
use App\Modules\EmployeeManagement\Resources\EmployeeOnboardingTaskResource;
use App\Modules\EmployeeManagement\Services\EmployeeLifecycleTaskTemplateService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeLifecycleTaskTemplateController
{
    public function __construct(private readonly EmployeeLifecycleTaskTemplateService $templateService) {}

    public function index(Request $request): JsonResponse
    {
        $templates = $this->templateService->list($request->only(['lifecycle_type', 'is_active']));

        $payload = ApiResponse::success(
            'Employee lifecycle task templates loaded successfully.',
            EmployeeLifecycleTaskTemplateResource::collection($templates),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeLifecycleTaskTemplateRequest $request): JsonResponse
    {
        $template = $this->templateService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee lifecycle task template created successfully.',
            new EmployeeLifecycleTaskTemplateResource($template),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeLifecycleTaskTemplateRequest $request, EmployeeLifecycleTaskTemplate $template): JsonResponse
    {
        $template = $this->templateService->update($template, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee lifecycle task template updated successfully.',
            new EmployeeLifecycleTaskTemplateResource($template),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function apply(ApplyEmployeeLifecycleTaskTemplatesRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $tasks = $this->templateService->apply($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee lifecycle task templates applied successfully.',
            EmployeeOnboardingTaskResource::collection($tasks),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
