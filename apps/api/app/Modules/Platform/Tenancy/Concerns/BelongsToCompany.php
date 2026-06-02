<?php

namespace App\Modules\Platform\Tenancy\Concerns;

use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $context = app(TenantContext::class);

            if ($context->isResolved()) {
                $builder->where($builder->qualifyColumn('company_id'), $context->companyId);
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);

            if ($context->isResolved() && empty($model->company_id)) {
                $model->company_id = $context->companyId;
            }
        });
    }
}
