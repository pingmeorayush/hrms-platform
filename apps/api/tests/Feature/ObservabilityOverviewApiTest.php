<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationSyncJob;
use App\Models\NotificationRecord;
use App\Models\PayrollCalendar;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\ReportDataset;
use App\Models\ReportExport;
use App\Models\ReportSubscription;
use App\Models\User;
use App\Models\WebhookSubscription;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStage;
use App\Models\WorkflowTask;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ObservabilityOverviewApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authorized_operator_can_view_observability_overview(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $this->seedWorkflowPressure($company, $tenantAdmin);
        $this->seedPayrollPressure($company, $tenantAdmin);
        $this->seedIntegrationPressure($company, $tenantAdmin);
        $this->seedReportingPressure($company, $tenantAdmin);
        $this->seedNotificationPressure($company, $tenantAdmin);

        Sanctum::actingAs($tenantAdmin);

        $this
            ->getJson('/api/v1/observability/overview')
            ->assertOk()
            ->assertJsonPath('data.summary.service_count', 7)
            ->assertJsonPath('data.summary.critical_service_count', 1)
            ->assertJsonPath('data.summary.active_alert_count', 7)
            ->assertJsonPath('data.summary.monitored_workflow_count', 3)
            ->assertJsonPath('data.summary.monitored_integration_count', 2)
            ->assertJsonPath('data.telemetry.health_endpoint', '/up')
            ->assertJsonFragment([
                'key' => 'payroll_controls',
                'status' => 'critical',
            ])
            ->assertJsonFragment([
                'key' => 'payroll_blocked_runs',
                'status' => 'critical',
                'severity' => 'sev1',
            ])
            ->assertJsonPath('data.coverage.release_critical.0.key', 'release_quality_gates')
            ->assertJsonPath('data.coverage.release_critical.0.coverage_state', 'monitored');
    }

    public function test_observability_overview_is_permission_protected(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');

        Sanctum::actingAs($manager);

        $this
            ->getJson('/api/v1/observability/overview')
            ->assertForbidden();
    }

    private function seedWorkflowPressure(Company $company, User $tenantAdmin): void
    {
        foreach (['leave-approval', 'employee-offboarding-clearance'] as $workflowKey) {
            $definition = WorkflowDefinition::query()
                ->where('company_id', $company->id)
                ->where('key', $workflowKey)
                ->firstOrFail();

            $stage = WorkflowStage::query()
                ->where('workflow_version_id', $definition->active_version_id)
                ->orderBy('sequence')
                ->firstOrFail();

            $instance = WorkflowInstance::query()->create([
                'company_id' => $company->id,
                'workflow_definition_id' => $definition->id,
                'workflow_version_id' => $definition->active_version_id,
                'reference_type' => 'demo_record',
                'reference_id' => (string) Str::uuid(),
                'status' => 'running',
                'current_stage_sequence' => $stage->sequence,
                'started_by_user_id' => $tenantAdmin->id,
                'payload' => ['workflow_key' => $workflowKey],
            ]);

            WorkflowTask::query()->create([
                'company_id' => $company->id,
                'workflow_instance_id' => $instance->id,
                'workflow_stage_id' => $stage->id,
                'stage_key' => $stage->key,
                'stage_name' => $stage->name,
                'sequence' => $stage->sequence,
                'assigned_to_user_id' => $tenantAdmin->id,
                'assigned_to_role' => 'tenant.admin',
                'status' => 'open',
                'available_actions' => ['approve', 'reject'],
                'due_at' => now()->subHours(6),
                'metadata' => ['demo' => true],
            ]);
        }
    }

    private function seedPayrollPressure(Company $company, User $tenantAdmin): void
    {
        $calendar = PayrollCalendar::query()->create([
            'company_id' => $company->id,
            'name' => 'Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => true,
            'status' => 'active',
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $period = PayrollPeriod::query()->create([
            'company_id' => $company->id,
            'payroll_calendar_id' => $calendar->id,
            'name' => 'June 2026',
            'frequency' => 'monthly',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-07-05',
            'status' => 'prepared',
            'prepared_at' => now()->subDay(),
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        PayrollRun::query()->create([
            'company_id' => $company->id,
            'payroll_period_id' => $period->id,
            'name' => 'Monthly Payroll · June 2026',
            'frequency' => 'monthly',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'status' => 'blocked',
            'prerequisite_snapshot' => ['checks' => [['key' => 'attendance', 'status' => 'failed']]],
            'prerequisite_summary' => ['ready_for_calculation' => false, 'failed_count' => 1],
            'prepared_at' => now()->subDay(),
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);
    }

    private function seedIntegrationPressure(Company $company, User $tenantAdmin): void
    {
        $directoryConnection = IntegrationConnection::query()->create([
            'company_id' => $company->id,
            'system_key' => 'identity_directory',
            'version' => 'v1',
            'name' => 'Directory Provisioning',
            'direction' => 'bidirectional',
            'status' => 'active',
            'auth_mode' => 'hmac_sha256',
            'endpoint_url' => 'https://directory.example.test/webhooks',
            'description' => 'Employee provisioning sync',
            'scopes' => ['employees'],
            'settings' => ['tenant' => 'phoenix-demo'],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $directorySubscription = WebhookSubscription::query()->create([
            'company_id' => $company->id,
            'integration_connection_id' => $directoryConnection->id,
            'subscription_key' => (string) Str::uuid(),
            'version' => 'v1',
            'event_key' => 'employee.updated',
            'direction' => 'outbound',
            'status' => 'active',
            'endpoint_url' => 'https://directory.example.test/webhooks/employee-updated',
            'secret' => 'directory-secret',
            'custom_headers' => ['X-Env' => 'demo'],
            'filter_rules' => ['department' => 'engineering'],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $payrollConnection = IntegrationConnection::query()->create([
            'company_id' => $company->id,
            'system_key' => 'payroll_partner',
            'version' => 'v1',
            'name' => 'Payroll Partner',
            'direction' => 'outbound',
            'status' => 'active',
            'auth_mode' => 'hmac_sha256',
            'endpoint_url' => 'https://payroll.example.test/webhooks',
            'description' => 'Payroll settlement sync',
            'scopes' => ['attendance', 'payslips'],
            'settings' => ['provider' => 'demo-payroll'],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $payrollSubscription = WebhookSubscription::query()->create([
            'company_id' => $company->id,
            'integration_connection_id' => $payrollConnection->id,
            'subscription_key' => (string) Str::uuid(),
            'version' => 'v1',
            'event_key' => 'attendance.record.updated',
            'direction' => 'outbound',
            'status' => 'active',
            'endpoint_url' => 'https://payroll.example.test/webhooks/attendance',
            'secret' => 'payroll-secret',
            'custom_headers' => ['X-Env' => 'demo'],
            'filter_rules' => ['frequency' => 'monthly'],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        IntegrationSyncJob::query()->create([
            'company_id' => $company->id,
            'integration_connection_id' => $directoryConnection->id,
            'webhook_subscription_id' => $directorySubscription->id,
            'job_uuid' => (string) Str::uuid(),
            'version' => 'v1',
            'system_key' => 'identity_directory',
            'event_key' => 'employee.updated',
            'direction' => 'outbound',
            'status' => 'failed',
            'trigger_source' => 'manual_event',
            'entity_type' => 'employee',
            'entity_id' => '1005',
            'request_payload' => ['employee_id' => 1005],
            'response_payload' => ['status' => 'error'],
            'request_headers' => ['Content-Type' => 'application/json'],
            'response_headers' => ['Retry-After' => '60'],
            'attempts_count' => 2,
            'last_attempt_at' => now()->subMinutes(30),
            'queued_at' => now()->subMinutes(35),
            'failed_at' => now()->subMinutes(29),
            'last_error' => 'Downstream directory rejected the payload.',
            'audit_metadata' => ['demo' => true],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        IntegrationSyncJob::query()->create([
            'company_id' => $company->id,
            'integration_connection_id' => $payrollConnection->id,
            'webhook_subscription_id' => $payrollSubscription->id,
            'job_uuid' => (string) Str::uuid(),
            'version' => 'v1',
            'system_key' => 'payroll_partner',
            'event_key' => 'attendance.record.updated',
            'direction' => 'outbound',
            'status' => 'queued',
            'trigger_source' => 'manual_event',
            'entity_type' => 'attendance_record',
            'entity_id' => '7012',
            'request_payload' => ['attendance_record_id' => 7012],
            'request_headers' => ['Content-Type' => 'application/json'],
            'queued_at' => now()->subMinutes(45),
            'audit_metadata' => ['demo' => true],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);
    }

    private function seedReportingPressure(Company $company, User $tenantAdmin): void
    {
        $dataset = ReportDataset::query()->create([
            'company_id' => $company->id,
            'key' => 'attendance_exceptions',
            'name' => 'Attendance Exceptions',
            'domain' => 'attendance',
            'description' => 'Attendance review exceptions',
            'source_references' => [['table' => 'attendance_records']],
            'grain' => 'attendance_record',
            'approved_fields' => [['key' => 'employee_code', 'label' => 'Employee code', 'type' => 'string']],
            'approved_filters' => [['key' => 'primary_status', 'label' => 'Primary status', 'type' => 'status']],
            'drilldown_paths' => [['key' => 'employee', 'label' => 'Employee profile']],
            'masking_posture' => ['fields' => []],
            'freshness_expectation_minutes' => 60,
            'certification_status' => 'certified',
            'review_notes' => 'Demo certified dataset',
            'owner_user_id' => $tenantAdmin->id,
            'reviewed_by_user_id' => $tenantAdmin->id,
            'reviewed_at' => now()->subDays(5),
            'certified_by_user_id' => $tenantAdmin->id,
            'certified_at' => now()->subDays(4),
            'version' => 1,
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $export = ReportExport::query()->create([
            'company_id' => $company->id,
            'report_dataset_id' => $dataset->id,
            'requested_by_user_id' => $tenantAdmin->id,
            'export_uuid' => (string) Str::uuid(),
            'status' => 'failed',
            'format' => 'csv',
            'execution_mode' => 'async',
            'delivery_target' => 'requestor_download',
            'requested_filters' => ['primary_status' => 'incomplete'],
            'requested_filter_operators' => ['primary_status' => '='],
            'estimated_row_count' => 12,
            'visibility_posture' => ['masking' => false],
            'freshness_snapshot' => ['status' => 'fresh'],
            'requested_at' => now()->subMinutes(50),
            'started_at' => now()->subMinutes(48),
            'failed_at' => now()->subMinutes(47),
            'last_error' => 'Object storage write failed.',
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        ReportSubscription::query()->create([
            'company_id' => $company->id,
            'report_dataset_id' => $dataset->id,
            'owner_user_id' => $tenantAdmin->id,
            'subscription_uuid' => (string) Str::uuid(),
            'name' => 'Daily attendance exceptions',
            'description' => 'Deliver daily exception summary',
            'status' => 'blocked',
            'delivery_channel' => 'in_app',
            'delivery_target' => 'requestor_download',
            'export_format' => 'csv',
            'frequency' => 'daily',
            'timezone' => 'Asia/Kolkata',
            'schedule_config' => ['hour' => 9, 'minute' => 0],
            'filters' => ['primary_status' => 'incomplete'],
            'filter_operators' => ['primary_status' => '='],
            'next_delivery_at' => now()->subHour(),
            'last_delivery_status' => 'failed',
            'last_delivery_error' => 'Dataset certification drifted before delivery.',
            'last_report_export_id' => $export->id,
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);
    }

    private function seedNotificationPressure(Company $company, User $tenantAdmin): void
    {
        foreach ([
            'Payroll alert delivery failed.',
            'Reporting export completion failed to notify the operator.',
        ] as $message) {
            NotificationRecord::query()->create([
                'company_id' => $company->id,
                'user_id' => $tenantAdmin->id,
                'type' => 'system',
                'channel' => 'in_app',
                'title' => 'Notification delivery failed',
                'message' => $message,
                'priority' => 'high',
                'status' => 'unread',
                'delivery_status' => 'failed',
                'retry_count' => 1,
                'last_error' => 'Notification transport was unavailable.',
                'data' => ['demo' => true],
            ]);
        }
    }
}
