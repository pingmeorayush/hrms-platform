<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Asset;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeContact;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @phpstan-type EmployeeSelfServiceDirectoryNode array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceManagerNode array{
 *   id: int,
 *   employee_code: string,
 *   full_name: string,
 *   email: string|null
 * }
 * @phpstan-type EmployeeSelfServiceLocationNode array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   timezone: string|null,
 *   currency: string|null,
 *   city: string|null,
 *   country: string|null,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceEmployee array{
 *   id: int,
 *   employee_code: string,
 *   first_name: string,
 *   middle_name: string|null,
 *   last_name: string|null,
 *   full_name: string,
 *   email: string|null,
 *   phone: string|null,
 *   date_of_birth: string|null,
 *   gender: string|null,
 *   marital_status: string|null,
 *   date_of_joining: string|null,
 *   employment_type: string|null,
 *   employment_status: string|null,
 *   termination_reason: string|null,
 *   terminated_at: string|null,
 *   department: EmployeeSelfServiceDirectoryNode|null,
 *   designation: EmployeeSelfServiceDirectoryNode|null,
 *   manager: EmployeeSelfServiceManagerNode|null,
 *   location: EmployeeSelfServiceLocationNode|null,
 *   cost_center: EmployeeSelfServiceDirectoryNode|null,
 *   user_id: int|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceContact array{
 *   id: int,
 *   employee_id: int,
 *   type: string,
 *   label: string|null,
 *   value: string,
 *   is_primary: bool,
 *   status: string,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceAddress array{
 *   id: int,
 *   employee_id: int,
 *   type: string,
 *   address_line_1: string,
 *   address_line_2: string|null,
 *   city: string|null,
 *   state: string|null,
 *   country: string|null,
 *   postal_code: string|null,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceEmergencyContact array{
 *   id: int,
 *   employee_id: int,
 *   name: string,
 *   relationship: string|null,
 *   phone_number: string|null,
 *   email: string|null,
 *   address: string|null,
 *   priority: int,
 *   notes: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceDocumentCategory array{
 *   id: int,
 *   code: string,
 *   name: string
 * }
 * @phpstan-type EmployeeSelfServiceDocumentItem array{
 *   id: string,
 *   source_type: 'policy_acknowledgement'|'employee_document'|'repository_document',
 *   source_id: int,
 *   title: string,
 *   subtitle: string,
 *   status: string,
 *   document_type: string,
 *   file_name: string|null,
 *   mime_type: string|null,
 *   file_size_bytes: int|null,
 *   due_date: string|null,
 *   expiry_date: string|null,
 *   visibility_scope: string|null,
 *   download_url: string|null,
 *   acknowledge_url: string|null,
 *   action_required: bool,
 *   notes: string|null,
 *   category: EmployeeSelfServiceDocumentCategory|null,
 *   repository_scope: string|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceAssetCategory array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   status: string
 * }
 * @phpstan-type EmployeeSelfServiceAssetAssignment array{
 *   id: int,
 *   status: string,
 *   assigned_at: string|null,
 *   issued_at: string|null,
 *   expected_return_date: string|null,
 *   returned_at: string|null,
 *   handover_condition: string|null,
 *   return_condition: string|null,
 *   assignment_notes: string|null,
 *   issue_notes: string|null,
 *   return_notes: string|null,
 *   due_state: string
 * }
 * @phpstan-type EmployeeSelfServiceAssetItem array{
 *   id: int,
 *   asset_tag: string,
 *   name: string,
 *   asset_type: string|null,
 *   status: string,
 *   serial_number: string|null,
 *   manufacturer: string|null,
 *   model_name: string|null,
 *   purchase_date: string|null,
 *   notes: string|null,
 *   category: EmployeeSelfServiceAssetCategory|null,
 *   assignment: EmployeeSelfServiceAssetAssignment|null,
 *   created_at: string|null,
 *   updated_at: string|null
 * }
 * @phpstan-type EmployeeSelfServiceWorkspace array{
 *   employee: EmployeeSelfServiceEmployee,
 *   profile: array{
 *     contacts: list<EmployeeSelfServiceContact>,
 *     addresses: list<EmployeeSelfServiceAddress>,
 *     emergency_contacts: list<EmployeeSelfServiceEmergencyContact>,
 *     sensitive_panels: array{
 *       bank_accounts: array{
 *         visible: bool,
 *         message: string|null
 *       }
 *     }
 *   },
 *   documents: array{
 *     summary: array{
 *       total_count: int,
 *       pending_acknowledgement_count: int,
 *       acknowledged_count: int,
 *       downloadable_count: int,
 *       hidden_sensitive_count: int
 *     },
 *     items: list<EmployeeSelfServiceDocumentItem>
 *   },
 *   assets: array{
 *     summary: array{
 *       active_count: int,
 *       assigned_count: int,
 *       issued_count: int,
 *       overdue_count: int
 *     },
 *     items: list<EmployeeSelfServiceAssetItem>
 *   }
 * }
 */
class EmployeeSelfServiceWorkspaceService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly EmployeeDocumentService $employeeDocumentService,
        private readonly EmployeeSelfServiceAccessScopeService $selfServiceAccessScopeService,
    ) {}

    /**
     * @return EmployeeSelfServiceWorkspace
     */
    public function workspace(User $actor): array
    {
        $employee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);
        $employee->loadMissing(['department', 'designation', 'manager', 'location', 'costCenter']);

        $policyAcknowledgements = $this->loadPolicyAcknowledgements($employee);
        $employeeDocuments = $this->loadEmployeeDocuments($employee);
        $repositoryDocuments = $this->loadAccessibleRepositoryDocuments($employee, $actor);
        $hiddenRepositoryDocumentCount = $this->loadRepositoryDocumentCount($employee) - $repositoryDocuments->count();
        $assets = $this->loadAssignedAssets($employee);

        $documentItems = $policyAcknowledgements
            ->map(fn (PolicyAcknowledgement $acknowledgement): array => $this->mapPolicyAcknowledgement($acknowledgement))
            ->concat($employeeDocuments->map(fn (EmployeeDocument $document): array => $this->mapEmployeeDocument($document)))
            ->concat($repositoryDocuments->map(fn (Document $document): array => $this->mapRepositoryDocument($document)))
            ->values()
            ->all();

        $assetItems = $assets
            ->map(fn (Asset $asset): array => $this->mapAssignedAsset($asset))
            ->values()
            ->all();

        $payload = [
            'employee' => $this->mapEmployee($employee),
            'profile' => [
                'contacts' => $employee->contacts()
                    ->orderByDesc('is_primary')
                    ->orderBy('type')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (EmployeeContact $contact): array => $this->mapContact($contact))
                    ->all(),
                'addresses' => $employee->addresses()
                    ->orderBy('type')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (EmployeeAddress $address): array => $this->mapAddress($address))
                    ->all(),
                'emergency_contacts' => $employee->emergencyContacts()
                    ->orderBy('priority')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (EmployeeEmergencyContact $contact): array => $this->mapEmergencyContact($contact))
                    ->all(),
                'sensitive_panels' => [
                    'bank_accounts' => [
                        'visible' => $actor->can('employee.bank.view'),
                        'message' => $actor->can('employee.bank.view')
                            ? null
                            : 'Sensitive banking details stay hidden in self-service unless the session has `employee.bank.view`.',
                    ],
                ],
            ],
            'documents' => [
                'summary' => [
                    'total_count' => count($documentItems),
                    'pending_acknowledgement_count' => $policyAcknowledgements->where('status', 'assigned')->count(),
                    'acknowledged_count' => $policyAcknowledgements->where('status', 'acknowledged')->count(),
                    'downloadable_count' => collect($documentItems)->filter(fn (array $item): bool => filled($item['download_url'] ?? null))->count(),
                    'hidden_sensitive_count' => max(0, $hiddenRepositoryDocumentCount),
                ],
                'items' => $documentItems,
            ],
            'assets' => [
                'summary' => [
                    'active_count' => $assets->count(),
                    'assigned_count' => $assets->filter(fn (Asset $asset): bool => $asset->currentAssignment?->status === 'assigned')->count(),
                    'issued_count' => $assets->filter(fn (Asset $asset): bool => $asset->currentAssignment?->status === 'issued')->count(),
                    'overdue_count' => $assets->filter(fn (Asset $asset): bool => $this->resolveDueState($asset->currentAssignment?->expected_return_date) === 'overdue')->count(),
                ],
                'items' => $assetItems,
            ],
        ];

        $this->auditLogger->record(
            eventType: 'employee.self_service.viewed',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'document_count' => count($documentItems),
                'asset_count' => count($assetItems),
            ],
            entityType: 'employee',
            entityId: (string) $employee->id,
        );

        return $payload;
    }

    public function downloadEmployeeDocument(User $actor, int $employeeDocumentId): StreamedResponse
    {
        $employee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);

        $document = EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->find($employeeDocumentId);

        if (! $document) {
            throw new NotFoundHttpException;
        }

        return $this->employeeDocumentService->download($document, $actor);
    }

    public function downloadRepositoryDocument(User $actor, int $documentId): StreamedResponse
    {
        $employee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);

        $document = $this->loadAccessibleRepositoryDocuments($employee, $actor)
            ->firstWhere('id', $documentId);

        if (! $document) {
            throw new NotFoundHttpException;
        }

        $this->auditLogger->record(
            eventType: 'employee.self_service.repository_document.downloaded',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'document_id' => $document->id,
                'document_title' => $document->title,
                'repository_scope' => $document->repository_scope,
                'visibility_scope' => $document->visibility_scope,
            ],
            entityType: 'document',
            entityId: (string) $document->id,
        );

        return Storage::disk($document->disk)->download($document->file_path, $document->original_file_name);
    }

    /**
     * @return EloquentCollection<int, PolicyAcknowledgement>
     */
    private function loadPolicyAcknowledgements(Employee $employee): EloquentCollection
    {
        return PolicyAcknowledgement::query()
            ->with('document')
            ->where('employee_id', $employee->id)
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return EloquentCollection<int, EmployeeDocument>
     */
    private function loadEmployeeDocuments(Employee $employee): EloquentCollection
    {
        return EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return SupportCollection<int, Document>
     */
    private function loadAccessibleRepositoryDocuments(Employee $employee, User $actor): SupportCollection
    {
        $roleNames = $actor->getRoleNames()
            ->map(static fn (string $roleName): string => $roleName)
            ->values()
            ->all();

        return Document::query()
            ->with('documentCategory')
            ->where('linked_entity_type', 'employee')
            ->where('linked_entity_id', $employee->id)
            ->where('repository_scope', '!=', 'policy')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Document $document): bool => $this->canViewRepositoryDocument($document, $actor, $roleNames))
            ->values();
    }

    private function loadRepositoryDocumentCount(Employee $employee): int
    {
        return Document::query()
            ->where('linked_entity_type', 'employee')
            ->where('linked_entity_id', $employee->id)
            ->where('repository_scope', '!=', 'policy')
            ->count();
    }

    /**
     * @return EloquentCollection<int, Asset>
     */
    private function loadAssignedAssets(Employee $employee): EloquentCollection
    {
        return Asset::query()
            ->with(['assetCategory', 'currentAssignment'])
            ->whereHas('currentAssignment', function ($query) use ($employee): void {
                $query->where('employee_id', $employee->id);
            })
            ->orderByRaw("case when status = 'issued' then 0 when status = 'assigned' then 1 else 2 end")
            ->orderBy('asset_tag')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  list<string>  $roleNames
     */
    private function canViewRepositoryDocument(Document $document, User $actor, array $roleNames): bool
    {
        if ($actor->can('document.manage') || $actor->can('document.view')) {
            return true;
        }

        if ($document->visibility_scope === 'confidential') {
            return false;
        }

        $allowedRoles = collect($document->documentCategory->allowed_role_names ?? [])
            ->filter(static fn (string $roleName): bool => $roleName !== '')
            ->values()
            ->all();

        if ($allowedRoles === []) {
            return true;
        }

        return count(array_intersect($roleNames, $allowedRoles)) > 0;
    }

    /**
     * @return EmployeeSelfServiceEmployee
     */
    private function mapEmployee(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'last_name' => $employee->last_name,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'date_of_birth' => $employee->date_of_birth?->toDateString(),
            'gender' => $employee->gender,
            'marital_status' => $employee->marital_status,
            'date_of_joining' => $employee->date_of_joining?->toDateString(),
            'employment_type' => $employee->employment_type,
            'employment_status' => $employee->employment_status,
            'termination_reason' => $employee->termination_reason,
            'terminated_at' => $employee->terminated_at?->toIso8601String(),
            'department' => $employee->department ? [
                'id' => $employee->department->id,
                'code' => $employee->department->code,
                'name' => $employee->department->name,
                'status' => $employee->department->status,
            ] : null,
            'designation' => $employee->designation ? [
                'id' => $employee->designation->id,
                'code' => $employee->designation->code,
                'name' => $employee->designation->name,
                'status' => $employee->designation->status,
            ] : null,
            'manager' => $employee->manager ? [
                'id' => $employee->manager->id,
                'employee_code' => $employee->manager->employee_code,
                'full_name' => $employee->manager->full_name,
                'email' => $employee->manager->email,
            ] : null,
            'location' => $employee->location ? [
                'id' => $employee->location->id,
                'code' => $employee->location->code,
                'name' => $employee->location->name,
                'timezone' => $employee->location->timezone,
                'currency' => $employee->location->currency,
                'city' => $employee->location->city,
                'country' => $employee->location->country,
                'status' => $employee->location->status,
            ] : null,
            'cost_center' => $employee->costCenter ? [
                'id' => $employee->costCenter->id,
                'code' => $employee->costCenter->code,
                'name' => $employee->costCenter->name,
                'status' => $employee->costCenter->status,
            ] : null,
            'user_id' => $employee->user_id,
            'created_at' => $employee->created_at?->toIso8601String(),
            'updated_at' => $employee->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceContact
     */
    private function mapContact(EmployeeContact $contact): array
    {
        return [
            'id' => $contact->id,
            'employee_id' => $contact->employee_id,
            'type' => $contact->type,
            'label' => $contact->label,
            'value' => $contact->value,
            'is_primary' => (bool) $contact->is_primary,
            'status' => $contact->status,
            'notes' => $contact->notes,
            'created_at' => $contact->created_at?->toIso8601String(),
            'updated_at' => $contact->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceAddress
     */
    private function mapAddress(EmployeeAddress $address): array
    {
        return [
            'id' => $address->id,
            'employee_id' => $address->employee_id,
            'type' => $address->type,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
            'notes' => $address->notes,
            'created_at' => $address->created_at?->toIso8601String(),
            'updated_at' => $address->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceEmergencyContact
     */
    private function mapEmergencyContact(EmployeeEmergencyContact $contact): array
    {
        return [
            'id' => $contact->id,
            'employee_id' => $contact->employee_id,
            'name' => $contact->name,
            'relationship' => $contact->relationship,
            'phone_number' => $contact->phone_number,
            'email' => $contact->email,
            'address' => $contact->address,
            'priority' => $contact->priority,
            'notes' => $contact->notes,
            'created_at' => $contact->created_at?->toIso8601String(),
            'updated_at' => $contact->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceDocumentItem
     */
    private function mapPolicyAcknowledgement(PolicyAcknowledgement $acknowledgement): array
    {
        return [
            'id' => 'policy-'.$acknowledgement->id,
            'source_type' => 'policy_acknowledgement',
            'source_id' => $acknowledgement->id,
            'title' => $acknowledgement->policy_title,
            'subtitle' => $acknowledgement->policy_version ? 'Version '.$acknowledgement->policy_version : 'Policy acknowledgement',
            'status' => $acknowledgement->status,
            'document_type' => 'policy',
            'file_name' => $acknowledgement->document?->original_file_name,
            'mime_type' => $acknowledgement->document?->mime_type,
            'file_size_bytes' => null,
            'due_date' => $acknowledgement->due_date?->toDateString(),
            'expiry_date' => null,
            'visibility_scope' => $acknowledgement->document?->visibility_scope,
            'download_url' => route('policy.acknowledgements.download', [
                'policyAcknowledgementId' => $acknowledgement->id,
            ], false),
            'acknowledge_url' => $acknowledgement->status === 'assigned'
                ? '/api/v1/policy-acknowledgements/'.$acknowledgement->id.'/acknowledge'
                : null,
            'action_required' => $acknowledgement->status === 'assigned',
            'notes' => $acknowledgement->assignment_notes,
            'category' => null,
            'repository_scope' => $acknowledgement->document?->repository_scope,
            'created_at' => $acknowledgement->created_at?->toIso8601String(),
            'updated_at' => $acknowledgement->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceDocumentItem
     */
    private function mapEmployeeDocument(EmployeeDocument $document): array
    {
        return [
            'id' => 'employee-document-'.$document->id,
            'source_type' => 'employee_document',
            'source_id' => $document->id,
            'title' => $document->document_type,
            'subtitle' => 'Employee file',
            'status' => 'available',
            'document_type' => $document->document_type,
            'file_name' => $document->original_file_name,
            'mime_type' => $document->mime_type,
            'file_size_bytes' => $document->file_size_bytes,
            'due_date' => null,
            'expiry_date' => $document->expiry_date?->toDateString(),
            'visibility_scope' => 'employee',
            'download_url' => route('self-service.employee-documents.download', [
                'employeeDocumentId' => $document->id,
            ], false),
            'acknowledge_url' => null,
            'action_required' => false,
            'notes' => $document->notes,
            'category' => null,
            'repository_scope' => 'employee_master',
            'created_at' => $document->created_at?->toIso8601String(),
            'updated_at' => $document->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceDocumentItem
     */
    private function mapRepositoryDocument(Document $document): array
    {
        $category = $document->documentCategory;

        return [
            'id' => 'repository-document-'.$document->id,
            'source_type' => 'repository_document',
            'source_id' => $document->id,
            'title' => $document->title,
            'subtitle' => $category ? $category->name : ucfirst(str_replace('_', ' ', $document->repository_scope)),
            'status' => 'available',
            'document_type' => $category ? $category->code : $document->repository_scope,
            'file_name' => $document->original_file_name,
            'mime_type' => $document->mime_type,
            'file_size_bytes' => $document->file_size_bytes,
            'due_date' => null,
            'expiry_date' => $document->retention_until?->toDateString(),
            'visibility_scope' => $document->visibility_scope,
            'download_url' => route('self-service.repository-documents.download', [
                'documentId' => $document->id,
            ], false),
            'acknowledge_url' => null,
            'action_required' => false,
            'notes' => $document->notes,
            'category' => $document->documentCategory ? [
                'id' => $document->documentCategory->id,
                'code' => $document->documentCategory->code,
                'name' => $document->documentCategory->name,
            ] : null,
            'repository_scope' => $document->repository_scope,
            'created_at' => $document->created_at?->toIso8601String(),
            'updated_at' => $document->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return EmployeeSelfServiceAssetItem
     */
    private function mapAssignedAsset(Asset $asset): array
    {
        $assignment = $asset->currentAssignment;

        return [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'asset_type' => $asset->asset_type,
            'status' => $assignment ? $assignment->status : $asset->status,
            'serial_number' => $asset->serial_number,
            'manufacturer' => $asset->manufacturer,
            'model_name' => $asset->model_name,
            'purchase_date' => $asset->purchase_date?->toDateString(),
            'notes' => $asset->notes,
            'category' => $asset->assetCategory ? [
                'id' => $asset->assetCategory->id,
                'code' => $asset->assetCategory->code,
                'name' => $asset->assetCategory->name,
                'status' => $asset->assetCategory->status,
            ] : null,
            'assignment' => $assignment ? [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'assigned_at' => $assignment->assigned_at?->toIso8601String(),
                'issued_at' => $assignment->issued_at?->toIso8601String(),
                'expected_return_date' => $assignment->expected_return_date?->toDateString(),
                'returned_at' => $assignment->returned_at?->toIso8601String(),
                'handover_condition' => $assignment->handover_condition,
                'return_condition' => $assignment->return_condition,
                'assignment_notes' => $assignment->assignment_notes,
                'issue_notes' => $assignment->issue_notes,
                'return_notes' => $assignment->return_notes,
                'due_state' => $this->resolveDueState($assignment->expected_return_date),
            ] : null,
            'created_at' => $asset->created_at?->toIso8601String(),
            'updated_at' => $asset->updated_at?->toIso8601String(),
        ];
    }

    private function resolveDueState(mixed $expectedReturnDate): string
    {
        if (! $expectedReturnDate) {
            return 'no_due_date';
        }

        $dueDate = Carbon::parse($expectedReturnDate)->startOfDay();
        $today = Carbon::today();

        if ($dueDate->lt($today)) {
            return 'overdue';
        }

        if ($dueDate->equalTo($today)) {
            return 'due_today';
        }

        return 'upcoming';
    }
}
