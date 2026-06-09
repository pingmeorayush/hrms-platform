<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_category_id' => $this->document_category_id,
            'document_category' => $this->documentCategory !== null ? [
                'id' => $this->documentCategory->id,
                'code' => $this->documentCategory->code,
                'name' => $this->documentCategory->name,
                'default_visibility_scope' => $this->documentCategory->default_visibility_scope,
                'retention_days' => $this->documentCategory->retention_days,
                'allowed_role_names' => $this->documentCategory->allowed_role_names ?? [],
                'status' => $this->documentCategory->status,
            ] : null,
            'title' => $this->title,
            'repository_scope' => $this->repository_scope,
            'linked_entity_type' => $this->linked_entity_type,
            'linked_entity_id' => $this->linked_entity_id,
            'visibility_scope' => $this->visibility_scope,
            'original_file_name' => $this->original_file_name,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'checksum_sha256' => $this->checksum_sha256,
            'retention_until' => $this->retention_until?->toDateString(),
            'metadata' => $this->metadata ?? [],
            'notes' => $this->notes,
            'download_url' => route('documents.download', [
                'documentId' => $this->id,
            ], false),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
