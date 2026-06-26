<?php

namespace App\Modules\DocumentManagement\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @phpstan-type DocumentRepositoryFilters array{
 *   document_category_id?: int|string,
 *   repository_scope?: string,
 *   linked_entity_type?: string,
 *   linked_entity_id?: int|string,
 *   visibility_scope?: string,
 *   retention_until_from?: string,
 *   retention_until_to?: string
 * }
 * @phpstan-type DocumentRepositoryPayload array{
 *   title: string,
 *   document_category_id?: int|string|null,
 *   repository_scope?: string|null,
 *   linked_entity_type?: string|null,
 *   linked_entity_id?: int|string|null,
 *   visibility_scope?: string|null,
 *   retention_until?: string|null,
 *   metadata?: array<string, mixed>,
 *   notes?: string|null
 * }
 */
class DocumentRepositoryService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  DocumentRepositoryFilters  $filters
     * @return Collection<int, Document>
     */
    public function listDocuments(User $actor, array $filters): Collection
    {
        $documents = Document::query()
            ->with('documentCategory')
            ->when(
                array_key_exists('document_category_id', $filters),
                fn ($query) => $query->where('document_category_id', (int) $filters['document_category_id']),
            )
            ->when(
                array_key_exists('repository_scope', $filters),
                fn ($query) => $query->where('repository_scope', $filters['repository_scope']),
            )
            ->when(
                array_key_exists('linked_entity_type', $filters),
                fn ($query) => $query->where('linked_entity_type', trim((string) $filters['linked_entity_type'])),
            )
            ->when(
                array_key_exists('linked_entity_id', $filters),
                fn ($query) => $query->where('linked_entity_id', (int) $filters['linked_entity_id']),
            )
            ->when(
                array_key_exists('visibility_scope', $filters),
                fn ($query) => $query->where('visibility_scope', $filters['visibility_scope']),
            )
            ->when(
                array_key_exists('retention_until_from', $filters),
                fn ($query) => $query->whereDate('retention_until', '>=', $filters['retention_until_from']),
            )
            ->when(
                array_key_exists('retention_until_to', $filters),
                fn ($query) => $query->whereDate('retention_until', '<=', $filters['retention_until_to']),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $documents = new Collection(
            $documents
                ->filter(fn (Document $document): bool => $this->canAccessDocument($document, $actor))
                ->values()
                ->all(),
        );

        $this->auditLogger->record(
            eventType: 'document.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'document_count' => $documents->count(),
            ],
            entityType: 'document',
            entityId: null,
        );

        return $documents;
    }

    /**
     * @param  DocumentRepositoryPayload  $payload
     */
    public function create(User $actor, UploadedFile $file, array $payload): Document
    {
        return DB::transaction(function () use ($actor, $file, $payload): Document {
            $category = $this->resolveCategory($payload);
            $disk = (string) config('document_repository.disk', 'local');
            $storedFileName = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
            $effectiveRepositoryScope = $this->resolveRepositoryScope($payload, $category);
            $directory = $this->makeDirectory(
                companyId: $actor->company_id,
                repositoryScope: $effectiveRepositoryScope,
                linkedEntityType: $payload['linked_entity_type'] ?? null,
                linkedEntityId: $payload['linked_entity_id'] ?? null,
            );
            $path = $file->storeAs($directory, $storedFileName, ['disk' => $disk]);
            $effectiveVisibilityScope = $this->resolveVisibilityScope($payload, $category);
            $effectiveRetentionUntil = $this->resolveRetentionUntil($payload, $category);

            $document = Document::query()->create([
                'company_id' => $actor->company_id,
                'document_category_id' => $category?->id,
                'title' => trim((string) $payload['title']),
                'repository_scope' => $effectiveRepositoryScope,
                'linked_entity_type' => filled($payload['linked_entity_type'] ?? null)
                    ? trim((string) $payload['linked_entity_type'])
                    : null,
                'linked_entity_id' => $payload['linked_entity_id'] ?? null,
                'visibility_scope' => $effectiveVisibilityScope,
                'original_file_name' => $file->getClientOriginalName(),
                'disk' => $disk,
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'file_size_bytes' => $file->getSize(),
                'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
                'retention_until' => $effectiveRetentionUntil,
                'metadata' => $payload['metadata'] ?? [],
                'notes' => $payload['notes'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'document.uploaded',
                actor: $actor,
                metadata: [
                    'document_id' => $document->id,
                    'title' => $document->title,
                    'document_category_id' => $document->document_category_id,
                    'document_category_code' => $category?->code,
                    'repository_scope' => $document->repository_scope,
                    'linked_entity_type' => $document->linked_entity_type,
                    'linked_entity_id' => $document->linked_entity_id,
                    'visibility_scope' => $document->visibility_scope,
                    'original_file_name' => $document->original_file_name,
                    'mime_type' => $document->mime_type,
                    'file_size_bytes' => $document->file_size_bytes,
                    'retention_until' => $document->retention_until?->toDateString(),
                ],
                entityType: 'document',
                entityId: (string) $document->id,
            );

            return $document->refresh()->load('documentCategory');
        });
    }

    public function showDocument(Document $document, User $actor): Document
    {
        $document->loadMissing('documentCategory');
        $this->assertDocumentAccessible($document, $actor);

        $this->auditLogger->record(
            eventType: 'document.viewed',
            actor: $actor,
            metadata: [
                'document_id' => $document->id,
                'document_category_id' => $document->document_category_id,
                'repository_scope' => $document->repository_scope,
                'linked_entity_type' => $document->linked_entity_type,
                'linked_entity_id' => $document->linked_entity_id,
                'visibility_scope' => $document->visibility_scope,
            ],
            entityType: 'document',
            entityId: (string) $document->id,
        );

        return $document;
    }

    public function download(Document $document, User $actor): StreamedResponse
    {
        $document->loadMissing('documentCategory');
        $this->assertDocumentAccessible($document, $actor);

        $this->auditLogger->record(
            eventType: 'document.downloaded',
            actor: $actor,
            metadata: [
                'document_id' => $document->id,
                'title' => $document->title,
                'document_category_id' => $document->document_category_id,
                'repository_scope' => $document->repository_scope,
                'linked_entity_type' => $document->linked_entity_type,
                'linked_entity_id' => $document->linked_entity_id,
                'original_file_name' => $document->original_file_name,
            ],
            entityType: 'document',
            entityId: (string) $document->id,
        );

        return Storage::disk($document->disk)->download($document->file_path, $document->original_file_name);
    }

    /**
     * @param  DocumentRepositoryPayload  $payload
     */
    private function resolveCategory(array $payload): ?DocumentCategory
    {
        if (! isset($payload['document_category_id'])) {
            return null;
        }

        $category = DocumentCategory::query()->findOrFail((int) $payload['document_category_id']);

        if ($category->status !== 'active') {
            throw ValidationException::withMessages([
                'document_category_id' => ['Only active document categories can be used for new document uploads.'],
            ]);
        }

        if (isset($payload['repository_scope']) && $payload['repository_scope'] !== $category->repository_scope) {
            throw ValidationException::withMessages([
                'repository_scope' => ['Repository scope must match the selected document category.'],
            ]);
        }

        return $category;
    }

    /**
     * @param  DocumentRepositoryPayload  $payload
     */
    private function resolveRepositoryScope(array $payload, ?DocumentCategory $category): string
    {
        return $category->repository_scope ?? (string) $payload['repository_scope'];
    }

    /**
     * @param  DocumentRepositoryPayload  $payload
     */
    private function resolveVisibilityScope(array $payload, ?DocumentCategory $category): string
    {
        if (isset($payload['visibility_scope'])) {
            return (string) $payload['visibility_scope'];
        }

        return $category->default_visibility_scope ?? 'internal';
    }

    /**
     * @param  DocumentRepositoryPayload  $payload
     */
    private function resolveRetentionUntil(array $payload, ?DocumentCategory $category): ?string
    {
        if (filled($payload['retention_until'] ?? null)) {
            return $payload['retention_until'];
        }

        if ($category?->retention_days === null) {
            return null;
        }

        return CarbonImmutable::now()->addDays($category->retention_days)->toDateString();
    }

    private function assertDocumentAccessible(Document $document, User $actor): void
    {
        if ($this->canAccessDocument($document, $actor)) {
            return;
        }

        throw (new ModelNotFoundException)->setModel(Document::class, [$document->id]);
    }

    private function canAccessDocument(Document $document, User $actor): bool
    {
        if ($actor->can('document.manage')) {
            return true;
        }

        $allowedRoles = $document->documentCategory->allowed_role_names ?? [];

        if ($allowedRoles === []) {
            return true;
        }

        return $actor->roles()
            ->whereIn('name', $allowedRoles)
            ->exists();
    }

    private function makeDirectory(
        int $companyId,
        string $repositoryScope,
        ?string $linkedEntityType,
        mixed $linkedEntityId,
    ): string {
        $segments = [
            'companies',
            $companyId,
            'documents',
            Str::slug($repositoryScope),
        ];

        if (filled($linkedEntityType)) {
            $segments[] = Str::slug((string) $linkedEntityType);
        }

        if ($linkedEntityId !== null) {
            $segments[] = (string) $linkedEntityId;
        }

        return implode('/', $segments);
    }
}
