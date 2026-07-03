<?php

namespace App\Modules\Platform\Admin\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AccessAdministrationService
{
    public function canManageRoleDefinitions(User $actor): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $actor::class)
            ->where('model_has_roles.model_id', $actor->getKey())
            ->where('roles.name', 'platform.super_admin')
            ->exists();
    }

    /**
     * @return Collection<int, Role>
     */
    public function visibleRolesFor(User $actor): Collection
    {
        return Role::query()
            ->with('permissions')
            ->when(
                ! $this->canManageRoleDefinitions($actor),
                fn ($query) => $query->where('name', 'not like', 'platform.%'),
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<string>
     */
    public function assignableRoleNamesFor(User $actor): array
    {
        return Role::query()
            ->when(
                ! $this->canManageRoleDefinitions($actor),
                fn ($query) => $query->where('name', 'not like', 'platform.%'),
            )
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function scopeManageableUsers(Builder $query, User $actor): Builder
    {
        if ($this->canManageRoleDefinitions($actor)) {
            return $query;
        }

        return $query->whereDoesntHave('roles', function (Builder $roleQuery): void {
            $roleQuery->where('name', 'like', 'platform.%');
        });
    }

    public function canManageTargetUser(User $actor, User $target): bool
    {
        if ($this->canManageRoleDefinitions($actor)) {
            return true;
        }

        return ! $target->roles()->where('name', 'like', 'platform.%')->exists();
    }

    /**
     * @param  list<string>  $requestedRoleNames
     * @return list<string>
     */
    public function validateAssignableRoleNames(User $actor, array $requestedRoleNames): array
    {
        $requested = collect($requestedRoleNames)
            ->map(static fn (mixed $roleName): string => trim((string) $roleName))
            ->filter()
            ->unique()
            ->values();

        $assignable = collect($this->assignableRoleNamesFor($actor));
        $disallowed = $requested->diff($assignable)->values();

        if ($disallowed->isNotEmpty()) {
            throw ValidationException::withMessages([
                'roles' => ['One or more selected roles are not assignable in this session.'],
            ]);
        }

        /** @var list<string> $validated */
        $validated = $requested->all();

        return $validated;
    }
}
