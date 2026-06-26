<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @phpstan-type EmployeeDocumentPayload array{
 *   document_type: string,
 *   expiry_date?: string|null,
 *   notes?: string|null
 * }
 */
class EmployeeDocumentService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return Collection<int, EmployeeDocument>
     */
    public function listForEmployee(Employee $employee, User $actor): Collection
    {
        $documents = $employee->documents()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $this->auditLogger->record(
            eventType: 'employee.document.listed',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'document_count' => $documents->count(),
            ],
            entityType: 'employee',
            entityId: (string) $employee->id,
        );

        return $documents;
    }

    /**
     * @param  EmployeeDocumentPayload  $payload
     */
    public function create(Employee $employee, User $actor, UploadedFile $file, array $payload): EmployeeDocument
    {
        return DB::transaction(function () use ($employee, $actor, $file, $payload): EmployeeDocument {
            $disk = (string) config('employee_documents.disk', 'local');
            $storedFileName = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
            $directory = 'companies/'.$employee->company_id.'/employees/'.$employee->id.'/documents';
            $path = $file->storeAs($directory, $storedFileName, ['disk' => $disk]);

            $document = $employee->documents()->create([
                'document_type' => $payload['document_type'],
                'original_file_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'file_size_bytes' => $file->getSize(),
                'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
                'expiry_date' => $payload['expiry_date'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'employee.document.uploaded',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'document_id' => $document->id,
                    'document_type' => $document->document_type,
                    'original_file_name' => $document->original_file_name,
                    'mime_type' => $document->mime_type,
                    'file_size_bytes' => $document->file_size_bytes,
                ],
                entityType: 'employee_document',
                entityId: (string) $document->id,
            );

            return $document->refresh();
        });
    }

    public function download(EmployeeDocument $document, User $actor): StreamedResponse
    {
        $this->auditLogger->record(
            eventType: 'employee.document.downloaded',
            actor: $actor,
            metadata: [
                'employee_id' => $document->employee_id,
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'original_file_name' => $document->original_file_name,
            ],
            entityType: 'employee_document',
            entityId: (string) $document->id,
        );

        return Storage::disk($document->disk)->download($document->file_path, $document->original_file_name);
    }
}
