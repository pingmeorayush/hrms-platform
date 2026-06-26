<?php

namespace App\Modules\AssetManagement\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type AssetCategoryFilters array{status?: string}
 * @phpstan-type AssetCategoryPayload array{
 *   code: string,
 *   name: string,
 *   status: string,
 *   notes?: string|null
 * }
 * @phpstan-type AssetFilters array{
 *   status?: string,
 *   asset_category_id?: int|string,
 *   employee_id?: int|string
 * }
 * @phpstan-type AssetPayload array{
 *   asset_category_id: int|string,
 *   asset_tag: string,
 *   name: string,
 *   asset_type: string,
 *   serial_number?: string|null,
 *   manufacturer?: string|null,
 *   model_name?: string|null,
 *   purchase_date?: string|null,
 *   status?: string,
 *   notes?: string|null
 * }
 * @phpstan-type AssetCategorySnapshot array{
 *   code: string,
 *   name: string,
 *   status: string,
 *   notes: string|null
 * }
 * @phpstan-type AssetSnapshot array{
 *   asset_category_id: int,
 *   asset_category_code: string,
 *   asset_tag: string,
 *   name: string,
 *   asset_type: string,
 *   serial_number: string|null,
 *   manufacturer: string|null,
 *   model_name: string|null,
 *   purchase_date: string|null,
 *   status: string
 * }
 */
class AssetCatalogService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  AssetCategoryFilters  $filters
     * @return Collection<int, AssetCategory>
     */
    public function listCategories(array $filters, User $actor): Collection
    {
        $categories = AssetCategory::query()
            ->when(
                array_key_exists('status', $filters),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        $this->auditLogger->record(
            eventType: 'asset.category.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'category_count' => $categories->count(),
            ],
            entityType: 'asset_category',
            entityId: null,
        );

        return $categories;
    }

    /**
     * @param  AssetCategoryPayload  $payload
     */
    public function createCategory(User $actor, array $payload): AssetCategory
    {
        return DB::transaction(function () use ($actor, $payload): AssetCategory {
            $category = AssetCategory::query()->create([
                'company_id' => $actor->company_id,
                'code' => trim((string) $payload['code']),
                'name' => trim((string) $payload['name']),
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->auditLogger->record(
                eventType: 'asset.category.created',
                actor: $actor,
                metadata: $this->categorySnapshot($category),
                entityType: 'asset_category',
                entityId: (string) $category->id,
            );

            return $category->refresh();
        });
    }

    /**
     * @param  AssetCategoryPayload  $payload
     */
    public function updateCategory(User $actor, AssetCategory $category, array $payload): AssetCategory
    {
        return DB::transaction(function () use ($actor, $category, $payload): AssetCategory {
            $before = $this->categorySnapshot($category);

            $category->fill([
                'code' => trim((string) $payload['code']),
                'name' => trim((string) $payload['name']),
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
            ]);
            $category->save();

            $this->auditLogger->record(
                eventType: 'asset.category.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $this->categorySnapshot($category),
                ],
                entityType: 'asset_category',
                entityId: (string) $category->id,
            );

            return $category->refresh();
        });
    }

    /**
     * @param  AssetFilters  $filters
     * @return Collection<int, Asset>
     */
    public function listAssets(array $filters, User $actor): Collection
    {
        $assets = Asset::query()
            ->with(['assetCategory', 'currentAssignment.employee'])
            ->when(
                array_key_exists('status', $filters),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('asset_category_id', $filters),
                fn ($query) => $query->where('asset_category_id', (int) $filters['asset_category_id']),
            )
            ->when(
                array_key_exists('employee_id', $filters),
                fn ($query) => $query->whereHas('currentAssignment', function ($assignmentQuery) use ($filters): void {
                    $assignmentQuery->where('employee_id', (int) $filters['employee_id']);
                }),
            )
            ->orderBy('asset_tag')
            ->orderBy('id')
            ->get();

        $this->auditLogger->record(
            eventType: 'asset.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'asset_count' => $assets->count(),
            ],
            entityType: 'asset',
            entityId: null,
        );

        return $assets;
    }

    /**
     * @param  AssetPayload  $payload
     */
    public function createAsset(User $actor, array $payload): Asset
    {
        return DB::transaction(function () use ($actor, $payload): Asset {
            $category = AssetCategory::query()->findOrFail((int) $payload['asset_category_id']);

            if ($category->status !== 'active') {
                throw ValidationException::withMessages([
                    'asset_category_id' => ['Only active asset categories can be used for asset registration.'],
                ]);
            }

            if (Asset::query()->where('asset_tag', trim((string) $payload['asset_tag']))->exists()) {
                throw ValidationException::withMessages([
                    'asset_tag' => ['The asset tag must be unique within the tenant.'],
                ]);
            }

            $asset = Asset::query()->create([
                'company_id' => $actor->company_id,
                'asset_category_id' => $category->id,
                'asset_tag' => trim((string) $payload['asset_tag']),
                'name' => trim((string) $payload['name']),
                'asset_type' => $payload['asset_type'],
                'serial_number' => $payload['serial_number'] ?? null,
                'manufacturer' => $payload['manufacturer'] ?? null,
                'model_name' => $payload['model_name'] ?? null,
                'purchase_date' => $payload['purchase_date'] ?? null,
                'status' => $payload['status'] ?? 'available',
                'notes' => $payload['notes'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'asset.created',
                actor: $actor,
                metadata: $this->assetSnapshot($asset, $category),
                entityType: 'asset',
                entityId: (string) $asset->id,
            );

            return $asset->refresh()->load(['assetCategory', 'currentAssignment.employee', 'assignments.employee']);
        });
    }

    public function showAsset(int $assetId): Asset
    {
        return Asset::query()
            ->with(['assetCategory', 'currentAssignment.employee', 'assignments.employee'])
            ->findOrFail($assetId);
    }

    /**
     * @return AssetCategorySnapshot
     */
    private function categorySnapshot(AssetCategory $category): array
    {
        return [
            'code' => $category->code,
            'name' => $category->name,
            'status' => $category->status,
            'notes' => $category->notes,
        ];
    }

    /**
     * @return AssetSnapshot
     */
    private function assetSnapshot(Asset $asset, AssetCategory $category): array
    {
        return [
            'asset_category_id' => $asset->asset_category_id,
            'asset_category_code' => $category->code,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'asset_type' => $asset->asset_type,
            'serial_number' => $asset->serial_number,
            'manufacturer' => $asset->manufacturer,
            'model_name' => $asset->model_name,
            'purchase_date' => $asset->purchase_date?->toDateString(),
            'status' => $asset->status,
        ];
    }
}
