<?php

namespace App\Modules\Platform\Admin\Controllers;

use App\Modules\Platform\Admin\Requests\StoreRoleRequest;
use App\Modules\Platform\Admin\Resources\RoleResource;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Roles loaded successfully.',
            RoleResource::collection($roles),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::query()->create([
            'name' => $request->string('name')->toString(),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->input('permissions', []));

        $this->auditLogger->record(
            eventType: 'auth.role.created',
            actor: $request->user(),
            metadata: [
                'role' => $role->name,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            entityType: 'role',
            entityId: (string) $role->id,
        );

        $payload = ApiResponse::success(
            'Role created successfully.',
            new RoleResource($role->load('permissions')),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }
}
