<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Models\EmployeeContact;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeContactRequest;
use App\Modules\EmployeeManagement\Requests\UpdateEmployeeContactRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeContactResource;
use App\Modules\EmployeeManagement\Services\EmployeeProfileDetailService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeContactController
{
    public function __construct(private readonly EmployeeProfileDetailService $employeeProfileDetailService) {}

    public function index(int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contacts = $this->employeeProfileDetailService->list($employee, 'contacts', ['type' => 'asc', 'id' => 'asc']);

        $payload = ApiResponse::success(
            'Employee contacts loaded successfully.',
            EmployeeContactResource::collection($contacts),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeContactRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contact = $this->employeeProfileDetailService->create(
            $employee,
            $request->user(),
            EmployeeContact::class,
            $request->validated(),
            'employee.contact.created',
            'employee_contact',
            function (Employee $employee, array $payload): void {
                if (($payload['is_primary'] ?? false) === true) {
                    $employee->contacts()->where('type', $payload['type'])->update(['is_primary' => false]);
                }
            },
        );

        $payload = ApiResponse::success(
            'Employee contact created successfully.',
            new EmployeeContactResource($contact),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateEmployeeContactRequest $request, int $employeeId, int $contactId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $contact = $employee->contacts()->findOrFail($contactId);

        $contact = $this->employeeProfileDetailService->update(
            $employee,
            $contact,
            $request->user(),
            $request->validated(),
            'employee.contact.updated',
            'employee_contact',
            ['type', 'label', 'value', 'is_primary', 'status', 'notes'],
            function (Employee $employee, array $payload, EmployeeContact $contact): void {
                if (($payload['is_primary'] ?? false) === true) {
                    $type = $payload['type'] ?? $contact->type;
                    $employee->contacts()
                        ->where('type', $type)
                        ->whereKeyNot($contact->id)
                        ->update(['is_primary' => false]);
                }
            },
        );

        $payload = ApiResponse::success(
            'Employee contact updated successfully.',
            new EmployeeContactResource($contact),
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
