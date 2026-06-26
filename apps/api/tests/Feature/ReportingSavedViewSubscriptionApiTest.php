<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\ReportDataset;
use App\Models\ReportSubscription;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportingSavedViewSubscriptionApiTest extends TestCase
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
    }

    public function test_manager_can_consume_role_shared_saved_view_and_deliver_subscription_with_masked_export(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');
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

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-O001',
            'first_name' => 'Omar',
            'last_name' => 'Other',
            'email' => 'omar.other@example.com',
            'employment_status' => 'active',
        ]);

        $dataset = $this->createWorkforceDataset($company, $hrAdmin);

        Sanctum::actingAs($hrAdmin);

        $sharedViewResponse = $this->postJson('/api/v1/reporting/saved-views', [
            'dataset_key' => $dataset->key,
            'name' => 'Team Active Workforce',
            'share_scope' => 'roles',
            'shared_role_names' => ['manager'],
            'filters' => [
                'employment_status' => 'active',
            ],
            'sort_by' => 'employee_code',
            'sort_direction' => 'asc',
            'presentation_preferences' => [
                'visible_columns' => ['employee_code', 'employee_name', 'employee_email'],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.share.scope', 'roles');

        $sharedViewId = $sharedViewResponse->json('data.id');

        $this->postJson('/api/v1/reporting/saved-views', [
            'dataset_key' => $dataset->key,
            'name' => 'Private Workforce Audit',
            'share_scope' => 'private',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->assertCreated();

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/reporting/saved-views')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $sharedViewId)
            ->assertJsonPath('data.items.0.validation.status', 'valid');

        $subscriptionResponse = $this->postJson('/api/v1/reporting/subscriptions', [
            'saved_report_view_id' => $sharedViewId,
            'name' => 'Weekly Team Workforce',
            'frequency' => 'weekly',
            'timezone' => 'Asia/Kolkata',
            'schedule_config' => [
                'time_of_day' => '09:30',
                'weekday' => 1,
            ],
            'export_format' => 'json',
        ])->assertCreated()
            ->assertJsonPath('data.source.saved_view.id', $sharedViewId)
            ->assertJsonPath('data.validation.status', 'valid');

        $subscriptionId = $subscriptionResponse->json('data.id');

        $deliveryResponse = $this->postJson('/api/v1/reporting/subscriptions/'.$subscriptionId.'/deliver')
            ->assertOk()
            ->assertJsonPath('data.last_delivery.status', 'completed')
            ->assertJsonPath('data.status', 'active');

        $subscription = ReportSubscription::query()->findOrFail($subscriptionId);
        $this->assertNotNull($subscription->last_report_export_id);

        $export = $subscription->lastReportExport()->firstOrFail();
        $payload = json_decode(Storage::disk('local')->get($export->file_path), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(['employee_email'], $payload['visibility']['masked_field_keys']);
        $this->assertCount(2, $payload['items']);
        $this->assertSame('k************@example.com', $payload['items'][0]['employee_email']);
        $this->assertSame('d**********@example.com', $payload['items'][1]['employee_email']);
        $this->assertDatabaseHas('notifications', [
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'title' => 'Report export is ready',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'reporting.subscription.delivered',
        ]);
    }

    public function test_archived_shared_saved_view_is_removed_from_shared_consumers(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id]);
        $designation = Designation::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'employment_status' => 'active',
        ]);

        $dataset = $this->createWorkforceDataset($company, $hrAdmin);

        Sanctum::actingAs($hrAdmin);

        $viewId = $this->postJson('/api/v1/reporting/saved-views', [
            'dataset_key' => $dataset->key,
            'name' => 'Company Workforce Lens',
            'share_scope' => 'company',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->json('data.id');

        Sanctum::actingAs($managerUser);
        $this->getJson('/api/v1/reporting/saved-views/'.$viewId)->assertOk();

        Sanctum::actingAs($hrAdmin);
        $this->deleteJson('/api/v1/reporting/saved-views/'.$viewId)
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');

        Sanctum::actingAs($managerUser);
        $this->getJson('/api/v1/reporting/saved-views/'.$viewId)->assertForbidden();
    }

    public function test_subscription_is_blocked_when_dataset_certification_changes_after_creation(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id]);
        $designation = Designation::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'employment_status' => 'active',
        ]);

        $dataset = $this->createWorkforceDataset($company, $managerUser);

        Sanctum::actingAs($managerUser);

        $subscriptionId = $this->postJson('/api/v1/reporting/subscriptions', [
            'dataset_key' => $dataset->key,
            'name' => 'Direct Workforce Delivery',
            'frequency' => 'daily',
            'timezone' => 'Asia/Kolkata',
            'schedule_config' => [
                'time_of_day' => '08:15',
            ],
            'export_format' => 'csv',
            'filters' => [
                'employment_status' => 'active',
            ],
        ])->json('data.id');

        $dataset->forceFill([
            'certification_status' => 'draft',
        ])->save();

        $this->postJson('/api/v1/reporting/subscriptions/'.$subscriptionId.'/deliver')
            ->assertOk()
            ->assertJsonPath('data.status', 'blocked')
            ->assertJsonPath('data.last_delivery.status', 'blocked')
            ->assertJsonPath('data.validation.status', 'blocked');

        $this->assertDatabaseHas('report_subscriptions', [
            'id' => $subscriptionId,
            'status' => 'blocked',
            'last_delivery_status' => 'blocked',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'reporting.subscription.blocked',
        ]);
    }

    private function createWorkforceDataset(Company $company, User $actor): ReportDataset
    {
        return ReportDataset::query()->create([
            'company_id' => $company->id,
            'key' => 'workforce_headcount_snapshot',
            'name' => 'Workforce Headcount Snapshot',
            'domain' => 'workforce',
            'description' => 'Saved view and subscription baseline.',
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
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);
    }
}
