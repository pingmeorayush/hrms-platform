<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property string $document_type
 * @property string $original_file_name
 * @property string $disk
 * @property string $file_path
 * @property string $mime_type
 * @property int $file_size_bytes
 * @property string $checksum_sha256
 * @property Carbon|null $expiry_date
 * @property string|null $notes
 * @property-read Employee|null $employee
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
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

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'file_size_bytes' => 'integer',
        ];
    }
}
