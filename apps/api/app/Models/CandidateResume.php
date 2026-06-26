<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $candidate_id
 * @property int $version_number
 * @property bool $is_current
 * @property string $original_file_name
 * @property string $disk
 * @property string $file_path
 * @property string $mime_type
 * @property int $file_size_bytes
 * @property string $checksum_sha256
 * @property string|null $notes
 * @property-read Candidate|null $candidate
 * @property-read User|null $uploader
 */
#[Fillable([
    'company_id',
    'candidate_id',
    'version_number',
    'is_current',
    'original_file_name',
    'disk',
    'file_path',
    'mime_type',
    'file_size_bytes',
    'checksum_sha256',
    'notes',
    'uploaded_by_user_id',
])]
class CandidateResume extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'is_current' => 'boolean',
            'file_size_bytes' => 'integer',
        ];
    }
}
