<?php

namespace App\Modules\Platform\Tenancy;

use App\Models\Company;

class TenantContext
{
    public function __construct(
        public readonly ?int $companyId,
        public readonly ?string $companyName,
        public readonly ?string $subscriptionPlan,
        public readonly ?string $timezone,
        public readonly ?string $currency,
    ) {}

    public static function empty(): self
    {
        return new self(
            companyId: null,
            companyName: null,
            subscriptionPlan: null,
            timezone: null,
            currency: null,
        );
    }

    public static function fromCompany(Company $company): self
    {
        return new self(
            companyId: $company->id,
            companyName: $company->name,
            subscriptionPlan: $company->subscription_plan,
            timezone: $company->timezone,
            currency: $company->currency,
        );
    }

    public function isResolved(): bool
    {
        return $this->companyId !== null;
    }
}
