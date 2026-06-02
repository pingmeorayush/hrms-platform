<?php

namespace App\Modules\OrganizationManagement\Controllers;

use App\Models\CostCenter;
use App\Modules\OrganizationManagement\Requests\StoreCostCenterRequest;
use App\Modules\OrganizationManagement\Requests\UpdateCostCenterRequest;
use App\Modules\OrganizationManagement\Resources\CostCenterResource;
use App\Modules\OrganizationManagement\Services\OrganizationStructureService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class CostCenterController
{
    public function __construct(private readonly OrganizationStructureService $organizationStructureService) {}

    public function index(): JsonResponse
    {
        $costCenters = CostCenter::query()->orderBy('name')->get();

        $payload = ApiResponse::success(
            'Cost centers loaded successfully.',
            CostCenterResource::collection($costCenters),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreCostCenterRequest $request): JsonResponse
    {
        $costCenter = $this->organizationStructureService->createMasterRecord(
            $request->user(),
            CostCenter::class,
            $request->validated(),
            'organization.cost_center.created',
            'cost_center',
        );

        $payload = ApiResponse::success(
            'Cost center created successfully.',
            new CostCenterResource($costCenter),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateCostCenterRequest $request, int $costCenterId): JsonResponse
    {
        $costCenter = CostCenter::query()->findOrFail($costCenterId);

        $costCenter = $this->organizationStructureService->updateMasterRecord(
            $request->user(),
            $costCenter,
            $request->validated(),
            'organization.cost_center.updated',
            'cost_center',
            ['code', 'name', 'description', 'status'],
        );

        $payload = ApiResponse::success(
            'Cost center updated successfully.',
            new CostCenterResource($costCenter),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
