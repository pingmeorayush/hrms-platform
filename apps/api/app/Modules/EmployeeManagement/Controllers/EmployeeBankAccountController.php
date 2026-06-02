<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeBankAccountRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeBankAccountRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeBankAccountResource;
use App\Modules\EmployeeManagement\Services\EmployeeBankAccountService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeBankAccountController
{
    public function __construct(private readonly EmployeeBankAccountService $employeeBankAccountService) {}

    public function index(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $bankAccounts = $this->employeeBankAccountService->listForEmployee($employee, $request->user());

        $payload = ApiResponse::success(
            'Employee bank accounts loaded successfully.',
            [
                'items' => EmployeeBankAccountResource::collection($bankAccounts),
                'meta' => [
                    'total' => $bankAccounts->count(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeBankAccountRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $bankAccount = $this->employeeBankAccountService->create($employee, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee bank account created successfully.',
            new EmployeeBankAccountResource($bankAccount),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeBankAccountRequest $request, int $employeeId, int $bankAccountId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $bankAccount = $employee->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount = $this->employeeBankAccountService->update($employee, $bankAccount, $request->user(), $request->validated());

        $payload = ApiResponse::success(
            'Employee bank account updated successfully.',
            new EmployeeBankAccountResource($bankAccount),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
