<?php

namespace App\Modules\EmployeeManagement\Controllers;

use App\Modules\EmployeeManagement\Services\EmployeeSelfServiceWorkspaceService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeSelfServiceController
{
    public function __construct(
        private readonly EmployeeSelfServiceWorkspaceService $employeeSelfServiceWorkspaceService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $payload = ApiResponse::success(
            'Employee self-service workspace loaded successfully.',
            $this->employeeSelfServiceWorkspaceService->workspace($request->user()),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function downloadEmployeeDocument(Request $request, int $employeeDocumentId): StreamedResponse
    {
        return $this->employeeSelfServiceWorkspaceService->downloadEmployeeDocument(
            $request->user(),
            $employeeDocumentId,
        );
    }

    public function downloadRepositoryDocument(Request $request, int $documentId): StreamedResponse
    {
        return $this->employeeSelfServiceWorkspaceService->downloadRepositoryDocument(
            $request->user(),
            $documentId,
        );
    }
}
