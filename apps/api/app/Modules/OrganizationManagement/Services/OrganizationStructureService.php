<?php

namespace App\Modules\OrganizationManagement\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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
            $before = $company->only([
                'name',
                'subscription_plan',
                'timezone',
                'currency',
                'country_code',
                'locale',
                'language',
                'time_format',
                'expansion_country_codes',
            ]);

            $company->fill($this->normalizeCompanyProfilePayload($payload));
            $company->save();

            $this->auditLogger->record(
                eventType: 'organization.company.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $company->only([
                        'name',
                        'subscription_plan',
                        'timezone',
                        'currency',
                        'country_code',
                        'locale',
                        'language',
                        'time_format',
                        'expansion_country_codes',
                    ]),
                ],
                entityType: 'company',
                entityId: (string) $company->id,
            );

            return $company->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeCompanyProfilePayload(array $payload): array
    {
        if (array_key_exists('currency', $payload)) {
            $payload['currency'] = strtoupper(trim((string) $payload['currency']));
        }

        if (array_key_exists('country_code', $payload)) {
            $payload['country_code'] = strtoupper(trim((string) $payload['country_code']));
        }

        if (array_key_exists('locale', $payload)) {
            $payload['locale'] = trim((string) $payload['locale']);
        }

        if (array_key_exists('language', $payload)) {
            $payload['language'] = strtolower(trim((string) $payload['language']));
        }

        if (array_key_exists('time_format', $payload)) {
            $payload['time_format'] = trim((string) $payload['time_format']);
        }

        if (array_key_exists('expansion_country_codes', $payload)) {
            $launchCountry = strtoupper((string) ($payload['country_code'] ?? ''));

            $payload['expansion_country_codes'] = collect(Arr::wrap($payload['expansion_country_codes']))
                ->filter(fn (mixed $value): bool => is_string($value) && trim($value) !== '')
                ->map(fn (string $value): string => strtoupper(trim($value)))
                ->reject(fn (string $value): bool => $value === $launchCountry)
                ->unique()
                ->values()
                ->all();
        }

        return $payload;
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
