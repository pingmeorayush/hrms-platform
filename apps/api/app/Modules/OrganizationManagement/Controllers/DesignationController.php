<?php

namespace App\Modules\OrganizationManagement\Controllers;

use App\Models\Designation;
use App\Modules\OrganizationManagement\Requests\StoreDesignationRequest;
use App\Modules\OrganizationManagement\Requests\UpdateDesignationRequest;
use App\Modules\OrganizationManagement\Resources\DesignationResource;
use App\Modules\OrganizationManagement\Services\OrganizationStructureService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class DesignationController
{
    public function __construct(private readonly OrganizationStructureService $organizationStructureService) {}

    public function index(): JsonResponse
    {
        $designations = Designation::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Designations loaded successfully.',
            DesignationResource::collection($designations),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $designation = $this->organizationStructureService->createMasterRecord(
            $request->user(),
            Designation::class,
            $request->validated(),
            'organization.designation.created',
            'designation',
        );

        $payload = ApiResponse::success(
            'Designation created successfully.',
            new DesignationResource($designation),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateDesignationRequest $request, int $designationId): JsonResponse
    {
        $designation = Designation::query()->findOrFail($designationId);

        $designation = $this->organizationStructureService->updateMasterRecord(
            $request->user(),
            $designation,
            $request->validated(),
            'organization.designation.updated',
            'designation',
            ['code', 'name', 'description', 'status'],
        );

        $payload = ApiResponse::success(
            'Designation updated successfully.',
            new DesignationResource($designation),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
