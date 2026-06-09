<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentRepositoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config()->set('document_repository.disk', 'documents');
    }

    public function test_tenant_admin_can_upload_list_show_and_download_documents_with_metadata_and_audits(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $response = $this->post('/api/v1/documents', [
            'title' => 'Employee Handbook 2026',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'retention_until' => '2027-12-31',
            'metadata' => [
                'document_code' => 'HB-2026',
                'language' => 'en',
            ],
            'notes' => 'Approved policy baseline for all active employees.',
            'file' => UploadedFile::fake()->create('employee-handbook.pdf', 240, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $documentId = $response->assertCreated()
            ->assertJsonPath('data.title', 'Employee Handbook 2026')
            ->assertJsonPath('data.repository_scope', 'policy')
            ->assertJsonPath('data.linked_entity_type', 'company')
            ->assertJsonPath('data.linked_entity_id', $company->id)
            ->assertJsonPath('data.visibility_scope', 'internal')
            ->assertJsonPath('data.retention_until', '2027-12-31')
            ->assertJsonPath('data.metadata.document_code', 'HB-2026')
            ->json('data.id');

        $document = Document::query()->findOrFail($documentId);

        Storage::disk('documents')->assertExists($document->file_path);

        $this->getJson('/api/v1/documents')
            ->assertOk()
            ->assertJsonPath('data.0.id', $documentId)
            ->assertJsonPath('data.0.download_url', '/api/v1/documents/'.$documentId.'/download');

        $this->getJson('/api/v1/documents/'.$documentId)
            ->assertOk()
            ->assertJsonPath('data.checksum_sha256', $document->checksum_sha256)
            ->assertJsonPath('data.metadata.language', 'en');

        $this->get('/api/v1/documents/'.$documentId.'/download')
            ->assertOk()
            ->assertDownload('employee-handbook.pdf');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'document.uploaded',
            'entity_id' => (string) $documentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'document.listed',
            'entity_type' => 'document',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'document.viewed',
            'entity_id' => (string) $documentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'document.downloaded',
            'entity_id' => (string) $documentId,
        ]);
    }

    public function test_document_categories_can_be_managed_and_documents_can_inherit_governance_defaults(): void
    {
        Storage::fake('documents');
        CarbonImmutable::setTestNow('2026-06-09 09:00:00');

        try {
            $company = Company::factory()->create(['status' => 'active']);
            $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
            $tenantAdmin->assignRole('tenant.admin');

            Sanctum::actingAs($tenantAdmin);

            $categoryId = $this->postJson('/api/v1/documents/categories', [
                'code' => 'POLICY-HANDBOOK',
                'name' => 'Policy Handbook',
                'repository_scope' => 'policy',
                'default_visibility_scope' => 'restricted',
                'retention_days' => 365,
                'allowed_role_names' => ['manager', 'employee'],
                'status' => 'active',
                'notes' => 'Visible to employees and managers for policy review.',
            ])->assertCreated()
                ->assertJsonPath('data.code', 'POLICY-HANDBOOK')
                ->assertJsonPath('data.allowed_role_names.0', 'manager')
                ->json('data.id');

            $this->patchJson('/api/v1/documents/categories/'.$categoryId, [
                'code' => 'POLICY-HANDBOOK',
                'name' => 'Policy Handbook',
                'repository_scope' => 'policy',
                'default_visibility_scope' => 'restricted',
                'retention_days' => 400,
                'allowed_role_names' => ['manager', 'employee'],
                'status' => 'active',
                'notes' => 'Updated retention baseline for handbook documents.',
            ])->assertOk()
                ->assertJsonPath('data.retention_days', 400);

            $this->getJson('/api/v1/documents/categories?repository_scope=policy&status=active')
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $categoryId);

            $documentId = $this->post('/api/v1/documents', [
                'title' => 'Employee Handbook 2026',
                'document_category_id' => $categoryId,
                'linked_entity_type' => 'company',
                'linked_entity_id' => $company->id,
                'file' => UploadedFile::fake()->create('employee-handbook.pdf', 240, 'application/pdf'),
            ], ['Accept' => 'application/json'])->assertCreated()
                ->assertJsonPath('data.document_category_id', $categoryId)
                ->assertJsonPath('data.document_category.code', 'POLICY-HANDBOOK')
                ->assertJsonPath('data.repository_scope', 'policy')
                ->assertJsonPath('data.visibility_scope', 'restricted')
                ->assertJsonPath('data.retention_until', '2027-07-14')
                ->json('data.id');

            $this->assertDatabaseHas('audit_logs', [
                'user_id' => $tenantAdmin->id,
                'event_type' => 'document.category.created',
                'entity_id' => (string) $categoryId,
            ]);

            $this->assertDatabaseHas('audit_logs', [
                'user_id' => $tenantAdmin->id,
                'event_type' => 'document.category.updated',
                'entity_id' => (string) $categoryId,
            ]);

            $document = Document::query()->findOrFail($documentId);

            $this->assertSame($categoryId, $document->document_category_id);
            $this->assertSame('2027-07-14', $document->retention_until?->toDateString());
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_document_repository_filters_and_tenant_scope_are_enforced(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);
        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('document.view');

        $otherCompany = Company::factory()->create(['status' => 'active']);

        $policyPath = 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/employee-handbook.pdf';
        Storage::disk('documents')->put($policyPath, 'policy file');

        $policyDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Employee Handbook 2026',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'employee-handbook.pdf',
            'disk' => 'documents',
            'file_path' => $policyPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('policy file'),
            'checksum_sha256' => hash('sha256', 'policy file'),
            'metadata' => ['document_code' => 'HB-2026'],
            'retention_until' => '2027-12-31',
        ]);

        $category = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'POLICY',
            'name' => 'Policy',
            'repository_scope' => 'policy',
            'default_visibility_scope' => 'restricted',
            'retention_days' => 365,
            'allowed_role_names' => ['manager'],
            'status' => 'active',
        ]);

        $policyDocument->forceFill(['document_category_id' => $category->id])->save();

        $otherPath = 'companies/'.$otherCompany->id.'/documents/compliance/company/'.$otherCompany->id.'/audit-report.pdf';
        Storage::disk('documents')->put($otherPath, 'other tenant file');

        $otherDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'title' => 'Audit Report',
            'repository_scope' => 'compliance',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $otherCompany->id,
            'visibility_scope' => 'confidential',
            'original_file_name' => 'audit-report.pdf',
            'disk' => 'documents',
            'file_path' => $otherPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('other tenant file'),
            'checksum_sha256' => hash('sha256', 'other tenant file'),
            'metadata' => ['cycle' => 'FY26'],
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/documents?document_category_id='.$category->id.'&repository_scope=policy&linked_entity_type=company&linked_entity_id='.$company->id.'&retention_until_from=2027-01-01&retention_until_to=2027-12-31')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $managerViewer = User::factory()->create(['company_id' => $company->id]);
        $managerViewer->assignRole('manager');
        $managerViewer->givePermissionTo('document.view');

        Sanctum::actingAs($managerViewer);

        $this->getJson('/api/v1/documents?document_category_id='.$category->id.'&repository_scope=policy&linked_entity_type=company&linked_entity_id='.$company->id.'&retention_until_from=2027-01-01&retention_until_to=2027-12-31')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $policyDocument->id)
            ->assertJsonPath('data.0.document_category.code', 'POLICY');

        $this->getJson('/api/v1/documents/'.$policyDocument->id)
            ->assertOk()
            ->assertJsonPath('data.title', 'Employee Handbook 2026');

        $this->getJson('/api/v1/documents/'.$otherDocument->id)
            ->assertNotFound();

        $this->get('/api/v1/documents/'.$otherDocument->id.'/download')
            ->assertNotFound();
    }

    public function test_document_repository_enforces_file_policy_and_permission_boundaries(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/documents')
            ->assertForbidden();

        $this->post('/api/v1/documents', [
            'title' => 'Suspicious Script',
            'repository_scope' => 'general',
            'visibility_scope' => 'restricted',
            'file' => UploadedFile::fake()->create('run.sh', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])
            ->assertForbidden();

        $documentManager = User::factory()->create(['company_id' => $company->id]);
        $documentManager->givePermissionTo('document.manage');

        Sanctum::actingAs($documentManager);

        $this->post('/api/v1/documents', [
            'title' => 'Unsupported Archive',
            'repository_scope' => 'general',
            'visibility_scope' => 'restricted',
            'file' => UploadedFile::fake()->create('archive.zip', 10, 'application/zip'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $inactiveCategory = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'ARCHIVE',
            'name' => 'Archive',
            'repository_scope' => 'general',
            'default_visibility_scope' => 'restricted',
            'retention_days' => 90,
            'allowed_role_names' => [],
            'status' => 'inactive',
        ]);

        $this->post('/api/v1/documents', [
            'title' => 'Inactive Category Upload',
            'document_category_id' => $inactiveCategory->id,
            'file' => UploadedFile::fake()->create('retention-note.pdf', 12, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['document_category_id']);
    }
}
