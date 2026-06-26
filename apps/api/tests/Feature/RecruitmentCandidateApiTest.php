<?php

namespace Tests\Feature;

use App\Models\CandidateResume;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruitmentCandidateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config()->set('recruitment.resume_disk', 'recruitment-resumes');
    }

    public function test_recruiter_can_create_update_and_version_candidate_resumes(): void
    {
        Storage::fake('recruitment-resumes');

        $company = Company::factory()->create(['status' => 'active']);
        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-ENG']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-SSE']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-BLR']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);

        Sanctum::actingAs($recruiter);

        $candidateId = $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $requisition->id,
            'first_name' => 'Rhea',
            'last_name' => 'Kapoor',
            'email' => 'Rhea.Kapoor@example.com',
            'phone' => '+91-9990001111',
            'source' => 'manual',
            'current_stage' => 'applied',
            'total_experience_years' => 6,
            'notice_period_days' => 60,
            'current_company' => 'Atlas Systems',
            'current_title' => 'Platform Engineer',
            'summary' => 'Strong backend and platform engineering profile.',
            'notes' => 'High-priority sourcing lead.',
        ])->assertCreated()
            ->assertJsonPath('data.email', 'rhea.kapoor@example.com')
            ->assertJsonPath('data.current_stage', 'applied')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.requisition.id', $requisition->id)
            ->json('data.id');

        $this->patchJson("/api/v1/recruitment/candidates/{$candidateId}", [
            'current_title' => 'Senior Platform Engineer',
            'notes' => 'Resume updated after recruiter call.',
        ])->assertOk()
            ->assertJsonPath('data.current_title', 'Senior Platform Engineer');

        $resumeOneId = $this->post('/api/v1/recruitment/candidates/'.$candidateId.'/resumes', [
            'notes' => 'Original recruiter upload.',
            'file' => UploadedFile::fake()->create('rhea-kapoor-resume-v1.pdf', 220, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.is_current', true)
            ->json('data.id');

        $resumeTwoId = $this->post('/api/v1/recruitment/candidates/'.$candidateId.'/resumes', [
            'notes' => 'Candidate sent an updated resume after the screening call.',
            'file' => UploadedFile::fake()->create('rhea-kapoor-resume-v2.docx', 260, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.version_number', 2)
            ->assertJsonPath('data.is_current', true)
            ->json('data.id');

        $this->getJson('/api/v1/recruitment/candidates/'.$candidateId)
            ->assertOk()
            ->assertJsonPath('data.resume_count', 2)
            ->assertJsonPath('data.latest_resume.version_number', 2)
            ->assertJsonPath('data.resumes.0.id', $resumeTwoId)
            ->assertJsonPath('data.resumes.1.id', $resumeOneId)
            ->assertJsonPath('data.stage_history.0.to_stage', 'applied');

        $this->get('/api/v1/recruitment/candidates/'.$candidateId.'/resumes/'.$resumeOneId.'/download')
            ->assertOk()
            ->assertDownload('rhea-kapoor-resume-v1.pdf');

        $this->assertDatabaseHas('candidate_resumes', [
            'id' => $resumeOneId,
            'candidate_id' => $candidateId,
            'version_number' => 1,
            'is_current' => false,
        ]);

        $this->assertDatabaseHas('candidate_resumes', [
            'id' => $resumeTwoId,
            'candidate_id' => $candidateId,
            'version_number' => 2,
            'is_current' => true,
        ]);

        $storedResume = CandidateResume::query()->findOrFail($resumeTwoId);
        Storage::disk('recruitment-resumes')->assertExists($storedResume->file_path);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.candidate.created',
            'entity_id' => (string) $candidateId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.candidate.resume_uploaded',
            'entity_id' => (string) $resumeTwoId,
        ]);
    }

    public function test_duplicate_candidate_email_is_blocked_per_tenant_and_stage_transitions_are_audited(): void
    {
        Storage::fake('recruitment-resumes');

        $company = Company::factory()->create(['status' => 'active']);
        $otherCompany = Company::factory()->create(['status' => 'active']);

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $otherRecruiter = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherRecruiter->assignRole('recruiter');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-DATA']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-DE']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-DEL']);

        $otherDepartment = Department::factory()->create(['company_id' => $otherCompany->id, 'code' => 'REC-OPS']);
        $otherDesignation = Designation::factory()->create(['company_id' => $otherCompany->id, 'code' => 'REC-OE']);
        $otherLocation = Location::factory()->create(['company_id' => $otherCompany->id, 'code' => 'REC-MUM']);

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');
        $otherManagerUser = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherManagerUser->assignRole('manager');

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $otherHiringManager = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'user_id' => $otherManagerUser->id,
            'department_id' => $otherDepartment->id,
            'designation_id' => $otherDesignation->id,
            'location_id' => $otherLocation->id,
        ]);

        $requisition = $this->approvedRequisition($company, $recruiter, $hiringManager);
        $otherRequisition = $this->approvedRequisition($otherCompany, $otherRecruiter, $otherHiringManager);

        Sanctum::actingAs($recruiter);

        $candidateId = $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $requisition->id,
            'first_name' => 'Aarav',
            'last_name' => 'Shah',
            'email' => 'aarav.shah@example.com',
            'source' => 'manual',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $requisition->id,
            'first_name' => 'Aarav',
            'last_name' => 'Shah',
            'email' => 'AARAV.SHAH@example.com',
            'source' => 'referral',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/v1/recruitment/candidates/'.$candidateId.'/stage-transitions', [
            'to_stage' => 'screening',
            'comment' => 'Recruiter moved the candidate into recruiter screening.',
        ])->assertOk()
            ->assertJsonPath('data.current_stage', 'screening')
            ->assertJsonPath('data.status', 'active');

        $this->postJson('/api/v1/recruitment/candidates/'.$candidateId.'/stage-transitions', [
            'to_stage' => 'rejected',
            'comment' => 'Candidate did not meet the must-have platform architecture requirement.',
        ])->assertOk()
            ->assertJsonPath('data.current_stage', 'rejected')
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.stage_history.0.to_stage', 'rejected')
            ->assertJsonPath('data.stage_history.1.to_stage', 'screening')
            ->assertJsonPath('data.stage_history.2.to_stage', 'applied');

        Sanctum::actingAs($otherRecruiter);

        $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $otherRequisition->id,
            'first_name' => 'Aarav',
            'last_name' => 'Shah',
            'email' => 'aarav.shah@example.com',
            'source' => 'manual',
        ])->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.candidate.stage_transitioned',
            'entity_id' => (string) $candidateId,
        ]);
    }

    public function test_candidate_scope_follows_requisition_ownership_and_only_approved_requisitions_accept_candidates(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $otherCompany = Company::factory()->create(['status' => 'active']);

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $otherManagerUser = User::factory()->create(['company_id' => $company->id]);
        $otherManagerUser->assignRole('manager');

        $otherTenantHr = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherTenantHr->assignRole('hr.admin');

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-OPS']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-OM']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-CHE']);

        $ownerManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $otherManagerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $approvedRequisition = $this->approvedRequisition($company, $recruiter, $ownerManager);
        $draftRequisition = JobRequisition::query()->create([
            'company_id' => $company->id,
            'requisition_code' => 'REQ-DRAFT-1',
            'title' => 'Draft Operations Role',
            'employment_type' => 'full_time',
            'hiring_type' => 'new_position',
            'priority' => 'medium',
            'openings_count' => 1,
            'recruiter_user_id' => $recruiter->id,
            'hiring_manager_employee_id' => $ownerManager->id,
            'requested_by_user_id' => $recruiter->id,
            'status' => 'draft',
            'justification' => 'Draft requisition pending approval.',
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);

        Sanctum::actingAs($recruiter);

        $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $draftRequisition->id,
            'first_name' => 'Kiran',
            'last_name' => 'Iyer',
            'email' => 'kiran.iyer@example.com',
            'source' => 'manual',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['job_requisition_id']);

        $candidateId = $this->postJson('/api/v1/recruitment/candidates', [
            'job_requisition_id' => $approvedRequisition->id,
            'first_name' => 'Kiran',
            'last_name' => 'Iyer',
            'email' => 'kiran.iyer@example.com',
            'source' => 'manual',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/recruitment/candidates')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $candidateId);

        $this->getJson('/api/v1/recruitment/candidates/'.$candidateId)
            ->assertOk()
            ->assertJsonPath('data.id', $candidateId);

        Sanctum::actingAs($otherManagerUser);

        $this->getJson('/api/v1/recruitment/candidates/'.$candidateId)
            ->assertNotFound();

        Sanctum::actingAs($otherTenantHr);

        $this->getJson('/api/v1/recruitment/candidates/'.$candidateId)
            ->assertNotFound();
    }

    private function approvedRequisition(Company $company, User $recruiter, Employee $hiringManager): JobRequisition
    {
        return JobRequisition::query()->create([
            'company_id' => $company->id,
            'requisition_code' => 'REQ-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => 'Data Platform Lead',
            'employment_type' => 'full_time',
            'hiring_type' => 'new_position',
            'priority' => 'high',
            'openings_count' => 1,
            'recruiter_user_id' => $recruiter->id,
            'hiring_manager_employee_id' => $hiringManager->id,
            'requested_by_user_id' => $recruiter->id,
            'status' => 'approved',
            'justification' => 'Approved requisition for active hiring.',
            'submitted_at' => now()->subDay(),
            'approved_at' => now()->subHours(12),
            'created_by_user_id' => $recruiter->id,
            'updated_by_user_id' => $recruiter->id,
        ]);
    }
}
