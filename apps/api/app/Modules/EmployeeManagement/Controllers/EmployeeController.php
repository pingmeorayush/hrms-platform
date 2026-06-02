<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Modules\EmployeeManagement\Requests\ListEmployeesRequest;
use App\Modules\EmployeeManagement\Requests\PromoteEmployeeRequest;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeRequest;
use App\Modules\EmployeeManagement\Requests\TerminateEmployeeRequest;
use App\Modules\EmployeeManagement\Requests\TransferEmployeeRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeResource;
use App\Modules\EmployeeManagement\Services\EmployeeDirectoryService;
use App\Modules\EmployeeManagement\Services\EmployeeService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeController
{
    public function __construct(
        private readonly EmployeeDirectoryService $employeeDirectoryService,
        private readonly EmployeeService $employeeService,
    ) {}

    public function index(ListEmployeesRequest $request): JsonResponse
    {
        $employees = $this->employeeDirectoryService->search($request->validated());

        $payload = ApiResponse::success(
            'Employees loaded successfully.',
            [
                'items' => EmployeeResource::collection($employees->getCollection()),
                'meta' => [
                    'page' => $employees->currentPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'last_page' => $employees->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee created successfully.',
            new EmployeeResource($employee),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function show(int $employeeId): JsonResponse
    {
        $employee = Employee::query()
            ->with(['department', 'designation', 'manager', 'location', 'costCenter'])
            ->findOrFail($employeeId);

        $payload = ApiResponse::success(
            'Employee loaded successfully.',
            new EmployeeResource($employee),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $employee = $this->employeeService->update($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee updated successfully.',
            new EmployeeResource($employee),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function transfer(TransferEmployeeRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $employee = $this->employeeService->transfer($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee transfer recorded successfully.',
            new EmployeeResource($employee),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function promote(PromoteEmployeeRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $employee = $this->employeeService->promote($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee promotion recorded successfully.',
            new EmployeeResource($employee),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function terminate(TerminateEmployeeRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $employee = $this->employeeService->terminate($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee termination recorded successfully.',
            new EmployeeResource($employee),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
