<?php

namespace App\Modules\Platform\Tenancy\Concerns;

use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

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

        static::saving(static function (Model $model): void {
            $context = app(TenantContext::class);

            if (! $context->isResolved()) {
                return;
            }

            $companyId = $model->getAttribute('company_id');

            if (empty($companyId)) {
                $model->setAttribute('company_id', $context->companyId);

                return;
            }

            if ((int) $companyId !== $context->companyId) {
                throw new LogicException(sprintf(
                    'Tenant integrity violation: attempted to persist %s with company_id=%d while tenant context is company_id=%d.',
                    $model::class,
                    (int) $companyId,
                    $context->companyId,
                ));
            }
        });
    }
}
