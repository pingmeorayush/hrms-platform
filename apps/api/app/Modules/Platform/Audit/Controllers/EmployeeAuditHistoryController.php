<?php

namespace App\Modules\Platform\Audit\Controllers;

use App\Models\Employee;
use App\Modules\Platform\Audit\Resources\AuditLogResource;
use App\Modules\Platform\Audit\Services\AuditHistoryService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeAuditHistoryController
{
    public function __construct(private readonly AuditHistoryService $auditHistoryService) {}

    public function index(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $logs = $this->auditHistoryService->paginateEmployeeHistory(
            $employee,
            (int) min($request->integer('per_page', 25), 100),
        );

        $payload = ApiResponse::success(
            'Employee audit history loaded successfully.',
            [
                'items' => AuditLogResource::collection($logs->getCollection()),
                'meta' => [
                    'page' => $logs->currentPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'last_page' => $logs->lastPage(),
                ],
            ],
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
