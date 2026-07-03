<?php

namespace App\Modules\Platform\Admin\Controllers;

use App\Models\User;
use App\Modules\Platform\Admin\Requests\StoreAdminUserRequest;
use App\Modules\Platform\Admin\Requests\UpdateAdminUserRequest;
use App\Modules\Platform\Admin\Resources\AdminUserResource;
use App\Modules\Platform\Admin\Services\AccessAdministrationService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Shared\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function __construct(
        private readonly AccessAdministrationService $accessAdministrationService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', 'all'));

        $users = $this->accessAdministrationService
            ->scopeManageableUsers(User::query(), $actor)
            ->with(['employee', 'roles'])
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }),
            )
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->get();

        $payload = ApiResponse::success(
            'Users loaded successfully.',
            AdminUserResource::collection($users),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $roleNames = $this->accessAdministrationService->validateAssignableRoleNames(
            $actor,
            $request->input('roles', []),
        );

        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'is_active' => $request->boolean('is_active', true),
            'requires_mfa' => $request->boolean('requires_mfa', false),
            'mfa_method' => $request->boolean('requires_mfa', false) ? 'email_otp' : null,
        ]);

        $user->syncRoles($roleNames);

        $this->auditLogger->record(
            eventType: 'auth.user.created',
            actor: $actor,
            metadata: [
                'user_id' => $user->id,
                'email' => $user->email,
                'roles' => $roleNames,
                'requires_mfa' => $user->requires_mfa,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            entityType: 'user',
            entityId: (string) $user->id,
        );

        $payload = ApiResponse::success(
            'User created successfully.',
            new AdminUserResource($user->load(['employee', 'roles'])),
            201,
        );

        return response()->json($payload['body'], $payload['status']);
    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->accessAdministrationService->canManageTargetUser($actor, $user)) {
            return $this->forbidden('This session cannot manage platform-level administrator accounts.');
        }

        $changes = [];

        if ($request->has('name')) {
            $user->name = $request->string('name')->toString();
            $changes['name'] = $user->name;
        }

        if ($request->has('email')) {
            $user->email = $request->string('email')->toString();
            $changes['email'] = $user->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->string('password')->toString());
            $changes['password'] = 'updated';
        }

        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
            $changes['is_active'] = $user->is_active;
        }

        if ($request->has('requires_mfa')) {
            $requiresMfa = $request->boolean('requires_mfa');
            $user->requires_mfa = $requiresMfa;
            $user->mfa_method = $requiresMfa ? 'email_otp' : null;

            if (! $requiresMfa) {
                $user->mfa_email_otp = null;
                $user->mfa_email_otp_expires_at = null;
                $user->mfa_confirmed_at = null;
            }

            $changes['requires_mfa'] = $user->requires_mfa;
        }

        $user->save();

        if ($request->has('roles')) {
            $roleNames = $this->accessAdministrationService->validateAssignableRoleNames(
                $actor,
                $request->input('roles', []),
            );
            $user->syncRoles($roleNames);
            $changes['roles'] = $roleNames;
        }

        $this->auditLogger->record(
            eventType: 'auth.user.updated',
            actor: $actor,
            metadata: [
                'user_id' => $user->id,
                'changes' => $changes,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            entityType: 'user',
            entityId: (string) $user->id,
        );

        $payload = ApiResponse::success(
            'User updated successfully.',
            new AdminUserResource($user->load(['employee', 'roles'])),
        );

        return response()->json($payload['body'], $payload['status']);
    }

    private function forbidden(string $message): JsonResponse
    {
        $payload = ApiResponse::error($message, [], 403);

        return response()->json($payload['body'], $payload['status']);
    }
}
