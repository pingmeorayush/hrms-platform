<?php

namespace App\Modules\PerformanceManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\PerformanceManagement\Services\PerformanceAccessScopeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PerformanceAccessScopeService $accessScope */
        $accessScope = app(PerformanceAccessScopeService::class);
        $actor = $request->user();
        $actorRole = $actor ? $accessScope->determineActorRole($actor, $this->resource) : null;

        $visibleSubmissions = $actor
            ? $this->submissions
                ->filter(fn ($submission) => $accessScope->canViewSubmission($actor, $this->resource, $submission))
                ->map(function ($submission) use ($accessScope, $actor, $actorRole): array {
                    $isAnonymous = $accessScope->shouldAnonymizeSubmissionForActor($actor, $this->resource, $submission);

                    return [
                        'id' => $submission->id,
                        'role_type' => $submission->role_type,
                        'submitted_by' => $isAnonymous
                            ? null
                            : [
                                'id' => $submission->reviewer?->id,
                                'name' => $submission->reviewer?->name,
                                'employee_id' => $submission->reviewerEmployee?->id,
                            ],
                        'is_anonymous_to_current_user' => $isAnonymous,
                        'overall_rating' => $submission->overall_rating,
                        'summary' => $submission->summary,
                        'confidential_notes' => $actorRole === 'hr' ? $submission->confidential_notes : null,
                        'section_payload' => $submission->section_payload,
                        'competency_payload' => $submission->competency_payload,
                        'submitted_at' => $submission->submitted_at,
                    ];
                })
                ->values()
            : collect();

        $finalPayloadVisible = $actorRole === 'hr'
            || $actorRole === 'manager'
            || ($actorRole === 'self' && $this->status === 'published');

        $calibrationPayloadVisible = $actorRole === 'hr' || $actorRole === 'manager';

        return [
            'id' => $this->id,
            'review_cycle' => [
                'id' => $this->reviewCycle?->id,
                'code' => $this->reviewCycle?->code,
                'name' => $this->reviewCycle?->name,
                'status' => $this->reviewCycle?->status,
                'self_review_due_on' => $this->reviewCycle?->self_review_due_on?->toDateString(),
                'manager_review_due_on' => $this->reviewCycle?->manager_review_due_on?->toDateString(),
            ],
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'manager_employee' => new EmployeeReferenceResource($this->whenLoaded('managerEmployee')),
            'reviewer_user_ids' => $this->reviewer_user_ids ?? [],
            'goal_snapshot' => $this->goal_snapshot,
            'competency_snapshot' => $this->competency_snapshot,
            'visibility_rules' => $this->visibility_rules,
            'status' => $this->status,
            'actor_role' => $actorRole,
            'submissions' => PerformanceReviewSubmissionResource::collection($visibleSubmissions),
            'calibration_payload' => $this->when($calibrationPayloadVisible, $this->calibration_payload),
            'final_payload' => $this->when($finalPayloadVisible, $this->final_payload),
            'launched_at' => $this->launched_at?->toIso8601String(),
            'self_submitted_at' => $this->self_submitted_at?->toIso8601String(),
            'manager_submitted_at' => $this->manager_submitted_at?->toIso8601String(),
            'calibration_completed_at' => $this->calibration_completed_at?->toIso8601String(),
            'finalized_at' => $this->finalized_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'reopened_at' => $this->reopened_at?->toIso8601String(),
            'reopen_count' => $this->reopen_count,
            'reopened_reason' => $this->reopened_reason,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
