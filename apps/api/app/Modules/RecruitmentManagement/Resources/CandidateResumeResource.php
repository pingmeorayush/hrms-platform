<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version_number' => $this->version_number,
            'is_current' => (bool) $this->is_current,
            'original_file_name' => $this->original_file_name,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'checksum_sha256' => $this->checksum_sha256,
            'notes' => $this->notes,
            'uploaded_by' => new UserReferenceResource($this->whenLoaded('uploader')),
            'download_url' => '/api/v1/recruitment/candidates/'.$this->candidate_id.'/resumes/'.$this->id.'/download',
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
