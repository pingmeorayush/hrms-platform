<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SalaryConfigurationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_manage_salary_components_and_versioned_salary_structures(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        Sanctum::actingAs($hrAdmin);

        $basicId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'basic',
            'name' => 'Basic Salary',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 50000,
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 1,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'BASIC')
            ->assertJsonPath('data.default_formula_inputs.flat_amount', '50000.00')
            ->json('data.id');

        $hraId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'hra',
            'name' => 'House Rent Allowance',
            'category' => 'earning',
            'calculation_type' => 'percentage',
            'percentage_value' => 40,
            'percentage_basis_component_codes' => ['basic'],
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 2,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.default_formula_inputs.percentage_value', '40.0000')
            ->assertJsonPath('data.default_formula_inputs.percentage_basis_component_codes.0', 'BASIC')
            ->json('data.id');

        $pfId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'pf',
            'name' => 'Provident Fund',
            'category' => 'deduction',
            'calculation_type' => 'percentage',
            'percentage_value' => 12,
            'percentage_basis_component_codes' => ['basic'],
            'is_taxable' => false,
            'is_proratable' => true,
            'display_order' => 3,
            'status' => 'active',
        ])->assertCreated()
            ->json('data.id');

        $this->patchJson("/api/v1/payroll/salary-components/{$pfId}", [
            'code' => 'PF',
            'name' => 'Provident Fund',
            'category' => 'deduction',
            'calculation_type' => 'expression',
            'expression_formula' => 'MIN(BASIC * 0.12, 1800)',
            'is_taxable' => false,
            'is_proratable' => true,
            'display_order' => 3,
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.calculation_type', 'expression')
            ->assertJsonPath('data.default_formula_inputs.expression_formula', 'MIN(BASIC * 0.12, 1800)');

        $structureId = $this->postJson('/api/v1/payroll/salary-structures', [
            'code' => 'ENG-G5',
            'name' => 'Engineering Grade 5',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'grade' => 'G5',
            'band' => 'B2',
            'level' => 'L1',
            'annual_ctc_amount' => 1800000,
            'basic_salary_amount' => 600000,
            'gross_salary_amount' => 150000,
            'net_salary_amount' => 118000,
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
            'status' => 'active',
            'notes' => 'Primary monthly engineering structure.',
            'components' => [
                [
                    'salary_component_id' => $basicId,
                    'display_order' => 1,
                    'configured_amount' => 50000,
                ],
                [
                    'salary_component_id' => $hraId,
                    'display_order' => 2,
                    'configured_percentage' => 40,
                    'configured_basis_component_codes' => ['BASIC'],
                ],
                [
                    'salary_component_id' => $pfId,
                    'display_order' => 3,
                    'configured_expression_formula' => 'MIN(BASIC * 0.12, 1800)',
                ],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.code', 'ENG-G5')
            ->assertJsonPath('data.version', 1)
            ->assertJsonPath('data.components.1.resolved_formula_inputs.percentage_basis_component_codes.0', 'BASIC')
            ->json('data.id');

        $versionedId = $this->patchJson("/api/v1/payroll/salary-structures/{$structureId}", [
            'code' => 'ENG-G5',
            'name' => 'Engineering Grade 5 Revised',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'grade' => 'G5',
            'band' => 'B2',
            'level' => 'L1',
            'annual_ctc_amount' => 1920000,
            'basic_salary_amount' => 640000,
            'gross_salary_amount' => 160000,
            'net_salary_amount' => 125000,
            'effective_from' => '2026-08-01',
            'revision_date' => '2026-08-01',
            'status' => 'active',
            'notes' => 'Revised structure for annual cycle.',
            'components' => [
                [
                    'salary_component_id' => $basicId,
                    'display_order' => 1,
                    'configured_amount' => 53333.33,
                ],
                [
                    'salary_component_id' => $hraId,
                    'display_order' => 2,
                    'configured_percentage' => 45,
                    'configured_basis_component_codes' => ['BASIC'],
                ],
                [
                    'salary_component_id' => $pfId,
                    'display_order' => 3,
                    'configured_expression_formula' => 'MIN(BASIC * 0.12, 1800)',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.previous_version_id', $structureId)
            ->assertJsonPath('data.components.1.resolved_formula_inputs.percentage_value', '45.0000')
            ->json('data.id');

        $this->getJson('/api/v1/payroll/salary-components')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'BASIC');

        $this->getJson('/api/v1/payroll/salary-structures')
            ->assertOk()
            ->assertJsonPath('data.0.id', $versionedId);

        $this->assertDatabaseHas('salary_structures', [
            'id' => $structureId,
            'status' => 'superseded',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'salary.component.created',
            'entity_id' => (string) $basicId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'salary.structure.versioned',
            'entity_id' => (string) $versionedId,
        ]);
    }

    public function test_salary_configuration_validation_and_scope_rules_are_enforced(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('hr.admin');

        Sanctum::actingAs($otherAdmin);

        $otherComponentId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'OTH-BASIC',
            'name' => 'Other Basic',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 42000,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'INVALID-PCT',
            'name' => 'Invalid Percentage',
            'category' => 'earning',
            'calculation_type' => 'percentage',
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['percentage_value', 'percentage_basis_component_codes']);

        $basicId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'BASIC',
            'name' => 'Basic Salary',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 50000,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $percentageId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'ALLOW',
            'name' => 'Allowance',
            'category' => 'earning',
            'calculation_type' => 'percentage',
            'percentage_value' => 15,
            'percentage_basis_component_codes' => ['BASIC'],
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'BASIC',
            'name' => 'Duplicate Basic',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 20000,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        $this->postJson('/api/v1/payroll/salary-structures', [
            'code' => 'BAD-SCOPE',
            'name' => 'Bad Scope',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'annual_ctc_amount' => 900000,
            'basic_salary_amount' => 360000,
            'gross_salary_amount' => 75000,
            'net_salary_amount' => 60000,
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
            'status' => 'active',
            'components' => [
                [
                    'salary_component_id' => $otherComponentId,
                    'configured_amount' => 1000,
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['components.0.salary_component_id']);

        $this->postJson('/api/v1/payroll/salary-structures', [
            'code' => 'BAD-BASIS',
            'name' => 'Bad Basis',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'annual_ctc_amount' => 900000,
            'basic_salary_amount' => 360000,
            'gross_salary_amount' => 75000,
            'net_salary_amount' => 60000,
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
            'status' => 'active',
            'components' => [
                [
                    'salary_component_id' => $percentageId,
                    'configured_basis_component_codes' => ['UNKNOWN'],
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['components.0.configured_basis_component_codes']);
    }

    public function test_salary_configuration_endpoints_are_tenant_scoped_and_permission_protected(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('hr.admin');

        Sanctum::actingAs($otherAdmin);

        $otherComponentId = $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'OTH-ALLOW',
            'name' => 'Other Allowance',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 12000,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $otherStructureId = $this->postJson('/api/v1/payroll/salary-structures', [
            'code' => 'OTH-G5',
            'name' => 'Other Structure',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'annual_ctc_amount' => 800000,
            'basic_salary_amount' => 320000,
            'gross_salary_amount' => 66666.67,
            'net_salary_amount' => 53000,
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
            'status' => 'active',
            'components' => [
                [
                    'salary_component_id' => $otherComponentId,
                    'configured_amount' => 12000,
                ],
            ],
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/payroll/salary-components')
            ->assertOk()
            ->assertJsonMissing(['id' => $otherComponentId]);

        $this->getJson('/api/v1/payroll/salary-structures')
            ->assertOk()
            ->assertJsonMissing(['id' => $otherStructureId]);

        $this->patchJson("/api/v1/payroll/salary-components/{$otherComponentId}", [
            'code' => 'OTH-ALLOW',
            'name' => 'Updated',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 14000,
            'status' => 'active',
        ])->assertNotFound();

        $employee = User::factory()->create(['company_id' => $company->id]);
        $employee->assignRole('employee');

        Sanctum::actingAs($employee);

        $this->postJson('/api/v1/payroll/salary-components', [
            'code' => 'EMP-TRY',
            'name' => 'Employee Try',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 1000,
            'status' => 'active',
        ])->assertForbidden();
    }
}
