<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $job_requisition_id
 * @property int $candidate_id
 * @property int|null $recruiter_user_id
 * @property int|null $requested_by_user_id
 * @property int|null $workflow_instance_id
 * @property string $offer_code
 * @property string $status
 * @property string $employment_type
 * @property string $currency
 * @property float $annual_ctc_amount
 * @property float|null $joining_bonus_amount
 * @property Carbon|null $proposed_start_date
 * @property Carbon|null $expires_on
 * @property string|null $notes
 * @property string|null $candidate_message
 * @property Carbon|null $submitted_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $declined_at
 * @property Carbon|null $expired_at
 * @property-read JobRequisition|null $requisition
 * @property-read Candidate|null $candidate
 * @property-read User|null $recruiter
 * @property-read User|null $requestedBy
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read EloquentCollection<int, OfferDecision> $decisions
 * @property-read RecruitmentHireHandoff|null $handoff
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'job_requisition_id',
    'candidate_id',
    'recruiter_user_id',
    'requested_by_user_id',
    'workflow_instance_id',
    'offer_code',
    'status',
    'employment_type',
    'currency',
    'annual_ctc_amount',
    'joining_bonus_amount',
    'proposed_start_date',
    'expires_on',
    'notes',
    'candidate_message',
    'submitted_at',
    'approved_at',
    'sent_at',
    'accepted_at',
    'declined_at',
    'expired_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Offer extends Model
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
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * @return HasMany<OfferDecision, $this>
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(OfferDecision::class)->orderByDesc('acted_at')->orderByDesc('id');
    }

    /**
     * @return HasOne<RecruitmentHireHandoff, $this>
     */
    public function handoff(): HasOne
    {
        return $this->hasOne(RecruitmentHireHandoff::class);
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
            'annual_ctc_amount' => 'float',
            'joining_bonus_amount' => 'float',
            'proposed_start_date' => 'date',
            'expires_on' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }
}
