<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationSyncJob;
use App\Models\User;
use App\Models\WebhookSubscription;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IntegrationsPlatformApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_tenant_admin_can_create_connections_subscriptions_and_dispatch_outbound_jobs(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $connectionResponse = $this
            ->postJson('/api/v1/integrations/connections', [
                'system_key' => 'identity_directory',
                'name' => 'Directory outbound bridge',
                'direction' => 'outbound',
                'status' => 'active',
                'auth_mode' => 'hmac_sha256',
                'endpoint_url' => 'https://integrations.example.test/connections/directory',
                'scopes' => ['employee.profile', 'employee.lifecycle'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.system_key', 'identity_directory')
            ->assertJsonPath('data.status', 'active');

        $connectionId = $connectionResponse->json('data.id');

        $subscriptionResponse = $this
            ->postJson('/api/v1/integrations/webhook-subscriptions', [
                'integration_connection_id' => $connectionId,
                'event_key' => 'employee.updated',
                'direction' => 'outbound',
                'status' => 'active',
                'endpoint_url' => 'https://integrations.example.test/webhooks/employee-updated',
                'secret' => 'integration-secret-employee-updated',
                'custom_headers' => [
                    'X-Partner-Key' => 'phoenix-demo',
                ],
                'filter_rules' => [
                    'entity_types' => ['employee'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.event_key', 'employee.updated')
            ->assertJsonPath('data.direction', 'outbound');

        Http::fake([
            'https://integrations.example.test/*' => Http::response([
                'received' => true,
                'source' => 'partner-mock',
            ], 202),
        ]);

        $dispatchResponse = $this
            ->postJson('/api/v1/integrations/events/dispatch', [
                'event_key' => 'employee.updated',
                'entity_type' => 'employee',
                'entity_id' => 'EMP-1001',
                'payload' => [
                    'employee_code' => 'EMP-1001',
                    'status' => 'active',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.status', 'completed')
            ->assertJsonPath('data.items.0.response_payload.body.received', true);

        $jobId = $dispatchResponse->json('data.items.0.id');

        $this
            ->getJson('/api/v1/integrations/sync-jobs?event_key=employee.updated')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $jobId)
            ->assertJsonPath('data.items.0.monitoring_state', 'completed');

        $this->assertDatabaseHas('integration_connections', [
            'id' => $connectionId,
            'company_id' => $company->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('webhook_subscriptions', [
            'id' => $subscriptionResponse->json('data.id'),
            'company_id' => $company->id,
            'event_key' => 'employee.updated',
            'direction' => 'outbound',
        ]);

        $this->assertDatabaseHas('integration_sync_jobs', [
            'id' => $jobId,
            'company_id' => $company->id,
            'event_key' => 'employee.updated',
            'status' => 'completed',
            'direction' => 'outbound',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'integrations.event.dispatched',
        ]);
    }

    public function test_failed_outbound_sync_job_can_be_retried_and_reviewed(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $connection = $this->createConnection($company->id, $tenantAdmin->id, 'payroll_partner', 'Payroll sync bridge', 'outbound');
        $subscription = $this->createSubscription(
            $company->id,
            $connection->id,
            $tenantAdmin->id,
            'leave.request.approved',
            'outbound',
            'https://integrations.example.test/webhooks/leave-approved',
            'retry-secret-leave-approved',
        );

        Sanctum::actingAs($tenantAdmin);

        Http::fake([
            'https://integrations.example.test/*' => Http::sequence()
                ->push([
                    'received' => false,
                    'error' => 'partner unavailable',
                ], 500)
                ->push([
                    'received' => true,
                    'retry' => 'successful',
                ], 200),
        ]);

        $dispatchResponse = $this
            ->postJson('/api/v1/integrations/events/dispatch', [
                'event_key' => 'leave.request.approved',
                'entity_type' => 'leave_request',
                'entity_id' => 'LV-1001',
                'payload' => [
                    'leave_request_code' => 'LV-1001',
                    'status' => 'approved',
                ],
                'subscription_ids' => [$subscription->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.status', 'failed');

        $jobId = $dispatchResponse->json('data.items.0.id');

        $this->assertDatabaseHas('integration_sync_errors', [
            'integration_sync_job_id' => $jobId,
            'attempt_number' => 1,
            'error_code' => 'delivery_failed',
        ]);

        $this
            ->postJson("/api/v1/integrations/sync-jobs/{$jobId}/retry")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.monitoring_state', 'retried')
            ->assertJsonPath('data.attempts_count', 2)
            ->assertJsonPath('data.can_retry', false);

        $this->assertDatabaseHas('integration_sync_jobs', [
            'id' => $jobId,
            'status' => 'completed',
            'attempts_count' => 2,
        ]);

        $this->assertDatabaseHas('integration_sync_errors', [
            'integration_sync_job_id' => $jobId,
            'attempt_number' => 1,
            'resolved_by_user_id' => $tenantAdmin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'integrations.sync-job.retry-requested',
        ]);
    }

    public function test_queued_sync_job_can_be_processed_from_operator_controls(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $connection = $this->createConnection($company->id, $tenantAdmin->id, 'identity_directory', 'Directory outbound bridge', 'outbound');
        $subscription = $this->createSubscription(
            $company->id,
            $connection->id,
            $tenantAdmin->id,
            'employee.updated',
            'outbound',
            'https://integrations.example.test/webhooks/employee-updated',
            'process-secret-employee-updated',
        );

        Sanctum::actingAs($tenantAdmin);

        Http::fake([
            'https://integrations.example.test/*' => Http::response([
                'processed' => true,
                'downstream' => 'directory',
            ], 202),
        ]);

        $dispatchResponse = $this
            ->postJson('/api/v1/integrations/events/dispatch', [
                'event_key' => 'employee.updated',
                'entity_type' => 'employee',
                'entity_id' => 'EMP-1012',
                'payload' => [
                    'employee_code' => 'EMP-1012',
                    'status' => 'active',
                ],
                'subscription_ids' => [$subscription->id],
                'process_now' => false,
            ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.status', 'queued');

        $jobId = $dispatchResponse->json('data.items.0.id');

        $this
            ->postJson("/api/v1/integrations/sync-jobs/{$jobId}/process")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.monitoring_state', 'completed')
            ->assertJsonPath('data.attempts_count', 1)
            ->assertJsonPath('data.response_payload.body.processed', true);

        $this->assertDatabaseHas('integration_sync_jobs', [
            'id' => $jobId,
            'status' => 'completed',
            'attempts_count' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'integrations.sync-job.processing',
        ]);
    }

    public function test_public_inbound_webhook_validates_signature_and_creates_sync_job(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $connection = $this->createConnection($company->id, $tenantAdmin->id, 'identity_directory', 'Directory inbound bridge', 'bidirectional');
        $subscription = $this->createSubscription(
            $company->id,
            $connection->id,
            $tenantAdmin->id,
            'directory.profile.sync',
            'inbound',
            null,
            'directory-inbound-secret',
        );

        $payload = [
            'entity_type' => 'employee',
            'entity_id' => 'EMP-9001',
            'records' => [
                ['employee_code' => 'EMP-9001', 'status' => 'active'],
            ],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = $this->signPayload('directory-inbound-secret', $body);

        $this
            ->call(
                'POST',
                "/api/v1/integrations/webhooks/{$subscription->subscription_key}",
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_X_PHOENIXHRMS_SIGNATURE' => $signature,
                ],
                $body,
            )
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.direction', 'inbound');

        $this->assertDatabaseHas('integration_sync_jobs', [
            'company_id' => $company->id,
            'webhook_subscription_id' => $subscription->id,
            'direction' => 'inbound',
            'trigger_source' => 'webhook',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'event_type' => 'integrations.webhook.received',
        ]);

        $invalidBody = json_encode([
            'entity_type' => 'employee',
            'entity_id' => 'EMP-9002',
        ], JSON_THROW_ON_ERROR);

        $this
            ->call(
                'POST',
                "/api/v1/integrations/webhooks/{$subscription->subscription_key}",
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_X_PHOENIXHRMS_SIGNATURE' => 'sha256=invalid-signature',
                ],
                $invalidBody,
            )
            ->assertStatus(422);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'event_type' => 'integrations.webhook.rejected',
        ]);
    }

    public function test_integration_endpoints_are_permission_protected_and_tenant_scoped(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $otherCompany = Company::factory()->create([
            'slug' => 'other-tenant',
            'name' => 'Other Tenant',
            'status' => 'active',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'country_code' => 'US',
            'locale' => 'en-US',
            'language' => 'en',
            'time_format' => '12h',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('tenant.admin');

        $localConnection = $this->createConnection($company->id, $tenantAdmin->id, 'document_archive', 'Archive bridge', 'outbound');
        $otherConnection = $this->createConnection($otherCompany->id, $otherAdmin->id, 'document_archive', 'Other archive bridge', 'outbound');
        $otherJob = IntegrationSyncJob::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'integration_connection_id' => $otherConnection->id,
            'job_uuid' => (string) Str::uuid(),
            'version' => 'v1',
            'system_key' => 'document_archive',
            'event_key' => 'payroll.payslip.generated',
            'direction' => 'outbound',
            'status' => 'queued',
            'trigger_source' => 'manual_event',
            'attempts_count' => 0,
            'queued_at' => now(),
            'created_by_user_id' => $otherAdmin->id,
            'updated_by_user_id' => $otherAdmin->id,
        ]);

        Sanctum::actingAs($manager);
        $this
            ->getJson('/api/v1/integrations/connections')
            ->assertForbidden();

        Sanctum::actingAs($tenantAdmin);
        $this
            ->getJson('/api/v1/integrations/connections')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $localConnection->id);

        $this
            ->getJson("/api/v1/integrations/sync-jobs/{$otherJob->id}")
            ->assertNotFound();
    }

    private function createConnection(
        int $companyId,
        ?int $actorId,
        string $systemKey,
        string $name,
        string $direction,
    ): IntegrationConnection {
        return IntegrationConnection::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'system_key' => $systemKey,
            'version' => 'v1',
            'name' => $name,
            'direction' => $direction,
            'status' => 'active',
            'auth_mode' => 'hmac_sha256',
            'created_by_user_id' => $actorId,
            'updated_by_user_id' => $actorId,
        ]);
    }

    private function createSubscription(
        int $companyId,
        int $connectionId,
        ?int $actorId,
        string $eventKey,
        string $direction,
        ?string $endpointUrl,
        string $secret,
    ): WebhookSubscription {
        return WebhookSubscription::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'integration_connection_id' => $connectionId,
            'subscription_key' => (string) Str::uuid(),
            'version' => 'v1',
            'event_key' => $eventKey,
            'direction' => $direction,
            'status' => 'active',
            'endpoint_url' => $endpointUrl,
            'secret' => $secret,
            'created_by_user_id' => $actorId,
            'updated_by_user_id' => $actorId,
        ]);
    }

    private function signPayload(string $secret, string $payload): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }
}
