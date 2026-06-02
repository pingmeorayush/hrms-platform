<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\NotificationRecord;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkflowNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_tenant_admin_can_create_version_and_publish_a_workflow_definition(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $createResponse = $this
            ->postJson('/api/v1/workflows', [
                'key' => 'expense-approval',
                'name' => 'Expense Approval Workflow',
                'module' => 'expense',
                'description' => 'Sequential expense approval workflow.',
                'is_template' => true,
                'publish' => true,
                'stages' => [
                    [
                        'key' => 'manager_review',
                        'name' => 'Manager Review',
                        'sequence' => 1,
                        'approver_type' => 'role',
                        'approver_value' => 'manager',
                        'available_actions' => ['approve', 'reject'],
                    ],
                    [
                        'key' => 'finance_review',
                        'name' => 'Finance Review',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'tenant.admin',
                        'available_actions' => ['approve', 'reject'],
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.key', 'expense-approval')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.active_version.version', 1);

        $workflowId = $createResponse->json('data.id');

        Sanctum::actingAs($tenantAdmin);

        $this
            ->patchJson("/api/v1/workflows/{$workflowId}", [
                'action' => 'new_version',
                'stages' => [
                    [
                        'key' => 'manager_review',
                        'name' => 'Manager Review',
                        'sequence' => 1,
                        'approver_type' => 'role',
                        'approver_value' => 'manager',
                        'available_actions' => ['approve', 'reject'],
                    ],
                    [
                        'key' => 'hr_review',
                        'name' => 'HR Review',
                        'sequence' => 2,
                        'approver_type' => 'role',
                        'approver_value' => 'hr.admin',
                        'available_actions' => ['approve', 'reject'],
                    ],
                    [
                        'key' => 'finance_review',
                        'name' => 'Finance Review',
                        'sequence' => 3,
                        'approver_type' => 'role',
                        'approver_value' => 'tenant.admin',
                        'available_actions' => ['approve', 'reject'],
                    ],
                ],
                'publish' => true,
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data.versions')
            ->assertJsonPath('data.active_version.version', 2);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'workflow.definition.versioned',
        ]);
    }

    public function test_leave_workflow_runs_sequentially_and_generates_notifications(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $starter = User::factory()->create(['company_id' => $company->id]);
        $starter->assignRole('tenant.admin');
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');
        $hrApprover = User::factory()->create(['company_id' => $company->id]);
        $hrApprover->assignRole('hr.admin');

        Sanctum::actingAs($starter);

        $startResponse = $this
            ->postJson('/api/v1/workflow-instances', [
                'workflow_key' => 'leave-approval',
                'reference_type' => 'leave_request',
                'reference_id' => 'LV-1001',
                'payload' => [
                    'employee_id' => 99,
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'running')
            ->assertJsonPath('data.tasks.0.assignee.id', $manager->id);

        $taskId = $startResponse->json('data.tasks.0.id');

        Sanctum::actingAs($manager);

        $this
            ->patchJson("/api/v1/tasks/{$taskId}", [
                'action' => 'approve',
            ])
            ->assertOk()
            ->assertJsonPath('data.decision', 'approve');

        $hrNotificationCount = NotificationRecord::query()
            ->where('user_id', $hrApprover->id)
            ->count();

        $this->assertSame(1, $hrNotificationCount);

        Sanctum::actingAs($hrApprover);

        $taskListing = $this
            ->getJson('/api/v1/tasks')
            ->assertOk();

        $nextTaskId = collect($taskListing->json('data'))
            ->firstWhere('assignee.id', $hrApprover->id)['id'];

        $this->assertNotNull($nextTaskId);

        Sanctum::actingAs($hrApprover);

        $this
            ->patchJson("/api/v1/tasks/{$nextTaskId}", [
                'action' => 'approve',
            ])
            ->assertOk();

        $workflowDefinition = WorkflowDefinition::query()->where('key', 'leave-approval')->firstOrFail();
        $this->assertDatabaseHas('workflow_instances', [
            'workflow_definition_id' => $workflowDefinition->id,
            'reference_id' => 'LV-1001',
            'status' => 'completed',
        ]);

        $starterNotification = NotificationRecord::query()
            ->where('user_id', $starter->id)
            ->where('title', 'Workflow Completed')
            ->first();

        $this->assertNotNull($starterNotification);
    }

    public function test_rejecting_a_workflow_task_notifies_the_starter_and_allows_mark_read(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $starter = User::factory()->create(['company_id' => $company->id]);
        $starter->assignRole('tenant.admin');
        $hrApprover = User::factory()->create(['company_id' => $company->id]);
        $hrApprover->assignRole('hr.admin');

        Sanctum::actingAs($starter);

        $instanceResponse = $this
            ->postJson('/api/v1/workflow-instances', [
                'workflow_key' => 'employee-approval',
                'reference_type' => 'employee_profile',
                'reference_id' => 'EMP-9001',
            ])
            ->assertCreated();

        $taskId = $instanceResponse->json('data.tasks.0.id');

        Sanctum::actingAs($hrApprover);

        $this
            ->patchJson("/api/v1/tasks/{$taskId}", [
                'action' => 'reject',
                'comment' => 'Missing contract attachment.',
            ])
            ->assertOk()
            ->assertJsonPath('data.decision', 'reject');

        $notification = NotificationRecord::query()
            ->where('user_id', $starter->id)
            ->where('delivery_status', 'delivered')
            ->latest('id')
            ->firstOrFail();

        Sanctum::actingAs($starter);

        $this
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.status', 'read');

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instanceResponse->json('data.id'),
            'status' => 'rejected',
        ]);
    }

    public function test_failed_notifications_can_be_retried_after_the_template_exists(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $actor = User::factory()->create(['company_id' => $company->id]);
        $actor->assignRole('tenant.admin');
        $recipient = User::factory()->create(['company_id' => $company->id]);
        $recipient->assignRole('employee');

        Sanctum::actingAs($actor);

        $createResponse = $this
            ->postJson('/api/v1/notifications', [
                'user_id' => $recipient->id,
                'template_key' => 'workflow.reminder.in_app',
                'variables' => [
                    'message' => 'Please review your pending approval.',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.delivery_status', 'failed');

        NotificationTemplate::query()->create([
            'company_id' => $company->id,
            'key' => 'workflow.reminder.in_app',
            'name' => 'Workflow Reminder',
            'category' => 'workflow',
            'channel' => 'in_app',
            'subject' => 'Workflow Reminder',
            'content' => '{{message}}',
            'variables' => ['message'],
            'status' => 'active',
        ]);

        $notificationId = $createResponse->json('data.id');

        Sanctum::actingAs($actor);

        $this
            ->postJson("/api/v1/notifications/{$notificationId}/retry")
            ->assertOk()
            ->assertJsonPath('data.delivery_status', 'delivered')
            ->assertJsonPath('data.retry_count', 2);
    }

    public function test_notification_center_returns_unread_counts_for_the_authenticated_user(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('employee');

        NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'system',
            'channel' => 'in_app',
            'title' => 'Unread notice',
            'message' => 'You have one unread notification.',
            'priority' => 'normal',
            'status' => 'unread',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ]);

        NotificationRecord::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'system',
            'channel' => 'in_app',
            'title' => 'Read notice',
            'message' => 'You have one read notification.',
            'priority' => 'normal',
            'status' => 'read',
            'delivery_status' => 'delivered',
            'read_at' => now(),
            'delivered_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.meta.unread_count', 1)
            ->assertJsonCount(2, 'data.items');
    }
}
