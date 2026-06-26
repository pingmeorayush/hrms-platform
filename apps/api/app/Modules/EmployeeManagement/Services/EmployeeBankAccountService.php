<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type EmployeeBankAccountPayload array{
 *   account_holder_name?: string,
 *   bank_name?: string,
 *   branch_name?: string|null,
 *   account_number?: string,
 *   ifsc_code?: string|null,
 *   routing_number?: string|null,
 *   iban?: string|null,
 *   swift_code?: string|null,
 *   status?: string,
 *   is_primary?: bool,
 *   verified_at?: string|null,
 *   notes?: string|null
 * }
 */
class EmployeeBankAccountService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return Collection<int, EmployeeBankAccount>
     */
    public function listForEmployee(Employee $employee, User $actor): Collection
    {
        $accounts = $employee->bankAccounts()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        $this->auditLogger->record(
            eventType: 'employee.bank_account.viewed',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'bank_account_count' => $accounts->count(),
            ],
            entityType: 'employee',
            entityId: (string) $employee->id,
        );

        return $accounts;
    }

    /**
     * @param  EmployeeBankAccountPayload  $payload
     */
    public function create(Employee $employee, User $actor, array $payload): EmployeeBankAccount
    {
        return DB::transaction(function () use ($employee, $actor, $payload): EmployeeBankAccount {
            $setPrimary = ($payload['is_primary'] ?? false) || ! $employee->bankAccounts()->exists();

            if ($setPrimary) {
                $employee->bankAccounts()->update(['is_primary' => false]);
            }

            $bankAccount = $employee->bankAccounts()->create([
                ...$payload,
                'is_primary' => $setPrimary,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'employee.bank_account.created',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'bank_account_id' => $bankAccount->id,
                    'is_primary' => $bankAccount->is_primary,
                    'status' => $bankAccount->status,
                ],
                entityType: 'employee_bank_account',
                entityId: (string) $bankAccount->id,
            );

            return $bankAccount->refresh();
        });
    }

    /**
     * @param  EmployeeBankAccountPayload  $payload
     */
    public function update(Employee $employee, EmployeeBankAccount $bankAccount, User $actor, array $payload): EmployeeBankAccount
    {
        return DB::transaction(function () use ($employee, $bankAccount, $actor, $payload): EmployeeBankAccount {
            $before = $bankAccount->only(['status', 'is_primary', 'verified_at', 'notes']);

            if (($payload['is_primary'] ?? false) === true) {
                $employee->bankAccounts()
                    ->whereKeyNot($bankAccount->id)
                    ->update(['is_primary' => false]);
            }

            $bankAccount->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $bankAccount->save();

            $this->auditLogger->record(
                eventType: 'employee.bank_account.updated',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'bank_account_id' => $bankAccount->id,
                    'before' => $before,
                    'after' => $bankAccount->only(['status', 'is_primary', 'verified_at', 'notes']),
                    'changed_fields' => array_keys($payload),
                ],
                entityType: 'employee_bank_account',
                entityId: (string) $bankAccount->id,
            );

            return $bankAccount->refresh();
        });
    }
}
