<?php

namespace App\Modules\EmployeeManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'document_type' => $this->document_type,
            'original_file_name' => $this->original_file_name,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'notes' => $this->notes,
            'download_url' => route('employees.documents.download', [
                'employeeId' => $this->employee_id,
                'documentId' => $this->id,
            ], false),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
