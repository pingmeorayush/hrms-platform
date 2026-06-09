<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Document;
use App\Models\Employee;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PolicyAcknowledgementService
{
    public function __construct(
        private readonly EmployeeSelfServiceAccessScopeService $selfServiceAccessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function search(User $actor, array $filters = []): Collection
    {
        $query = PolicyAcknowledgement::query()
            ->with(['document', 'employee'])
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', (int) $filters['employee_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', (string) $filters['status']),
            )
            ->when(
                ! $actor->can('employee.manage'),
                function (Builder $builder) use ($actor): void {
                    $linkedEmployee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);
                    $builder->where('employee_id', $linkedEmployee->id);
                },
            )
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id');

        $acknowledgements = $query->get();

        $this->auditLogger->record(
            eventType: 'employee.policy_acknowledgement.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'acknowledgement_count' => $acknowledgements->count(),
            ],
            entityType: 'policy_acknowledgement',
            entityId: null,
        );

        return $acknowledgements;
    }

    public function assign(User $actor, array $payload): Collection
    {
        return DB::transaction(function () use ($actor, $payload): Collection {
            $document = Document::query()->findOrFail((int) $payload['document_id']);

            if ($document->repository_scope !== 'policy') {
                throw ValidationException::withMessages([
                    'document_id' => ['Only policy-scope documents can be assigned for acknowledgement.'],
                ]);
            }

            $employees = Employee::query()
                ->whereIn('id', $payload['employee_ids'])
                ->get();

            if ($employees->count() !== count($payload['employee_ids'])) {
                throw new NotFoundHttpException;
            }

            $acknowledgements = $employees->map(function (Employee $employee) use ($actor, $payload, $document) {
                return PolicyAcknowledgement::query()->updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'company_id' => $actor->company_id,
                        'policy_title' => $document->title,
                        'policy_version' => $payload['policy_version'] ?? null,
                        'status' => 'assigned',
                        'assigned_by_user_id' => $actor->id,
                        'due_date' => $payload['due_date'] ?? null,
                        'assignment_notes' => $payload['assignment_notes'] ?? null,
                        'acknowledged_at' => null,
                        'acknowledged_by_user_id' => null,
                        'acknowledgement_notes' => null,
                    ],
                );
            });

            foreach ($acknowledgements as $acknowledgement) {
                $this->auditLogger->record(
                    eventType: 'employee.policy_acknowledgement.assigned',
                    actor: $actor,
                    metadata: [
                        'policy_acknowledgement_id' => $acknowledgement->id,
                        'document_id' => $document->id,
                        'employee_id' => $acknowledgement->employee_id,
                        'policy_version' => $acknowledgement->policy_version,
                        'due_date' => $acknowledgement->due_date?->toDateString(),
                    ],
                    entityType: 'policy_acknowledgement',
                    entityId: (string) $acknowledgement->id,
                );
            }

            return PolicyAcknowledgement::query()
                ->with(['document', 'employee'])
                ->whereIn('id', $acknowledgements->pluck('id'))
                ->orderBy('id')
                ->get();
        });
    }

    public function acknowledge(PolicyAcknowledgement $acknowledgement, User $actor, array $payload): PolicyAcknowledgement
    {
        $acknowledgement = $this->resolveAccessibleAcknowledgement($actor, $acknowledgement->id, ['document', 'employee']);

        if ($acknowledgement->status === 'acknowledged') {
            throw ValidationException::withMessages([
                'acknowledgement' => ['This policy has already been acknowledged.'],
            ]);
        }

        $acknowledgement->forceFill([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by_user_id' => $actor->id,
            'acknowledgement_notes' => $payload['acknowledgement_notes'] ?? null,
        ])->save();

        $this->auditLogger->record(
            eventType: 'employee.policy_acknowledgement.acknowledged',
            actor: $actor,
            metadata: [
                'policy_acknowledgement_id' => $acknowledgement->id,
                'document_id' => $acknowledgement->document_id,
                'employee_id' => $acknowledgement->employee_id,
            ],
            entityType: 'policy_acknowledgement',
            entityId: (string) $acknowledgement->id,
        );

        return $acknowledgement->refresh()->load(['document', 'employee']);
    }

    public function download(PolicyAcknowledgement $acknowledgement, User $actor): StreamedResponse
    {
        $acknowledgement = $this->resolveAccessibleAcknowledgement($actor, $acknowledgement->id, ['document', 'employee']);

        $document = $acknowledgement->document;

        if (! $document) {
            throw new NotFoundHttpException;
        }

        $this->auditLogger->record(
            eventType: 'employee.policy_acknowledgement.downloaded',
            actor: $actor,
            metadata: [
                'policy_acknowledgement_id' => $acknowledgement->id,
                'document_id' => $document->id,
                'employee_id' => $acknowledgement->employee_id,
            ],
            entityType: 'policy_acknowledgement',
            entityId: (string) $acknowledgement->id,
        );

        return Storage::disk($document->disk)->download($document->file_path, $document->original_file_name);
    }

    public function resolveAccessibleAcknowledgement(User $actor, int $policyAcknowledgementId, array $with = []): PolicyAcknowledgement
    {
        $query = PolicyAcknowledgement::query()->with($with);

        if (! $actor->can('employee.manage')) {
            $linkedEmployee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);
            $query->where('employee_id', $linkedEmployee->id);
        }

        $acknowledgement = $query->find($policyAcknowledgementId);

        if (! $acknowledgement) {
            throw new NotFoundHttpException;
        }

        return $acknowledgement;
    }
}
