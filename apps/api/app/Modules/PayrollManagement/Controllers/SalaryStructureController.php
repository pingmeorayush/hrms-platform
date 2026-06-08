<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\SalaryStructure;
use App\Modules\PayrollManagement\Requests\StoreSalaryStructureRequest;
use App\Modules\PayrollManagement\Requests\UpdateSalaryStructureRequest;
use App\Modules\PayrollManagement\Resources\SalaryStructureResource;
use App\Modules\PayrollManagement\Services\SalaryConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class SalaryStructureController
{
    public function __construct(private readonly SalaryConfigurationService $salaryConfigurationService) {}

    public function index(): JsonResponse
    {
        $structures = SalaryStructure::query()
            ->with(['components.salaryComponent'])
            ->orderBy('code')
            ->orderByDesc('version')
            ->get();

        $payload = ApiResponse::success(
            'Salary structures loaded successfully.',
            SalaryStructureResource::collection($structures),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreSalaryStructureRequest $request): JsonResponse
    {
        $structure = $this->salaryConfigurationService->createStructure($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Salary structure created successfully.',
            new SalaryStructureResource($structure),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateSalaryStructureRequest $request, int $salaryStructureId): JsonResponse
    {
        $structure = SalaryStructure::query()->findOrFail($salaryStructureId);
        $structure = $this->salaryConfigurationService->versionStructure($request->user(), $structure, $request->validated());

        $payload = ApiResponse::success(
            'Salary structure version created successfully.',
            new SalaryStructureResource($structure),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
