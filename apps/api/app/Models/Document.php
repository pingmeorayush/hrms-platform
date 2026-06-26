<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $document_category_id
 * @property string $title
 * @property string $repository_scope
 * @property string|null $linked_entity_type
 * @property int|null $linked_entity_id
 * @property string|null $visibility_scope
 * @property string $original_file_name
 * @property string $disk
 * @property string $file_path
 * @property string $mime_type
 * @property int $file_size_bytes
 * @property string $checksum_sha256
 * @property Carbon|null $retention_until
 * @property array<string, mixed>|null $metadata
 * @property string|null $notes
 * @property-read DocumentCategory|null $documentCategory
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read EloquentCollection<int, PolicyAcknowledgement> $policyAcknowledgements
 */
#[Fillable([
    'company_id',
    'document_category_id',
    'title',
    'repository_scope',
    'linked_entity_type',
    'linked_entity_id',
    'visibility_scope',
    'original_file_name',
    'disk',
    'file_path',
    'mime_type',
    'file_size_bytes',
    'checksum_sha256',
    'retention_until',
    'metadata',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Document extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<DocumentCategory, $this>
     */
    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * @return HasMany<PolicyAcknowledgement, $this>
     */
    public function policyAcknowledgements(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgement::class);
    }

    protected function casts(): array
    {
        return [
            'document_category_id' => 'integer',
            'linked_entity_id' => 'integer',
            'file_size_bytes' => 'integer',
            'retention_until' => 'date',
            'metadata' => 'array',
        ];
    }
}
