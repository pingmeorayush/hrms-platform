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
 * @property int|null $job_requisition_id
 * @property string $candidate_code
 * @property int|null $recruiter_user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $source
 * @property string $current_stage
 * @property string $status
 * @property Carbon|null $stage_entered_at
 * @property float|null $total_experience_years
 * @property int|null $notice_period_days
 * @property string|null $current_company
 * @property string|null $current_title
 * @property string|null $summary
 * @property string|null $notes
 * @property-read int|null $resumes_count
 * @property-read JobRequisition|null $requisition
 * @property-read User|null $recruiter
 * @property-read EloquentCollection<int, CandidateResume> $resumes
 * @property-read EloquentCollection<int, CandidateStageTransition> $stageTransitions
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'job_requisition_id',
    'candidate_code',
    'recruiter_user_id',
    'first_name',
    'last_name',
    'email',
    'phone',
    'source',
    'current_stage',
    'status',
    'stage_entered_at',
    'total_experience_years',
    'notice_period_days',
    'current_company',
    'current_title',
    'summary',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Candidate extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<JobRequisition, $this>
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_requisition_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    /**
     * @return HasMany<CandidateResume, $this>
     */
    public function resumes(): HasMany
    {
        return $this->hasMany(CandidateResume::class)->orderByDesc('version_number');
    }

    /**
     * @return HasMany<CandidateResume, $this>
     */
    public function latestResume(): HasMany
    {
        return $this->hasMany(CandidateResume::class)->where('is_current', true)->latest('version_number');
    }

    /**
     * @return HasMany<CandidateStageTransition, $this>
     */
    public function stageTransitions(): HasMany
    {
        return $this->hasMany(CandidateStageTransition::class)->orderByDesc('transitioned_at')->orderByDesc('id');
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
            'stage_entered_at' => 'datetime',
            'total_experience_years' => 'float',
            'notice_period_days' => 'integer',
        ];
    }
}
