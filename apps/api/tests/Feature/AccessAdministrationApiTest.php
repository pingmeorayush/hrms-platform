<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccessAdministrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_tenant_admin_can_list_create_and_update_users_within_their_tenant(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');
        Sanctum::actingAs($tenantAdmin);

        $managedUser = User::factory()->create([
            'company_id' => $tenantAdmin->company_id,
            'name' => 'Team Member',
            'email' => 'team.member@phoenixhrms.test',
        ]);
        $managedUser->assignRole('employee');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        User::factory()->create([
            'company_id' => $otherCompany->id,
            'email' => 'outsider@phoenixhrms.test',
        ]);

        $listResponse = $this->getJson('/api/v1/admin/users')
            ->assertOk();

        $emails = collect($listResponse->json('data'))->pluck('email')->all();

        $this->assertContains($tenantAdmin->email, $emails);
        $this->assertContains('team.member@phoenixhrms.test', $emails);
        $this->assertNotContains('outsider@phoenixhrms.test', $emails);

        $this->postJson('/api/v1/admin/users', [
                'name' => 'Access Analyst',
                'email' => 'access.analyst@phoenixhrms.test',
                'password' => 'Password@12345',
                'password_confirmation' => 'Password@12345',
                'roles' => ['employee'],
                'requires_mfa' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'access.analyst@phoenixhrms.test')
            ->assertJsonPath('data.requires_mfa', true)
            ->assertJsonPath('data.roles.0', 'employee');

        $createdUser = User::query()->where('email', 'access.analyst@phoenixhrms.test')->firstOrFail();
        $this->assertSame($tenantAdmin->company_id, $createdUser->company_id);

        $this->patchJson("/api/v1/admin/users/{$managedUser->id}", [
                'roles' => ['manager', 'employee'],
                'is_active' => false,
                'requires_mfa' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.requires_mfa', true);

        $managedUser->refresh();

        $this->assertFalse($managedUser->is_active);
        $this->assertTrue($managedUser->requires_mfa);
        $this->assertEqualsCanonicalizing(['employee', 'manager'], $managedUser->getRoleNames()->all());

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $tenantAdmin->company_id,
            'user_id' => $tenantAdmin->id,
            'event_type' => 'auth.user.created',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $tenantAdmin->company_id,
            'user_id' => $tenantAdmin->id,
            'event_type' => 'auth.user.updated',
        ]);
    }

    public function test_non_platform_admin_cannot_assign_platform_roles(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');
        Sanctum::actingAs($tenantAdmin);

        $managedUser = User::factory()->create([
            'company_id' => $tenantAdmin->company_id,
        ]);

        $this->patchJson("/api/v1/admin/users/{$managedUser->id}", [
                'roles' => ['platform.super_admin'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.roles.0', 'One or more selected roles are not assignable in this session.');
    }

    public function test_non_platform_admin_cannot_view_or_manage_platform_level_admin_users(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');
        Sanctum::actingAs($tenantAdmin);

        $platformAdmin = User::factory()->create([
            'company_id' => $tenantAdmin->company_id,
            'email' => 'platform.admin@phoenixhrms.test',
        ]);
        $platformAdmin->assignRole('platform.super_admin');

        $listResponse = $this->getJson('/api/v1/admin/users')
            ->assertOk();

        $this->assertNotContains(
            'platform.admin@phoenixhrms.test',
            collect($listResponse->json('data'))->pluck('email')->all(),
        );

        $this->patchJson("/api/v1/admin/users/{$platformAdmin->id}", [
                'is_active' => false,
            ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'This session cannot manage platform-level administrator accounts.');
    }

    public function test_role_listing_hides_platform_roles_for_non_platform_admins(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');
        Sanctum::actingAs($tenantAdmin);

        $tenantRoles = $this->getJson('/api/v1/admin/roles')
            ->assertOk()
            ->json('data');

        $this->assertNotContains(
            'platform.super_admin',
            collect($tenantRoles)->pluck('name')->all(),
        );

        $platformAdmin = User::factory()->create([
            'company_id' => $tenantAdmin->company_id,
        ]);
        $platformAdmin->assignRole('platform.super_admin');
        Sanctum::actingAs($platformAdmin);

        $platformRoles = $this->getJson('/api/v1/admin/roles')
            ->assertOk()
            ->json('data');

        $this->assertContains(
            'platform.super_admin',
            collect($platformRoles)->pluck('name')->all(),
        );
    }

    public function test_only_platform_super_admin_can_update_shared_role_definitions(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');
        Sanctum::actingAs($tenantAdmin);

        $employeeRoleId = \Spatie\Permission\Models\Role::query()
            ->where('name', 'employee')
            ->value('id');

        $this->patchJson("/api/v1/admin/roles/{$employeeRoleId}", [
                'permissions' => ['notification.view'],
            ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Only platform super admins can change shared role definitions.');

        $platformAdmin = User::factory()->create([
            'company_id' => $tenantAdmin->company_id,
        ]);
        $platformAdmin->assignRole('platform.super_admin');
        Sanctum::actingAs($platformAdmin);

        $this->patchJson("/api/v1/admin/roles/{$employeeRoleId}", [
                'permissions' => ['notification.view', 'leave.view'],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'employee');

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $platformAdmin->company_id,
            'user_id' => $platformAdmin->id,
            'event_type' => 'auth.role.updated',
        ]);
    }
}
