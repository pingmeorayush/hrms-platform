<?php

namespace App\Modules\OrganizationManagement\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrganizationStructureService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateCompanyProfile(User $actor, Company $company, array $payload): Company
    {
        return DB::transaction(function () use ($actor, $company, $payload): Company {
            $before = $company->only(['name', 'subscription_plan', 'timezone', 'currency']);

            $company->fill($payload);
            $company->save();

            $this->auditLogger->record(
                eventType: 'organization.company.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $company->only(['name', 'subscription_plan', 'timezone', 'currency']),
                ],
                entityType: 'company',
                entityId: (string) $company->id,
            );

            return $company->refresh();
        });
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $payload
     */
    public function createMasterRecord(
        User $actor,
        string $modelClass,
        array $payload,
        string $eventType,
        string $entityType,
    ): Model {
        return DB::transaction(function () use ($actor, $modelClass, $payload, $eventType, $entityType): Model {
            /** @var Model $record */
            $record = $modelClass::query()->create($payload);

            $this->auditLogger->record(
                eventType: $eventType,
                actor: $actor,
                metadata: $record->only(['code', 'name', 'status']),
                entityType: $entityType,
                entityId: (string) $record->getKey(),
            );

            return $record->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $trackedFields
     */
    public function updateMasterRecord(
        User $actor,
        Model $record,
        array $payload,
        string $eventType,
        string $entityType,
        array $trackedFields,
    ): Model {
        return DB::transaction(function () use ($actor, $record, $payload, $eventType, $entityType, $trackedFields): Model {
            $before = $record->only($trackedFields);

            $record->fill($payload);
            $record->save();

            $this->auditLogger->record(
                eventType: $eventType,
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $record->only($trackedFields),
                ],
                entityType: $entityType,
                entityId: (string) $record->getKey(),
            );

            return $record->refresh();
        });
    }
}
