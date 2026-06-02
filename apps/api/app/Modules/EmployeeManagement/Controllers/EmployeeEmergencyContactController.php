<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeEmergencyContactRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeEmergencyContactRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeEmergencyContactResource;
use App\Modules\EmployeeManagement\Services\EmployeeProfileDetailService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeEmergencyContactController
{
    public function __construct(private readonly EmployeeProfileDetailService $employeeProfileDetailService) {}

    public function index(int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contacts = $this->employeeProfileDetailService->list($employee, 'emergencyContacts', ['priority' => 'asc', 'id' => 'asc']);

        $payload = ApiResponse::success(
            'Employee emergency contacts loaded successfully.',
            EmployeeEmergencyContactResource::collection($contacts),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeEmergencyContactRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contact = $this->employeeProfileDetailService->create(
            $employee,
            $request->user(),
            EmployeeEmergencyContact::class,
            $request->validated(),
            'employee.emergency_contact.created',
            'employee_emergency_contact',
        );

        $payload = ApiResponse::success(
            'Employee emergency contact created successfully.',
            new EmployeeEmergencyContactResource($contact),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeEmergencyContactRequest $request, int $employeeId, int $emergencyContactId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contact = $employee->emergencyContacts()->findOrFail($emergencyContactId);

        $contact = $this->employeeProfileDetailService->update(
            $employee,
            $contact,
            $request->user(),
            $request->validated(),
            'employee.emergency_contact.updated',
            'employee_emergency_contact',
            ['name', 'relationship', 'phone_number', 'email', 'address', 'priority', 'notes'],
        );

        $payload = ApiResponse::success(
            'Employee emergency contact updated successfully.',
            new EmployeeEmergencyContactResource($contact),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
