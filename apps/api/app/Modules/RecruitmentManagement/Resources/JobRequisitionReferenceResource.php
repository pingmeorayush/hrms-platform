<?php

namespace App\Modules\RecruitmentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequisitionReferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requisition_code' => $this->requisition_code,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }
}
