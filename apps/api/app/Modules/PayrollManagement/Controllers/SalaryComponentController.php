<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Models\SalaryComponent;
use App\Modules\PayrollManagement\Requests\StoreSalaryComponentRequest;
use App\Modules\PayrollManagement\Requests\UpdateSalaryComponentRequest;
use App\Modules\PayrollManagement\Resources\SalaryComponentResource;
use App\Modules\PayrollManagement\Services\SalaryConfigurationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class SalaryComponentController
{
    public function __construct(private readonly SalaryConfigurationService $salaryConfigurationService) {}

    public function index(): JsonResponse
    {
        $components = SalaryComponent::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Salary components loaded successfully.',
            SalaryComponentResource::collection($components),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreSalaryComponentRequest $request): JsonResponse
    {
        $component = $this->salaryConfigurationService->createComponent($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Salary component created successfully.',
            new SalaryComponentResource($component),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateSalaryComponentRequest $request, int $salaryComponentId): JsonResponse
    {
        $component = SalaryComponent::query()->findOrFail($salaryComponentId);
        $component = $this->salaryConfigurationService->updateComponent($request->user(), $component, $request->validated());

        $payload = ApiResponse::success(
            'Salary component updated successfully.',
            new SalaryComponentResource($component),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
