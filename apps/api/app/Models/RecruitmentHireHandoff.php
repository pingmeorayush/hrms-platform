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
 * @property int $job_requisition_id
 * @property int $candidate_id
 * @property int $offer_id
 * @property int $employee_id
 * @property int|null $recruiter_user_id
 * @property int|null $converted_by_user_id
 * @property int|null $source_resume_id
 * @property string $status
 * @property array<string, mixed>|null $offer_snapshot
 * @property array<string, mixed>|null $candidate_snapshot
 * @property array<string, mixed>|null $requisition_snapshot
 * @property array<int, mixed>|null $document_references
 * @property array<int, int>|null $onboarding_template_ids
 * @property array<int, int>|null $onboarding_task_ids
 * @property string|null $notes
 * @property Carbon|null $converted_at
 * @property Carbon|null $onboarding_triggered_at
 * @property-read JobRequisition|null $requisition
 * @property-read Candidate|null $candidate
 * @property-read Offer|null $offer
 * @property-read Employee|null $employee
 * @property-read User|null $recruiter
 * @property-read User|null $convertedBy
 * @property-read CandidateResume|null $sourceResume
 */
#[Fillable([
    'company_id',
    'job_requisition_id',
    'candidate_id',
    'offer_id',
    'employee_id',
    'recruiter_user_id',
    'converted_by_user_id',
    'source_resume_id',
    'status',
    'offer_snapshot',
    'candidate_snapshot',
    'requisition_snapshot',
    'document_references',
    'onboarding_template_ids',
    'onboarding_task_ids',
    'notes',
    'converted_at',
    'onboarding_triggered_at',
])]
class RecruitmentHireHandoff extends Model
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
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * @return BelongsTo<Offer, $this>
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

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
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by_user_id');
    }

    /**
     * @return BelongsTo<CandidateResume, $this>
     */
    public function sourceResume(): BelongsTo
    {
        return $this->belongsTo(CandidateResume::class, 'source_resume_id');
    }

    protected function casts(): array
    {
        return [
            'offer_snapshot' => 'array',
            'candidate_snapshot' => 'array',
            'requisition_snapshot' => 'array',
            'document_references' => 'array',
            'onboarding_template_ids' => 'array',
            'onboarding_task_ids' => 'array',
            'converted_at' => 'datetime',
            'onboarding_triggered_at' => 'datetime',
        ];
    }
}
