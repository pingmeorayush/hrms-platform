<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureComponent;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type SalaryComponentPayload array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   calculation_type: string,
 *   flat_amount?: int|float|string|null,
 *   percentage_value?: int|float|string|null,
 *   percentage_basis_component_codes?: list<string>|null,
 *   expression_formula?: string|null,
 *   is_taxable?: bool|int|string,
 *   is_proratable?: bool|int|string,
 *   display_order?: int|string,
 *   status: string
 * }
 * @phpstan-type SalaryComponentNormalizedPayload array{
 *   company_id: int|null,
 *   code: string,
 *   name: string,
 *   category: string,
 *   calculation_type: string,
 *   flat_amount: float|null,
 *   percentage_value: float|null,
 *   percentage_basis_component_codes: list<string>,
 *   expression_formula: string|null,
 *   is_taxable: bool,
 *   is_proratable: bool,
 *   display_order: int,
 *   status: string
 * }
 * @phpstan-type SalaryStructureLinePayload array{
 *   salary_component_id: int|string,
 *   display_order?: int|string,
 *   configured_amount?: int|float|string|null,
 *   configured_percentage?: int|float|string|null,
 *   configured_basis_component_codes?: list<string>|null,
 *   configured_expression_formula?: string|null
 * }
 * @phpstan-type SalaryStructurePayload array{
 *   code: string,
 *   name: string,
 *   currency: string,
 *   country_code: string,
 *   pay_frequency: string,
 *   grade?: string|null,
 *   band?: string|null,
 *   level?: string|null,
 *   annual_ctc_amount: int|float|string,
 *   basic_salary_amount: int|float|string,
 *   gross_salary_amount: int|float|string,
 *   net_salary_amount: int|float|string,
 *   effective_from: string,
 *   revision_date: string,
 *   status: string,
 *   notes?: string|null,
 *   components: list<SalaryStructureLinePayload>
 * }
 * @phpstan-type SalaryStructureNormalizedPayload array{
 *   code: string,
 *   name: string,
 *   currency: string,
 *   country_code: string,
 *   pay_frequency: string,
 *   grade: string|null,
 *   band: string|null,
 *   level: string|null,
 *   annual_ctc_amount: float,
 *   basic_salary_amount: float,
 *   gross_salary_amount: float,
 *   net_salary_amount: float,
 *   effective_from: string,
 *   revision_date: string,
 *   status: string,
 *   notes: string|null,
 *   components: list<SalaryStructureLinePayload>
 * }
 * @phpstan-type SalaryStructureAttributes array{
 *   code: string,
 *   name: string,
 *   currency: string,
 *   country_code: string,
 *   pay_frequency: string,
 *   grade: string|null,
 *   band: string|null,
 *   level: string|null,
 *   annual_ctc_amount: float,
 *   basic_salary_amount: float,
 *   gross_salary_amount: float,
 *   net_salary_amount: float,
 *   effective_from: string,
 *   revision_date: string,
 *   status: string,
 *   notes: string|null
 * }
 */
class SalaryConfigurationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  SalaryComponentPayload  $payload
     */
    public function createComponent(User $actor, array $payload): SalaryComponent
    {
        return DB::transaction(function () use ($actor, $payload): SalaryComponent {
            $payload = $this->normalizeComponentPayload($payload);
            $this->ensureComponentCodeUnique($payload['code']);

            $component = SalaryComponent::query()->create([
                ...$payload,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'salary.component.created',
                actor: $actor,
                metadata: $component->only([
                    'code',
                    'name',
                    'category',
                    'calculation_type',
                    'flat_amount',
                    'percentage_value',
                    'percentage_basis_component_codes',
                    'expression_formula',
                    'is_taxable',
                    'is_proratable',
                    'display_order',
                    'status',
                ]),
                entityType: 'salary_component',
                entityId: (string) $component->id,
            );

            return $component->refresh();
        });
    }

    /**
     * @param  SalaryComponentPayload  $payload
     */
    public function updateComponent(User $actor, SalaryComponent $component, array $payload): SalaryComponent
    {
        return DB::transaction(function () use ($actor, $component, $payload): SalaryComponent {
            $before = $component->only([
                'code',
                'name',
                'category',
                'calculation_type',
                'flat_amount',
                'percentage_value',
                'percentage_basis_component_codes',
                'expression_formula',
                'is_taxable',
                'is_proratable',
                'display_order',
                'status',
            ]);

            $payload = $this->normalizeComponentPayload($payload);
            $this->ensureComponentCodeUnique($payload['code'], $component->id);

            $component->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $component->save();

            $this->auditLogger->record(
                eventType: 'salary.component.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $component->only([
                        'code',
                        'name',
                        'category',
                        'calculation_type',
                        'flat_amount',
                        'percentage_value',
                        'percentage_basis_component_codes',
                        'expression_formula',
                        'is_taxable',
                        'is_proratable',
                        'display_order',
                        'status',
                    ]),
                ],
                entityType: 'salary_component',
                entityId: (string) $component->id,
            );

            return $component->refresh();
        });
    }

    /**
     * @param  SalaryStructurePayload  $payload
     */
    public function createStructure(User $actor, array $payload): SalaryStructure
    {
        return DB::transaction(function () use ($actor, $payload): SalaryStructure {
            $payload = $this->normalizeStructurePayload($payload);

            if (SalaryStructure::query()->where('code', $payload['code'])->exists()) {
                throw ValidationException::withMessages([
                    'code' => ['A salary structure with this code already exists. Use the update endpoint to create the next version.'],
                ]);
            }

            $componentsById = $this->loadStructureComponents($payload['components']);

            $structure = SalaryStructure::query()->create([
                ...$this->structureAttributes($payload),
                'company_id' => $actor->company_id,
                'previous_version_id' => null,
                'version' => 1,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->syncStructureComponents($structure, $payload['components'], $componentsById);

            $this->auditLogger->record(
                eventType: 'salary.structure.created',
                actor: $actor,
                metadata: [
                    'code' => $structure->code,
                    'version' => $structure->version,
                    'status' => $structure->status,
                    'pay_frequency' => $structure->pay_frequency,
                    'component_count' => $structure->components()->count(),
                ],
                entityType: 'salary_structure',
                entityId: (string) $structure->id,
            );

            return $structure->load(['components.salaryComponent']);
        });
    }

    /**
     * @param  SalaryStructurePayload  $payload
     */
    public function versionStructure(User $actor, SalaryStructure $structure, array $payload): SalaryStructure
    {
        return DB::transaction(function () use ($actor, $structure, $payload): SalaryStructure {
            if ($structure->status === 'superseded') {
                throw ValidationException::withMessages([
                    'status' => ['Superseded salary structures cannot be versioned again. Start from the latest active revision.'],
                ]);
            }

            $payload = $this->normalizeStructurePayload($payload);

            if ($payload['code'] !== $structure->code) {
                throw ValidationException::withMessages([
                    'code' => ['Salary structure code cannot change during versioning.'],
                ]);
            }

            $componentsById = $this->loadStructureComponents($payload['components']);
            $nextVersion = (int) SalaryStructure::query()
                ->where('code', $structure->code)
                ->max('version') + 1;

            $before = $structure->load(['components.salaryComponent']);

            $newStructure = SalaryStructure::query()->create([
                ...$this->structureAttributes($payload),
                'company_id' => $actor->company_id,
                'previous_version_id' => $structure->id,
                'version' => $nextVersion,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->syncStructureComponents($newStructure, $payload['components'], $componentsById);

            $structure->forceFill([
                'status' => 'superseded',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'salary.structure.versioned',
                actor: $actor,
                metadata: [
                    'before' => [
                        'salary_structure_id' => $before->id,
                        'code' => $before->code,
                        'version' => $before->version,
                        'status' => $before->status,
                    ],
                    'after' => [
                        'salary_structure_id' => $newStructure->id,
                        'code' => $newStructure->code,
                        'version' => $newStructure->version,
                        'status' => $newStructure->status,
                        'component_count' => $newStructure->components()->count(),
                    ],
                ],
                entityType: 'salary_structure',
                entityId: (string) $newStructure->id,
            );

            return $newStructure->load(['components.salaryComponent']);
        });
    }

    /**
     * @param  SalaryComponentPayload  $payload
     * @return SalaryComponentNormalizedPayload
     */
    private function normalizeComponentPayload(array $payload): array
    {
        $flatAmount = $payload['flat_amount'] ?? null;
        $percentageValue = $payload['percentage_value'] ?? null;
        $expressionFormula = $payload['expression_formula'] ?? null;

        return [
            'company_id' => auth()->user()?->company_id,
            'code' => strtoupper(trim((string) $payload['code'])),
            'name' => trim((string) $payload['name']),
            'category' => $payload['category'],
            'calculation_type' => $payload['calculation_type'],
            'flat_amount' => $flatAmount !== null ? round((float) $flatAmount, 2) : null,
            'percentage_value' => $percentageValue !== null ? round((float) $percentageValue, 4) : null,
            'percentage_basis_component_codes' => $this->normalizeCodeArray($payload['percentage_basis_component_codes'] ?? []),
            'expression_formula' => filled($expressionFormula)
                ? trim((string) $expressionFormula)
                : null,
            'is_taxable' => (bool) ($payload['is_taxable'] ?? true),
            'is_proratable' => (bool) ($payload['is_proratable'] ?? true),
            'display_order' => (int) ($payload['display_order'] ?? 0),
            'status' => $payload['status'],
        ];
    }

    /**
     * @param  SalaryStructurePayload  $payload
     * @return SalaryStructureNormalizedPayload
     */
    private function normalizeStructurePayload(array $payload): array
    {
        return [
            'code' => strtoupper(trim((string) $payload['code'])),
            'name' => trim((string) $payload['name']),
            'currency' => strtoupper(trim((string) $payload['currency'])),
            'country_code' => strtoupper(trim((string) $payload['country_code'])),
            'pay_frequency' => $payload['pay_frequency'],
            'grade' => filled($payload['grade'] ?? null) ? trim((string) $payload['grade']) : null,
            'band' => filled($payload['band'] ?? null) ? trim((string) $payload['band']) : null,
            'level' => filled($payload['level'] ?? null) ? trim((string) $payload['level']) : null,
            'annual_ctc_amount' => round((float) $payload['annual_ctc_amount'], 2),
            'basic_salary_amount' => round((float) $payload['basic_salary_amount'], 2),
            'gross_salary_amount' => round((float) $payload['gross_salary_amount'], 2),
            'net_salary_amount' => round((float) $payload['net_salary_amount'], 2),
            'effective_from' => $payload['effective_from'],
            'revision_date' => $payload['revision_date'],
            'status' => $payload['status'],
            'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
            'components' => $payload['components'],
        ];
    }

    /**
     * @param  SalaryStructureNormalizedPayload  $payload
     * @return SalaryStructureAttributes
     */
    private function structureAttributes(array $payload): array
    {
        return [
            'code' => $payload['code'],
            'name' => $payload['name'],
            'currency' => $payload['currency'],
            'country_code' => $payload['country_code'],
            'pay_frequency' => $payload['pay_frequency'],
            'grade' => $payload['grade'],
            'band' => $payload['band'],
            'level' => $payload['level'],
            'annual_ctc_amount' => $payload['annual_ctc_amount'],
            'basic_salary_amount' => $payload['basic_salary_amount'],
            'gross_salary_amount' => $payload['gross_salary_amount'],
            'net_salary_amount' => $payload['net_salary_amount'],
            'effective_from' => $payload['effective_from'],
            'revision_date' => $payload['revision_date'],
            'status' => $payload['status'],
            'notes' => $payload['notes'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return Collection<int, SalaryComponent>
     */
    private function loadStructureComponents(array $components): Collection
    {
        $componentIds = collect($components)
            ->pluck('salary_component_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $loaded = SalaryComponent::query()
            ->whereIn('id', $componentIds)
            ->get()
            ->keyBy('id');

        $this->validateStructureLineConfiguration($components, $loaded);

        return $loaded;
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @param  Collection<int, SalaryComponent>  $loaded
     */
    private function validateStructureLineConfiguration(array $components, Collection $loaded): void
    {
        $availableCodes = SalaryComponent::query()
            ->pluck('code')
            ->map(fn (mixed $code): string => strtoupper((string) $code))
            ->all();

        foreach ($components as $index => $componentPayload) {
            /** @var SalaryComponent|null $component */
            $component = $loaded->get((int) $componentPayload['salary_component_id']);

            if (! $component) {
                throw ValidationException::withMessages([
                    "components.{$index}.salary_component_id" => ['The selected salary component could not be resolved.'],
                ]);
            }

            $configuredAmount = $componentPayload['configured_amount'] ?? null;
            $configuredPercentage = $componentPayload['configured_percentage'] ?? null;
            $configuredBasis = $this->normalizeCodeArray($componentPayload['configured_basis_component_codes'] ?? []);
            $configuredExpression = filled($componentPayload['configured_expression_formula'] ?? null)
                ? trim((string) $componentPayload['configured_expression_formula'])
                : null;

            if ($component->calculation_type === 'fixed' && $configuredAmount === null && $component->flat_amount === null) {
                throw ValidationException::withMessages([
                    "components.{$index}.configured_amount" => ['A fixed structure line requires an explicit amount or a default component amount.'],
                ]);
            }

            if ($component->calculation_type === 'percentage') {
                if ($configuredPercentage === null && $component->percentage_value === null) {
                    throw ValidationException::withMessages([
                        "components.{$index}.configured_percentage" => ['A percentage structure line requires an explicit percentage or a default component percentage.'],
                    ]);
                }

                $basisCodes = $configuredBasis !== [] ? $configuredBasis : ($component->percentage_basis_component_codes ?? []);

                if ($basisCodes === []) {
                    throw ValidationException::withMessages([
                        "components.{$index}.configured_basis_component_codes" => ['A percentage structure line requires explicit basis component codes.'],
                    ]);
                }

                $unknownCodes = collect($basisCodes)
                    ->filter(fn (string $code): bool => ! in_array($code, $availableCodes, true))
                    ->values()
                    ->all();

                if ($unknownCodes !== []) {
                    throw ValidationException::withMessages([
                        "components.{$index}.configured_basis_component_codes" => [
                            sprintf('Unknown basis component codes: %s.', implode(', ', $unknownCodes)),
                        ],
                    ]);
                }
            }

            if ($component->calculation_type === 'expression' && $configuredExpression === null && blank($component->expression_formula)) {
                throw ValidationException::withMessages([
                    "components.{$index}.configured_expression_formula" => ['An expression structure line requires an explicit expression or a default component expression.'],
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @param  Collection<int, SalaryComponent>  $componentsById
     */
    private function syncStructureComponents(SalaryStructure $structure, array $components, Collection $componentsById): void
    {
        foreach ($components as $index => $componentPayload) {
            $component = $componentsById->get((int) $componentPayload['salary_component_id']);
            $configuredAmount = $componentPayload['configured_amount'] ?? null;
            $configuredPercentage = $componentPayload['configured_percentage'] ?? null;
            $configuredExpression = $componentPayload['configured_expression_formula'] ?? null;

            SalaryStructureComponent::query()->create([
                'company_id' => $structure->company_id,
                'salary_structure_id' => $structure->id,
                'salary_component_id' => $component->id,
                'display_order' => (int) ($componentPayload['display_order'] ?? $component->display_order ?? $index),
                'configured_amount' => $configuredAmount !== null
                    ? round((float) $configuredAmount, 2)
                    : null,
                'configured_percentage' => $configuredPercentage !== null
                    ? round((float) $configuredPercentage, 4)
                    : null,
                'configured_basis_component_codes' => $this->normalizeCodeArray($componentPayload['configured_basis_component_codes'] ?? []),
                'configured_expression_formula' => filled($configuredExpression)
                    ? trim((string) $configuredExpression)
                    : null,
            ]);
        }
    }

    private function ensureComponentCodeUnique(string $code, ?int $ignoreId = null): void
    {
        $query = SalaryComponent::query()->where('code', $code);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'code' => ['A salary component with this code already exists.'],
            ]);
        }
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private function normalizeCodeArray(array $values): array
    {
        return collect($values)
            ->map(fn (mixed $value): string => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
