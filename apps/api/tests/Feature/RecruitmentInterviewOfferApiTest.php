<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowVersion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruitmentInterviewOfferApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_recruiter_can_schedule_interviews_with_overlap_protection_and_interviewer_can_submit_immutable_feedback(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $interviewer = User::factory()->create(['company_id' => $company->id]);
        $interviewer->assignRole('interviewer');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-ENG']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-SSE']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-BLR']);

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);
        $candidate = $this->candidateForRequisition($company, $requisition, $recruiter, 'shortlisted');

        Sanctum::actingAs($recruiter);

        $interviewId = $this->postJson('/api/v1/recruitment/interviews', [
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'interviewer_user_id' => $interviewer->id,
            'round_number' => 1,
            'interview_type' => 'technical',
            'timezone' => 'Asia/Kolkata',
            'scheduled_start_at' => '2026-06-18T09:00:00+05:30',
            'scheduled_end_at' => '2026-06-18T10:00:00+05:30',
            'meeting_mode' => 'virtual',
            'meeting_link' => 'https://meet.example.test/interviews/rhea-kapoor-1',
            'agenda' => 'Platform design discussion and deep-dive on backend fundamentals.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.interviewer.id', $interviewer->id)
            ->assertJsonPath('data.candidate.id', $candidate->id)
            ->json('data.id');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'current_stage' => 'interview',
        ]);

        $this->postJson('/api/v1/recruitment/interviews', [
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'interviewer_user_id' => $interviewer->id,
            'round_number' => 2,
            'interview_type' => 'managerial',
            'timezone' => 'Asia/Kolkata',
            'scheduled_start_at' => '2026-06-18T09:30:00+05:30',
            'scheduled_end_at' => '2026-06-18T10:30:00+05:30',
            'meeting_mode' => 'virtual',
            'meeting_link' => 'https://meet.example.test/interviews/rhea-kapoor-2',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_start_at']);

        Sanctum::actingAs($interviewer);

        $this->getJson("/api/v1/recruitment/interviews/{$interviewId}")
            ->assertOk()
            ->assertJsonPath('data.id', $interviewId);

        $this->postJson("/api/v1/recruitment/interviews/{$interviewId}/feedback", [
            'rating' => 5,
            'recommendation' => 'strong_hire',
            'comments' => 'Excellent systems thinking and strong ownership signals throughout the interview.',
            'strengths' => 'Distributed systems knowledge, API tradeoff reasoning, and calm communication.',
            'concerns' => 'Should probe stakeholder management in later rounds.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.feedback.rating', 5)
            ->assertJsonPath('data.feedback.recommendation', 'strong_hire');

        $this->postJson("/api/v1/recruitment/interviews/{$interviewId}/feedback", [
            'rating' => 4,
            'recommendation' => 'hire',
            'comments' => 'Second submission should fail.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['interview']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.interview.scheduled',
            'entity_id' => (string) $interviewId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $interviewer->id,
            'event_type' => 'recruitment.interview.feedback_submitted',
            'entity_id' => (string) $interviewId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $interviewer->id,
            'title' => 'Interview assigned',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recruiter->id,
            'title' => 'Interview feedback submitted',
        ]);
    }

    public function test_offer_workflow_supports_duplicate_protection_approval_send_and_candidate_acceptance_history(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-DATA']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-ARCH']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-MUM']);

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $this->seedRecruitmentOfferApprovalWorkflowForCompany($company, $hrAdmin);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);
        $candidate = $this->candidateForRequisition($company, $requisition, $recruiter, 'shortlisted');

        Sanctum::actingAs($recruiter);

        $offerId = $this->postJson('/api/v1/recruitment/offers', [
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'employment_type' => 'full_time',
            'currency' => 'INR',
            'annual_ctc_amount' => 3600000,
            'joining_bonus_amount' => 250000,
            'proposed_start_date' => '2026-08-01',
            'expires_on' => '2026-06-25',
            'notes' => 'Leadership-approved offer draft for the principal platform role.',
            'candidate_message' => 'We would be excited to welcome you to the platform architecture charter.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.candidate.id', $candidate->id)
            ->json('data.id');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'current_stage' => 'offer',
        ]);

        $this->postJson('/api/v1/recruitment/offers', [
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'employment_type' => 'full_time',
            'currency' => 'INR',
            'annual_ctc_amount' => 3650000,
            'expires_on' => '2026-06-27',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['candidate_id']);

        $this->patchJson("/api/v1/recruitment/offers/{$offerId}", [
            'action' => 'submit',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.workflow.definition.key', 'recruitment-offer-approval')
            ->assertJsonPath('data.workflow.tasks.0.assignee.id', $managerUser->id);

        Sanctum::actingAs($managerUser);

        $this->patchJson("/api/v1/recruitment/offers/{$offerId}", [
            'action' => 'approve',
            'comment' => 'Offer posture looks good from the hiring-manager side.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.workflow.tasks.0.status', 'completed')
            ->assertJsonPath('data.workflow.tasks.1.assignee.id', $hrAdmin->id);

        Sanctum::actingAs($hrAdmin);

        $this->patchJson("/api/v1/recruitment/offers/{$offerId}", [
            'action' => 'approve',
            'comment' => 'HR approves the offer package.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.workflow.status', 'completed');

        Sanctum::actingAs($recruiter);

        $this->patchJson("/api/v1/recruitment/offers/{$offerId}", [
            'action' => 'mark_sent',
            'comment' => 'Offer sent to candidate after compensation sign-off.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'sent');

        $this->patchJson("/api/v1/recruitment/offers/{$offerId}", [
            'action' => 'record_acceptance',
            'comment' => 'Candidate accepted verbally and on email.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.decision_history.0.decision_type', 'candidate_accepted')
            ->assertJsonPath('data.decision_history.0.to_status', 'accepted');

        $this->assertDatabaseHas('offers', [
            'id' => $offerId,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('offer_decisions', [
            'offer_id' => $offerId,
            'decision_type' => 'candidate_accepted',
            'to_status' => 'accepted',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.offer.submitted',
            'entity_id' => (string) $offerId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'recruitment.offer.approved',
            'entity_id' => (string) $offerId,
        ]);
    }

    private function approvedRequisition(Company $company, User $recruiter, Employee $hiringManager): JobRequisition
    {
        return JobRequisition::query()->create([
            'company_id' => $company->id,
            'requisition_code' => 'REC-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => 'Principal Platform Architect',
            'employment_type' => 'full_time',
            'hiring_type' => 'replacement',
            'priority' => 'high',
            'openings_count' => 1,
            'min_experience_years' => 8,
            'target_start_date' => '2026-08-01',
            'department_id' => $hiringManager->department_id,
            'designation_id' => $hiringManager->designation_id,
            'location_id' => $hiringManager->location_id,
            'recruiter_user_id' => $recruiter->id,
            'hiring_manager_employee_id' => $hiringManager->id,
            'requested_by_user_id' => $recruiter->id,
            'status' => 'approved',
            'justification' => 'Approved backfill for a critical platform leadership opening.',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);
    }

    private function candidateForRequisition(Company $company, JobRequisition $requisition, User $recruiter, string $stage): Candidate
    {
        return Candidate::query()->create([
            'company_id' => $company->id,
            'job_requisition_id' => $requisition->id,
            'candidate_code' => 'CAN-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'recruiter_user_id' => $recruiter->id,
            'first_name' => 'Rhea',
            'last_name' => 'Kapoor',
            'email' => 'rhea.kapoor+'.random_int(1000, 9999).'@example.com',
            'source' => 'manual',
            'current_stage' => $stage,
            'status' => 'active',
            'stage_entered_at' => now()->subDay(),
            'current_company' => 'Atlas Systems',
            'current_title' => 'Platform Engineer',
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);
    }

    private function seedRecruitmentOfferApprovalWorkflowForCompany(Company $company, User $actor): WorkflowDefinition
    {
        $definition = WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'key' => 'recruitment-offer-approval',
            ],
            [
                'name' => 'Recruitment Offer Approval Workflow',
                'module' => 'recruitment',
                'description' => 'Sequential offer approval through the assigned hiring manager and HR.',
                'is_template' => true,
                'status' => 'published',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ],
        );

        $version = WorkflowVersion::withoutGlobalScopes()->updateOrCreate(
            [
                'workflow_definition_id' => $definition->id,
                'version' => 1,
            ],
            [
                'status' => 'published',
                'definition' => [
                    'module' => 'recruitment',
                    'stages' => [
                        [
                            'key' => 'hiring_manager_review',
                            'name' => 'Hiring Manager Review',
                            'sequence' => 1,
                            'approver_type' => 'payload_user',
                            'approver_value' => 'hiring_manager_user_id',
                            'available_actions' => ['approve', 'reject', 'request_changes'],
                            'sla_hours' => 48,
                        ],
                        [
                            'key' => 'hr_review',
                            'name' => 'HR Review',
                            'sequence' => 2,
                            'approver_type' => 'role',
                            'approver_value' => 'hr.admin',
                            'available_actions' => ['approve', 'reject', 'request_changes'],
                            'sla_hours' => 48,
                        ],
                    ],
                ],
                'created_by' => $actor->id,
                'published_at' => now(),
            ],
        );

        foreach ([
            [
                'key' => 'hiring_manager_review',
                'name' => 'Hiring Manager Review',
                'sequence' => 1,
                'approver_type' => 'payload_user',
                'approver_value' => 'hiring_manager_user_id',
                'available_actions' => ['approve', 'reject', 'request_changes'],
                'sla_hours' => 48,
            ],
            [
                'key' => 'hr_review',
                'name' => 'HR Review',
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_value' => 'hr.admin',
                'available_actions' => ['approve', 'reject', 'request_changes'],
                'sla_hours' => 48,
            ],
        ] as $stageData) {
            WorkflowStage::withoutGlobalScopes()->updateOrCreate(
                [
                    'workflow_version_id' => $version->id,
                    'key' => $stageData['key'],
                ],
                $stageData,
            );
        }

        $definition->forceFill([
            'active_version_id' => $version->id,
        ])->save();

        return $definition->refresh();
    }
}
