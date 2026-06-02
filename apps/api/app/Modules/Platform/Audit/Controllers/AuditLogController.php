<?php

namespace App\Modules\Platform\Audit\Controllers;

use App\Models\AuditLog;
use App\Modules\Platform\Audit\Resources\AuditLogResource;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController
{
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when(
                $request->filled('event_type'),
                fn ($query) => $query->where('event_type', $request->string('event_type')->toString()),
            )
            ->latest('created_at')
            ->paginate((int) min($request->integer('per_page', 25), 100));

        $payload = ApiResponse::success(
            'Audit logs loaded successfully.',
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
