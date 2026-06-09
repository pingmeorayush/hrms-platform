<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

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
