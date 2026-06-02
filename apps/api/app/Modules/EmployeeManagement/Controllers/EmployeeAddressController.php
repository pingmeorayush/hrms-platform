<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeAddressRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeAddressRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeAddressResource;
use App\Modules\EmployeeManagement\Services\EmployeeProfileDetailService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeAddressController
{
    public function __construct(private readonly EmployeeProfileDetailService $employeeProfileDetailService) {}

    public function index(int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $addresses = $this->employeeProfileDetailService->list($employee, 'addresses', ['type' => 'asc', 'id' => 'asc']);

        $payload = ApiResponse::success(
            'Employee addresses loaded successfully.',
            EmployeeAddressResource::collection($addresses),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeAddressRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $address = $this->employeeProfileDetailService->create(
            $employee,
            $request->user(),
            EmployeeAddress::class,
            $request->validated(),
            'employee.address.created',
            'employee_address',
        );

        $payload = ApiResponse::success(
            'Employee address created successfully.',
            new EmployeeAddressResource($address),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeAddressRequest $request, int $employeeId, int $addressId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $address = $employee->addresses()->findOrFail($addressId);

        $address = $this->employeeProfileDetailService->update(
            $employee,
            $address,
            $request->user(),
            $request->validated(),
            'employee.address.updated',
            'employee_address',
            ['type', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code', 'notes'],
        );

        $payload = ApiResponse::success(
            'Employee address updated successfully.',
            new EmployeeAddressResource($address),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
