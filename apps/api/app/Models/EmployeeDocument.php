<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'document_type',
    'original_file_name',
    'disk',
    'file_path',
    'mime_type',
    'file_size_bytes',
    'checksum_sha256',
    'expiry_date',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeDocument extends Model
{
    use BelongsToCompany;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'file_size_bytes' => 'integer',
        ];
    }
}
