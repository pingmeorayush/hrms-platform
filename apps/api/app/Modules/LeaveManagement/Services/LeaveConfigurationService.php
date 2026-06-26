<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type LeaveEligibilityRuleInput array{
 *   employment_types?: list<string>,
 *   employment_statuses?: list<string>,
 *   genders?: list<string>,
 *   marital_statuses?: list<string>,
 *   minimum_tenure_days?: int|string|null
 * }
 * @phpstan-type LeaveEligibilityRule array{
 *   employment_types: list<string>,
 *   employment_statuses: list<string>,
 *   genders: list<string>,
 *   marital_statuses: list<string>,
 *   minimum_tenure_days: int|null
 * }
 * @phpstan-type LeaveTypePayload array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description?: string|null,
 *   is_paid: bool|int|string,
 *   requires_approval: bool|int|string,
 *   allows_half_day: bool|int|string,
 *   color_token: string,
 *   status: string
 * }
 * @phpstan-type LeaveTypeNormalizedPayload array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description: string|null,
 *   is_paid: bool,
 *   requires_approval: bool,
 *   allows_half_day: bool,
 *   color_token: string,
 *   status: string
 * }
 * @phpstan-type LeavePolicyPayload array{
 *   leave_type_id: int|string,
 *   annual_allowance_days: int|float|string,
 *   opening_balance_days: int|float|string,
 *   accrual_frequency: string,
 *   carry_forward_limit_days: int|float|string,
 *   encashment_limit_days: int|float|string,
 *   max_consecutive_days: int|float|string,
 *   min_notice_days: int|string,
 *   requires_documentation_after_days?: int|string|null,
 *   applicable_department_id?: int|string|null,
 *   applicable_location_id?: int|string|null,
 *   eligibility_rule?: LeaveEligibilityRuleInput|null,
 *   status: string,
 *   version?: int|string
 * }
 * @phpstan-type LeavePolicyNormalizedPayload array{
 *   leave_type_id: int,
 *   version: int,
 *   annual_allowance_days: float,
 *   opening_balance_days: float,
 *   accrual_frequency: string,
 *   carry_forward_limit_days: float,
 *   encashment_limit_days: float,
 *   max_consecutive_days: float,
 *   min_notice_days: int,
 *   requires_documentation_after_days: int|null,
 *   applicable_department_id: int|null,
 *   applicable_location_id: int|null,
 *   eligibility_rule: LeaveEligibilityRule,
 *   status: string,
 *   scope_key: string
 * }
 */
class LeaveConfigurationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  LeaveTypePayload  $payload
     */
    public function createLeaveType(User $actor, array $payload): LeaveType
    {
        return DB::transaction(function () use ($actor, $payload): LeaveType {
            $leaveType = LeaveType::query()->create($this->normalizeLeaveTypePayload($payload));

            $this->auditLogger->record(
                eventType: 'leave.type.created',
                actor: $actor,
                metadata: $leaveType->only([
                    'code',
                    'name',
                    'category',
                    'is_paid',
                    'requires_approval',
                    'allows_half_day',
                    'color_token',
                    'status',
                ]),
                entityType: 'leave_type',
                entityId: (string) $leaveType->id,
            );

            return $leaveType->refresh();
        });
    }

    /**
     * @param  LeaveTypePayload  $payload
     */
    public function updateLeaveType(User $actor, LeaveType $leaveType, array $payload): LeaveType
    {
        return DB::transaction(function () use ($actor, $leaveType, $payload): LeaveType {
            $before = $leaveType->only([
                'code',
                'name',
                'category',
                'description',
                'is_paid',
                'requires_approval',
                'allows_half_day',
                'color_token',
                'status',
            ]);

            $leaveType->fill($this->normalizeLeaveTypePayload($payload));
            $leaveType->save();

            $this->auditLogger->record(
                eventType: 'leave.type.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $leaveType->only([
                        'code',
                        'name',
                        'category',
                        'description',
                        'is_paid',
                        'requires_approval',
                        'allows_half_day',
                        'color_token',
                        'status',
                    ]),
                ],
                entityType: 'leave_type',
                entityId: (string) $leaveType->id,
            );

            return $leaveType->refresh();
        });
    }

    /**
     * @param  LeavePolicyPayload  $payload
     */
    public function createLeavePolicy(User $actor, array $payload): LeavePolicy
    {
        return DB::transaction(function () use ($actor, $payload): LeavePolicy {
            $payload = $this->normalizePolicyPayload($payload);
            $this->ensurePolicyScopeIsUnique($payload);

            $policy = LeavePolicy::query()->create($payload);

            $this->auditLogger->record(
                eventType: 'leave.policy.created',
                actor: $actor,
                metadata: $policy->only([
                    'leave_type_id',
                    'version',
                    'scope_key',
                    'annual_allowance_days',
                    'opening_balance_days',
                    'accrual_frequency',
                    'carry_forward_limit_days',
                    'encashment_limit_days',
                    'max_consecutive_days',
                    'min_notice_days',
                    'requires_documentation_after_days',
                    'applicable_department_id',
                    'applicable_location_id',
                    'eligibility_rule',
                    'status',
                ]),
                entityType: 'leave_policy',
                entityId: (string) $policy->id,
            );

            return $policy->load(['leaveType', 'applicableDepartment', 'applicableLocation']);
        });
    }

    /**
     * @param  LeavePolicyPayload  $payload
     */
    public function updateLeavePolicy(User $actor, LeavePolicy $policy, array $payload): LeavePolicy
    {
        return DB::transaction(function () use ($actor, $policy, $payload): LeavePolicy {
            $before = $policy->only([
                'leave_type_id',
                'version',
                'scope_key',
                'annual_allowance_days',
                'opening_balance_days',
                'accrual_frequency',
                'carry_forward_limit_days',
                'encashment_limit_days',
                'max_consecutive_days',
                'min_notice_days',
                'requires_documentation_after_days',
                'applicable_department_id',
                'applicable_location_id',
                'eligibility_rule',
                'status',
            ]);

            $payload = $this->normalizePolicyPayload($payload);
            $payload['version'] = $policy->version + 1;
            $this->ensurePolicyScopeIsUnique($payload, $policy->id);

            $policy->fill($payload);
            $policy->save();

            $this->auditLogger->record(
                eventType: 'leave.policy.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $policy->only([
                        'leave_type_id',
                        'version',
                        'scope_key',
                        'annual_allowance_days',
                        'opening_balance_days',
                        'accrual_frequency',
                        'carry_forward_limit_days',
                        'encashment_limit_days',
                        'max_consecutive_days',
                        'min_notice_days',
                        'requires_documentation_after_days',
                        'applicable_department_id',
                        'applicable_location_id',
                        'eligibility_rule',
                        'status',
                    ]),
                ],
                entityType: 'leave_policy',
                entityId: (string) $policy->id,
            );

            return $policy->refresh()->load(['leaveType', 'applicableDepartment', 'applicableLocation']);
        });
    }

    /**
     * @param  LeaveTypePayload  $payload
     * @return LeaveTypeNormalizedPayload
     */
    private function normalizeLeaveTypePayload(array $payload): array
    {
        return [
            'code' => strtoupper(trim($payload['code'])),
            'name' => trim($payload['name']),
            'category' => $payload['category'],
            'description' => filled($payload['description'] ?? null)
                ? trim((string) $payload['description'])
                : null,
            'is_paid' => (bool) $payload['is_paid'],
            'requires_approval' => (bool) $payload['requires_approval'],
            'allows_half_day' => (bool) $payload['allows_half_day'],
            'color_token' => strtoupper(trim($payload['color_token'])),
            'status' => $payload['status'],
        ];
    }

    /**
     * @param  LeavePolicyPayload  $payload
     * @return LeavePolicyNormalizedPayload
     */
    private function normalizePolicyPayload(array $payload): array
    {
        $normalized = [
            'leave_type_id' => (int) $payload['leave_type_id'],
            'version' => (int) ($payload['version'] ?? 1),
            'annual_allowance_days' => round((float) $payload['annual_allowance_days'], 2),
            'opening_balance_days' => round((float) $payload['opening_balance_days'], 2),
            'accrual_frequency' => $payload['accrual_frequency'],
            'carry_forward_limit_days' => round((float) $payload['carry_forward_limit_days'], 2),
            'encashment_limit_days' => round((float) $payload['encashment_limit_days'], 2),
            'max_consecutive_days' => round((float) $payload['max_consecutive_days'], 2),
            'min_notice_days' => (int) $payload['min_notice_days'],
            'requires_documentation_after_days' => array_key_exists('requires_documentation_after_days', $payload) && $payload['requires_documentation_after_days'] !== null
                ? (int) $payload['requires_documentation_after_days']
                : null,
            'applicable_department_id' => array_key_exists('applicable_department_id', $payload) && $payload['applicable_department_id'] !== null
                ? (int) $payload['applicable_department_id']
                : null,
            'applicable_location_id' => array_key_exists('applicable_location_id', $payload) && $payload['applicable_location_id'] !== null
                ? (int) $payload['applicable_location_id']
                : null,
            'eligibility_rule' => $this->normalizeEligibilityRule($payload['eligibility_rule'] ?? []),
            'status' => $payload['status'],
            'scope_key' => '',
        ];
        $normalized['scope_key'] = $this->makePolicyScopeKey($normalized);

        return $normalized;
    }

    /**
     * @param  LeaveEligibilityRuleInput  $payload
     * @return LeaveEligibilityRule
     */
    private function normalizeEligibilityRule(array $payload): array
    {
        return [
            'employment_types' => $this->normalizeStringArray($payload['employment_types'] ?? []),
            'employment_statuses' => $this->normalizeStringArray($payload['employment_statuses'] ?? []),
            'genders' => $this->normalizeStringArray($payload['genders'] ?? []),
            'marital_statuses' => $this->normalizeStringArray($payload['marital_statuses'] ?? []),
            'minimum_tenure_days' => array_key_exists('minimum_tenure_days', $payload) && $payload['minimum_tenure_days'] !== null
                ? (int) $payload['minimum_tenure_days']
                : null,
        ];
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private function normalizeStringArray(array $values): array
    {
        return collect($values)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function makePolicyScopeKey(array $payload): string
    {
        return hash('sha256', json_encode([
            'leave_type_id' => (int) $payload['leave_type_id'],
            'applicable_department_id' => $payload['applicable_department_id'] ? (int) $payload['applicable_department_id'] : null,
            'applicable_location_id' => $payload['applicable_location_id'] ? (int) $payload['applicable_location_id'] : null,
            'eligibility_rule' => $payload['eligibility_rule'],
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ensurePolicyScopeIsUnique(array $payload, ?int $ignoreId = null): void
    {
        $exists = LeavePolicy::query()
            ->where('leave_type_id', $payload['leave_type_id'])
            ->where('scope_key', $payload['scope_key'])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'leave_type_id' => ['A leave policy already exists for the selected leave type and eligibility scope.'],
            ]);
        }
    }
}
