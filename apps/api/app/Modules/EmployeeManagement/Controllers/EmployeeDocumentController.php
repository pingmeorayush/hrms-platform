<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Models\Employee;
use App\Modules\EmployeeManagement\Requests\StoreEmployeeDocumentRequest;
use App\Modules\EmployeeManagement\Resources\EmployeeDocumentResource;
use App\Modules\EmployeeManagement\Services\EmployeeDocumentService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController
{
    public function __construct(private readonly EmployeeDocumentService $employeeDocumentService) {}

    public function index(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $documents = $this->employeeDocumentService->listForEmployee($employee, $request->user());

        $payload = ApiResponse::success(
            'Employee documents loaded successfully.',
            EmployeeDocumentResource::collection($documents),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreEmployeeDocumentRequest $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $document = $this->employeeDocumentService->create(
            $employee,
            $request->user(),
            $request->file('file'),
            $request->validated(),
        );

        $payload = ApiResponse::success(
            'Employee document uploaded successfully.',
            new EmployeeDocumentResource($document),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function download(Request $request, int $employeeId, int $documentId): StreamedResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $document = $employee->documents()->findOrFail($documentId);

        return $this->employeeDocumentService->download($document, $request->user());
    }
}
