<?php

namespace App\Modules\Platform\Audit\Controllers;

use App\Modules\Platform\Audit\Resources\AuditLogResource;
use App\Modules\Platform\Audit\Services\AuditHistoryService;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationAuditHistoryController
{
    public function __construct(private readonly AuditHistoryService $auditHistoryService) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => ['sometimes', Rule::in(['company', 'department', 'designation', 'location', 'cost_center'])],
            'entity_id' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $logs = $this->auditHistoryService->paginateOrganizationHistory(
            filters: $validated,
            perPage: (int) min($request->integer('per_page', 25), 100),
        );

        $payload = ApiResponse::success(
            'Organization audit history loaded successfully.',
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
