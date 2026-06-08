<?php

namespace App\Modules\PayrollManagement\Controllers;

use App\Modules\PayrollManagement\Requests\ListEmployeeCompensationsRequest;
use App\Modules\PayrollManagement\Requests\StoreEmployeeCompensationRequest;
use App\Modules\PayrollManagement\Resources\EmployeeCompensationResource;
use App\Modules\PayrollManagement\Services\EmployeeCompensationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeCompensationController
{
    public function __construct(private readonly EmployeeCompensationService $employeeCompensationService) {}

    public function index(ListEmployeeCompensationsRequest $request): JsonResponse
    {
        $compensations = $this->employeeCompensationService->listCompensations($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee compensations loaded successfully.',
            EmployeeCompensationResource::collection($compensations),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(ListEmployeeCompensationsRequest $request, int $employeeId): JsonResponse
    {
        $detail = $this->employeeCompensationService->showEmployeeCompensations($request->user(), $employeeId);

        $payload = ApiResponse::success(
            'Employee compensation detail loaded successfully.',
            [
                'employee' => $detail['employee'],
                'current_assignment' => $detail['current_assignment'] !== null
                    ? new EmployeeCompensationResource($detail['current_assignment'])
                    : null,
                'history' => EmployeeCompensationResource::collection($detail['history']),
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeCompensationRequest $request): JsonResponse
    {
        $compensation = $this->employeeCompensationService->assignCompensation($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee compensation assigned successfully.',
            new EmployeeCompensationResource($compensation),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
