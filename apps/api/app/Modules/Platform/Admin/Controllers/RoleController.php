<?php

namespace App\Modules\Platform\Admin\Controllers;

use App\Models\User;
use App\Modules\Platform\Admin\Requests\StoreRoleRequest;
use App\Modules\Platform\Admin\Requests\UpdateRoleRequest;
use App\Modules\Platform\Admin\Resources\RoleResource;
use App\Modules\Platform\Admin\Services\AccessAdministrationService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController
{
    public function __construct(
        private readonly AccessAdministrationService $accessAdministrationService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $roles = $this->accessAdministrationService->visibleRolesFor($actor);

        $payload = ApiResponse::success(
            'Roles loaded successfully.',
            RoleResource::collection($roles),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        if (! $this->accessAdministrationService->canManageRoleDefinitions($request->user())) {
            return $this->forbidden('Only platform super admins can change shared role definitions.');
        }

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

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if (! $this->accessAdministrationService->canManageRoleDefinitions($request->user())) {
            return $this->forbidden('Only platform super admins can change shared role definitions.');
        }

        $role->syncPermissions($request->input('permissions', []));

        $this->auditLogger->record(
            eventType: 'auth.role.updated',
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
            'Role updated successfully.',
            new RoleResource($role->load('permissions')),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    private function forbidden(string $message): JsonResponse
    {
        $payload = ApiResponse::error($message, [], 403);

        return response()->json($payload['body'], $payload['status']);
    }
}
