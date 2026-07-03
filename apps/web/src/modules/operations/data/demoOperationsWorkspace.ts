import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import type { EmployeeRecord } from '../../employees/types'
import type {
  OperationsAssetCategoryRecord,
  OperationsAssetRecord,
  OperationsDocumentCategoryRecord,
  OperationsDocumentRecord,
  OperationsObservabilityAlertRecord,
  OperationsObservabilityAlertRouteRecord,
  OperationsObservabilityCoverageItemRecord,
  OperationsObservabilityOverviewRecord,
  OperationsObservabilityServiceRecord,
  OperationsObservabilitySignalRecord,
  OperationsObservabilitySummaryRecord,
  OperationsObservabilityTelemetryRecord,
  OperationsIntegrationConnectionRecord,
  OperationsIntegrationEventRecord,
  OperationsIntegrationSubscriptionRecord,
  OperationsIntegrationSyncJobRecord,
  OperationsIntegrationSystemRecord,
  OperationsResilienceOverviewRecord,
  OperationsResiliencePolicyRecord,
  OperationsResilienceRunbookStepRecord,
  OperationsResilienceScenarioRecord,
  OperationsResilienceSummaryRecord,
  OperationsResilienceValidationRunRecord,
  OperationsReleaseEnvironmentRecord,
  OperationsReleaseGateRecord,
  OperationsReleasePolicyRecord,
  OperationsReleaseReadinessAreaItemRecord,
  OperationsReleaseReadinessAreaRecord,
  OperationsReleaseReadinessBlockerRecord,
  OperationsReleaseReadinessDecisionRecord,
  OperationsReleaseReadinessOverviewRecord,
  OperationsReleaseReadinessPolicyRecord,
  OperationsReleaseReadinessRecommendationRecord,
  OperationsReleaseReadinessRunbookRecord,
  OperationsReleaseReadinessSummaryRecord,
  OperationsReleaseSummaryRecord,
  OperationsLifecycleTaskCollection,
  OperationsLifecycleTaskRecord,
  OperationsLifecycleType,
  OperationsWorkspaceData,
  ReleaseReadinessDecisionFormValues,
} from '../types'

export function buildDemoOperationsWorkspace(snapshot: AccessSnapshot | null): OperationsWorkspaceData {
  const employees = buildDemoEmployees(snapshot)
  const documentCategories = buildDocumentCategories()
  const documents = buildRepositoryDocuments(documentCategories, employees)
  const assetCategories = buildAssetCategories()
  const assets = buildAssets(assetCategories, employees)
  const lifecycleTaskDetails = buildLifecycleTaskDetails(employees)
  const integrationSystems = buildIntegrationSystems()
  const integrationEvents = buildIntegrationEvents()
  const integrationConnections = buildIntegrationConnections()
  const integrationSubscriptions = buildIntegrationSubscriptions(integrationConnections)
  const integrationSyncJobs = buildIntegrationSyncJobs(integrationConnections, integrationSubscriptions)
  const releaseGates = buildReleaseGates()
  const releaseEnvironments = buildReleaseEnvironments(releaseGates)
  const resilienceValidationRuns = buildResilienceValidationRuns()
  const resilience = buildDemoResilienceOverview(resilienceValidationRuns)
  const observability = buildObservabilityOverview(
    integrationSubscriptions,
    integrationSyncJobs,
    releaseGates,
    releaseEnvironments,
  )
  const releaseReadinessAreas = buildReleaseReadinessAreas(
    {
      summary: buildReleaseSummary(releaseGates, releaseEnvironments),
      policy: buildReleasePolicy(),
      gates: releaseGates,
      environments: releaseEnvironments,
    },
    resilience,
    observability,
  )
  const releaseReadinessDecisions = buildInitialReleaseReadinessDecisions(releaseReadinessAreas)
  const releaseReadiness = buildDemoReleaseReadiness(
    {
      summary: buildReleaseSummary(releaseGates, releaseEnvironments),
      policy: buildReleasePolicy(),
      gates: releaseGates,
      environments: releaseEnvironments,
    },
    resilience,
    observability,
    releaseReadinessDecisions,
  )

  return {
    documentCategories,
    documents,
    assetCategories,
    assets,
    employees,
    lifecycle: {
      onboarding: buildLifecycleStatuses(employees, lifecycleTaskDetails, 'onboarding'),
      offboarding: buildLifecycleStatuses(employees, lifecycleTaskDetails, 'offboarding'),
    },
    integrations: {
      systems: integrationSystems,
      events: integrationEvents,
      connections: integrationConnections,
      subscriptions: integrationSubscriptions,
      syncJobs: integrationSyncJobs,
      syncJobMeta: {
        page: 1,
        per_page: 25,
        total: integrationSyncJobs.length,
        last_page: 1,
      },
    },
    release: {
      summary: buildReleaseSummary(releaseGates, releaseEnvironments),
      policy: buildReleasePolicy(),
      gates: releaseGates,
      environments: releaseEnvironments,
    },
    releaseReadiness,
    resilience,
    observability,
    lifecycleTaskDetails,
  }
}

export function getDemoLifecycleTasks(
  workspace: OperationsWorkspaceData | null,
  employeeId: number | null,
  lifecycleType: OperationsLifecycleType,
) {
  if (!workspace || employeeId === null) {
    return null
  }

  return workspace.lifecycleTaskDetails?.[employeeId]?.[lifecycleType] ?? null
}

function buildDocumentCategories(): OperationsDocumentCategoryRecord[] {
  return [
    {
      id: 9001,
      code: 'POLICY-HANDBOOK',
      name: 'Policy handbook',
      repository_scope: 'policy',
      default_visibility_scope: 'restricted',
      retention_days: 400,
      allowed_role_names: ['manager', 'employee'],
      status: 'active',
      notes: 'Visible to employees and managers for handbook review.',
      created_at: daysAgo(120),
      updated_at: daysAgo(8),
    },
    {
      id: 9002,
      code: 'OFFBOARD-CLR',
      name: 'Offboarding clearance',
      repository_scope: 'compliance',
      default_visibility_scope: 'confidential',
      retention_days: 730,
      allowed_role_names: ['hr.admin', 'tenant.admin'],
      status: 'active',
      notes: 'Protected exit and compliance artifacts.',
      created_at: daysAgo(84),
      updated_at: daysAgo(3),
    },
    {
      id: 9003,
      code: 'ASSET-WARRANTY',
      name: 'Asset warranty file',
      repository_scope: 'asset',
      default_visibility_scope: 'internal',
      retention_days: 365,
      allowed_role_names: [],
      status: 'active',
      notes: 'Tracked by IT for repair and recovery operations.',
      created_at: daysAgo(90),
      updated_at: daysAgo(11),
    },
  ]
}

function buildRepositoryDocuments(
  categories: OperationsDocumentCategoryRecord[],
  employees: EmployeeRecord[],
): OperationsDocumentRecord[] {
  const kabir = employees.find((employee) => employee.id === 1005) ?? employees[0]

  return [
    {
      id: 9101,
      document_category_id: 9001,
      document_category: toDocumentCategorySummary(categories[0]),
      title: 'Employee handbook FY26',
      repository_scope: 'policy',
      linked_entity_type: 'company',
      linked_entity_id: 1,
      visibility_scope: 'restricted',
      original_file_name: 'employee-handbook-fy26.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 2_840_144,
      checksum_sha256: 'demo-handbook-checksum',
      retention_until: '2027-07-14',
      metadata: { language: 'en', acknowledgement_required: true },
      notes: 'Assigned through self-service acknowledgements for all active staff.',
      download_url: '#demo-doc-handbook',
      created_at: daysAgo(64),
      updated_at: daysAgo(4),
    },
    {
      id: 9102,
      document_category_id: 9002,
      document_category: toDocumentCategorySummary(categories[1]),
      title: `Exit clearance packet · ${kabir.full_name}`,
      repository_scope: 'compliance',
      linked_entity_type: 'employee',
      linked_entity_id: kabir.id,
      visibility_scope: 'confidential',
      original_file_name: 'kabir-malik-exit-clearance.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 864_221,
      checksum_sha256: 'demo-exit-clearance-checksum',
      retention_until: '2028-05-16',
      metadata: { owner: 'people-ops', workflow_state: 'awaiting_asset_clearance' },
      notes: 'Blocked until IT confirms laptop and badge recovery.',
      download_url: '#demo-doc-exit-clearance',
      created_at: daysAgo(18),
      updated_at: daysAgo(1),
    },
    {
      id: 9103,
      document_category_id: 9003,
      document_category: toDocumentCategorySummary(categories[2]),
      title: 'MacBook Pro warranty certificate',
      repository_scope: 'asset',
      linked_entity_type: 'asset',
      linked_entity_id: 9201,
      visibility_scope: 'internal',
      original_file_name: 'macbook-pro-warranty.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 442_002,
      checksum_sha256: 'demo-warranty-checksum',
      retention_until: '2027-03-31',
      metadata: { vendor: 'Apple', incident_ticket: 'AST-2214' },
      notes: 'Needed for the currently blocked repair cycle.',
      download_url: '#demo-doc-warranty',
      created_at: daysAgo(52),
      updated_at: daysAgo(2),
    },
  ]
}

function buildAssetCategories(): OperationsAssetCategoryRecord[] {
  return [
    {
      id: 9301,
      code: 'LAPTOP',
      name: 'Laptop',
      status: 'active',
      notes: 'Portable workstation inventory.',
      created_at: daysAgo(140),
      updated_at: daysAgo(7),
    },
    {
      id: 9302,
      code: 'BADGE',
      name: 'Access badge',
      status: 'active',
      notes: 'Building and floor access controls.',
      created_at: daysAgo(140),
      updated_at: daysAgo(7),
    },
    {
      id: 9303,
      code: 'PHONE',
      name: 'Mobile phone',
      status: 'active',
      notes: 'Field and support communication devices.',
      created_at: daysAgo(140),
      updated_at: daysAgo(7),
    },
  ]
}

function buildAssets(
  categories: OperationsAssetCategoryRecord[],
  employees: EmployeeRecord[],
): OperationsAssetRecord[] {
  const rohit = employees.find((employee) => employee.id === 1003) ?? employees[0]
  const kabir = employees.find((employee) => employee.id === 1005) ?? employees[0]
  const sana = employees.find((employee) => employee.id === 1006) ?? employees[0]

  return [
    {
      id: 9201,
      asset_category_id: 9301,
      asset_category: toAssetCategorySummary(categories[0]),
      asset_tag: 'AST-LTP-1001',
      name: 'MacBook Pro 14',
      asset_type: 'physical',
      serial_number: 'SN-LTP-1001',
      manufacturer: 'Apple',
      model_name: 'MacBook Pro 14',
      purchase_date: '2026-03-31',
      status: 'issued',
      notes: 'Primary engineering laptop. Warranty review is currently in progress.',
      current_assignment: {
        id: 9401,
        asset_id: 9201,
        employee_id: rohit.id,
        employee: toEmployeeSummary(rohit),
        status: 'issued',
        assigned_at: daysAgo(62),
        issued_at: daysAgo(61),
        expected_return_date: futureDay(10),
        returned_at: null,
        handover_condition: 'sealed',
        return_condition: null,
        assignment_notes: 'Issued during new-joiner setup.',
        issue_notes: 'Imaged and enrolled into device management.',
        return_notes: null,
        created_at: daysAgo(62),
        updated_at: daysAgo(61),
      },
      assignment_history: [],
      created_at: daysAgo(90),
      updated_at: daysAgo(61),
    },
    {
      id: 9202,
      asset_category_id: 9302,
      asset_category: toAssetCategorySummary(categories[1]),
      asset_tag: 'AST-BDG-1005',
      name: 'North Tower access badge',
      asset_type: 'physical',
      serial_number: 'BDG-1881',
      manufacturer: null,
      model_name: null,
      purchase_date: '2025-08-01',
      status: 'assigned',
      notes: 'Queued for return because the employee is in notice period.',
      current_assignment: {
        id: 9402,
        asset_id: 9202,
        employee_id: kabir.id,
        employee: toEmployeeSummary(kabir),
        status: 'assigned',
        assigned_at: daysAgo(210),
        issued_at: null,
        expected_return_date: futureDay(2),
        returned_at: null,
        handover_condition: 'good',
        return_condition: null,
        assignment_notes: 'Badge recovery blocked until final floor-access audit.',
        issue_notes: null,
        return_notes: null,
        created_at: daysAgo(210),
        updated_at: daysAgo(4),
      },
      assignment_history: [],
      created_at: daysAgo(240),
      updated_at: daysAgo(4),
    },
    {
      id: 9203,
      asset_category_id: 9301,
      asset_category: toAssetCategorySummary(categories[0]),
      asset_tag: 'AST-LTP-REPAIR',
      name: 'Loaner Dell Latitude',
      asset_type: 'physical',
      serial_number: 'LTP-REPAIR-774',
      manufacturer: 'Dell',
      model_name: 'Latitude 7440',
      purchase_date: '2025-10-18',
      status: 'maintenance',
      notes: 'Blocked for reassignment until keyboard repair completes.',
      current_assignment: null,
      assignment_history: [],
      created_at: daysAgo(180),
      updated_at: daysAgo(5),
    },
    {
      id: 9204,
      asset_category_id: 9303,
      asset_category: toAssetCategorySummary(categories[2]),
      asset_tag: 'AST-PHN-1006',
      name: 'iPhone 15',
      asset_type: 'physical',
      serial_number: 'PHN-1006',
      manufacturer: 'Apple',
      model_name: 'iPhone 15',
      purchase_date: '2025-11-11',
      status: 'returned',
      notes: 'Recovered during offboarding closeout.',
      current_assignment: null,
      assignment_history: [
        {
          id: 9404,
          asset_id: 9204,
          employee_id: sana.id,
          employee: toEmployeeSummary(sana),
          status: 'returned',
          assigned_at: daysAgo(160),
          issued_at: daysAgo(159),
          expected_return_date: daysAgo(12).slice(0, 10),
          returned_at: daysAgo(12),
          handover_condition: 'new',
          return_condition: 'good',
          assignment_notes: 'Issued for field coordination.',
          issue_notes: 'SIM activated for recruiting events.',
          return_notes: 'Recovered with charger and case.',
          created_at: daysAgo(160),
          updated_at: daysAgo(12),
        },
      ],
      created_at: daysAgo(190),
      updated_at: daysAgo(12),
    },
    {
      id: 9205,
      asset_category_id: 9301,
      asset_category: toAssetCategorySummary(categories[0]),
      asset_tag: 'AST-LTP-1005',
      name: 'ThinkPad X1 Carbon',
      asset_type: 'physical',
      serial_number: 'SN-LTP-1005',
      manufacturer: 'Lenovo',
      model_name: 'ThinkPad X1 Carbon',
      purchase_date: '2025-12-10',
      status: 'issued',
      notes: 'Current return target already passed; needs urgent follow-up.',
      current_assignment: {
        id: 9405,
        asset_id: 9205,
        employee_id: kabir.id,
        employee: toEmployeeSummary(kabir),
        status: 'issued',
        assigned_at: daysAgo(140),
        issued_at: daysAgo(139),
        expected_return_date: pastDay(3),
        returned_at: null,
        handover_condition: 'sealed',
        return_condition: null,
        assignment_notes: 'Will be reclaimed during offboarding.',
        issue_notes: 'Device encryption verified.',
        return_notes: null,
        created_at: daysAgo(140),
        updated_at: daysAgo(2),
      },
      assignment_history: [],
      created_at: daysAgo(150),
      updated_at: daysAgo(2),
    },
  ]
}

function buildIntegrationSystems(): OperationsIntegrationSystemRecord[] {
  return [
    {
      key: 'identity_directory',
      name: 'Identity Directory',
      description: 'Directory bridge for employee profile and lifecycle updates.',
      directions: ['inbound', 'outbound', 'bidirectional'],
    },
    {
      key: 'payroll_partner',
      name: 'Payroll Partner',
      description: 'Approved payroll sync for leave, attendance, and payslip events.',
      directions: ['inbound', 'outbound', 'bidirectional'],
    },
    {
      key: 'document_archive',
      name: 'Document Archive',
      description: 'Governed mirror for payslip and policy artifacts.',
      directions: ['outbound', 'bidirectional'],
    },
  ]
}

function buildIntegrationEvents(): OperationsIntegrationEventRecord[] {
  return [
    {
      key: 'employee.updated',
      name: 'Employee updated',
      description: 'Approved profile changes are ready for downstream synchronization.',
      entity_type: 'employee',
      directions: ['outbound', 'inbound'],
      systems: ['identity_directory', 'payroll_partner'],
    },
    {
      key: 'leave.request.approved',
      name: 'Leave request approved',
      description: 'Final leave decisions can now synchronize to downstream payroll systems.',
      entity_type: 'leave_request',
      directions: ['outbound'],
      systems: ['payroll_partner'],
    },
    {
      key: 'directory.profile.sync',
      name: 'Directory profile sync',
      description: 'Inbound directory payloads are captured into the governed sync queue.',
      entity_type: 'employee',
      directions: ['inbound'],
      systems: ['identity_directory'],
    },
    {
      key: 'payroll.payslip.generated',
      name: 'Payslip generated',
      description: 'Published payslips can be mirrored to the approved archive.',
      entity_type: 'payslip',
      directions: ['outbound'],
      systems: ['document_archive'],
    },
  ]
}

function buildIntegrationConnections(): OperationsIntegrationConnectionRecord[] {
  return [
    {
      id: 9801,
      system_key: 'identity_directory',
      version: 'v1',
      name: 'Directory outbound bridge',
      direction: 'outbound',
      status: 'active',
      auth_mode: 'hmac_sha256',
      endpoint_url: 'https://directory.partner.test/hooks/employee-updated',
      description: 'Push employee profile updates into the approved identity directory tenant.',
      scopes: ['employee.profile', 'employee.lifecycle'],
      settings: { retry_policy: 'manual_review' },
      active_subscription_count: 1,
      last_synced_at: daysAgo(1),
      created_at: daysAgo(34),
      updated_at: daysAgo(1),
    },
    {
      id: 9802,
      system_key: 'payroll_partner',
      version: 'v1',
      name: 'Payroll sync bridge',
      direction: 'bidirectional',
      status: 'active',
      auth_mode: 'hmac_sha256',
      endpoint_url: 'https://payroll.partner.test/hooks/hrms',
      description: 'Exchange approved leave and inbound directory alignment payloads with the payroll partner.',
      scopes: ['leave.decisions', 'employee.sync'],
      settings: { requires_operator_retry: true },
      active_subscription_count: 2,
      last_synced_at: daysAgo(0),
      created_at: daysAgo(26),
      updated_at: daysAgo(0),
    },
    {
      id: 9803,
      system_key: 'document_archive',
      version: 'v1',
      name: 'Payslip archive mirror',
      direction: 'outbound',
      status: 'paused',
      auth_mode: 'hmac_sha256',
      endpoint_url: 'https://archive.partner.test/hooks/payslips',
      description: 'Paused while archive retention headers are being revalidated.',
      scopes: ['payslip.publish'],
      settings: { retention_review: 'pending' },
      active_subscription_count: 0,
      last_synced_at: daysAgo(9),
      created_at: daysAgo(42),
      updated_at: daysAgo(2),
    },
  ]
}

function buildIntegrationSubscriptions(
  connections: OperationsIntegrationConnectionRecord[],
): OperationsIntegrationSubscriptionRecord[] {
  const directoryConnection = connections.find((connection) => connection.id === 9801) ?? connections[0]
  const payrollConnection = connections.find((connection) => connection.id === 9802) ?? connections[0]
  const archiveConnection = connections.find((connection) => connection.id === 9803) ?? connections[0]

  return [
    {
      id: 9811,
      subscription_key: 'sub-demo-directory-updated',
      integration_connection_id: directoryConnection.id,
      version: 'v1',
      event_key: 'employee.updated',
      direction: 'outbound',
      status: 'active',
      endpoint_url: directoryConnection.endpoint_url,
      secret_preview: '••••d8a4',
      custom_headers: {
        'X-Partner-Key': 'directory-prod',
      },
      filter_rules: {
        entity_types: ['employee'],
      },
      connection: {
        id: directoryConnection.id,
        system_key: directoryConnection.system_key,
        name: directoryConnection.name,
        status: directoryConnection.status,
      },
      last_delivery_at: daysAgo(1),
      last_received_at: null,
      created_at: daysAgo(30),
      updated_at: daysAgo(1),
    },
    {
      id: 9812,
      subscription_key: 'sub-demo-payroll-leave',
      integration_connection_id: payrollConnection.id,
      version: 'v1',
      event_key: 'leave.request.approved',
      direction: 'outbound',
      status: 'active',
      endpoint_url: 'https://payroll.partner.test/hooks/leave-approved',
      secret_preview: '••••e915',
      custom_headers: {
        'X-Partner-Key': 'payroll-core',
      },
      filter_rules: {
        entity_types: ['leave_request'],
      },
      connection: {
        id: payrollConnection.id,
        system_key: payrollConnection.system_key,
        name: payrollConnection.name,
        status: payrollConnection.status,
      },
      last_delivery_at: daysAgo(0),
      last_received_at: null,
      created_at: daysAgo(20),
      updated_at: daysAgo(0),
    },
    {
      id: 9813,
      subscription_key: 'sub-demo-payroll-inbound',
      integration_connection_id: payrollConnection.id,
      version: 'v1',
      event_key: 'directory.profile.sync',
      direction: 'inbound',
      status: 'active',
      endpoint_url: null,
      secret_preview: '••••1fd2',
      custom_headers: {},
      filter_rules: {},
      connection: {
        id: payrollConnection.id,
        system_key: payrollConnection.system_key,
        name: payrollConnection.name,
        status: payrollConnection.status,
      },
      last_delivery_at: null,
      last_received_at: daysAgo(0),
      created_at: daysAgo(18),
      updated_at: daysAgo(0),
    },
    {
      id: 9814,
      subscription_key: 'sub-demo-archive-payslip',
      integration_connection_id: archiveConnection.id,
      version: 'v1',
      event_key: 'payroll.payslip.generated',
      direction: 'outbound',
      status: 'paused',
      endpoint_url: archiveConnection.endpoint_url,
      secret_preview: '••••7c11',
      custom_headers: {},
      filter_rules: {},
      connection: {
        id: archiveConnection.id,
        system_key: archiveConnection.system_key,
        name: archiveConnection.name,
        status: archiveConnection.status,
      },
      last_delivery_at: daysAgo(9),
      last_received_at: null,
      created_at: daysAgo(17),
      updated_at: daysAgo(2),
    },
  ]
}

function buildIntegrationSyncJobs(
  connections: OperationsIntegrationConnectionRecord[],
  subscriptions: OperationsIntegrationSubscriptionRecord[],
): OperationsIntegrationSyncJobRecord[] {
  const directoryConnection = connections.find((connection) => connection.id === 9801) ?? connections[0]
  const payrollConnection = connections.find((connection) => connection.id === 9802) ?? connections[0]
  const directorySubscription = subscriptions.find((subscription) => subscription.id === 9811) ?? subscriptions[0]
  const leaveSubscription = subscriptions.find((subscription) => subscription.id === 9812) ?? subscriptions[0]
  const inboundSubscription = subscriptions.find((subscription) => subscription.id === 9813) ?? subscriptions[0]

  return [
    {
      id: 9821,
      job_uuid: 'job-demo-directory-failed',
      version: 'v1',
      system_key: directoryConnection.system_key,
      event_key: 'employee.updated',
      direction: 'outbound',
      status: 'failed',
      monitoring_state: 'failed',
      trigger_source: 'manual_event',
      entity_type: 'employee',
      entity_id: 'EMP-1005',
      request_payload: {
        employee_code: 'EMP-1005',
        status: 'inactive',
      },
      response_payload: {
        status_code: 500,
        body: {
          error: 'Directory API unavailable',
        },
      },
      request_headers: {
        'X-PhoenixHRMS-Event': 'employee.updated',
      },
      response_headers: {},
      attempts_count: 1,
      last_attempt_at: daysAgo(0),
      queued_at: daysAgo(0),
      started_at: daysAgo(0),
      completed_at: null,
      failed_at: daysAgo(0),
      retried_at: null,
      last_error: 'Directory API unavailable during the last delivery window.',
      can_retry: true,
      connection: {
        id: directoryConnection.id,
        system_key: directoryConnection.system_key,
        name: directoryConnection.name,
        status: directoryConnection.status,
      },
      subscription: {
        id: directorySubscription.id,
        subscription_key: directorySubscription.subscription_key,
        event_key: directorySubscription.event_key,
        direction: directorySubscription.direction,
        status: directorySubscription.status,
      },
      errors: [
        {
          id: 9831,
          attempt_number: 1,
          error_code: 'delivery_failed',
          error_message: 'Directory API unavailable during the last delivery window.',
          request_payload: {
            employee_code: 'EMP-1005',
            status: 'inactive',
          },
          response_payload: {
            status_code: 500,
            body: {
              error: 'Directory API unavailable',
            },
          },
          request_headers: {
            'X-PhoenixHRMS-Event': 'employee.updated',
          },
          response_headers: {},
          occurred_at: daysAgo(0),
          resolved_at: null,
        },
      ],
      created_at: daysAgo(0),
      updated_at: daysAgo(0),
    },
    {
      id: 9822,
      job_uuid: 'job-demo-leave-retried',
      version: 'v1',
      system_key: payrollConnection.system_key,
      event_key: 'leave.request.approved',
      direction: 'outbound',
      status: 'completed',
      monitoring_state: 'retried',
      trigger_source: 'manual_event',
      entity_type: 'leave_request',
      entity_id: 'LV-1408',
      request_payload: {
        leave_request_code: 'LV-1408',
        status: 'approved',
      },
      response_payload: {
        status_code: 200,
        body: {
          synced: true,
          retried: true,
        },
      },
      request_headers: {
        'X-PhoenixHRMS-Event': 'leave.request.approved',
      },
      response_headers: {},
      attempts_count: 2,
      last_attempt_at: daysAgo(0),
      queued_at: daysAgo(1),
      started_at: daysAgo(0),
      completed_at: daysAgo(0),
      failed_at: null,
      retried_at: daysAgo(0),
      last_error: null,
      can_retry: false,
      connection: {
        id: payrollConnection.id,
        system_key: payrollConnection.system_key,
        name: payrollConnection.name,
        status: payrollConnection.status,
      },
      subscription: {
        id: leaveSubscription.id,
        subscription_key: leaveSubscription.subscription_key,
        event_key: leaveSubscription.event_key,
        direction: leaveSubscription.direction,
        status: leaveSubscription.status,
      },
      errors: [
        {
          id: 9832,
          attempt_number: 1,
          error_code: 'delivery_failed',
          error_message: 'Payroll partner timed out during the first attempt.',
          request_payload: {
            leave_request_code: 'LV-1408',
            status: 'approved',
          },
          response_payload: {
            status_code: 504,
            body: {
              error: 'Gateway timeout',
            },
          },
          request_headers: {
            'X-PhoenixHRMS-Event': 'leave.request.approved',
          },
          response_headers: {},
          occurred_at: daysAgo(1),
          resolved_at: daysAgo(0),
        },
      ],
      created_at: daysAgo(1),
      updated_at: daysAgo(0),
    },
    {
      id: 9823,
      job_uuid: 'job-demo-inbound-directory',
      version: 'v1',
      system_key: payrollConnection.system_key,
      event_key: 'directory.profile.sync',
      direction: 'inbound',
      status: 'completed',
      monitoring_state: 'completed',
      trigger_source: 'webhook',
      entity_type: 'employee',
      entity_id: 'EMP-2001',
      request_payload: {
        records: [
          {
            employee_code: 'EMP-2001',
            status: 'active',
          },
        ],
      },
      response_payload: {
        accepted: true,
        queued_for_review: true,
        handled_as: 'directory.profile.sync',
        received_records: 1,
      },
      request_headers: {
        'X-PhoenixHRMS-Event': 'directory.profile.sync',
      },
      response_headers: {},
      attempts_count: 1,
      last_attempt_at: daysAgo(0),
      queued_at: daysAgo(0),
      started_at: daysAgo(0),
      completed_at: daysAgo(0),
      failed_at: null,
      retried_at: null,
      last_error: null,
      can_retry: false,
      connection: {
        id: payrollConnection.id,
        system_key: payrollConnection.system_key,
        name: payrollConnection.name,
        status: payrollConnection.status,
      },
      subscription: {
        id: inboundSubscription.id,
        subscription_key: inboundSubscription.subscription_key,
        event_key: inboundSubscription.event_key,
        direction: inboundSubscription.direction,
        status: inboundSubscription.status,
      },
      errors: [],
      created_at: daysAgo(0),
      updated_at: daysAgo(0),
    },
    {
      id: 9824,
      job_uuid: 'job-demo-directory-queued',
      version: 'v1',
      system_key: directoryConnection.system_key,
      event_key: 'employee.updated',
      direction: 'outbound',
      status: 'queued',
      monitoring_state: 'queued',
      trigger_source: 'manual_event',
      entity_type: 'employee',
      entity_id: 'EMP-1012',
      request_payload: {
        employee_code: 'EMP-1012',
        status: 'active',
      },
      response_payload: {},
      request_headers: {
        'X-PhoenixHRMS-Event': 'employee.updated',
      },
      response_headers: {},
      attempts_count: 0,
      last_attempt_at: null,
      queued_at: daysAgo(0),
      started_at: null,
      completed_at: null,
      failed_at: null,
      retried_at: null,
      last_error: null,
      can_retry: false,
      connection: {
        id: directoryConnection.id,
        system_key: directoryConnection.system_key,
        name: directoryConnection.name,
        status: directoryConnection.status,
      },
      subscription: {
        id: directorySubscription.id,
        subscription_key: directorySubscription.subscription_key,
        event_key: directorySubscription.event_key,
        direction: directorySubscription.direction,
        status: directorySubscription.status,
      },
      errors: [],
      created_at: daysAgo(0),
      updated_at: daysAgo(0),
    },
  ]
}

function buildReleaseSummary(
  gates: OperationsReleaseGateRecord[],
  environments: OperationsReleaseEnvironmentRecord[],
): OperationsReleaseSummaryRecord {
  return {
    total_gate_count: gates.length,
    blocking_gate_count: gates.filter((gate) => gate.blocking && gate.status !== 'passing').length,
    passing_gate_count: gates.filter((gate) => gate.status === 'passing').length,
    pending_gate_count:
      gates.filter((gate) => gate.status === 'pending').length +
      environments.filter((environment) => environment.status === 'pending').length,
    warning_gate_count: gates.filter((gate) => gate.status === 'warning').length,
    blocked_environment_count: environments.filter((environment) => environment.status === 'blocked').length,
    protected_environment_count: environments.length,
  }
}

function buildReleasePolicy(): OperationsReleasePolicyRecord {
  return {
    protected_branch: 'main',
    promotion_rule:
      'Any blocking gate that is failing, pending, or missing current evidence blocks protected-environment promotion until an authorized operator reviews the run.',
    required_workflow_names: ['Release Quality Gates', 'API CI', 'Web CI'],
    reviewer_roles: ['platform.super_admin', 'platform.support', 'tenant.admin', 'it.admin'],
    artifact_paths: [
      '.github/workflows/release-quality-gates.yml',
      '.github/workflows/api-ci.yml',
      '.github/workflows/web-ci.yml',
      'apps/api/openapi/README.md',
    ],
  }
}

function buildReleaseGates(): OperationsReleaseGateRecord[] {
  return [
    {
      key: 'api_quality',
      name: 'API quality gate',
      category: 'backend',
      status: 'passing',
      blocking: true,
      owner_role: 'platform.support',
      workflow_name: 'API CI',
      workflow_path: '.github/workflows/api-ci.yml',
      summary:
        'Protects the Laravel API surface with manifest validation, style checks, static analysis, automated tests, dependency audit, and OpenAPI linting.',
      last_run_at: '2026-07-01T09:10:00+05:30',
      required_for: ['pull_request', 'staging', 'production'],
      artifact_refs: ['apps/api/composer.json', 'apps/api/scripts/openapi-lint.sh', 'apps/api/openapi/README.md'],
      check_count: 4,
      failing_check_count: 0,
      checks: [
        {
          key: 'composer_validate',
          label: 'Composer manifest validation',
          status: 'passing',
          command: 'composer validate --strict',
        },
        {
          key: 'code_style',
          label: 'Laravel Pint code style',
          status: 'passing',
          command: 'composer lint',
        },
        {
          key: 'static_analysis',
          label: 'Larastan and PHPStan',
          status: 'passing',
          command: 'composer analyse',
        },
        {
          key: 'backend_tests',
          label: 'Backend regression suite',
          status: 'passing',
          command: 'php artisan test',
        },
      ],
    },
    {
      key: 'web_quality',
      name: 'Web quality gate',
      category: 'frontend',
      status: 'passing',
      blocking: true,
      owner_role: 'it.admin',
      workflow_name: 'Web CI',
      workflow_path: '.github/workflows/web-ci.yml',
      summary:
        'Protects the website workspace with type safety, linting, route tests, page tests, and production build validation.',
      last_run_at: '2026-07-01T09:14:00+05:30',
      required_for: ['pull_request', 'staging', 'production'],
      artifact_refs: ['apps/web/package.json', 'apps/web/src/app/routes/AppRoutes.test.tsx'],
      check_count: 4,
      failing_check_count: 0,
      checks: [
        { key: 'typecheck', label: 'TypeScript typecheck', status: 'passing', command: 'npm run typecheck' },
        { key: 'lint', label: 'ESLint', status: 'passing', command: 'npm run lint' },
        { key: 'tests', label: 'Vitest suite', status: 'passing', command: 'npm run test:run' },
        { key: 'build', label: 'Production build', status: 'passing', command: 'npm run build' },
      ],
    },
    {
      key: 'dependency_security',
      name: 'Dependency security gate',
      category: 'security',
      status: 'passing',
      blocking: true,
      owner_role: 'platform.support',
      workflow_name: 'Release Quality Gates',
      workflow_path: '.github/workflows/release-quality-gates.yml',
      summary:
        'Runs dependency audits for PHP and JavaScript toolchains before any protected promotion can proceed.',
      last_run_at: '2026-07-01T09:18:00+05:30',
      required_for: ['pull_request', 'staging', 'production'],
      artifact_refs: ['apps/api/composer.lock', 'apps/api/package-lock.json', 'apps/web/package-lock.json'],
      check_count: 3,
      failing_check_count: 0,
      checks: [
        { key: 'composer_audit', label: 'Composer audit', status: 'passing', command: 'composer audit --locked' },
        { key: 'api_npm_audit', label: 'API toolchain npm audit', status: 'passing', command: 'npm audit --audit-level=high' },
        { key: 'web_npm_audit', label: 'Web runtime npm audit', status: 'passing', command: 'npm audit --omit=dev --audit-level=high' },
      ],
    },
    {
      key: 'contract_validation',
      name: 'Contract validation gate',
      category: 'governance',
      status: 'passing',
      blocking: true,
      owner_role: 'tenant.admin',
      workflow_name: 'API CI',
      workflow_path: '.github/workflows/api-ci.yml',
      summary:
        'Validates the governed OpenAPI inventory so downstream web, QA, and integration consumers are blocked from drifting contracts.',
      last_run_at: '2026-07-01T09:21:00+05:30',
      required_for: ['pull_request', 'staging', 'production'],
      artifact_refs: [
        'apps/api/openapi/README.md',
        'apps/api/openapi/sprint-09-integrations.yaml',
        'apps/api/openapi/sprint-09-mobile-ess-globalization.yaml',
      ],
      check_count: 2,
      failing_check_count: 0,
      checks: [
        {
          key: 'openapi_inventory',
          label: 'OpenAPI inventory maintained',
          status: 'passing',
          command: 'apps/api/openapi/README.md reviewed in pull requests',
        },
        { key: 'openapi_lint', label: 'OpenAPI lint', status: 'passing', command: 'npm run openapi:lint' },
      ],
    },
  ]
}

function buildReleaseEnvironments(
  gates: OperationsReleaseGateRecord[],
): OperationsReleaseEnvironmentRecord[] {
  const requiredGateKeys = gates.map((gate) => gate.key)

  return [
    createReleaseEnvironment('pull_request', 'Pull request merge gate', requiredGateKeys, gates),
    createReleaseEnvironment('staging', 'Staging promotion', requiredGateKeys, gates),
    createReleaseEnvironment('production', 'Production promotion', requiredGateKeys, gates, true),
  ]
}

function createReleaseEnvironment(
  key: string,
  name: string,
  requiredGateKeys: string[],
  gates: OperationsReleaseGateRecord[],
  manualApprovalRequired = false,
): OperationsReleaseEnvironmentRecord {
  const requiredGates = gates.filter((gate) => requiredGateKeys.includes(gate.key))
  const blockingGates = requiredGates.filter((gate) => gate.blocking && gate.status !== 'passing')

  if (blockingGates.length) {
    return {
      key,
      name,
      status: 'blocked',
      manual_approval_required: manualApprovalRequired,
      required_gate_keys: requiredGateKeys,
      required_gate_count: requiredGates.length,
      blocking_gate_count: blockingGates.length,
      blocked_reason: `Promotion blocked until ${blockingGates.map((gate) => gate.name).join(', ')} returns to passing state.`,
    }
  }

  if (manualApprovalRequired) {
    return {
      key,
      name,
      status: 'pending',
      manual_approval_required: true,
      required_gate_keys: requiredGateKeys,
      required_gate_count: requiredGates.length,
      blocking_gate_count: 0,
      blocked_reason: 'Authorized release approval is still required after automated gates pass.',
    }
  }

  return {
    key,
    name,
    status: 'passing',
    manual_approval_required: false,
    required_gate_keys: requiredGateKeys,
    required_gate_count: requiredGates.length,
    blocking_gate_count: 0,
    blocked_reason: null,
  }
}

function buildReleaseReadinessPolicy(): OperationsReleaseReadinessPolicyRecord {
  return {
    review_cadence:
      'Every production promotion requires a same-day go or no-go review with accountable release ownership recorded before launch approval is granted.',
    decision_owner_roles: ['platform.super_admin', 'platform.support', 'tenant.admin', 'it.admin'],
    target_environments: ['staging', 'production'],
    artifact_refs: [
      'docs/runbooks/release-incident-response.md',
      'docs/runbooks/release-rollback.md',
      'docs/runbooks/release-common-launch-issues.md',
      'docs/runbooks/backup-restore-disaster-recovery-validation.md',
    ],
  }
}

function buildReleaseWorkflowVerifications(): OperationsReleaseReadinessAreaItemRecord[] {
  return [
    {
      key: 'employee_directory_search',
      label: 'Employee directory search and profile drill-in',
      status: 'ready',
      owner_role: 'employee.manage',
      summary:
        'HR can search employees and open routed profile views used during launch support and onboarding triage.',
      last_reviewed_at: '2026-07-01T10:05:00+05:30',
      artifact_refs: [
        'apps/web/src/modules/employees/pages/EmployeeSectionPages.tsx',
        'apps/web/src/modules/employees/components/EmployeeDetailShell.tsx',
      ],
    },
    {
      key: 'attendance_review_flow',
      label: 'Attendance operational review and correction handling',
      status: 'ready',
      owner_role: 'attendance.approve',
      summary:
        'Managers and HR can still resolve attendance exceptions and approvals through the governed operational-review surface.',
      last_reviewed_at: '2026-07-01T10:12:00+05:30',
      artifact_refs: ['apps/web/src/modules/attendance/pages/AttendanceOperationalReviewPage.tsx'],
    },
    {
      key: 'payroll_payslip_access',
      label: 'Payroll review and payslip access verification',
      status: 'ready',
      owner_role: 'payroll.process',
      summary:
        'Payroll reviewers can inspect run posture and employees can still reach governed payslip download surfaces.',
      last_reviewed_at: '2026-07-01T10:18:00+05:30',
      artifact_refs: [
        'apps/web/src/modules/payroll/pages/PayrollReviewPage.tsx',
        'apps/web/src/modules/payroll/pages/PayrollSelfServicePage.tsx',
      ],
    },
  ]
}

function buildReleaseRunbooks(): OperationsReleaseReadinessRunbookRecord[] {
  return [
    {
      key: 'incident_response',
      name: 'Launch incident response runbook',
      path: 'docs/runbooks/release-incident-response.md',
      owner_role: 'platform.super_admin',
      summary:
        'Defines how incident command, communications, triage, and customer-impact decisions are handled during launch.',
      when_to_use: 'Use when launch telemetry or user reports indicate a Sev1 or Sev2 production incident.',
    },
    {
      key: 'rollback',
      name: 'Release rollback runbook',
      path: 'docs/runbooks/release-rollback.md',
      owner_role: 'platform.support',
      summary:
        'Documents the controlled rollback sequence, evidence capture, and validation needed after reversing a launch.',
      when_to_use: 'Use when the approved go-live decision changes because production behavior is unsafe or non-compliant.',
    },
    {
      key: 'common_launch_issues',
      name: 'Common launch issues playbook',
      path: 'docs/runbooks/release-common-launch-issues.md',
      owner_role: 'tenant.admin',
      summary:
        'Lists repeatable responses for the most common post-launch issues across auth, integrations, payroll, and reporting.',
      when_to_use: 'Use when operators need a fast recovery pattern for known launch issues before escalation expands.',
    },
  ]
}

function buildReleaseReadinessAreas(
  release: OperationsWorkspaceData['release'],
  resilience: OperationsResilienceOverviewRecord,
  observability: OperationsObservabilityOverviewRecord,
): OperationsReleaseReadinessAreaRecord[] {
  const gateIndex = new Map(release.gates.map((gate) => [gate.key, gate]))
  const scenarioIndex = new Map(resilience.scenarios.map((scenario) => [scenario.key, scenario]))
  const serviceIndex = new Map(observability.services.map((service) => [service.key, service]))
  const workflowChecks = buildReleaseWorkflowVerifications()

  return [
    buildReleaseReadinessArea(
      {
        key: 'testing',
        name: 'Testing and build verification',
        source: 'release_gates',
        owner_role: 'platform.support',
        summary: 'API and web regression gates must stay green before launch review can proceed.',
        evidence_requirements: ['Latest API CI workflow result', 'Latest Web CI workflow result'],
      },
      ['api_quality', 'web_quality']
        .map((key) => gateIndex.get(key))
        .filter((gate): gate is OperationsReleaseGateRecord => Boolean(gate))
        .map((gate) => ({
          key: gate.key,
          label: gate.name,
          status: normalizeReleaseGateReadinessStatus(gate.status),
          owner_role: gate.owner_role,
          summary: gate.summary,
          last_reviewed_at: gate.last_run_at,
          artifact_refs: gate.artifact_refs,
        })),
    ),
    buildReleaseReadinessArea(
      {
        key: 'security',
        name: 'Security and dependency posture',
        source: 'release_gates',
        owner_role: 'platform.support',
        summary: 'Dependency and security checks must remain current before protected promotion is approved.',
        evidence_requirements: ['Composer audit result', 'npm audit result for API and web toolchains'],
      },
      ['dependency_security']
        .map((key) => gateIndex.get(key))
        .filter((gate): gate is OperationsReleaseGateRecord => Boolean(gate))
        .map((gate) => ({
          key: gate.key,
          label: gate.name,
          status: normalizeReleaseGateReadinessStatus(gate.status),
          owner_role: gate.owner_role,
          summary: gate.summary,
          last_reviewed_at: gate.last_run_at,
          artifact_refs: gate.artifact_refs,
        })),
    ),
    buildReleaseReadinessArea(
      {
        key: 'contracts',
        name: 'Contract and interface validation',
        source: 'release_gates',
        owner_role: 'tenant.admin',
        summary:
          'Approved API and release-control contracts must stay linted and reviewable for downstream consumers.',
        evidence_requirements: ['OpenAPI inventory review', 'OpenAPI lint result'],
      },
      ['contract_validation']
        .map((key) => gateIndex.get(key))
        .filter((gate): gate is OperationsReleaseGateRecord => Boolean(gate))
        .map((gate) => ({
          key: gate.key,
          label: gate.name,
          status: normalizeReleaseGateReadinessStatus(gate.status),
          owner_role: gate.owner_role,
          summary: gate.summary,
          last_reviewed_at: gate.last_run_at,
          artifact_refs: gate.artifact_refs,
        })),
    ),
    buildReleaseReadinessArea(
      {
        key: 'backups',
        name: 'Backups and recovery evidence',
        source: 'resilience_scenarios',
        owner_role: 'platform.super_admin',
        summary:
          'Backup, restore, and failover evidence must be current enough to support launch recovery commitments.',
        evidence_requirements: [
          'Recent recovery validation runs',
          'Backup retention confirmation',
          'Failover drill evidence',
        ],
      },
      [
        'daily_application_backup',
        'monthly_database_restore',
        'payroll_artifact_restore',
        'regional_failover_drill',
      ]
        .map((key) => scenarioIndex.get(key))
        .filter((scenario): scenario is OperationsResilienceScenarioRecord => Boolean(scenario))
        .map((scenario) => ({
          key: scenario.key,
          label: scenario.name,
          status: normalizeResilienceReadinessStatus(scenario.status),
          owner_role: scenario.owner_role,
          summary: scenario.summary,
          last_reviewed_at: scenario.last_validated_at,
          artifact_refs: scenario.latest_run?.evidence_refs ?? [],
        })),
    ),
    buildReleaseReadinessArea(
      {
        key: 'monitoring',
        name: 'Monitoring and operator telemetry',
        source: 'observability_services',
        owner_role: 'platform.support',
        summary: 'Release-critical monitoring must stay healthy so launch issues can be detected and routed quickly.',
        evidence_requirements: ['Healthy observability service posture', 'No unresolved critical alerts'],
      },
      [
        'core_api',
        'integration_delivery',
        'workflow_approvals',
        'payroll_controls',
        'notification_delivery',
        'reporting_delivery',
        'release_governance',
      ]
        .map((key) => serviceIndex.get(key))
        .filter((service): service is OperationsObservabilityServiceRecord => Boolean(service))
        .map((service) => ({
          key: service.key,
          label: service.name,
          status: normalizeObservabilityReadinessStatus(service.status),
          owner_role: service.owner_role,
          summary: service.summary,
          last_reviewed_at: daysAgo(0),
          artifact_refs: [],
        })),
    ),
    buildReleaseReadinessArea(
      {
        key: 'critical_workflows',
        name: 'Critical workflow verification',
        source: 'workflow_checks',
        owner_role: 'tenant.admin',
        summary: 'Launch-critical user journeys require explicit smoke-test evidence rather than assumption.',
        evidence_requirements: [
          'Smoke-test output for critical workflows',
          'Operator acknowledgement of workflow verification',
        ],
      },
      workflowChecks,
    ),
  ]
}

function buildReleaseReadinessArea(
  area: {
    key: string
    name: string
    source: string
    owner_role: string
    summary: string
    evidence_requirements: string[]
  },
  items: OperationsReleaseReadinessAreaItemRecord[],
): OperationsReleaseReadinessAreaRecord {
  const blockingItemCount = items.filter((item) => item.status === 'blocked').length
  const attentionItemCount = items.filter((item) => item.status === 'attention').length

  return {
    key: area.key,
    name: area.name,
    status: blockingItemCount ? 'blocked' : attentionItemCount ? 'attention' : 'ready',
    source: area.source,
    owner_role: area.owner_role,
    summary: area.summary,
    evidence_requirements: area.evidence_requirements,
    artifact_refs: Array.from(new Set(items.flatMap((item) => item.artifact_refs))),
    check_count: items.length,
    blocking_item_count: blockingItemCount,
    attention_item_count: attentionItemCount,
    last_reviewed_at: [...items]
      .map((item) => item.last_reviewed_at)
      .filter((value): value is string => Boolean(value))
      .sort()
      .at(-1) ?? null,
    items,
  }
}

function normalizeReleaseGateReadinessStatus(status: string) {
  if (status === 'passing') {
    return 'ready'
  }

  return status === 'warning' ? 'attention' : 'blocked'
}

function normalizeResilienceReadinessStatus(status: string) {
  if (status === 'ready') {
    return 'ready'
  }

  return status === 'failed' ? 'blocked' : 'attention'
}

function normalizeObservabilityReadinessStatus(status: string) {
  if (status === 'healthy') {
    return 'ready'
  }

  return status === 'critical' ? 'blocked' : 'attention'
}

function buildInitialReleaseReadinessDecisions(
  areas: OperationsReleaseReadinessAreaRecord[],
): OperationsReleaseReadinessDecisionRecord[] {
  return [
    {
      id: 9901,
      release_window_label: 'FY26 payroll launch wave 1',
      target_environment: 'production',
      decision_status: 'conditional',
      summary: 'Launch can proceed only if recovery evidence is refreshed and alert owners remain actively engaged.',
      blockers: [
        {
          area_key: 'backups',
          title: 'Regional failover drill remains failed',
          owner_role: 'platform.super_admin',
          status: 'open',
          notes: 'Recovery rehearsal must be rerun before production promotion is approved.',
        },
        {
          area_key: 'monitoring',
          title: 'Release-critical alerts must stay on named owners until cleared',
          owner_role: 'platform.support',
          status: 'open',
          notes: 'Integration delivery and payroll control lanes still need active recovery follow-through.',
        },
      ],
      artifact_refs: [
        'docs/runbooks/release-incident-response.md',
        'docs/runbooks/release-common-launch-issues.md',
      ],
      checklist_snapshot: createReleaseReadinessChecklistSnapshot(areas),
      decision_notes:
        'Staging validation is complete, but production promotion stays conditional until DR evidence and alert cleanup are reconfirmed.',
      decided_at: '2026-07-01T11:15:00+05:30',
      decided_by_user_id: 9001,
      decided_by_name: 'Aditi Rao',
      created_at: '2026-07-01T11:15:00+05:30',
      updated_at: '2026-07-01T11:15:00+05:30',
    },
  ]
}

function createReleaseReadinessChecklistSnapshot(
  areas: OperationsReleaseReadinessAreaRecord[],
) {
  return areas.map((area) => ({
    key: area.key,
    name: area.name,
    status: area.status,
    blocking_item_count: area.blocking_item_count,
    attention_item_count: area.attention_item_count,
    last_reviewed_at: area.last_reviewed_at,
  }))
}

function buildReleaseReadinessBlockers(
  areas: OperationsReleaseReadinessAreaRecord[],
  latestDecision: OperationsReleaseReadinessDecisionRecord | null,
): OperationsReleaseReadinessBlockerRecord[] {
  const systemBlockers = areas
    .filter((area) => area.status !== 'ready')
    .map((area) => ({
      key: `system:${area.key}`,
      area_key: area.key,
      area_name: area.name,
      title: `${area.name} still needs release review`,
      status: 'open',
      owner_role: area.owner_role,
      source: 'system',
      summary:
        area.status === 'blocked'
          ? `${Math.max(1, area.blocking_item_count)} blocking checklist item(s) still need remediation before launch approval can proceed.`
          : `${Math.max(1, area.attention_item_count)} checklist item(s) still need fresh evidence or operator acknowledgement before launch review is complete.`,
      artifact_refs: area.artifact_refs,
    }))

  const decisionBlockers = (latestDecision?.blockers ?? []).map((blocker) => ({
    key: `decision:${latestDecision?.id ?? 'latest'}:${blocker.area_key ?? 'general'}:${blocker.title}`,
    area_key: blocker.area_key,
    area_name: areas.find((area) => area.key === blocker.area_key)?.name ?? null,
    title: blocker.title,
    status: blocker.status,
    owner_role: blocker.owner_role,
    source: 'decision',
    summary: blocker.notes ?? 'Decision-scoped blocker recorded during go or no-go review.',
    artifact_refs: [],
  }))

  return [...systemBlockers, ...decisionBlockers]
}

function buildReleaseReadinessSummary(
  areas: OperationsReleaseReadinessAreaRecord[],
  blockers: OperationsReleaseReadinessBlockerRecord[],
  decisions: OperationsReleaseReadinessDecisionRecord[],
): OperationsReleaseReadinessSummaryRecord {
  return {
    total_area_count: areas.length,
    ready_area_count: areas.filter((area) => area.status === 'ready').length,
    attention_area_count: areas.filter((area) => area.status === 'attention').length,
    blocked_area_count: areas.filter((area) => area.status === 'blocked').length,
    blocker_count: blockers.filter((blocker) => blocker.status === 'open').length,
    runbook_count: buildReleaseRunbooks().length,
    decision_count: decisions.length,
    latest_decision_at: decisions[0]?.decided_at ?? null,
  }
}

function buildReleaseReadinessRecommendation(
  areas: OperationsReleaseReadinessAreaRecord[],
  latestDecision: OperationsReleaseReadinessDecisionRecord | null,
  blockers: OperationsReleaseReadinessBlockerRecord[],
): OperationsReleaseReadinessRecommendationRecord {
  if (areas.some((area) => area.status === 'blocked')) {
    return {
      status: 'no_go',
      summary: 'Blocking readiness areas still need remediation before launch approval can proceed.',
    }
  }

  if (!latestDecision) {
    return {
      status: 'pending_review',
      summary: 'Checklist evidence is available, but no accountable go or no-go decision has been recorded yet.',
    }
  }

  if (latestDecision.decision_status === 'no_go') {
    return {
      status: 'no_go',
      summary: `Latest go-live review for ${latestDecision.release_window_label} is recorded as no-go.`,
    }
  }

  if (
    latestDecision.decision_status === 'conditional' ||
    areas.some((area) => area.status === 'attention') ||
    blockers.some((blocker) => blocker.status === 'open')
  ) {
    return {
      status: 'conditional',
      summary: 'Launch is conditionally approved only if the remaining evidence and blocker owners are actively tracked.',
    }
  }

  return {
    status: 'go',
    summary: `Latest go-live review for ${latestDecision.release_window_label} is approved with no unresolved blockers.`,
  }
}

function sortReleaseReadinessDecisions(
  decisions: OperationsReleaseReadinessDecisionRecord[],
) {
  return [...decisions].sort(
    (left, right) =>
      new Date(right.decided_at ?? right.created_at ?? 0).getTime() -
      new Date(left.decided_at ?? left.created_at ?? 0).getTime(),
  )
}

function buildDemoReleaseReadiness(
  release: OperationsWorkspaceData['release'],
  resilience: OperationsResilienceOverviewRecord,
  observability: OperationsObservabilityOverviewRecord,
  decisions: OperationsReleaseReadinessDecisionRecord[],
): OperationsReleaseReadinessOverviewRecord {
  const areas = buildReleaseReadinessAreas(release, resilience, observability)
  const sortedDecisions = sortReleaseReadinessDecisions(decisions)
  const latestDecision = sortedDecisions[0] ?? null
  const blockers = buildReleaseReadinessBlockers(areas, latestDecision)

  return {
    summary: buildReleaseReadinessSummary(areas, blockers, sortedDecisions),
    policy: buildReleaseReadinessPolicy(),
    recommendation: buildReleaseReadinessRecommendation(areas, latestDecision, blockers),
    areas,
    blockers,
    runbooks: buildReleaseRunbooks(),
    latest_decision: latestDecision,
    decision_history: sortedDecisions,
  }
}

export function createDemoReleaseReadinessDecision(
  workspace: OperationsWorkspaceData,
  values: ReleaseReadinessDecisionFormValues,
  actorName: string,
  actorId: number | null,
) {
  const existingDecisions = workspace.releaseReadiness.decision_history
  const nextId =
    existingDecisions.reduce((largest, decision) => Math.max(largest, decision.id), 0) + 1
  const blockerLines = values.blockers
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)
  const blockers = blockerLines.map((line) => {
    const [areaKey = '', ownerRole = '', title = '', notes = ''] = line.split('|').map((item) => item.trim())

    return {
      area_key: areaKey || null,
      owner_role: ownerRole || 'tenant.admin',
      title: title || line,
      notes: notes || null,
      status: 'open',
    }
  })
  const decision: OperationsReleaseReadinessDecisionRecord = {
    id: nextId,
    release_window_label: values.release_window_label.trim(),
    target_environment: values.target_environment,
    decision_status: values.decision_status,
    summary: values.summary.trim(),
    blockers,
    artifact_refs: values.artifact_refs
      .split(/[\n,]/)
      .map((item) => item.trim())
      .filter(Boolean),
    checklist_snapshot: createReleaseReadinessChecklistSnapshot(workspace.releaseReadiness.areas),
    decision_notes: values.decision_notes.trim() || null,
    decided_at: new Date().toISOString(),
    decided_by_user_id: actorId,
    decided_by_name: actorName,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }
  const decisionHistory = [decision, ...existingDecisions]

  return {
    ...workspace,
    releaseReadiness: buildDemoReleaseReadiness(
      workspace.release,
      workspace.resilience,
      workspace.observability,
      decisionHistory,
    ),
  }
}

export function buildDemoResilienceOverview(
  validationRuns: OperationsResilienceValidationRunRecord[],
): OperationsResilienceOverviewRecord {
  const sortedValidationRuns = [...validationRuns].sort(
    (left, right) =>
      new Date(right.completed_at ?? right.started_at ?? 0).getTime() -
      new Date(left.completed_at ?? left.started_at ?? 0).getTime(),
  )
  const scenarios = buildResilienceScenarios(sortedValidationRuns)

  return {
    summary: buildResilienceSummary(scenarios, sortedValidationRuns),
    policy: buildResiliencePolicy(),
    scenarios,
    runbook: buildResilienceRunbook(),
    validation_runs: sortedValidationRuns,
  }
}

function buildResiliencePolicy(): OperationsResiliencePolicyRecord {
  return {
    primary_region: 'ap-south-1',
    secondary_region: 'ap-southeast-1',
    backup_cadence:
      'Continuous database WAL shipping with nightly application snapshots at 02:00 Asia/Kolkata.',
    restore_validation_cadence:
      'Critical restore paths are validated weekly or monthly according to scenario cadence.',
    dr_drill_cadence: 'Regional failover drills are required at least once per quarter.',
    retention_policy:
      'Daily backups retained for 35 days, weekly checkpoints for 12 weeks, and monthly archives for 13 months.',
    encryption_posture:
      'Backup artifacts are encrypted at rest with tenant-approved KMS keys and in transit over private transport.',
    coverage_scope:
      'Application data, payroll artifacts, document storage metadata, and release-critical configuration baselines.',
    default_rpo_minutes: 60,
    default_rto_minutes: 240,
    artifact_refs: [
      '.github/workflows/release-quality-gates.yml',
      'docs/runbooks/backup-restore-disaster-recovery-validation.md',
      'docs/files/40-production-operations-runbook.md',
    ],
  }
}

function buildResilienceRunbook(): OperationsResilienceRunbookStepRecord[] {
  return [
    {
      key: 'declare',
      name: 'Declare recovery scenario and assign incident command',
      sequence: 1,
      owner_role: 'platform.super_admin',
      objective:
        'Identify the scenario, assign accountable leads, and freeze unsafe changes before recovery actions begin.',
      evidence_requirements: ['Scenario declaration timestamp', 'Incident commander and communications owner'],
    },
    {
      key: 'stabilize',
      name: 'Stabilize writes and confirm backup set',
      sequence: 2,
      owner_role: 'platform.support',
      objective:
        'Protect data integrity by stopping risky writes and confirming the recovery source is complete and encrypted.',
      evidence_requirements: ['Protected-write freeze confirmation', 'Backup identifier and checksum proof'],
    },
    {
      key: 'execute',
      name: 'Execute restore or failover sequence',
      sequence: 3,
      owner_role: 'it.admin',
      objective:
        'Restore the governed dataset or shift runtime to the approved secondary environment using the validated sequence.',
      evidence_requirements: ['Execution log with timestamps', 'Recovered environment reference'],
    },
    {
      key: 'validate',
      name: 'Validate launch-critical workflows',
      sequence: 4,
      owner_role: 'tenant.admin',
      objective:
        'Verify sign-in, employee search, payroll evidence, and release-critical monitoring before declaring readiness.',
      evidence_requirements: ['Smoke-test checklist output', 'Monitoring and alert baseline confirmation'],
    },
    {
      key: 'close',
      name: 'Capture evidence, decisions, and follow-up actions',
      sequence: 5,
      owner_role: 'tenant.admin',
      objective:
        'Record the recovery outcome, residual risk, and remediation owners so readiness remains reviewable.',
      evidence_requirements: ['Linked evidence artifacts', 'Outcome summary and remediation owners'],
    },
  ]
}

function buildResilienceScenarios(
  validationRuns: OperationsResilienceValidationRunRecord[],
): OperationsResilienceScenarioRecord[] {
  const scenarioConfigs = [
    {
      key: 'daily_application_backup',
      name: 'Daily application backup',
      scenario_type: 'backup',
      environment: 'production',
      owner_role: 'platform.support',
      cadence_days: 1,
      recovery_point_objective_minutes: 60,
      recovery_time_objective_minutes: 240,
      summary:
        'Confirms production application and database backup jobs complete successfully with encrypted artifact evidence.',
      evidence_requirements: [
        'Backup job identifier or snapshot reference',
        'Retention and checksum confirmation',
        'Operator sign-off in the recovery log',
      ],
    },
    {
      key: 'monthly_database_restore',
      name: 'Monthly database restore validation',
      scenario_type: 'restore',
      environment: 'staging',
      owner_role: 'tenant.admin',
      cadence_days: 30,
      recovery_point_objective_minutes: 60,
      recovery_time_objective_minutes: 180,
      summary:
        'Restores the governed production backup into staging and verifies authentication, employee directory, and reporting read paths.',
      evidence_requirements: [
        'Restore execution log',
        'Schema and row-count validation output',
        'Smoke-test evidence for auth and employee search',
      ],
    },
    {
      key: 'payroll_artifact_restore',
      name: 'Payroll artifact restore rehearsal',
      scenario_type: 'restore',
      environment: 'staging',
      owner_role: 'it.admin',
      cadence_days: 14,
      recovery_point_objective_minutes: 30,
      recovery_time_objective_minutes: 120,
      summary:
        'Verifies protected payslip and payroll-export artifacts can be restored with intact metadata, access controls, and audit visibility.',
      evidence_requirements: [
        'Restored artifact reference',
        'Access-control verification notes',
        'Payroll owner acknowledgement',
      ],
    },
    {
      key: 'regional_failover_drill',
      name: 'Regional failover drill',
      scenario_type: 'disaster_recovery',
      environment: 'secondary_region',
      owner_role: 'platform.super_admin',
      cadence_days: 90,
      recovery_point_objective_minutes: 15,
      recovery_time_objective_minutes: 120,
      summary:
        'Exercises failover sequencing, role handoffs, and launch-critical smoke checks before approving regional recovery readiness.',
      evidence_requirements: [
        'Incident timeline and command log',
        'Failover and failback checkpoint evidence',
        'Launch-critical workflow verification results',
      ],
    },
  ] satisfies Array<
    Omit<
      OperationsResilienceScenarioRecord,
      'status' | 'overdue' | 'blocked_reason' | 'last_validated_at' | 'next_validation_due_at' | 'latest_run'
    >
  >

  return scenarioConfigs.map((scenario) => {
    const latestRun = validationRuns.find((record) => record.scenario_key === scenario.key) ?? null
    const latestValidationAt = latestRun?.completed_at ?? latestRun?.started_at ?? null
    const nextValidationDueAt = latestValidationAt
      ? new Date(new Date(latestValidationAt).getTime() + scenario.cadence_days * 24 * 60 * 60 * 1000).toISOString()
      : null
    const overdue = latestRun === null || (nextValidationDueAt ? new Date(nextValidationDueAt).getTime() < Date.now() : true)

    const status =
      latestRun?.status === 'failed'
        ? 'failed'
        : latestRun?.status === 'issues_found' || latestRun?.status === 'in_progress'
          ? 'attention'
          : latestRun?.status === 'passed'
            ? overdue
              ? 'attention'
              : 'ready'
            : 'attention'

    const blockedReason =
      latestRun === null
        ? 'No validation run has been recorded yet for this recovery scenario.'
        : latestRun.status === 'failed'
          ? 'The latest validation failed and recovery readiness is not approved.'
          : latestRun.status === 'issues_found'
            ? 'The latest validation found issues that still require remediation and retest evidence.'
            : latestRun.status === 'in_progress'
              ? 'Validation is still in progress and evidence collection is not complete yet.'
              : overdue
                ? 'The latest successful validation is older than the agreed cadence and must be rerun.'
                : null

    return {
      ...scenario,
      status,
      overdue,
      blocked_reason: blockedReason,
      last_validated_at: latestValidationAt,
      next_validation_due_at: nextValidationDueAt,
      latest_run: latestRun,
    }
  })
}

function buildResilienceSummary(
  scenarios: OperationsResilienceScenarioRecord[],
  validationRuns: OperationsResilienceValidationRunRecord[],
): OperationsResilienceSummaryRecord {
  return {
    total_scenario_count: scenarios.length,
    ready_scenario_count: scenarios.filter((scenario) => scenario.status === 'ready').length,
    attention_scenario_count: scenarios.filter((scenario) => scenario.status === 'attention').length,
    failed_scenario_count: scenarios.filter((scenario) => scenario.status === 'failed').length,
    overdue_scenario_count: scenarios.filter((scenario) => scenario.overdue).length,
    validation_run_count: validationRuns.length,
    latest_validation_at: validationRuns[0]?.completed_at ?? validationRuns[0]?.started_at ?? null,
  }
}

function buildResilienceValidationRuns(): OperationsResilienceValidationRunRecord[] {
  return [
    {
      id: 9901,
      scenario_key: 'daily_application_backup',
      scenario_name: 'Daily application backup',
      scenario_type: 'backup',
      environment: 'production',
      status: 'passed',
      recovery_point_actual_minutes: 18,
      recovery_time_actual_minutes: 72,
      evidence_refs: ['backup-job-20260701-0200', 'checksum-report-20260701'],
      notes: 'Nightly backup completed inside the expected window.',
      started_at: hoursAgo(8),
      completed_at: hoursAgo(8),
      executed_by_user_id: 1,
      executed_by_name: 'Platform Admin',
      created_at: hoursAgo(8),
      updated_at: hoursAgo(8),
    },
    {
      id: 9902,
      scenario_key: 'payroll_artifact_restore',
      scenario_name: 'Payroll artifact restore rehearsal',
      scenario_type: 'restore',
      environment: 'staging',
      status: 'issues_found',
      recovery_point_actual_minutes: 36,
      recovery_time_actual_minutes: 141,
      evidence_refs: ['payslip-archive-restore-20260629', 'access-review-ticket-441'],
      notes: 'Restored artifacts were readable, but the archive mirror missed one protected payroll export tag.',
      started_at: daysAgo(2),
      completed_at: daysAgo(2),
      executed_by_user_id: 5,
      executed_by_name: 'IT Operator',
      created_at: daysAgo(2),
      updated_at: daysAgo(2),
    },
    {
      id: 9903,
      scenario_key: 'monthly_database_restore',
      scenario_name: 'Monthly database restore validation',
      scenario_type: 'restore',
      environment: 'staging',
      status: 'passed',
      recovery_point_actual_minutes: 29,
      recovery_time_actual_minutes: 118,
      evidence_refs: ['db-restore-20260619', 'smoke-auth-directory-20260619'],
      notes: 'Authentication and directory search checks passed after restore.',
      started_at: daysAgo(12),
      completed_at: daysAgo(12),
      executed_by_user_id: 2,
      executed_by_name: 'Tenant Administrator',
      created_at: daysAgo(12),
      updated_at: daysAgo(12),
    },
    {
      id: 9904,
      scenario_key: 'regional_failover_drill',
      scenario_name: 'Regional failover drill',
      scenario_type: 'disaster_recovery',
      environment: 'secondary_region',
      status: 'failed',
      recovery_point_actual_minutes: 47,
      recovery_time_actual_minutes: 301,
      evidence_refs: ['incident-log-drill-q2', 'failover-smoke-check-q2'],
      notes: 'Failover completed, but queue workers and report exports did not recover cleanly in the secondary region.',
      started_at: daysAgo(104),
      completed_at: daysAgo(104),
      executed_by_user_id: 1,
      executed_by_name: 'Platform Admin',
      created_at: daysAgo(104),
      updated_at: daysAgo(104),
    },
  ]
}

function buildObservabilityOverview(
  subscriptions: OperationsIntegrationSubscriptionRecord[],
  syncJobs: OperationsIntegrationSyncJobRecord[],
  releaseGates: OperationsReleaseGateRecord[],
  releaseEnvironments: OperationsReleaseEnvironmentRecord[],
): OperationsObservabilityOverviewRecord {
  const alertRoutes = buildObservabilityAlertRoutes()
  const routeIndex = new Map(alertRoutes.map((route) => [route.key, route]))
  const signals = buildObservabilitySignals(syncJobs, releaseGates, releaseEnvironments).map((signal) => {
    const route = signal.route_key ? routeIndex.get(signal.route_key) : null

    return {
      ...signal,
      route_name: route?.name ?? null,
      route_channels: route?.channels ?? [],
    }
  })
  const services = buildObservabilityServices(signals)
  const serviceIndex = new Map(services.map((service) => [service.key, service]))
  const alerts = buildObservabilityAlerts(signals, serviceIndex)
  const coverage = buildObservabilityCoverage(subscriptions, syncJobs, releaseGates, releaseEnvironments)

  return {
    summary: buildObservabilitySummary(services, alerts, coverage),
    telemetry: buildObservabilityTelemetry(),
    services,
    signals,
    alerts,
    alert_routes: alertRoutes,
    coverage,
  }
}

function buildObservabilitySummary(
  services: OperationsObservabilityServiceRecord[],
  alerts: OperationsObservabilityAlertRecord[],
  coverage: OperationsObservabilityOverviewRecord['coverage'],
): OperationsObservabilitySummaryRecord {
  return {
    service_count: services.length,
    healthy_service_count: services.filter((service) => service.status === 'healthy').length,
    degraded_service_count: services.filter((service) => service.status === 'degraded').length,
    critical_service_count: services.filter((service) => service.status === 'critical').length,
    active_alert_count: alerts.length,
    routed_alert_count: alerts.filter((alert) => alert.route_key).length,
    monitored_workflow_count: coverage.workflows.length,
    monitored_integration_count: coverage.integrations.length,
    release_critical_coverage_count: coverage.release_critical.length,
  }
}

function buildObservabilityTelemetry(): OperationsObservabilityTelemetryRecord {
  return {
    health_endpoint: '/up',
    default_log_channel: 'stack',
    slack_alert_channel: '#platform-sev1',
    dashboard_refresh_minutes: 5,
    required_release_workflows: ['Release Quality Gates', 'API CI', 'Web CI'],
  }
}

function buildObservabilityAlertRoutes(): OperationsObservabilityAlertRouteRecord[] {
  return [
    {
      key: 'sev1_platform',
      severity: 'sev1',
      name: 'SEV1 platform command',
      owner_team: 'platform.support',
      channels: ['slack:#platform-sev1', 'incident:on-call-primary'],
      initial_response_minutes: 5,
      escalation_minutes: 15,
    },
    {
      key: 'sev2_operations',
      severity: 'sev2',
      name: 'SEV2 operations response',
      owner_team: 'tenant.admin',
      channels: ['slack:#ops-alerts', 'email:ops-leads@phoenixhrms.test'],
      initial_response_minutes: 15,
      escalation_minutes: 60,
    },
    {
      key: 'sev3_business',
      severity: 'sev3',
      name: 'SEV3 business workflow review',
      owner_team: 'hr.admin',
      channels: ['slack:#business-observability', 'queue:daily-ops-review'],
      initial_response_minutes: 60,
      escalation_minutes: 240,
    },
  ]
}

function buildObservabilitySignals(
  syncJobs: OperationsIntegrationSyncJobRecord[],
  releaseGates: OperationsReleaseGateRecord[],
  releaseEnvironments: OperationsReleaseEnvironmentRecord[],
): OperationsObservabilitySignalRecord[] {
  const failedIntegrationJobs = syncJobs.filter((job) => job.status === 'failed').length
  const staleIntegrationJobs = syncJobs.filter((job) => job.status === 'queued').length
  const releaseBlockingGates = buildReleaseSummary(releaseGates, releaseEnvironments).blocking_gate_count

  return [
    createObservabilitySignal({
      key: 'integration_failed_jobs',
      name: 'Failed integration sync jobs',
      category: 'integrations',
      service_key: 'integration_delivery',
      owner_role: 'integration.manage',
      unit: 'jobs',
      value: failedIntegrationJobs,
      warning_threshold: 1,
      critical_threshold: 3,
      warning_route_key: 'sev2_operations',
      critical_route_key: 'sev1_platform',
      drill_in_label: 'Open integration sync queue',
      drill_in_path: '/operations/integrations',
      healthy_summary: 'No failed integration sync jobs are awaiting operator retry.',
      warning_summary: `${failedIntegrationJobs} integration sync job(s) failed and require retry or payload review.`,
    }),
    createObservabilitySignal({
      key: 'integration_stale_queue',
      name: 'Queued integration jobs beyond SLA',
      category: 'integrations',
      service_key: 'integration_delivery',
      owner_role: 'integration.manage',
      unit: 'jobs',
      value: staleIntegrationJobs,
      warning_threshold: 1,
      critical_threshold: 4,
      warning_route_key: 'sev2_operations',
      critical_route_key: 'sev1_platform',
      drill_in_label: 'Review queued jobs',
      drill_in_path: '/operations/integrations',
      healthy_summary: 'No queued integration jobs have breached the stale-delivery threshold.',
      warning_summary: `${staleIntegrationJobs} queued integration job(s) are older than the agreed delivery window and need processing.`,
    }),
    createObservabilitySignal({
      key: 'workflow_overdue_tasks',
      name: 'Workflow tasks beyond SLA',
      category: 'workflow',
      service_key: 'workflow_approvals',
      owner_role: 'workflow.monitor',
      unit: 'tasks',
      value: 2,
      warning_threshold: 1,
      critical_threshold: 4,
      warning_route_key: 'sev3_business',
      critical_route_key: 'sev2_operations',
      drill_in_label: 'Open workflow queue',
      drill_in_path: '/operations/lifecycle',
      healthy_summary: 'No open workflow tasks are overdue against configured SLA targets.',
      warning_summary: '2 workflow task(s) are overdue against stage SLA targets.',
    }),
    createObservabilitySignal({
      key: 'payroll_blocked_runs',
      name: 'Blocked or failed payroll runs',
      category: 'payroll',
      service_key: 'payroll_controls',
      owner_role: 'payroll.process',
      unit: 'runs',
      value: 1,
      warning_threshold: 1,
      critical_threshold: 1,
      warning_route_key: 'sev1_platform',
      critical_route_key: 'sev1_platform',
      drill_in_label: 'Open payroll review',
      drill_in_path: '/payroll/review',
      healthy_summary: 'No payroll runs are blocked or failed.',
      warning_summary: '1 payroll run is blocked ahead of calculation or approval.',
    }),
    createObservabilitySignal({
      key: 'notification_failed_delivery',
      name: 'Failed notification deliveries',
      category: 'notifications',
      service_key: 'notification_delivery',
      owner_role: 'notification.manage',
      unit: 'deliveries',
      value: 2,
      warning_threshold: 1,
      critical_threshold: 5,
      warning_route_key: 'sev3_business',
      critical_route_key: 'sev2_operations',
      drill_in_label: 'Review delivery failures',
      drill_in_path: '/foundation',
      healthy_summary: 'Notification delivery is clear of failed sends.',
      warning_summary: '2 notification delivery failures still need retry or channel review.',
    }),
    createObservabilitySignal({
      key: 'report_failed_exports',
      name: 'Failed report exports',
      category: 'reporting',
      service_key: 'reporting_delivery',
      owner_role: 'reporting.manage',
      unit: 'exports',
      value: 1,
      warning_threshold: 1,
      critical_threshold: 3,
      warning_route_key: 'sev3_business',
      critical_route_key: 'sev2_operations',
      drill_in_label: 'Open report exports',
      drill_in_path: '/reporting/exports',
      healthy_summary: 'No report exports are currently failed.',
      warning_summary: '1 report export failed before governed delivery completed.',
    }),
    createObservabilitySignal({
      key: 'report_blocked_subscriptions',
      name: 'Blocked or paused report subscriptions',
      category: 'reporting',
      service_key: 'reporting_delivery',
      owner_role: 'reporting.manage',
      unit: 'subscriptions',
      value: 1,
      warning_threshold: 1,
      critical_threshold: 2,
      warning_route_key: 'sev3_business',
      critical_route_key: 'sev2_operations',
      drill_in_label: 'Open report subscriptions',
      drill_in_path: '/reporting/subscriptions',
      healthy_summary: 'No report subscriptions are blocked or paused.',
      warning_summary: '1 report subscription is blocked and needs governance review.',
    }),
    createObservabilitySignal({
      key: 'release_blocking_gates',
      name: 'Blocking release-quality gates',
      category: 'release',
      service_key: 'release_governance',
      owner_role: 'release.manage',
      unit: 'gates',
      value: releaseBlockingGates,
      warning_threshold: 1,
      critical_threshold: 1,
      warning_route_key: 'sev1_platform',
      critical_route_key: 'sev1_platform',
      drill_in_label: 'Open release baseline',
      drill_in_path: '/operations/release',
      healthy_summary: `All ${releaseGates.length} blocking release gate(s) are currently passing.`,
      warning_summary: `${releaseBlockingGates} blocking release gate(s) are preventing protected promotion.`,
    }),
  ]
}

function createObservabilitySignal(
  config: {
    key: string
    name: string
    category: string
    service_key: string
    owner_role: string
    unit: string
    value: number
    warning_threshold: number
    critical_threshold: number
    warning_route_key: string
    critical_route_key: string
    drill_in_label: string
    drill_in_path: string
    healthy_summary: string
    warning_summary: string
  },
): OperationsObservabilitySignalRecord {
  const status =
    config.value >= config.critical_threshold ? 'critical' : config.value >= config.warning_threshold ? 'warning' : 'healthy'
  const routeKey =
    status === 'critical'
      ? config.critical_route_key
      : status === 'warning'
        ? config.warning_route_key
        : null

  return {
    key: config.key,
    name: config.name,
    category: config.category,
    service_key: config.service_key,
    status,
    severity: routeKey ? routeKey.split('_')[0] : null,
    owner_role: config.owner_role,
    value: config.value,
    threshold: status === 'critical' ? config.critical_threshold : config.warning_threshold,
    unit: config.unit,
    summary: status === 'healthy' ? config.healthy_summary : config.warning_summary,
    observed_at: daysAgo(0),
    route_key: routeKey,
    route_name: null,
    route_channels: [],
    drill_in_label: config.drill_in_label,
    drill_in_path: config.drill_in_path,
  }
}

function buildObservabilityServices(
  signals: OperationsObservabilitySignalRecord[],
): OperationsObservabilityServiceRecord[] {
  const serviceConfigs = [
    {
      key: 'core_api',
      name: 'Core API and tenant health',
      category: 'platform',
      owner_role: 'platform.support',
      summary: 'The application health endpoint and centralized log pipeline form the baseline tenant-wide service availability contract.',
      signal_keys: [],
    },
    {
      key: 'integration_delivery',
      name: 'Integration delivery',
      category: 'integrations',
      owner_role: 'integration.manage',
      summary: 'Outbound and inbound sync posture is monitored so failed deliveries and stale queues are visible before downstream drift grows.',
      signal_keys: ['integration_failed_jobs', 'integration_stale_queue'],
    },
    {
      key: 'workflow_approvals',
      name: 'Workflow approvals',
      category: 'workflow',
      owner_role: 'workflow.monitor',
      summary: 'Approval-task SLA pressure is surfaced from the workflow engine so people operations and reviewers can recover stalled decisions.',
      signal_keys: ['workflow_overdue_tasks'],
    },
    {
      key: 'payroll_controls',
      name: 'Payroll controls',
      category: 'payroll',
      owner_role: 'payroll.process',
      summary: 'Blocked or failed payroll runs are tracked as release-critical operational issues because they directly affect pay-cycle readiness.',
      signal_keys: ['payroll_blocked_runs'],
    },
    {
      key: 'notification_delivery',
      name: 'Notification delivery',
      category: 'communications',
      owner_role: 'notification.manage',
      summary: 'Failed employee and operator notifications stay visible so retry handling and channel remediation are reviewable.',
      signal_keys: ['notification_failed_delivery'],
    },
    {
      key: 'reporting_delivery',
      name: 'Reporting delivery',
      category: 'reporting',
      owner_role: 'reporting.manage',
      summary: 'Governed report exports and subscriptions remain observable so scheduled delivery does not silently drift from certification posture.',
      signal_keys: ['report_failed_exports', 'report_blocked_subscriptions'],
    },
    {
      key: 'release_governance',
      name: 'Release governance',
      category: 'release',
      owner_role: 'release.manage',
      summary: 'Protected promotion depends on the same blocking release-quality gates surfaced in the release engineering baseline.',
      signal_keys: ['release_blocking_gates'],
    },
  ]

  return serviceConfigs.map((service) => {
    const serviceSignals = signals.filter((signal) => service.signal_keys.includes(signal.key))
    const status = serviceSignals.some((signal) => signal.status === 'critical')
      ? 'critical'
      : serviceSignals.some((signal) => signal.status === 'warning')
        ? 'degraded'
        : 'healthy'

    return {
      key: service.key,
      name: service.name,
      category: service.category,
      owner_role: service.owner_role,
      status,
      summary: service.summary,
      signal_keys: [...service.signal_keys],
      alert_count: serviceSignals.filter((signal) => signal.severity).length,
      metric_count: serviceSignals.length,
      metrics: serviceSignals.map((signal) => ({
        key: signal.key,
        label: signal.name,
        value: signal.value,
        threshold: signal.threshold,
        unit: signal.unit,
        status: signal.status,
      })),
    }
  })
}

function buildObservabilityAlerts(
  signals: OperationsObservabilitySignalRecord[],
  serviceIndex: Map<string, OperationsObservabilityServiceRecord>,
): OperationsObservabilityAlertRecord[] {
  return signals
    .filter((signal) => signal.severity)
    .map((signal) => ({
      key: signal.key,
      title: observabilityAlertTitle(signal.key),
      severity: signal.severity as string,
      service_key: signal.service_key,
      service_name: serviceIndex.get(signal.service_key)?.name ?? signal.service_key,
      signal_key: signal.key,
      status: 'active',
      owner_role: signal.owner_role,
      route_key: signal.route_key,
      route_name: signal.route_name,
      channels: signal.route_channels,
      summary: signal.summary,
      started_at: signal.observed_at,
    }))
}

function observabilityAlertTitle(signalKey: string) {
  switch (signalKey) {
    case 'integration_failed_jobs':
      return 'Integration delivery failures are accumulating'
    case 'integration_stale_queue':
      return 'Integration queue breached delivery SLA'
    case 'workflow_overdue_tasks':
      return 'Workflow approvals breached SLA'
    case 'payroll_blocked_runs':
      return 'Payroll control lane is blocked'
    case 'notification_failed_delivery':
      return 'Notification delivery needs recovery'
    case 'report_failed_exports':
      return 'Report export delivery failed'
    case 'report_blocked_subscriptions':
      return 'Report subscriptions are blocked'
    case 'release_blocking_gates':
      return 'Release promotion is blocked'
    default:
      return 'Observability alert'
  }
}

function buildObservabilityCoverage(
  subscriptions: OperationsIntegrationSubscriptionRecord[],
  syncJobs: OperationsIntegrationSyncJobRecord[],
  releaseGates: OperationsReleaseGateRecord[],
  releaseEnvironments: OperationsReleaseEnvironmentRecord[],
): OperationsObservabilityOverviewRecord['coverage'] {
  const releaseSummary = buildReleaseSummary(releaseGates, releaseEnvironments)

  return {
    workflows: [
      createCoverageItem(
        'leave_approval_workflow',
        'Leave approval workflow',
        'workflow',
        'workflow.monitor',
        1,
        1,
        ['workflow_overdue_tasks'],
        'Manager and HR leave approvals inherit workflow-task SLA monitoring so stalled decisions are visible before employee balances drift.',
      ),
      createCoverageItem(
        'attendance_correction_workflow',
        'Attendance correction workflow',
        'workflow',
        'workflow.monitor',
        1,
        0,
        ['workflow_overdue_tasks'],
        'Attendance corrections stay within the observability baseline so unresolved manager or HR approvals cannot disappear between shifts.',
      ),
      createCoverageItem(
        'offboarding_clearance_workflow',
        'Employee offboarding clearance workflow',
        'workflow',
        'workflow.monitor',
        1,
        1,
        ['workflow_overdue_tasks'],
        'Exit clearances are tracked as release-critical workflow coverage because blocked offboarding can hold asset and compliance closure open.',
      ),
    ],
    integrations: [
      createCoverageItem(
        'identity_directory_sync',
        'Identity directory sync',
        'integration',
        'integration.manage',
        subscriptions.filter(
          (subscription) =>
            subscription.connection?.system_key === 'identity_directory' &&
            ['employee.updated', 'directory.profile.sync', 'employee.created'].includes(subscription.event_key) &&
            subscription.status === 'active',
        ).length,
        syncJobs.filter((job) => job.system_key === 'identity_directory' && ['failed', 'queued'].includes(job.status)).length,
        ['integration_failed_jobs', 'integration_stale_queue'],
        'Provisioning and inbound profile synchronization remain monitored across approved directory events and active webhook subscriptions.',
      ),
      createCoverageItem(
        'payroll_partner_delivery',
        'Payroll partner delivery',
        'integration',
        'integration.manage',
        subscriptions.filter(
          (subscription) =>
            subscription.connection?.system_key === 'payroll_partner' &&
            ['attendance.record.updated', 'leave.request.approved', 'payroll.payslip.generated'].includes(subscription.event_key) &&
            subscription.status === 'active',
        ).length,
        syncJobs.filter((job) => job.system_key === 'payroll_partner' && ['failed', 'queued'].includes(job.status)).length,
        ['integration_failed_jobs', 'integration_stale_queue'],
        'Payroll-critical downstream updates remain under monitoring so attendance, leave, and payslip events stay reviewable before pay-cycle cutover.',
      ),
    ],
    release_critical: [
      createCoverageItem(
        'release_quality_gates',
        'Release quality gates',
        'release_critical',
        'release.manage',
        releaseSummary.total_gate_count,
        releaseSummary.blocking_gate_count,
        ['release_blocking_gates'],
        'Blocking CI, dependency-security, and contract gates are part of the same operational telemetry contract as live runtime signals.',
      ),
      createCoverageItem(
        'protected_promotion_lanes',
        'Protected promotion lanes',
        'release_critical',
        'release.manage',
        releaseSummary.protected_environment_count,
        releaseSummary.blocked_environment_count,
        ['release_blocking_gates'],
        'Protected pull-request, staging, and production promotion lanes remain monitored so launch blockers are visible before go-live review.',
      ),
    ],
  }
}

function createCoverageItem(
  key: string,
  name: string,
  area: string,
  ownerRole: string,
  monitoredEntityCount: number,
  issueCount: number,
  signalKeys: string[],
  summary: string,
): OperationsObservabilityCoverageItemRecord {
  return {
    key,
    name,
    area,
    owner_role: ownerRole,
    coverage_state: issueCount > 0 ? 'attention' : 'monitored',
    monitored_entity_count: monitoredEntityCount,
    issue_count: issueCount,
    signal_keys: signalKeys,
    summary,
  }
}

function buildLifecycleTaskDetails(employees: EmployeeRecord[]) {
  const aarav = employees.find((employee) => employee.id === 1001) ?? employees[0]
  const rohit = employees.find((employee) => employee.id === 1003) ?? employees[0]
  const kabir = employees.find((employee) => employee.id === 1005) ?? employees[0]
  const sana = employees.find((employee) => employee.id === 1006) ?? employees[0]

  return {
    [aarav.id]: {
      onboarding: buildLifecycleCollection(
        'onboarding',
        [
          createLifecycleTask(9501, aarav.id, 'Collect signed NDA', 'hr', 'employee', 'completed', pastDay(60), {
            completed_at: daysAgo(58),
            notes: 'Completed on the first working day.',
          }),
          createLifecycleTask(9502, aarav.id, 'Enroll laptop into device management', 'it', 'it_team', 'pending', futureDay(1), {
            task_type: 'setup_equipment',
            notes: 'Waiting for the new endpoint policy package.',
          }),
        ],
      ),
    },
    [rohit.id]: {
      onboarding: buildLifecycleCollection(
        'onboarding',
        [
          createLifecycleTask(9503, rohit.id, 'Verify probation goals with manager', 'manager', 'manager', 'in_progress', futureDay(2), {
            task_type: 'meet_manager',
            notes: 'Manager alignment session booked for tomorrow.',
          }),
          createLifecycleTask(9504, rohit.id, 'Issue workstation and access pack', 'it', 'it_team', 'in_progress', futureDay(1), {
            task_type: 'setup_equipment',
            assigned_to_user_name: 'Ishaan Nair',
            notes: 'Laptop is issued, but security accessories are still pending.',
          }),
          createLifecycleTask(9505, rohit.id, 'Finish security orientation', 'compliance', 'employee', 'pending', futureDay(3), {
            task_type: 'complete_training',
            notes: 'Required before production access is expanded.',
          }),
        ],
      ),
    },
    [kabir.id]: {
      offboarding: buildLifecycleCollection(
        'offboarding',
        [
          createLifecycleTask(9506, kabir.id, 'Approve final asset clearance', 'hr', 'manager', 'awaiting_approval', pastDay(1), {
            lifecycle_type: 'offboarding',
            task_type: 'complete_forms',
            requires_approval: true,
            approval_workflow_key: 'employee-offboarding-clearance',
            workflow_instance_id: 8801,
            assigned_to_user_name: 'Manager Reviewer',
            notes: 'Manager decision is pending before People Ops can close the exit packet.',
          }),
          createLifecycleTask(9507, kabir.id, 'Disable identity provider access', 'security', 'hr', 'pending', futureDay(0), {
            lifecycle_type: 'offboarding',
            notes: 'Security should close this after the final working day cutover.',
          }),
          createLifecycleTask(9508, kabir.id, 'Collect laptop and charger', 'it', 'it_team', 'in_progress', pastDay(3), {
            lifecycle_type: 'offboarding',
            task_type: 'setup_equipment',
            assigned_to_user_name: 'Ishaan Nair',
            notes: 'Asset recovery is overdue because the charger is still missing.',
          }),
        ],
      ),
    },
    [sana.id]: {
      offboarding: buildLifecycleCollection(
        'offboarding',
        [
          createLifecycleTask(9509, sana.id, 'Collect access badge', 'security', 'hr', 'completed', pastDay(14), {
            lifecycle_type: 'offboarding',
            completed_at: daysAgo(12),
            notes: 'Completed during the final office visit.',
          }),
          createLifecycleTask(9510, sana.id, 'Archive finance credentials', 'hr', 'hr', 'completed', pastDay(14), {
            lifecycle_type: 'offboarding',
            completed_at: daysAgo(11),
            notes: 'Audit archive synced to the compliance folder.',
          }),
        ],
      ),
    },
  } satisfies OperationsWorkspaceData['lifecycleTaskDetails']
}

function buildLifecycleStatuses(
  employees: EmployeeRecord[],
  lifecycleTaskDetails: OperationsWorkspaceData['lifecycleTaskDetails'],
  lifecycleType: OperationsLifecycleType,
) {
  return employees
    .map((employee) => {
      const collection = lifecycleTaskDetails?.[employee.id]?.[lifecycleType]
      if (!collection || collection.summary.incomplete_count === 0) {
        return null
      }

      return {
        employee: {
          id: employee.id,
          employee_code: employee.employee_code,
          full_name: employee.full_name,
          email: employee.email,
          date_of_joining: employee.date_of_joining,
          department: employee.department.name,
          designation: employee.designation.name,
        },
        lifecycle_type: lifecycleType,
        summary: {
          total_count: collection.summary.total_count,
          closed_count: collection.summary.completed_count + collection.summary.skipped_count,
          incomplete_count: collection.summary.incomplete_count,
          progress_percentage: collection.summary.progress_percentage,
          is_complete: collection.summary.is_complete,
        },
      }
    })
    .filter((entry): entry is NonNullable<typeof entry> => entry !== null)
}

function buildLifecycleCollection(
  lifecycleType: OperationsLifecycleType,
  items: OperationsLifecycleTaskRecord[],
): OperationsLifecycleTaskCollection {
  const completedCount = items.filter((item) => item.status === 'completed').length
  const skippedCount = items.filter((item) => item.status === 'skipped').length
  const pendingCount = items.filter((item) => item.status === 'pending').length
  const inProgressCount = items.filter((item) => item.status === 'in_progress').length
  const awaitingApprovalCount = items.filter((item) => item.status === 'awaiting_approval').length
  const changesRequestedCount = items.filter((item) => item.status === 'changes_requested').length
  const rejectedCount = items.filter((item) => item.status === 'rejected').length
  const totalCount = items.length
  const incompleteCount =
    pendingCount + inProgressCount + awaitingApprovalCount + changesRequestedCount + rejectedCount

  return {
    lifecycle_type: lifecycleType,
    items,
    summary: {
      total_count: totalCount,
      completed_count: completedCount,
      skipped_count: skippedCount,
      pending_count: pendingCount,
      in_progress_count: inProgressCount,
      awaiting_approval_count: awaitingApprovalCount,
      changes_requested_count: changesRequestedCount,
      rejected_count: rejectedCount,
      incomplete_count: incompleteCount,
      progress_percentage:
        totalCount === 0 ? 0 : Math.round(((completedCount + skippedCount) / totalCount) * 100),
      is_complete: totalCount > 0 && incompleteCount === 0,
    },
  }
}

function createLifecycleTask(
  id: number,
  employeeId: number,
  title: string,
  category: string,
  assigneeType: string,
  status: string,
  dueDate: string | null,
  overrides: Partial<OperationsLifecycleTaskRecord> = {},
): OperationsLifecycleTaskRecord {
  const lifecycleType = (overrides.lifecycle_type ?? 'onboarding') as OperationsLifecycleType

  return {
    id,
    employee_id: employeeId,
    lifecycle_type: lifecycleType,
    template_id: overrides.template_id ?? null,
    title,
    category,
    task_type: overrides.task_type ?? null,
    assignee_type: assigneeType,
    assigned_to_user_id: overrides.assigned_to_user_id ?? null,
    assigned_to_user_name: overrides.assigned_to_user_name ?? null,
    requires_approval: overrides.requires_approval ?? false,
    approval_workflow_key: overrides.approval_workflow_key ?? null,
    workflow_instance_id: overrides.workflow_instance_id ?? null,
    status,
    sort_order: overrides.sort_order ?? id,
    due_date: dueDate,
    due_state: overrides.due_state ?? deriveTaskDueState(dueDate, status),
    completed_at: overrides.completed_at ?? null,
    completed_by_user_id: overrides.completed_by_user_id ?? null,
    latest_action_by_user_id: overrides.latest_action_by_user_id ?? null,
    approved_at: overrides.approved_at ?? null,
    notes: overrides.notes ?? null,
    created_at: overrides.created_at ?? daysAgo(45),
    updated_at: overrides.updated_at ?? daysAgo(2),
  }
}

function deriveTaskDueState(dueDate: string | null, status: string) {
  if (!dueDate) {
    return 'no_due_date'
  }

  if (status === 'completed' || status === 'skipped') {
    return 'closed'
  }

  const today = new Date().toISOString().slice(0, 10)

  if (dueDate < today) {
    return 'overdue'
  }

  if (dueDate === today) {
    return 'due_today'
  }

  return 'upcoming'
}

function toDocumentCategorySummary(category: OperationsDocumentCategoryRecord) {
  return {
    id: category.id,
    code: category.code,
    name: category.name,
    default_visibility_scope: category.default_visibility_scope,
    retention_days: category.retention_days,
    allowed_role_names: category.allowed_role_names,
    status: category.status,
  }
}

function toAssetCategorySummary(category: OperationsAssetCategoryRecord) {
  return {
    id: category.id,
    code: category.code,
    name: category.name,
    status: category.status,
  }
}

function toEmployeeSummary(employee: EmployeeRecord) {
  return {
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
  }
}

function isoFromDate(baseDate: Date) {
  return new Date(baseDate.getTime()).toISOString()
}

function daysAgo(days: number) {
  const date = new Date()
  date.setDate(date.getDate() - days)
  return isoFromDate(date)
}

function hoursAgo(hours: number) {
  const date = new Date()
  date.setHours(date.getHours() - hours)
  return isoFromDate(date)
}

function pastDay(days: number) {
  return daysAgo(days).slice(0, 10)
}

function futureDay(days: number) {
  const date = new Date()
  date.setDate(date.getDate() + days)
  return isoFromDate(date).slice(0, 10)
}
