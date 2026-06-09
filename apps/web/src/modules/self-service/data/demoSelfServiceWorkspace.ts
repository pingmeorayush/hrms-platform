import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployeeWorkspace } from '../../employees/data/demoEmployeeProfiles'
import type { SelfServiceAssetRecord, SelfServiceDocumentRecord, SelfServiceWorkspaceData } from '../types'

const selfServiceEmployeeIdByUserId: Record<number, number | null> = {
  1: null,
  2: null,
  3: 1001,
  4: 1005,
}

export function buildDemoSelfServiceWorkspace(snapshot: AccessSnapshot | null): SelfServiceWorkspaceData | null {
  if (!snapshot) {
    return null
  }

  const employeeId = resolveDemoSelfServiceEmployeeId(snapshot.user.id)

  if (!employeeId) {
    return null
  }

  const employeeWorkspace = buildDemoEmployeeWorkspace(snapshot, employeeId)

  if (!employeeWorkspace) {
    return null
  }

  const policyItems = buildPolicyItems(employeeId)
  const repositoryItems = buildRepositoryItems(employeeId)
  const employeeDocumentItems: SelfServiceDocumentRecord[] = employeeWorkspace.documents.map((document) => ({
    id: `employee-document-${document.id}`,
    source_type: 'employee_document',
    source_id: document.id,
    title: document.document_type,
    subtitle: 'Employee file',
    status: 'available',
    document_type: document.document_type,
    file_name: document.original_file_name,
    mime_type: document.mime_type,
    file_size_bytes: document.file_size_bytes,
    due_date: null,
    expiry_date: document.expiry_date,
    visibility_scope: 'employee',
    download_url: document.download_url,
    acknowledge_url: null,
    action_required: false,
    notes: document.notes,
    category: null,
    repository_scope: 'employee_master',
    created_at: document.created_at,
    updated_at: document.updated_at,
  }))

  const documentItems = [...policyItems, ...employeeDocumentItems, ...repositoryItems]
  const assetItems = buildAssetItems(employeeId)

  return {
    employee: employeeWorkspace.employee,
    profile: {
      contacts: employeeWorkspace.contacts,
      addresses: employeeWorkspace.addresses,
      emergency_contacts: employeeWorkspace.emergencyContacts,
      sensitive_panels: {
        bank_accounts: {
          visible: false,
          message: 'Sensitive banking details stay hidden in self-service unless the session has `employee.bank.view`.',
        },
      },
    },
    documents: {
      summary: summarizeDocuments(documentItems, employeeId === 1005 ? 1 : 0),
      items: documentItems,
    },
    assets: {
      summary: summarizeAssets(assetItems),
      items: assetItems,
    },
  }
}

export function resolveDemoSelfServiceEmployeeId(userId: number | null | undefined) {
  if (!userId) {
    return null
  }

  return selfServiceEmployeeIdByUserId[userId] ?? null
}

function buildPolicyItems(employeeId: number): SelfServiceDocumentRecord[] {
  if (employeeId === 1005) {
    return [
      {
        id: 'policy-5101',
        source_type: 'policy_acknowledgement',
        source_id: 5101,
        title: 'Code of Conduct',
        subtitle: 'Version 2026.1',
        status: 'assigned',
        document_type: 'policy',
        file_name: 'code-of-conduct.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 412_000,
        due_date: '2026-06-20',
        expiry_date: null,
        visibility_scope: 'internal',
        download_url: '#demo-policy-download-assigned',
        acknowledge_url: '/api/v1/policy-acknowledgements/5101/acknowledge',
        action_required: true,
        notes: 'Review and acknowledge before your next leave approval cycle.',
        category: null,
        repository_scope: 'policy',
        created_at: '2026-06-01T09:00:00+05:30',
        updated_at: '2026-06-01T09:00:00+05:30',
      },
      {
        id: 'policy-5094',
        source_type: 'policy_acknowledgement',
        source_id: 5094,
        title: 'Remote Work Standards',
        subtitle: 'Version 2025.4',
        status: 'acknowledged',
        document_type: 'policy',
        file_name: 'remote-work-standards.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 388_500,
        due_date: '2026-05-15',
        expiry_date: null,
        visibility_scope: 'internal',
        download_url: '#demo-policy-download-acknowledged',
        acknowledge_url: null,
        action_required: false,
        notes: 'Acknowledged during the Q2 operations refresh.',
        category: null,
        repository_scope: 'policy',
        created_at: '2026-05-01T09:00:00+05:30',
        updated_at: '2026-05-10T10:00:00+05:30',
      },
    ]
  }

  return [
    {
      id: 'policy-5001',
      source_type: 'policy_acknowledgement',
      source_id: 5001,
      title: 'Leadership Travel Policy',
      subtitle: 'Version 2026.1',
      status: 'acknowledged',
      document_type: 'policy',
      file_name: 'leadership-travel-policy.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 256_000,
      due_date: '2026-05-20',
      expiry_date: null,
      visibility_scope: 'internal',
      download_url: '#demo-leadership-policy-download',
      acknowledge_url: null,
      action_required: false,
      notes: 'Confirmed during the leadership compliance review.',
      category: null,
      repository_scope: 'policy',
      created_at: '2026-05-02T09:00:00+05:30',
      updated_at: '2026-05-18T11:30:00+05:30',
    },
  ]
}

function buildRepositoryItems(employeeId: number): SelfServiceDocumentRecord[] {
  if (employeeId === 1005) {
    return [
      {
        id: 'repository-6201',
        source_type: 'repository_document',
        source_id: 6201,
        title: 'Travel Desk Guide',
        subtitle: 'Employee Guides',
        status: 'available',
        document_type: 'EMP-GUIDE',
        file_name: 'travel-desk-guide.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 296_400,
        due_date: null,
        expiry_date: '2027-06-01',
        visibility_scope: 'restricted',
        download_url: '#demo-repository-download',
        acknowledge_url: null,
        action_required: false,
        notes: 'Use this guide for travel booking, reimbursement, and escalation contacts.',
        category: {
          id: 8201,
          code: 'EMP-GUIDE',
          name: 'Employee Guides',
        },
        repository_scope: 'employee',
        created_at: '2026-05-19T11:00:00+05:30',
        updated_at: '2026-05-19T11:00:00+05:30',
      },
    ]
  }

  return []
}

function buildAssetItems(employeeId: number): SelfServiceAssetRecord[] {
  if (employeeId === 1005) {
    return [
      {
        id: 7301,
        asset_tag: 'AST-LTP-3001',
        name: 'Dell Latitude 7440',
        asset_type: 'physical',
        status: 'issued',
        serial_number: 'SN-LTP-3001',
        manufacturer: 'Dell',
        model_name: 'Latitude 7440',
        purchase_date: '2026-04-15',
        notes: 'Primary device with charger, dock, and privacy filter.',
        category: {
          id: 9201,
          code: 'LAPTOP',
          name: 'Laptop',
          status: 'active',
        },
        assignment: {
          id: 8101,
          status: 'issued',
          assigned_at: '2026-05-20T09:00:00+05:30',
          issued_at: '2026-05-20T10:00:00+05:30',
          expected_return_date: '2027-05-20',
          returned_at: null,
          handover_condition: 'sealed',
          return_condition: null,
          assignment_notes: 'Issued during contract onboarding.',
          issue_notes: 'Imaged and handed over with MFA token.',
          return_notes: null,
          due_state: 'upcoming',
        },
        created_at: '2026-05-18T14:00:00+05:30',
        updated_at: '2026-05-20T10:00:00+05:30',
      },
    ]
  }

  return []
}

function summarizeDocuments(items: SelfServiceDocumentRecord[], hiddenSensitiveCount: number) {
  return {
    total_count: items.length,
    pending_acknowledgement_count: items.filter((item) => item.status === 'assigned').length,
    acknowledged_count: items.filter((item) => item.status === 'acknowledged').length,
    downloadable_count: items.filter((item) => Boolean(item.download_url)).length,
    hidden_sensitive_count: hiddenSensitiveCount,
  }
}

function summarizeAssets(items: SelfServiceAssetRecord[]) {
  return {
    active_count: items.length,
    assigned_count: items.filter((item) => item.assignment?.status === 'assigned').length,
    issued_count: items.filter((item) => item.assignment?.status === 'issued').length,
    overdue_count: items.filter((item) => item.assignment?.due_state === 'overdue').length,
  }
}
