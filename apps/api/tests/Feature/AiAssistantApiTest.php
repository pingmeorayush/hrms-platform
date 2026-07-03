<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiInteraction;
use App\Models\AiRecommendation;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\LearningAssignment;
use App\Models\LearningAssignmentTarget;
use App\Models\LearningItem;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\PayrollCalendar;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AiAssistantApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Carbon::setTestNow('2026-07-02 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_workspace_returns_capabilities_recent_history_and_linked_employee_context(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-001',
            userId: $employeeUser->id,
        );

        $conversation = AiConversation::query()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'title' => 'Leave assistant',
            'persona' => 'employee_copilot',
            'status' => 'active',
            'metadata' => ['created_from' => 'test'],
            'last_interacted_at' => now(),
        ]);

        AiInteraction::query()->create([
            'company_id' => $company->id,
            'ai_conversation_id' => $conversation->id,
            'user_id' => $employeeUser->id,
            'interaction_type' => 'answer',
            'use_case' => 'leave_balance',
            'question' => 'How many leave days do I have left?',
            'answer' => 'You have leave available.',
            'status' => 'answered',
            'confidence_score' => 0.91,
            'citations' => [['type' => 'leave_balance', 'reference' => 'Leave balance #1']],
            'guardrails' => [],
            'metadata' => ['subject_employee_id' => $employee->id],
            'responded_at' => now(),
        ]);

        AiRecommendation::query()->create([
            'company_id' => $company->id,
            'ai_conversation_id' => $conversation->id,
            'user_id' => $employeeUser->id,
            'employee_id' => $employee->id,
            'scenario' => 'learning_next_best_action',
            'title' => 'Review pending training',
            'summary' => 'Open the governed learning workspace to review overdue work.',
            'rationale' => ['One learning target is overdue.'],
            'confidence_score' => 0.87,
            'suggested_actions' => [['type' => 'open_route', 'path' => '/learning/my-learning']],
            'supporting_citations' => [['type' => 'learning_assignment_target', 'reference' => 'Learning target #1']],
            'status' => 'pending_review',
            'human_review_required' => true,
            'metadata' => ['subject_employee_id' => $employee->id],
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/ai/workspace')
            ->assertOk()
            ->assertJsonPath('data.persona.key', 'employee_copilot')
            ->assertJsonPath('data.linked_employee.id', $employee->id)
            ->assertJsonPath('data.permissions.can_chat', true)
            ->assertJsonPath('data.permissions.can_generate_recommendations', true)
            ->assertJsonPath('data.summary.interaction_count', 1)
            ->assertJsonPath('data.summary.recommendation_count', 1)
            ->assertJsonPath('data.summary.pending_recommendation_count', 1)
            ->assertJsonPath('data.summary.answered_interaction_count', 1)
            ->assertJsonPath('data.summary.guardrailed_interaction_count', 0)
            ->assertJsonPath('data.review_analytics.answer_quality.cited_answer_count', 1)
            ->assertJsonPath('data.review_analytics.answer_quality.citation_coverage_percent', 100)
            ->assertJsonPath('data.review_analytics.audit_activity.workspace_view_count', 1)
            ->assertJsonPath('data.audit_timeline.0.event_type', 'ai.assistant.workspace.viewed')
            ->assertJsonCount(5, 'data.capabilities.supported_use_cases')
            ->assertJsonCount(3, 'data.capabilities.approved_recommendation_scenarios')
            ->assertJsonPath('data.subject_options.0.id', $employee->id)
            ->assertJsonPath('data.recent_interactions.0.use_case', 'leave_balance')
            ->assertJsonPath('data.recent_interactions.0.citations.0.rank', 1)
            ->assertJsonPath('data.recent_recommendations.0.scenario', 'learning_next_best_action')
            ->assertJsonPath('data.recent_recommendations.0.supporting_citations.0.rank', 1);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'ai.assistant.workspace.viewed',
            'entity_type' => 'ai_workspace',
        ]);
    }

    public function test_employee_chat_returns_grounded_leave_balance_answer_with_citations_and_audit(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-002',
            userId: $employeeUser->id,
        );

        $leaveType = LeaveType::query()->create([
            'company_id' => $company->id,
            'code' => 'AL',
            'name' => 'Annual Leave',
            'category' => 'earned',
            'description' => 'Annual leave balance',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ]);

        $leavePolicy = LeavePolicy::query()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => 'tenant_default',
            'annual_allowance_days' => 18,
            'opening_balance_days' => 2,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 5,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 10,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => 3,
            'eligibility_rule' => [],
            'status' => 'active',
        ]);

        LeaveBalance::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'available_days' => 9.5,
            'booked_days' => 2,
            'used_days' => 4.5,
            'accrued_days' => 12,
            'carry_forward_days' => 1,
            'projected_encashable_days' => 0,
            'current_period_start' => '2026-01-01',
            'current_period_end' => '2026-12-31',
            'status' => 'active',
        ]);

        Sanctum::actingAs($employeeUser);

        $this->postJson('/api/v1/ai/chat', [
            'question' => 'How many leave days do I have left right now?',
        ])->assertCreated()
            ->assertJsonPath('data.interaction.use_case', 'leave_balance')
            ->assertJsonPath('data.interaction.status', 'answered')
            ->assertJsonPath('data.interaction.metadata.subject_employee_id', $employee->id)
            ->assertJsonPath('data.interaction.citations.0.type', 'leave_balance')
            ->assertJsonPath('data.interaction.citations.0.entity_id', 1)
            ->assertJsonPath('data.conversation.persona', 'employee_copilot');

        $this->assertDatabaseHas('ai_interactions', [
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'interaction_type' => 'answer',
            'use_case' => 'leave_balance',
            'status' => 'answered',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'ai.interaction.generated',
            'entity_type' => 'ai_interaction',
        ]);
    }

    public function test_mutating_questions_are_guardrailed_and_do_not_trigger_backend_action(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-003',
            userId: $employeeUser->id,
        );

        Sanctum::actingAs($employeeUser);

        $this->postJson('/api/v1/ai/chat', [
            'question' => 'Approve my leave request for tomorrow.',
        ])->assertCreated()
            ->assertJsonPath('data.interaction.interaction_type', 'guardrail')
            ->assertJsonPath('data.interaction.use_case', 'guardrail')
            ->assertJsonPath('data.interaction.status', 'guardrailed')
            ->assertJsonPath('data.interaction.guardrails.0.code', 'approval_required')
            ->assertJsonPath('data.interaction.guardrails.1.code', 'read_only_v1');

        $this->assertDatabaseHas('ai_interactions', [
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'interaction_type' => 'guardrail',
            'status' => 'guardrailed',
        ]);

        $this->assertDatabaseCount('ai_recommendations', 0);
    }

    public function test_manager_recommendations_remain_scoped_review_only_and_auditable(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $directReportUser = User::factory()->create(['company_id' => $company->id]);
        $directReportUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $manager = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-MGR-001',
            userId: $managerUser->id,
        );
        $directReport = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-REP-001',
            userId: $directReportUser->id,
            managerId: $manager->id,
        );
        $peer = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-PEER-001',
            userId: $peerUser->id,
        );

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $directReport->id,
            'attendance_date' => now()->subDay()->toDateString(),
            'check_in_at' => now()->subDay()->setTime(9, 34),
            'check_out_at' => null,
            'worked_minutes' => 430,
            'primary_status' => 'present',
            'scheduled_start_at' => now()->subDay()->setTime(9, 0),
            'scheduled_end_at' => now()->subDay()->setTime(18, 0),
            'scheduled_work_minutes' => 540,
            'break_duration_minutes' => 30,
            'is_late' => true,
            'late_minutes' => 34,
            'is_half_day' => false,
            'overtime_minutes' => 0,
            'is_weekend' => false,
            'is_holiday' => false,
            'is_early_departure' => false,
            'early_departure_minutes' => 0,
            'calculated_at' => now()->subDay()->setTime(18, 5),
        ]);

        Sanctum::actingAs($managerUser);

        $recommendationId = $this->postJson('/api/v1/ai/recommendations', [
            'scenario' => 'attendance_follow_up',
            'subject_employee_id' => $directReport->id,
            'context_note' => 'Review before payroll cutoff.',
        ])->assertCreated()
            ->assertJsonPath('data.scenario', 'attendance_follow_up')
            ->assertJsonPath('data.employee.id', $directReport->id)
            ->assertJsonPath('data.status', 'pending_review')
            ->assertJsonPath('data.human_review_required', true)
            ->assertJsonPath('data.suggested_actions.0.requires_confirmation', true)
            ->assertJsonPath('data.supporting_citations.0.type', 'attendance_record')
            ->json('data.id');

        $this->postJson('/api/v1/ai/recommendations/'.$recommendationId.'/decisions', [
            'decision' => 'accepted',
            'decision_notes' => 'Manager reviewed the attendance exception manually.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.decision', 'accepted')
            ->assertJsonPath('data.decision_notes', 'Manager reviewed the attendance exception manually.')
            ->assertJsonPath('data.decided_by.id', $managerUser->id);

        $this->postJson('/api/v1/ai/recommendations', [
            'scenario' => 'attendance_follow_up',
            'subject_employee_id' => $peer->id,
        ])->assertNotFound();

        $this->assertDatabaseHas('ai_recommendations', [
            'id' => $recommendationId,
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'employee_id' => $directReport->id,
            'scenario' => 'attendance_follow_up',
            'status' => 'accepted',
            'decision' => 'accepted',
            'decided_by_user_id' => $managerUser->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'ai.recommendation.generated',
            'entity_type' => 'ai_recommendation',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'ai.recommendation.decision_recorded',
            'entity_type' => 'ai_recommendation',
        ]);
    }

    public function test_interaction_feedback_updates_owned_record(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-004',
            userId: $employeeUser->id,
        );

        $learningItem = LearningItem::query()->create([
            'company_id' => $company->id,
            'code' => 'AI-LEARN-001',
            'title' => 'Security Awareness Refresher',
            'description' => 'Mandatory annual refresher.',
            'category' => 'compliance',
            'delivery_mode' => 'self_paced',
            'duration_minutes' => 45,
            'requires_completion_evidence' => false,
            'renewal_frequency_months' => 12,
            'default_due_days' => 14,
            'metadata' => ['provider' => 'Phoenix Academy'],
            'status' => 'active',
            'created_by_user_id' => $employeeUser->id,
            'updated_by_user_id' => $employeeUser->id,
        ]);

        $assignment = LearningAssignment::query()->create([
            'company_id' => $company->id,
            'learning_item_id' => $learningItem->id,
            'assignment_code' => 'AI-LRN-ASG-001',
            'assigned_by_user_id' => $employeeUser->id,
            'audience_type' => 'employee',
            'audience_rules' => ['employee_ids' => [$employee->id]],
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(10)->toDateString(),
            'completion_rules' => ['renewal_frequency_months' => 12],
            'notes' => 'Required before access renewal.',
            'status' => 'active',
            'target_count' => 1,
            'completion_count' => 0,
        ]);

        LearningAssignmentTarget::query()->create([
            'company_id' => $company->id,
            'learning_assignment_id' => $assignment->id,
            'learning_item_id' => $learningItem->id,
            'employee_id' => $employee->id,
            'assigned_by_user_id' => $employeeUser->id,
            'assigned_on' => now()->toDateString(),
            'due_on' => now()->addDays(10)->toDateString(),
            'status' => 'assigned',
            'completion_progress_percent' => 0,
        ]);

        Sanctum::actingAs($employeeUser);

        $interactionId = $this->postJson('/api/v1/ai/chat', [
            'question' => 'Summarize my learning assignments.',
        ])->assertCreated()
            ->assertJsonPath('data.interaction.use_case', 'learning_summary')
            ->json('data.interaction.id');

        $this->postJson('/api/v1/ai/interactions/'.$interactionId.'/feedback', [
            'rating' => 4,
            'sentiment' => 'positive',
            'notes' => 'Helpful summary and citations.',
        ])->assertOk()
            ->assertJsonPath('data.feedback.rating', 4)
            ->assertJsonPath('data.feedback.sentiment', 'positive')
            ->assertJsonPath('data.feedback.notes', 'Helpful summary and citations.');

        $this->assertDatabaseHas('ai_interactions', [
            'id' => $interactionId,
            'feedback_rating' => 4,
            'feedback_sentiment' => 'positive',
            'feedback_notes' => 'Helpful summary and citations.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'ai.interaction.feedback_recorded',
            'entity_type' => 'ai_interaction',
            'entity_id' => (string) $interactionId,
        ]);
    }

    public function test_workspace_surfaces_review_analytics_recent_audit_activity_and_ranked_citations(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-006',
            userId: $employeeUser->id,
        );

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => now()->subDay()->toDateString(),
            'check_in_at' => now()->subDay()->setTime(9, 29),
            'check_out_at' => null,
            'worked_minutes' => 425,
            'primary_status' => 'present',
            'scheduled_start_at' => now()->subDay()->setTime(9, 0),
            'scheduled_end_at' => now()->subDay()->setTime(18, 0),
            'scheduled_work_minutes' => 540,
            'break_duration_minutes' => 30,
            'is_late' => true,
            'late_minutes' => 29,
            'is_half_day' => false,
            'overtime_minutes' => 0,
            'is_weekend' => false,
            'is_holiday' => false,
            'is_early_departure' => false,
            'early_departure_minutes' => 0,
            'calculated_at' => now()->subDay()->setTime(18, 5),
        ]);

        Sanctum::actingAs($employeeUser);

        $interactionId = $this->postJson('/api/v1/ai/chat', [
            'question' => 'Summarize my recent attendance posture.',
        ])->assertCreated()
            ->assertJsonPath('data.interaction.use_case', 'attendance_summary')
            ->assertJsonPath('data.interaction.citations.0.rank', 1)
            ->assertJsonPath('data.interaction.citations.0.evidence_strength', 'primary')
            ->json('data.interaction.id');

        $this->postJson('/api/v1/ai/interactions/'.$interactionId.'/feedback', [
            'rating' => 5,
            'sentiment' => 'positive',
            'notes' => 'The attendance source trail was clear and grounded.',
        ])->assertOk();

        $recommendationId = $this->postJson('/api/v1/ai/recommendations', [
            'scenario' => 'attendance_follow_up',
            'context_note' => 'Review before timesheet lock.',
        ])->assertCreated()
            ->assertJsonPath('data.supporting_citations.0.rank', 1)
            ->assertJsonPath('data.supporting_citations.0.evidence_strength', 'primary')
            ->json('data.id');

        $this->postJson('/api/v1/ai/recommendations/'.$recommendationId.'/decisions', [
            'decision' => 'accepted',
            'decision_notes' => 'Employee reviewed the guidance and will use the governed attendance route.',
        ])->assertOk();

        $this->getJson('/api/v1/ai/workspace')
            ->assertOk()
            ->assertJsonPath('data.summary.interaction_count', 1)
            ->assertJsonPath('data.summary.feedback_recorded_count', 1)
            ->assertJsonPath('data.summary.accepted_recommendation_count', 1)
            ->assertJsonPath('data.review_analytics.answer_quality.answered_count', 1)
            ->assertJsonPath('data.review_analytics.answer_quality.cited_answer_count', 1)
            ->assertJsonPath('data.review_analytics.answer_quality.citation_coverage_percent', 100)
            ->assertJsonPath('data.review_analytics.answer_quality.feedback_recorded_count', 1)
            ->assertJsonPath('data.review_analytics.answer_quality.average_feedback_rating', 5)
            ->assertJsonPath('data.review_analytics.recommendation_queue.pending_review_count', 0)
            ->assertJsonPath('data.review_analytics.recommendation_queue.accepted_count', 1)
            ->assertJsonPath('data.review_analytics.audit_activity.workspace_view_count', 1)
            ->assertJsonPath('data.review_analytics.audit_activity.interaction_generated_count', 1)
            ->assertJsonPath('data.review_analytics.audit_activity.feedback_event_count', 1)
            ->assertJsonPath('data.review_analytics.audit_activity.recommendation_generated_count', 1)
            ->assertJsonPath('data.review_analytics.audit_activity.recommendation_decision_count', 1)
            ->assertJsonPath('data.audit_timeline.0.event_type', 'ai.assistant.workspace.viewed')
            ->assertJsonPath('data.audit_timeline.1.event_type', 'ai.recommendation.decision_recorded')
            ->assertJsonPath('data.audit_timeline.2.event_type', 'ai.recommendation.generated')
            ->assertJsonPath('data.audit_timeline.3.event_type', 'ai.interaction.feedback_recorded')
            ->assertJsonPath('data.audit_timeline.4.event_type', 'ai.interaction.generated');
    }

    public function test_hr_can_query_policy_and_payslip_sources_for_selected_employee(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrUser = User::factory()->create(['company_id' => $company->id]);
        $hrUser->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);
        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'AI-EMP-005',
            userId: $employeeUser->id,
        );

        $document = Document::query()->create([
            'company_id' => $company->id,
            'title' => 'Remote work policy FY26',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'restricted',
            'original_file_name' => 'remote-work-policy-fy26.pdf',
            'disk' => 'local',
            'file_path' => 'documents/remote-work-policy-fy26.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 204800,
            'checksum_sha256' => str_repeat('a', 64),
            'retention_until' => '2027-07-01',
            'metadata' => ['acknowledgement_required' => true],
            'notes' => 'Updated remote work expectations and travel approval guardrails.',
            'created_by_user_id' => $hrUser->id,
            'updated_by_user_id' => $hrUser->id,
        ]);

        PolicyAcknowledgement::query()->create([
            'company_id' => $company->id,
            'document_id' => $document->id,
            'employee_id' => $employee->id,
            'policy_title' => 'Remote work policy FY26',
            'policy_version' => '2026.1',
            'status' => 'assigned',
            'assigned_by_user_id' => $hrUser->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'assignment_notes' => 'Review before hybrid schedule renewal.',
        ]);

        $calendar = PayrollCalendar::query()->create([
            'company_id' => $company->id,
            'name' => 'Monthly payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => true,
            'status' => 'active',
            'created_by_user_id' => $hrUser->id,
            'updated_by_user_id' => $hrUser->id,
        ]);

        $period = PayrollPeriod::query()->create([
            'company_id' => $company->id,
            'payroll_calendar_id' => $calendar->id,
            'name' => 'June 2026',
            'frequency' => 'monthly',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
            'status' => 'closed',
            'opened_at' => '2026-06-01 09:00:00',
            'prepared_at' => '2026-06-29 18:00:00',
            'closed_at' => '2026-06-30 19:00:00',
            'created_by_user_id' => $hrUser->id,
            'updated_by_user_id' => $hrUser->id,
        ]);

        $run = PayrollRun::query()->create([
            'company_id' => $company->id,
            'payroll_period_id' => $period->id,
            'name' => 'June 2026 primary run',
            'frequency' => 'monthly',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'locked',
            'prerequisite_snapshot' => ['inputs' => 'complete'],
            'prerequisite_summary' => ['blocking_issues' => 0],
            'input_summary' => ['employee_count' => 1],
            'calculation_summary' => ['net_pay_total' => 118500],
            'prepared_at' => '2026-06-29 18:30:00',
            'inputs_generated_at' => '2026-06-29 19:00:00',
            'calculated_at' => '2026-06-30 09:00:00',
            'approved_at' => '2026-06-30 11:00:00',
            'locked_at' => '2026-06-30 12:00:00',
            'created_by_user_id' => $hrUser->id,
            'updated_by_user_id' => $hrUser->id,
        ]);

        Payslip::query()->create([
            'company_id' => $company->id,
            'payroll_run_id' => $run->id,
            'payroll_period_id' => $period->id,
            'payroll_item_id' => null,
            'employee_id' => $employee->id,
            'employee_compensation_id' => null,
            'slip_number' => 'PS-2026-06-0001',
            'status' => 'generated',
            'currency' => 'INR',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
            'file_name' => 'PS-2026-06-0001.html',
            'gross_salary' => 135000,
            'total_earnings' => 140000,
            'total_deductions' => 21500,
            'net_salary' => 118500,
            'employer_cost' => 145000,
            'earnings_breakdown' => [['label' => 'Base', 'amount' => 120000]],
            'deductions_breakdown' => [['label' => 'Tax', 'amount' => 18000]],
            'employer_contribution_breakdown' => [['label' => 'PF', 'amount' => 5000]],
            'employee_snapshot' => ['full_name' => $employee->full_name],
            'company_snapshot' => ['name' => $company->name],
            'rendered_format' => 'html',
            'rendered_content' => '<p>Demo payslip</p>',
            'checksum_sha256' => str_repeat('b', 64),
            'generated_at' => '2026-06-30 12:05:00',
            'created_by_user_id' => $hrUser->id,
            'updated_by_user_id' => $hrUser->id,
        ]);

        Sanctum::actingAs($hrUser);

        $this->postJson('/api/v1/ai/chat', [
            'question' => 'Find the remote work policy documents and pending acknowledgements.',
            'subject_employee_id' => $employee->id,
        ])->assertCreated()
            ->assertJsonPath('data.interaction.use_case', 'policy_document')
            ->assertJsonPath('data.interaction.citations.0.type', 'policy_document')
            ->assertJsonPath('data.interaction.citations.1.type', 'policy_acknowledgement');

        $this->postJson('/api/v1/ai/chat', [
            'question' => 'Show the latest payslip for this employee.',
            'subject_employee_id' => $employee->id,
        ])->assertCreated()
            ->assertJsonPath('data.interaction.use_case', 'payslip_summary')
            ->assertJsonPath('data.interaction.citations.0.type', 'payslip')
            ->assertJsonPath('data.interaction.citations.0.label', 'PS-2026-06-0001');
    }

    private function createOrganizationContext(int $companyId): array
    {
        return [
            Department::factory()->create([
                'company_id' => $companyId,
                'code' => 'AI-OPS',
                'name' => 'AI Operations',
            ]),
            Designation::factory()->create([
                'company_id' => $companyId,
                'code' => 'AI-ANL',
                'name' => 'AI Analyst',
            ]),
            Location::factory()->create([
                'company_id' => $companyId,
                'code' => 'AI-BLR',
                'name' => 'Bengaluru HQ',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'city' => 'Bengaluru',
                'country' => 'India',
            ]),
        ];
    }

    private function createEmployee(
        int $companyId,
        int $departmentId,
        int $designationId,
        int $locationId,
        string $employeeCode,
        ?int $userId = null,
        ?int $managerId = null,
    ): Employee {
        return Employee::factory()->create([
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'location_id' => $locationId,
            'employee_code' => $employeeCode,
            'user_id' => $userId,
            'manager_id' => $managerId,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'date_of_joining' => '2025-01-15',
        ]);
    }
}
