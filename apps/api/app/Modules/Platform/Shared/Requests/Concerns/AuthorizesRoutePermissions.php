<?php

namespace App\Modules\Platform\Shared\Requests\Concerns;

use App\Models\User;
use Illuminate\Routing\Route;

trait AuthorizesRoutePermissions
{
    protected function authorizeFromRoutePermissions(): bool
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        $permissions = $this->resolveRoutePermissions();

        if ($permissions === []) {
            return true;
        }

        return $user->canAny($permissions);
    }

    /**
     * @return list<string>
     */
    protected function resolveRoutePermissions(): array
    {
        $route = $this->route();

        if (! $route instanceof Route) {
            return [];
        }

        $permissions = [];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! str_starts_with($middleware, 'permission:')) {
                continue;
            }

            $configuredPermissions = explode('|', explode(',', substr($middleware, 11))[0]);

            foreach ($configuredPermissions as $permission) {
                $normalizedPermission = trim($permission);

                if ($normalizedPermission === '') {
                    continue;
                }

                $permissions[$normalizedPermission] = true;
            }
        }

        return array_keys($permissions);
    }
}
