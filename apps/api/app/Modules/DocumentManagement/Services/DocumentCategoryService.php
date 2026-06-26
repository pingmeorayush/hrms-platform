<?php

namespace App\Modules\DocumentManagement\Services;

use App\Models\DocumentCategory;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type DocumentCategoryFilters array{
 *   repository_scope?: string,
 *   status?: string
 * }
 * @phpstan-type DocumentCategoryPayload array{
 *   code: string,
 *   name: string,
 *   repository_scope: string,
 *   default_visibility_scope: string,
 *   retention_days?: int|null,
 *   allowed_role_names?: array<int, string>,
 *   status: string,
 *   notes?: string|null
 * }
 * @phpstan-type DocumentCategorySnapshot array{
 *   code: string,
 *   name: string,
 *   repository_scope: string,
 *   default_visibility_scope: string,
 *   retention_days: int|null,
 *   allowed_role_names: array<int, string>,
 *   status: string,
 *   notes: string|null
 * }
 */
class DocumentCategoryService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  DocumentCategoryFilters  $filters
     * @return Collection<int, DocumentCategory>
     */
    public function listCategories(User $actor, array $filters): Collection
    {
        $categories = DocumentCategory::query()
            ->when(
                array_key_exists('repository_scope', $filters),
                fn ($query) => $query->where('repository_scope', $filters['repository_scope']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->orderBy('repository_scope')
            ->orderBy('name')
            ->get();

        $this->auditLogger->record(
            eventType: 'document.category.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'category_count' => $categories->count(),
            ],
            entityType: 'document_category',
            entityId: null,
        );

        return $categories;
    }

    /**
     * @param  DocumentCategoryPayload  $payload
     */
    public function create(User $actor, array $payload): DocumentCategory
    {
        return DB::transaction(function () use ($actor, $payload): DocumentCategory {
            $category = DocumentCategory::query()->create([
                'company_id' => $actor->company_id,
                'code' => trim((string) $payload['code']),
                'name' => trim((string) $payload['name']),
                'repository_scope' => $payload['repository_scope'],
                'default_visibility_scope' => $payload['default_visibility_scope'],
                'retention_days' => $payload['retention_days'] ?? null,
                'allowed_role_names' => array_values($payload['allowed_role_names'] ?? []),
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->auditLogger->record(
                eventType: 'document.category.created',
                actor: $actor,
                metadata: $this->categorySnapshot($category),
                entityType: 'document_category',
                entityId: (string) $category->id,
            );

            return $category->refresh();
        });
    }

    /**
     * @param  DocumentCategoryPayload  $payload
     */
    public function update(User $actor, DocumentCategory $category, array $payload): DocumentCategory
    {
        return DB::transaction(function () use ($actor, $category, $payload): DocumentCategory {
            $before = $this->categorySnapshot($category);

            $category->fill([
                'code' => trim((string) $payload['code']),
                'name' => trim((string) $payload['name']),
                'repository_scope' => $payload['repository_scope'],
                'default_visibility_scope' => $payload['default_visibility_scope'],
                'retention_days' => $payload['retention_days'] ?? null,
                'allowed_role_names' => array_values($payload['allowed_role_names'] ?? []),
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
            ]);
            $category->save();

            $this->auditLogger->record(
                eventType: 'document.category.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $this->categorySnapshot($category),
                ],
                entityType: 'document_category',
                entityId: (string) $category->id,
            );

            return $category->refresh();
        });
    }

    /**
     * @return DocumentCategorySnapshot
     */
    private function categorySnapshot(DocumentCategory $category): array
    {
        return [
            'code' => $category->code,
            'name' => $category->name,
            'repository_scope' => $category->repository_scope,
            'default_visibility_scope' => $category->default_visibility_scope,
            'retention_days' => $category->retention_days,
            'allowed_role_names' => $category->allowed_role_names ?? [],
            'status' => $category->status,
            'notes' => $category->notes,
        ];
    }
}
