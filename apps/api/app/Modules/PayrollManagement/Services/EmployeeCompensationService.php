<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureComponent;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type EmployeeCompensationFilters array{
 *   employee_id?: int|string|null,
 *   current_only?: bool
 * }
 * @phpstan-type EmployeeCompensationSummaryEmployee array{
 *   id: int,
 *   employee_code: string,
 *   full_name: string,
 *   email: string|null,
 *   employment_status: string|null
 * }
 * @phpstan-type PayrollResolvedFormulaInputs array{
 *   calculation_type: string|null,
 *   flat_amount: float|int|string|null,
 *   percentage_value: float|int|string|null,
 *   percentage_basis_component_codes: list<string>,
 *   expression_formula: string|null
 * }
 * @phpstan-type PayrollComponentSnapshotLine array{
 *   salary_component_id: int,
 *   code: string|null,
 *   name: string|null,
 *   category: string|null,
 *   display_order: int,
 *   is_proratable?: bool,
 *   resolved_formula_inputs: PayrollResolvedFormulaInputs
 * }
 * @phpstan-type EmployeeCompensationAssignmentPayload array{
 *   employee_id: int|string,
 *   salary_structure_id: int|string,
 *   revision_reason: string,
 *   effective_from: string,
 *   revision_date: string,
 *   notes?: string|null
 * }
 * @phpstan-type EmployeeCompensationSummary array{
 *   employee: EmployeeCompensationSummaryEmployee,
 *   current_assignment: EmployeeCompensation|null,
 *   history: Collection<int, EmployeeCompensation>
 * }
 */
class EmployeeCompensationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  EmployeeCompensationFilters  $filters
     * @return Collection<int, EmployeeCompensation>
     */
    public function listCompensations(User $actor, array $filters): Collection
    {
        $query = EmployeeCompensation::query()
            ->with(['employee', 'salaryStructure'])
            ->orderBy('employee_id')
            ->orderByDesc('effective_from')
            ->orderByDesc('revision_date')
            ->orderByDesc('id');

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }

        $compensations = $query->get();

        if (($filters['current_only'] ?? true) === true) {
            return $compensations->unique('employee_id')->values();
        }

        return $compensations->values();
    }

    /**
     * @return EmployeeCompensationSummary
     */
    public function showEmployeeCompensations(User $actor, int $employeeId): array
    {
        $employee = Employee::query()->findOrFail($employeeId);

        $history = EmployeeCompensation::query()
            ->with(['employee', 'salaryStructure'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('effective_from')
            ->orderByDesc('revision_date')
            ->orderByDesc('id')
            ->get();

        return [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'employment_status' => $employee->employment_status,
            ],
            'current_assignment' => $history->first(),
            'history' => $history,
        ];
    }

    /**
     * @param  EmployeeCompensationAssignmentPayload  $payload
     */
    public function assignCompensation(User $actor, array $payload): EmployeeCompensation
    {
        return DB::transaction(function () use ($actor, $payload): EmployeeCompensation {
            $employee = Employee::query()->findOrFail((int) $payload['employee_id']);
            $salaryStructure = SalaryStructure::query()
                ->with(['components.salaryComponent'])
                ->findOrFail((int) $payload['salary_structure_id']);

            $effectiveFrom = Carbon::parse((string) $payload['effective_from'])->startOfDay();
            $revisionDate = Carbon::parse((string) $payload['revision_date'])->startOfDay();

            $this->ensureAssignmentAllowed($employee, $salaryStructure, $effectiveFrom);

            $previousRevision = EmployeeCompensation::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('effective_from')
                ->orderByDesc('revision_date')
                ->orderByDesc('id')
                ->first();

            if ($previousRevision && $effectiveFrom->lessThanOrEqualTo($previousRevision->effective_from)) {
                throw ValidationException::withMessages([
                    'effective_from' => ['Employee compensation revisions must use a later effective date than the current latest assignment.'],
                ]);
            }

            $compensation = EmployeeCompensation::query()->create([
                'company_id' => $actor->company_id,
                'employee_id' => $employee->id,
                'salary_structure_id' => $salaryStructure->id,
                'previous_revision_id' => $previousRevision?->id,
                'salary_structure_code' => $salaryStructure->code,
                'salary_structure_version' => $salaryStructure->version,
                'currency' => $salaryStructure->currency,
                'pay_frequency' => $salaryStructure->pay_frequency,
                'annual_ctc_amount' => $salaryStructure->annual_ctc_amount,
                'basic_salary_amount' => $salaryStructure->basic_salary_amount,
                'gross_salary_amount' => $salaryStructure->gross_salary_amount,
                'net_salary_amount' => $salaryStructure->net_salary_amount,
                'revision_reason' => $payload['revision_reason'],
                'effective_from' => $effectiveFrom->toDateString(),
                'revision_date' => $revisionDate->toDateString(),
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
                'component_snapshot' => $this->buildComponentSnapshot($salaryStructure),
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: $previousRevision ? 'employee.compensation.revised' : 'employee.compensation.assigned',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'salary_structure_id' => $salaryStructure->id,
                    'salary_structure_code' => $salaryStructure->code,
                    'salary_structure_version' => $salaryStructure->version,
                    'previous_revision_id' => $previousRevision?->id,
                    'revision_reason' => $payload['revision_reason'],
                    'effective_from' => $effectiveFrom->toDateString(),
                    'revision_date' => $revisionDate->toDateString(),
                    'annual_ctc_amount' => $salaryStructure->annual_ctc_amount,
                    'gross_salary_amount' => $salaryStructure->gross_salary_amount,
                    'net_salary_amount' => $salaryStructure->net_salary_amount,
                ],
                entityType: 'employee_compensation',
                entityId: (string) $compensation->id,
            );

            return $compensation->load(['employee', 'salaryStructure']);
        });
    }

    private function ensureAssignmentAllowed(Employee $employee, SalaryStructure $salaryStructure, Carbon $effectiveFrom): void
    {
        if ($salaryStructure->status !== 'active') {
            throw ValidationException::withMessages([
                'salary_structure_id' => ['Only active salary structures can be assigned to employees.'],
            ]);
        }

        if ($employee->employment_status === 'terminated') {
            throw ValidationException::withMessages([
                'employee_id' => ['Terminated employees cannot receive a new compensation assignment.'],
            ]);
        }

        if ($employee->date_of_joining !== null && $effectiveFrom->lt($employee->date_of_joining->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'effective_from' => ['Compensation effective date cannot be earlier than the employee joining date.'],
            ]);
        }

        if (EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->whereDate('effective_from', $effectiveFrom->toDateString())
            ->exists()) {
            throw ValidationException::withMessages([
                'effective_from' => ['A compensation assignment already exists for this employee on the selected effective date.'],
            ]);
        }
    }

    /**
     * @return list<PayrollComponentSnapshotLine>
     */
    private function buildComponentSnapshot(SalaryStructure $salaryStructure): array
    {
        return $salaryStructure->components
            ->sortBy([
                ['display_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
            ->map(function (SalaryStructureComponent $line): array {
                $component = $line->salaryComponent;

                return [
                    'salary_component_id' => $line->salary_component_id,
                    'code' => $component?->code,
                    'name' => $component?->name,
                    'category' => $component?->category,
                    'display_order' => $line->display_order,
                    'is_proratable' => (bool) ($component->is_proratable ?? true),
                    'resolved_formula_inputs' => [
                        'calculation_type' => $component?->calculation_type,
                        'flat_amount' => $line->configured_amount ?? $component?->flat_amount,
                        'percentage_value' => $line->configured_percentage ?? $component?->percentage_value,
                        'percentage_basis_component_codes' => $line->configured_basis_component_codes
                            ?? $component->percentage_basis_component_codes
                            ?? [],
                        'expression_formula' => $line->configured_expression_formula ?? $component?->expression_formula,
                    ],
                ];
            })
            ->all();
    }
}
