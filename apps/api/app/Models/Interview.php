<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $job_requisition_id
 * @property int $candidate_id
 * @property int $interviewer_user_id
 * @property string $interview_code
 * @property int $round_number
 * @property string $interview_type
 * @property string $status
 * @property string $timezone
 * @property Carbon|null $scheduled_start_at
 * @property Carbon|null $scheduled_end_at
 * @property string|null $meeting_mode
 * @property string|null $meeting_location
 * @property string|null $meeting_link
 * @property string|null $agenda
 * @property string|null $cancellation_reason
 * @property-read JobRequisition|null $requisition
 * @property-read Candidate|null $candidate
 * @property-read User|null $interviewer
 * @property-read InterviewFeedback|null $feedback
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'job_requisition_id',
    'candidate_id',
    'interviewer_user_id',
    'interview_code',
    'round_number',
    'interview_type',
    'status',
    'timezone',
    'scheduled_start_at',
    'scheduled_end_at',
    'meeting_mode',
    'meeting_location',
    'meeting_link',
    'agenda',
    'cancellation_reason',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Interview extends Model
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
     * @return BelongsTo<User, $this>
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_user_id');
    }

    /**
     * @return HasOne<InterviewFeedback, $this>
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(InterviewFeedback::class);
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
            'round_number' => 'integer',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
        ];
    }
}
