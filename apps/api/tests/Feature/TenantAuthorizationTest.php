<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\NotificationRecord;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_me_endpoint_returns_tenant_context_and_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tenant.admin');
        $token = $user->createToken('browser');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.tenant.company_id', $user->company_id);

        $this->assertContains('auth.manage_permissions', $response->json('data.permissions'));
    }

    public function test_inactive_tenant_blocks_access_to_protected_routes(): void
    {
        $company = Company::factory()->create(['status' => 'inactive']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $token = $user->createToken('browser');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(403)
            ->assertJsonPath('message', 'The tenant context is invalid or inactive.');
    }

    public function test_permission_middleware_denies_unprivileged_access_and_writes_an_audit_log(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('browser');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/admin/permissions')
            ->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to perform this action.');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'auth.permission.denied',
        ]);
    }

    public function test_authorized_user_can_list_roles_permissions_and_audit_logs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('platform.super_admin');
        $token = $user->createToken('browser');

        AuditLog::withoutGlobalScopes()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'auth.login.succeeded',
            'metadata' => [],
            'created_at' => now(),
        ]);

        $permissionsResponse = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/admin/permissions')
            ->assertOk();

        $this->assertContains('audit.view', collect($permissionsResponse->json('data'))->pluck('name')->all());

        $rolesResponse = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/admin/roles')
            ->assertOk();

        $this->assertContains('platform.super_admin', collect($rolesResponse->json('data'))->pluck('name')->all());

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/audit-logs')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_tenant_scoped_reads_exclude_other_company_records(): void
    {
        $user = User::factory()->create();
        $user->assignRole('platform.super_admin');
        $token = $user->createToken('browser');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $ownWorkflow = WorkflowDefinition::withoutGlobalScopes()->create([
            'company_id' => $user->company_id,
            'key' => 'own-workflow',
            'name' => 'Own Workflow',
            'module' => 'platform',
            'status' => 'draft',
            'is_template' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        WorkflowDefinition::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'key' => 'other-workflow',
            'name' => 'Other Workflow',
            'module' => 'platform',
            'status' => 'draft',
            'is_template' => false,
            'created_by' => $otherUser->id,
            'updated_by' => $otherUser->id,
        ]);

        AuditLog::withoutGlobalScopes()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event_type' => 'auth.login.succeeded',
            'metadata' => ['scope' => 'own'],
            'created_at' => now(),
        ]);

        AuditLog::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'user_id' => $otherUser->id,
            'event_type' => 'auth.login.succeeded',
            'metadata' => ['scope' => 'other'],
            'created_at' => now(),
        ]);

        NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'type' => 'system',
            'channel' => 'in_app',
            'title' => 'Own notice',
            'message' => 'Own company notification.',
            'priority' => 'normal',
            'status' => 'unread',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'user_id' => $otherUser->id,
            'type' => 'system',
            'channel' => 'in_app',
            'title' => 'Other notice',
            'message' => 'Other company notification.',
            'priority' => 'normal',
            'status' => 'unread',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $workflowResponse = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/workflows')
            ->assertOk();

        $this->assertSame([$ownWorkflow->id], collect($workflowResponse->json('data'))->pluck('id')->all());

        $auditResponse = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/audit-logs')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1);

        $this->assertSame(
            ['own'],
            collect($auditResponse->json('data.items'))->pluck('metadata.scope')->filter()->values()->all(),
        );

        $notificationResponse = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1);

        $this->assertSame(
            ['Own notice'],
            collect($notificationResponse->json('data.items'))->pluck('title')->all(),
        );
    }

    public function test_cross_tenant_notification_writes_are_blocked_by_default(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tenant.admin');
        $token = $user->createToken('browser');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/notifications', [
                'user_id' => $otherUser->id,
                'title' => 'Cross tenant notification',
                'message' => 'This should not be delivered.',
            ])->assertNotFound();

        $this->assertDatabaseMissing('notifications', [
            'title' => 'Cross tenant notification',
        ]);
    }

    public function test_role_creation_audits_the_assigned_permission_mapping(): void
    {
        $user = User::factory()->create();
        $user->assignRole('platform.super_admin');
        $token = $user->createToken('browser');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/admin/roles', [
                'name' => 'tenant.reviewer',
                'permissions' => ['notification.view', 'workflow.view'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'tenant.reviewer');

        $auditRecord = AuditLog::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('event_type', 'auth.role.created')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('tenant.reviewer', $auditRecord->metadata['role']);
        $this->assertSame(
            ['notification.view', 'workflow.view'],
            $auditRecord->metadata['permissions'],
        );
    }

    public function test_ui_visibility_contract_hides_restricted_navigation_for_an_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $token = $user->createToken('browser');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/ui/visibility')
            ->assertOk()
            ->assertJsonPath('data.meta.visible_navigation_count', 2)
            ->assertJsonPath('data.meta.hidden_navigation_count', 4);

        $navigation = collect($response->json('data.navigation'))->keyBy('id');
        $governanceActions = collect(
            collect($response->json('data.action_groups'))->keyBy('id')->get('governance')['actions'],
        )->keyBy('id');

        $this->assertTrue($navigation->get('foundation-overview')['visible']);
        $this->assertTrue($navigation->get('notification-center')['visible']);
        $this->assertFalse($navigation->get('workflow-console')['visible']);
        $this->assertFalse($navigation->get('access-control')['visible']);
        $this->assertFalse($governanceActions->get('create-role')['visible']);
        $this->assertFalse($governanceActions->get('review-audit-log')['visible']);
    }

    public function test_ui_visibility_contract_supports_any_match_navigation_rules(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('auth.manage_roles');
        $token = $user->createToken('browser');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/ui/visibility')
            ->assertOk()
            ->assertJsonPath('data.meta.visible_navigation_count', 2);

        $navigation = collect($response->json('data.navigation'))->keyBy('id');
        $governanceActions = collect(
            collect($response->json('data.action_groups'))->keyBy('id')->get('governance')['actions'],
        )->keyBy('id');

        $this->assertTrue($navigation->get('access-control')['visible']);
        $this->assertFalse($navigation->get('audit-trail')['visible']);
        $this->assertTrue($governanceActions->get('create-role')['visible']);
        $this->assertFalse($governanceActions->get('review-audit-log')['visible']);
    }
}
