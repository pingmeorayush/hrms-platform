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
 * @property int $performance_review_id
 * @property int $reviewer_user_id
 * @property int|null $reviewer_employee_id
 * @property string $role_type
 * @property array<string, mixed>|null $visibility_scope
 * @property array<string, mixed>|null $section_payload
 * @property array<string, mixed>|null $competency_payload
 * @property float|null $overall_rating
 * @property string|null $summary
 * @property string|null $confidential_notes
 * @property Carbon|null $submitted_at
 * @property-read Company|null $company
 * @property-read PerformanceReview|null $review
 * @property-read User|null $reviewer
 * @property-read Employee|null $reviewerEmployee
 */
#[Fillable([
    'company_id',
    'performance_review_id',
    'reviewer_user_id',
    'reviewer_employee_id',
    'role_type',
    'visibility_scope',
    'section_payload',
    'competency_payload',
    'overall_rating',
    'summary',
    'confidential_notes',
    'submitted_at',
])]
class PerformanceReviewSubmission extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<PerformanceReview, $this>
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(PerformanceReview::class, 'performance_review_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function reviewerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_employee_id');
    }

    protected function casts(): array
    {
        return [
            'visibility_scope' => 'array',
            'section_payload' => 'array',
            'competency_payload' => 'array',
            'overall_rating' => 'float',
            'submitted_at' => 'datetime',
        ];
    }
}
