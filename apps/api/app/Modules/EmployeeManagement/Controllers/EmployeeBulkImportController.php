<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Modules\EmployeeManagement\Requests\ValidateEmployeeBulkImportRequest;
use App\Modules\EmployeeManagement\Services\EmployeeBulkImportValidationService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeBulkImportController
{
    public function __construct(private readonly EmployeeBulkImportValidationService $employeeBulkImportValidationService) {}

    public function validate(ValidateEmployeeBulkImportRequest $request): JsonResponse
    {
        $source = $request->hasFile('file') ? 'csv' : 'rows';
        $rows = $request->hasFile('file')
            ? $this->employeeBulkImportValidationService->parseCsv($request->file('file'))
            : $request->validated('rows');

        $result = $this->employeeBulkImportValidationService->validateRows(
            $request->user(),
            $rows,
            $source,
        );

        $payload = ApiResponse::success(
            'Employee bulk import validation completed successfully.',
            $result,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
