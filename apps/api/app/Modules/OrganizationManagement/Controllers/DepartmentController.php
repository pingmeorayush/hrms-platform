<?php

namespace App\Modules\OrganizationManagement\Controllers;

use App\Models\Department;
use App\Modules\OrganizationManagement\Requests\StoreDepartmentRequest;
use App\Modules\OrganizationManagement\Requests\UpdateDepartmentRequest;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Services\OrganizationStructureService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class DepartmentController
{
    public function __construct(private readonly OrganizationStructureService $organizationStructureService) {}

    public function index(): JsonResponse
    {
        $departments = Department::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Departments loaded successfully.',
            DepartmentResource::collection($departments),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->organizationStructureService->createMasterRecord(
            $request->user(),
            Department::class,
            $request->validated(),
            'organization.department.created',
            'department',
        );

        $payload = ApiResponse::success(
            'Department created successfully.',
            new DepartmentResource($department),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateDepartmentRequest $request, int $departmentId): JsonResponse
    {
        $department = Department::query()->findOrFail($departmentId);

        $department = $this->organizationStructureService->updateMasterRecord(
            $request->user(),
            $department,
            $request->validated(),
            'organization.department.updated',
            'department',
            ['code', 'name', 'description', 'status'],
        );

        $payload = ApiResponse::success(
            'Department updated successfully.',
            new DepartmentResource($department),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
