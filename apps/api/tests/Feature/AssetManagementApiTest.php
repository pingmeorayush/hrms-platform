<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssetManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_tenant_admin_can_manage_asset_catalog_and_lifecycle_history(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employment_status' => 'active',
            'date_of_joining' => '2024-01-01',
        ]);

        Sanctum::actingAs($tenantAdmin);

        $categoryId = $this->postJson('/api/v1/assets/categories', [
            'code' => 'LAPTOP',
            'name' => 'Laptop',
            'status' => 'active',
            'notes' => 'Portable employee devices.',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'LAPTOP')
            ->json('data.id');

        $assetId = $this->postJson('/api/v1/assets', [
            'asset_category_id' => $categoryId,
            'asset_tag' => 'AST-LTP-1001',
            'name' => 'MacBook Pro 14',
            'asset_type' => 'physical',
            'serial_number' => 'SN-LTP-1001',
            'manufacturer' => 'Apple',
            'model_name' => 'MacBook Pro 14',
            'purchase_date' => '2026-05-15',
            'notes' => 'Primary engineering laptop.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.asset_category.code', 'LAPTOP')
            ->json('data.id');

        $this->getJson('/api/v1/assets?status=available')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $assetId);

        $this->postJson('/api/v1/assets/'.$assetId.'/assign', [
            'employee_id' => $employee->id,
            'assigned_at' => '2026-06-10 09:00:00',
            'expected_return_date' => '2027-06-10',
            'handover_condition' => 'sealed',
            'assignment_notes' => 'Assigned during onboarding.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.current_assignment.employee_id', $employee->id)
            ->assertJsonPath('data.current_assignment.status', 'assigned');

        $this->postJson('/api/v1/assets/'.$assetId.'/issue', [
            'issued_at' => '2026-06-10 10:00:00',
            'issue_notes' => 'Issued after device imaging.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'issued')
            ->assertJsonPath('data.current_assignment.status', 'issued');

        $this->getJson('/api/v1/assets?status=issued&employee_id='.$employee->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $assetId);

        $this->postJson('/api/v1/assets/'.$assetId.'/return', [
            'returned_at' => '2026-12-15 17:00:00',
            'return_condition' => 'good',
            'return_notes' => 'Returned with charger and sleeve.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.current_assignment', null)
            ->assertJsonPath('data.assignment_history.0.status', 'returned');

        $this->getJson('/api/v1/assets/'.$assetId)
            ->assertOk()
            ->assertJsonPath('data.assignment_history.0.employee_id', $employee->id)
            ->assertJsonPath('data.assignment_history.0.return_condition', 'good');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'asset.category.created',
            'entity_id' => (string) $categoryId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'asset.created',
            'entity_id' => (string) $assetId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'asset.assigned',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'asset.issued',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'asset.returned',
        ]);
    }

    public function test_asset_endpoints_enforce_tenant_scope_and_state_transitions(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $otherCompany = Company::factory()->create(['status' => 'active']);

        $category = AssetCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'PHONE',
            'name' => 'Phone',
            'status' => 'active',
        ]);

        $otherCategory = AssetCategory::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'code' => 'PHONE',
            'name' => 'Phone',
            'status' => 'active',
        ]);

        $asset = Asset::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'asset_category_id' => $category->id,
            'asset_tag' => 'AST-PHN-1001',
            'name' => 'Pixel 9',
            'asset_type' => 'physical',
            'status' => 'available',
        ]);

        $otherAsset = Asset::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'asset_category_id' => $otherCategory->id,
            'asset_tag' => 'AST-PHN-2001',
            'name' => 'Galaxy S25',
            'asset_type' => 'physical',
            'status' => 'available',
        ]);

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employment_status' => 'active',
            'date_of_joining' => '2024-01-01',
        ]);

        Sanctum::actingAs($tenantAdmin);

        $this->getJson('/api/v1/assets/'.$otherAsset->id)
            ->assertNotFound();

        $this->postJson('/api/v1/assets/'.$asset->id.'/return', [
            'return_condition' => 'good',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->postJson('/api/v1/assets/'.$asset->id.'/assign', [
            'employee_id' => $employee->id,
        ])->assertOk()
            ->assertJsonPath('data.status', 'assigned');

        $this->postJson('/api/v1/assets/'.$asset->id.'/assign', [
            'employee_id' => $employee->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->postJson('/api/v1/assets/'.$asset->id.'/issue', [])
            ->assertOk()
            ->assertJsonPath('data.status', 'issued');

        $this->postJson('/api/v1/assets/'.$asset->id.'/issue', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_asset_permissions_and_category_validation_are_enforced(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/assets')
            ->assertForbidden();

        $this->postJson('/api/v1/assets/categories', [
            'code' => 'TAB',
            'name' => 'Tablet',
            'status' => 'active',
        ])->assertForbidden();

        $assetManager = User::factory()->create(['company_id' => $company->id]);
        $assetManager->givePermissionTo('asset.manage');

        $inactiveCategory = AssetCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'LTC',
            'name' => 'Legacy Tech',
            'status' => 'inactive',
        ]);

        Sanctum::actingAs($assetManager);

        $this->postJson('/api/v1/assets', [
            'asset_category_id' => $inactiveCategory->id,
            'asset_tag' => 'AST-LTC-1001',
            'name' => 'Old Laptop',
            'asset_type' => 'physical',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['asset_category_id']);
    }

    private function createOrganizationContext(int $companyId): array
    {
        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'IT',
            'name' => 'IT',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'ENG',
            'name' => 'Engineer',
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'BLR',
            'name' => 'Bengaluru',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        return [$department, $designation, $location];
    }
}
