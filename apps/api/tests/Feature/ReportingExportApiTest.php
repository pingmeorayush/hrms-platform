<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\ReportDataset;
use App\Models\ReportExport;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportingExportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Storage::fake('local');
        config()->set('reporting.exports.disk', 'local');
        config()->set('reporting.exports.sync_row_limit', 10);
        config()->set('reporting.exports.max_row_limit', 50);
        config()->set('reporting.exports.retention_hours', 24);
    }

    public function test_hr_admin_can_generate_and_download_sync_csv_export(): void
    {
        [$company, $hrAdmin, $department, $designation, $location] = $this->createHrReportingContext();

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1001',
            'first_name' => 'Aarav',
            'last_name' => 'Singh',
            'email' => 'aarav.singh@example.com',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1002',
            'first_name' => 'Meera',
            'last_name' => 'Joshi',
            'email' => 'meera.joshi@example.com',
            'employment_status' => 'active',
        ]);

        $this->createWorkforceDataset($company);

        Sanctum::actingAs($hrAdmin);

        $response = $this->postJson('/api/v1/reporting/exports', [
            'dataset_key' => 'workforce_headcount_snapshot',
            'format' => 'csv',
            'execution_mode' => 'sync',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.format', 'csv')
            ->assertJsonPath('data.counts.exported_row_count', 2)
            ->assertJsonPath('data.file.download_available', true);

        $export = ReportExport::query()->with('reportDataset')->findOrFail($response->json('data.id'));

        $this->assertNotNull($export->file_path);
        $this->assertTrue(Storage::disk('local')->exists($export->file_path));

        $storedContent = Storage::disk('local')->get($export->file_path);
        $this->assertStringContainsString('Employee Email', $storedContent);
        $this->assertStringContainsString('aarav.singh@example.com', $storedContent);
        $this->assertStringContainsString('meera.joshi@example.com', $storedContent);

        $this->get('/api/v1/reporting/exports/'.$export->id.'/download')
            ->assertOk()
            ->assertDownload($export->file_name);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.export.requested',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.export.completed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.export.downloaded',
        ]);
    }

    public function test_manager_export_preserves_masked_sensitive_fields(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Operations']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Manager']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Mumbai']);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP-M001',
            'first_name' => 'Karan',
            'last_name' => 'Manager',
            'email' => 'karan.manager@example.com',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'employee_code' => 'EMP-R001',
            'first_name' => 'Diya',
            'last_name' => 'Report',
            'email' => 'diya.report@example.com',
            'employment_status' => 'active',
        ]);

        $this->createWorkforceDataset($company);

        Sanctum::actingAs($managerUser);

        $response = $this->postJson('/api/v1/reporting/exports', [
            'dataset_key' => 'workforce_headcount_snapshot',
            'format' => 'json',
            'execution_mode' => 'sync',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.visibility.masked_field_keys.0', 'employee_email');

        $export = ReportExport::query()->findOrFail($response->json('data.id'));
        $payload = json_decode(Storage::disk('local')->get($export->file_path), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('workforce_headcount_snapshot', $payload['dataset']['key']);
        $this->assertSame(['employee_email'], $payload['visibility']['masked_field_keys']);
        $this->assertSame('k************@example.com', $payload['items'][0]['employee_email']);
        $this->assertSame('d**********@example.com', $payload['items'][1]['employee_email']);
    }

    public function test_large_export_is_queued_then_processed_with_completion_notification(): void
    {
        config()->set('reporting.exports.sync_row_limit', 1);
        config()->set('reporting.exports.max_row_limit', 10);

        [$company, $hrAdmin, $department, $designation, $location] = $this->createHrReportingContext();

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1001',
            'first_name' => 'Aarav',
            'last_name' => 'Singh',
            'email' => 'aarav.singh@example.com',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1002',
            'first_name' => 'Meera',
            'last_name' => 'Joshi',
            'email' => 'meera.joshi@example.com',
            'employment_status' => 'active',
        ]);

        $this->createWorkforceDataset($company);

        Sanctum::actingAs($hrAdmin);

        $queuedResponse = $this->postJson('/api/v1/reporting/exports', [
            'dataset_key' => 'workforce_headcount_snapshot',
            'format' => 'csv',
            'execution_mode' => 'auto',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'queued')
            ->assertJsonPath('data.counts.estimated_row_count', 2);

        $exportId = $queuedResponse->json('data.id');

        $this->postJson('/api/v1/reporting/exports/'.$exportId.'/process')
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.counts.exported_row_count', 2)
            ->assertJsonPath('data.file.download_available', true);

        $export = ReportExport::query()->findOrFail($exportId);

        $this->assertNotNull($export->notified_at);
        $this->assertNotNull($export->retention_expires_at);
        $this->assertTrue(Storage::disk('local')->exists($export->file_path));
        $this->assertDatabaseHas('notifications', [
            'company_id' => $company->id,
            'user_id' => $hrAdmin->id,
            'title' => 'Report export is ready',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.export.queued',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.export.processing',
        ]);
    }

    private function createHrReportingContext(): array
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Engineering']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Engineer']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Bengaluru']);

        return [$company, $hrAdmin, $department, $designation, $location];
    }

    private function createWorkforceDataset(Company $company): ReportDataset
    {
        return ReportDataset::query()->create([
            'company_id' => $company->id,
            'key' => 'workforce_headcount_snapshot',
            'name' => 'Workforce Headcount Snapshot',
            'domain' => 'workforce',
            'description' => 'Workforce export baseline',
            'source_references' => [
                ['module' => 'employee', 'entity' => 'employees', 'field' => 'employment_status'],
            ],
            'grain' => 'employee',
            'approved_fields' => [
                [
                    'key' => 'employee_code',
                    'label' => 'Employee Code',
                    'type' => 'string',
                    'sensitive' => false,
                ],
                [
                    'key' => 'employee_name',
                    'label' => 'Employee Name',
                    'type' => 'string',
                    'sensitive' => false,
                ],
                [
                    'key' => 'employee_email',
                    'label' => 'Employee Email',
                    'type' => 'string',
                    'sensitive' => true,
                    'masking_strategy' => 'partial',
                ],
                [
                    'key' => 'employment_status',
                    'label' => 'Employment Status',
                    'type' => 'status',
                    'sensitive' => false,
                ],
            ],
            'approved_filters' => [
                [
                    'key' => 'employment_status',
                    'label' => 'Employment Status',
                    'type' => 'status',
                    'required' => false,
                    'operators' => ['eq', 'in'],
                ],
            ],
            'drilldown_paths' => [
                [
                    'key' => 'employee_profile',
                    'label' => 'Employee profile',
                    'target_dataset_key' => 'workforce_headcount_snapshot',
                    'description' => 'Governed employee detail drilldown.',
                    'allowed_filter_keys' => ['employment_status'],
                ],
            ],
            'masking_posture' => [
                'default_strategy' => 'partial',
                'sensitive_field_keys' => ['employee_email'],
                'notes' => 'Mask employee email outside elevated workforce reporting roles.',
            ],
            'freshness_expectation_minutes' => 60,
            'certification_status' => 'certified',
            'version' => 1,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);
    }
}
