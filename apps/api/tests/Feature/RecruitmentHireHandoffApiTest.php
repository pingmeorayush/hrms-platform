<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Models\JobRequisition;
use App\Models\Location;
use App\Models\Offer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruitmentHireHandoffApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_convert_accepted_offer_into_hire_handoff_and_queue_onboarding_tasks(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-OPS']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-LEAD']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-HYD']);

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);
        $candidate = $this->candidateForRequisition($company, $requisition, $recruiter);
        $resume = CandidateResume::query()->create([
            'company_id' => $company->id,
            'candidate_id' => $candidate->id,
            'version_number' => 1,
            'is_current' => true,
            'original_file_name' => 'rhea-kapoor-resume.pdf',
            'disk' => 'local',
            'file_path' => 'companies/'.$company->id.'/recruitment/candidates/'.$candidate->id.'/resumes/rhea-kapoor-resume.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'checksum_sha256' => hash('sha256', 'resume-v1'),
            'notes' => 'Current submitted resume.',
            'uploaded_by_user_id' => $recruiter->id,
        ]);

        $offer = Offer::query()->create([
            'company_id' => $company->id,
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'recruiter_user_id' => $recruiter->id,
            'requested_by_user_id' => $recruiter->id,
            'offer_code' => 'OFF-2001',
            'status' => 'accepted',
            'employment_type' => 'full_time',
            'currency' => 'INR',
            'annual_ctc_amount' => 2800000,
            'joining_bonus_amount' => 150000,
            'proposed_start_date' => '2026-07-15',
            'expires_on' => '2026-06-25',
            'accepted_at' => now()->subDay(),
            'candidate_message' => 'Welcome aboard.',
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);

        $templateA = EmployeeLifecycleTaskTemplate::query()->create([
            'company_id' => $company->id,
            'name' => 'Preboarding documents',
            'lifecycle_type' => 'onboarding',
            'title' => 'Collect joining documents',
            'category' => 'hr',
            'task_type' => 'collect_documents',
            'assignee_type' => 'hr',
            'due_offset_days' => 0,
            'sort_order' => 1,
            'is_active' => true,
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        $templateB = EmployeeLifecycleTaskTemplate::query()->create([
            'company_id' => $company->id,
            'name' => 'IT provisioning',
            'lifecycle_type' => 'onboarding',
            'title' => 'Prepare equipment and accounts',
            'category' => 'it',
            'task_type' => 'setup_equipment',
            'assignee_type' => 'hr',
            'due_offset_days' => 1,
            'sort_order' => 2,
            'is_active' => true,
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $handoffId = $this->postJson('/api/v1/recruitment/offers/'.$offer->id.'/handoff', [
            'trigger_onboarding' => true,
            'notes' => 'Ready for preboarding launch.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'onboarding_queued')
            ->assertJsonPath('data.offer.id', $offer->id)
            ->assertJsonPath('data.candidate.id', $candidate->id)
            ->assertJsonPath('data.employee.email', $candidate->email)
            ->assertJsonPath('data.source_resume.id', $resume->id)
            ->assertJsonCount(2, 'data.onboarding_template_ids')
            ->assertJsonCount(2, 'data.onboarding_task_ids')
            ->json('data.id');

        $this->assertDatabaseHas('recruitment_hire_handoffs', [
            'id' => $handoffId,
            'offer_id' => $offer->id,
            'candidate_id' => $candidate->id,
            'source_resume_id' => $resume->id,
            'status' => 'onboarding_queued',
        ]);

        $employee = Employee::query()->where('email', $candidate->email)->firstOrFail();

        $this->assertSame('inactive', $employee->employment_status);
        $this->assertSame($requisition->department_id, $employee->department_id);
        $this->assertSame($requisition->designation_id, $employee->designation_id);
        $this->assertSame($requisition->hiring_manager_employee_id, $employee->manager_id);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'current_stage' => 'hired',
            'status' => 'hired',
        ]);

        $this->assertDatabaseCount('employee_onboarding_tasks', 2);

        $this->getJson('/api/v1/recruitment/handoffs?offer_id='.$offer->id)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $handoffId);

        Sanctum::actingAs($recruiter);

        $this->getJson('/api/v1/recruitment/handoffs/'.$handoffId)
            ->assertOk()
            ->assertJsonPath('data.id', $handoffId)
            ->assertJsonPath('data.employee.id', $employee->id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'recruitment.hire_handoff.created',
            'entity_id' => (string) $handoffId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recruiter->id,
            'title' => 'Hire handoff created',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $managerUser->id,
            'title' => 'Hire handoff created',
        ]);
    }

    public function test_handoff_requires_accepted_offer_and_prevents_duplicate_conversion(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-PM']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-PM-D']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-PUN']);

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);
        $candidate = $this->candidateForRequisition($company, $requisition, $recruiter);

        $offer = Offer::query()->create([
            'company_id' => $company->id,
            'job_requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'recruiter_user_id' => $recruiter->id,
            'requested_by_user_id' => $recruiter->id,
            'offer_code' => 'OFF-2002',
            'status' => 'sent',
            'employment_type' => 'full_time',
            'currency' => 'INR',
            'annual_ctc_amount' => 1800000,
            'proposed_start_date' => '2026-07-01',
            'expires_on' => '2026-06-20',
            'sent_at' => now()->subDay(),
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/recruitment/offers/'.$offer->id.'/handoff', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['offer']);

        $offer->forceFill([
            'status' => 'accepted',
            'accepted_at' => now(),
        ])->save();

        $this->postJson('/api/v1/recruitment/offers/'.$offer->id.'/handoff', [
            'trigger_onboarding' => false,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'onboarding_skipped');

        $this->postJson('/api/v1/recruitment/offers/'.$offer->id.'/handoff', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['offer']);
    }

    private function approvedRequisition(Company $company, User $recruiter, Employee $hiringManager): JobRequisition
    {
        return JobRequisition::query()->create([
            'company_id' => $company->id,
            'requisition_code' => 'REC-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => 'Growth Product Manager',
            'employment_type' => 'full_time',
            'hiring_type' => 'new_position',
            'priority' => 'high',
            'openings_count' => 1,
            'min_experience_years' => 5,
            'target_start_date' => '2026-07-15',
            'department_id' => $hiringManager->department_id,
            'designation_id' => $hiringManager->designation_id,
            'location_id' => $hiringManager->location_id,
            'recruiter_user_id' => $recruiter->id,
            'hiring_manager_employee_id' => $hiringManager->id,
            'requested_by_user_id' => $recruiter->id,
            'status' => 'approved',
            'justification' => 'Approved business-critical opening.',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);
    }

    private function candidateForRequisition(Company $company, JobRequisition $requisition, User $recruiter): Candidate
    {
        return Candidate::query()->create([
            'company_id' => $company->id,
            'job_requisition_id' => $requisition->id,
            'candidate_code' => 'CAN-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'recruiter_user_id' => $recruiter->id,
            'first_name' => 'Rhea',
            'last_name' => 'Kapoor',
            'email' => 'rhea.kapoor+'.random_int(1000, 9999).'@example.com',
            'phone' => '+91-9990001111',
            'source' => 'manual',
            'current_stage' => 'offer',
            'status' => 'active',
            'stage_entered_at' => now()->subDay(),
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);
    }
}
