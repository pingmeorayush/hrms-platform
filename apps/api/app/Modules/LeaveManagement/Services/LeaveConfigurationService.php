<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveConfigurationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

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

    private function normalizeLeaveTypePayload(array $payload): array
    {
        $payload['code'] = strtoupper(trim((string) $payload['code']));
        $payload['name'] = trim((string) $payload['name']);
        $payload['description'] = filled($payload['description'] ?? null)
            ? trim((string) $payload['description'])
            : null;
        $payload['color_token'] = strtoupper(trim((string) $payload['color_token']));

        return $payload;
    }

    private function normalizePolicyPayload(array $payload): array
    {
        $payload['version'] = (int) ($payload['version'] ?? 1);
        $payload['annual_allowance_days'] = round((float) $payload['annual_allowance_days'], 2);
        $payload['opening_balance_days'] = round((float) $payload['opening_balance_days'], 2);
        $payload['carry_forward_limit_days'] = round((float) $payload['carry_forward_limit_days'], 2);
        $payload['encashment_limit_days'] = round((float) $payload['encashment_limit_days'], 2);
        $payload['max_consecutive_days'] = round((float) $payload['max_consecutive_days'], 2);
        $payload['applicable_department_id'] = $payload['applicable_department_id'] ?? null;
        $payload['applicable_location_id'] = $payload['applicable_location_id'] ?? null;
        $payload['eligibility_rule'] = $this->normalizeEligibilityRule($payload['eligibility_rule'] ?? []);
        $payload['scope_key'] = $this->makePolicyScopeKey($payload);

        return $payload;
    }

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
