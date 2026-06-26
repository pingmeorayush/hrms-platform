<?php

namespace App\Modules\AssetManagement\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type AssetAssignmentPayload array{
 *   employee_id: int|string,
 *   assigned_at?: string|null,
 *   expected_return_date?: string|null,
 *   handover_condition?: string|null,
 *   assignment_notes?: string|null
 * }
 * @phpstan-type AssetIssuePayload array{
 *   issued_at?: string|null,
 *   issue_notes?: string|null
 * }
 * @phpstan-type AssetReturnPayload array{
 *   returned_at?: string|null,
 *   return_condition?: string|null,
 *   return_notes?: string|null
 * }
 */
class AssetLifecycleService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  AssetAssignmentPayload  $payload
     */
    public function assignAsset(User $actor, Asset $asset, array $payload): Asset
    {
        return DB::transaction(function () use ($actor, $asset, $payload): Asset {
            $asset->loadMissing('currentAssignment');

            if (! in_array($asset->status, ['available', 'returned'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only available or returned assets can be assigned.'],
                ]);
            }

            if ($asset->currentAssignment !== null) {
                throw ValidationException::withMessages([
                    'status' => ['The asset already has an active assignment.'],
                ]);
            }

            $employee = Employee::query()->findOrFail((int) $payload['employee_id']);

            if ($employee->employment_status !== 'active') {
                throw ValidationException::withMessages([
                    'employee_id' => ['Only active employees can receive assets.'],
                ]);
            }

            $assignedAt = isset($payload['assigned_at'])
                ? Carbon::parse((string) $payload['assigned_at'])
                : now();

            $assignment = AssetAssignment::query()->create([
                'company_id' => $actor->company_id,
                'asset_id' => $asset->id,
                'employee_id' => $employee->id,
                'status' => 'assigned',
                'assigned_at' => $assignedAt,
                'expected_return_date' => $payload['expected_return_date'] ?? null,
                'handover_condition' => $payload['handover_condition'] ?? null,
                'assignment_notes' => $payload['assignment_notes'] ?? null,
                'assigned_by_user_id' => $actor->id,
            ]);

            $asset->forceFill([
                'status' => 'assigned',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'asset.assigned',
                actor: $actor,
                metadata: [
                    'asset_id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'assignment_id' => $assignment->id,
                    'assigned_at' => $assignment->assigned_at?->toIso8601String(),
                    'expected_return_date' => $assignment->expected_return_date?->toDateString(),
                ],
                entityType: 'asset_assignment',
                entityId: (string) $assignment->id,
            );

            return $asset->fresh(['assetCategory', 'currentAssignment.employee', 'assignments.employee']);
        });
    }

    /**
     * @param  AssetIssuePayload  $payload
     */
    public function issueAsset(User $actor, Asset $asset, array $payload): Asset
    {
        return DB::transaction(function () use ($actor, $asset, $payload): Asset {
            $asset->loadMissing('currentAssignment');
            $assignment = $asset->currentAssignment;

            if ($asset->status !== 'assigned' || $assignment === null || $assignment->status !== 'assigned') {
                throw ValidationException::withMessages([
                    'status' => ['Only assigned assets can be issued.'],
                ]);
            }

            $issuedAt = isset($payload['issued_at'])
                ? Carbon::parse((string) $payload['issued_at'])
                : now();

            $assignment->forceFill([
                'status' => 'issued',
                'issued_at' => $issuedAt,
                'issue_notes' => $payload['issue_notes'] ?? null,
                'issued_by_user_id' => $actor->id,
            ])->save();

            $asset->forceFill([
                'status' => 'issued',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'asset.issued',
                actor: $actor,
                metadata: [
                    'asset_id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'assignment_id' => $assignment->id,
                    'employee_id' => $assignment->employee_id,
                    'issued_at' => $assignment->issued_at?->toIso8601String(),
                ],
                entityType: 'asset_assignment',
                entityId: (string) $assignment->id,
            );

            return $asset->fresh(['assetCategory', 'currentAssignment.employee', 'assignments.employee']);
        });
    }

    /**
     * @param  AssetReturnPayload  $payload
     */
    public function returnAsset(User $actor, Asset $asset, array $payload): Asset
    {
        return DB::transaction(function () use ($actor, $asset, $payload): Asset {
            $asset->loadMissing('currentAssignment');
            $assignment = $asset->currentAssignment;

            if ($assignment === null || ! in_array($assignment->status, ['assigned', 'issued'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only assigned or issued assets can be returned.'],
                ]);
            }

            $returnedAt = isset($payload['returned_at'])
                ? Carbon::parse((string) $payload['returned_at'])
                : now();

            $assignment->forceFill([
                'status' => 'returned',
                'returned_at' => $returnedAt,
                'return_condition' => $payload['return_condition'] ?? null,
                'return_notes' => $payload['return_notes'] ?? null,
                'returned_by_user_id' => $actor->id,
            ])->save();

            $asset->forceFill([
                'status' => 'returned',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'asset.returned',
                actor: $actor,
                metadata: [
                    'asset_id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'assignment_id' => $assignment->id,
                    'employee_id' => $assignment->employee_id,
                    'returned_at' => $assignment->returned_at?->toIso8601String(),
                    'return_condition' => $assignment->return_condition,
                ],
                entityType: 'asset_assignment',
                entityId: (string) $assignment->id,
            );

            return $asset->fresh(['assetCategory', 'currentAssignment.employee', 'assignments.employee']);
        });
    }
}
