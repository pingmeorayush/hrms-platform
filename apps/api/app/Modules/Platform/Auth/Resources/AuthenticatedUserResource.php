<?php

namespace App\Modules\Platform\Auth\Resources;

use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tenantContext = app(TenantContext::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => $this->initials,
            'email' => $this->email,
            'roles' => $this->getRoleNames()->values()->all(),
            'permissions' => $this->getAllPermissions()->pluck('name')->sort()->values()->all(),
            'tenant' => [
                'company_id' => $tenantContext->companyId,
                'company_name' => $tenantContext->companyName,
                'subscription_plan' => $tenantContext->subscriptionPlan,
                'timezone' => $tenantContext->timezone,
                'currency' => $tenantContext->currency,
            ],
        ];
    }
}
