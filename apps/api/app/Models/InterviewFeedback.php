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
 * @property int $interview_id
 * @property int $candidate_id
 * @property int $job_requisition_id
 * @property int $interviewer_user_id
 * @property int $rating
 * @property string $recommendation
 * @property string $comments
 * @property string|null $strengths
 * @property string|null $concerns
 * @property Carbon|null $submitted_at
 * @property-read Interview|null $interview
 * @property-read Candidate|null $candidate
 * @property-read JobRequisition|null $requisition
 * @property-read User|null $interviewer
 */
#[Fillable([
    'company_id',
    'interview_id',
    'candidate_id',
    'job_requisition_id',
    'interviewer_user_id',
    'rating',
    'recommendation',
    'comments',
    'strengths',
    'concerns',
    'submitted_at',
])]
class InterviewFeedback extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Interview, $this>
     */
    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

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
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_user_id');
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }
}
