<?php

namespace App\Modules\IntegrationsPlatform\Requests\Concerns;

use App\Models\User;

trait AuthorizesIntegrationRequests
{
    protected function integrationUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    protected function canViewIntegrations(): bool
    {
        $user = $this->integrationUser();

        return $user?->canAny(['integration.view', 'integration.manage']) ?? false;
    }

    protected function canManageIntegrations(): bool
    {
        $user = $this->integrationUser();

        return $user?->can('integration.manage') ?? false;
    }
}
