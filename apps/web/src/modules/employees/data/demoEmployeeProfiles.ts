import type { AccessSnapshot } from '../../access/types'
import {
  type AuditLogEntry,
  type EmployeeAddressRecord,
  type EmployeeBankAccountCollection,
  type EmployeeContactRecord,
  type EmployeeDocumentRecord,
  type EmployeeEmergencyContactRecord,
  type EmployeeOnboardingData,
  type EmployeeOnboardingTaskRecord,
  type EmployeeProfileWorkspaceData,
  type EmployeeRecord,
  type PaginatedAuditLogEntries,
} from '../types'
import { buildDemoEmployees } from './demoEmployees'

export function buildDemoEmployeeWorkspace(
  snapshot: AccessSnapshot | null,
  employeeId: number | null,
): EmployeeProfileWorkspaceData | null {
  if (employeeId === null) {
    return null
  }

  const employee = buildDemoEmployees(snapshot).find((record) => record.id === employeeId)

  if (!employee) {
    return null
  }

  const onboardingItems = buildOnboardingItems(employee)

  return {
    employee,
    contacts: buildContacts(employee),
    addresses: buildAddresses(employee),
    emergencyContacts: buildEmergencyContacts(employee),
    onboarding: {
      items: onboardingItems,
      summary: summarizeOnboarding(onboardingItems),
    },
    documents: buildDocuments(employee),
    bankAccounts: buildBankAccounts(employee),
    auditHistory: buildAuditHistory(employee),
  }
}

export function summarizeOnboarding(
  items: EmployeeOnboardingTaskRecord[],
): EmployeeOnboardingData['summary'] {
  const completedCount = items.filter((item) => item.status === 'completed').length
  const skippedCount = items.filter((item) => item.status === 'skipped').length
  const pendingCount = items.filter((item) => item.status === 'pending').length
  const inProgressCount = items.filter((item) => item.status === 'in_progress').length
  const incompleteCount = pendingCount + inProgressCount
  const totalCount = items.length

  return {
    total_count: totalCount,
    completed_count: completedCount,
    skipped_count: skippedCount,
    pending_count: pendingCount,
    in_progress_count: inProgressCount,
    incomplete_count: incompleteCount,
    progress_percentage: totalCount === 0 ? 0 : Math.round(((completedCount + skippedCount) / totalCount) * 100),
    is_complete: totalCount > 0 && incompleteCount === 0,
  }
}

function buildContacts(employee: EmployeeRecord): EmployeeContactRecord[] {
  return [
    {
      id: employee.id * 10 + 1,
      employee_id: employee.id,
      type: 'email',
      label: 'Work email',
      value: employee.email,
      is_primary: true,
      status: 'active',
      notes: 'Primary work contact for HR operations.',
      created_at: timestamp(120),
      updated_at: timestamp(8),
    },
    {
      id: employee.id * 10 + 2,
      employee_id: employee.id,
      type: 'mobile',
      label: 'Mobile',
      value: employee.phone ?? '+91 90000 00000',
      is_primary: true,
      status: 'active',
      notes: 'Used for onboarding and urgent attendance communication.',
      created_at: timestamp(110),
      updated_at: timestamp(12),
    },
    {
      id: employee.id * 10 + 3,
      employee_id: employee.id,
      type: 'whatsapp',
      label: 'WhatsApp',
      value: employee.phone ?? '+91 90000 00000',
      is_primary: false,
      status: 'active',
      notes: 'Optional messaging channel.',
      created_at: timestamp(90),
      updated_at: timestamp(18),
    },
  ]
}

function buildAddresses(employee: EmployeeRecord): EmployeeAddressRecord[] {
  return [
    {
      id: employee.id * 10 + 1,
      employee_id: employee.id,
      type: 'current',
      address_line_1: '221 Phoenix Residency',
      address_line_2: 'Sector 18',
      city: 'Bengaluru',
      state: 'Karnataka',
      country: 'India',
      postal_code: '560001',
      notes: 'Current residence used for courier shipments.',
      created_at: timestamp(150),
      updated_at: timestamp(9),
    },
    {
      id: employee.id * 10 + 2,
      employee_id: employee.id,
      type: 'permanent',
      address_line_1: '17 Heritage Avenue',
      address_line_2: null,
      city: 'Pune',
      state: 'Maharashtra',
      country: 'India',
      postal_code: '411001',
      notes: 'Permanent address for statutory records.',
      created_at: timestamp(150),
      updated_at: timestamp(14),
    },
  ]
}

function buildEmergencyContacts(employee: EmployeeRecord): EmployeeEmergencyContactRecord[] {
  return [
    {
      id: employee.id * 10 + 1,
      employee_id: employee.id,
      name: `${employee.first_name} Family Contact`,
      relationship: 'Sibling',
      phone_number: '+91 98888 10001',
      email: `family.${employee.id}@phoenixhrms.test`,
      address: 'Available on request for emergency operations.',
      priority: 1,
      notes: 'Primary escalation path for employee welfare incidents.',
      created_at: timestamp(130),
      updated_at: timestamp(16),
    },
  ]
}

function buildOnboardingItems(employee: EmployeeRecord): EmployeeOnboardingTaskRecord[] {
  return [
    {
      id: employee.id * 100 + 1,
      employee_id: employee.id,
      title: 'Complete personal record verification',
      category: 'hr',
      task_type: 'complete_forms',
      assignee_type: 'employee',
      status: 'completed',
      sort_order: 10,
      due_date: employee.date_of_joining,
      completed_at: timestamp(300),
      notes: 'Employee submitted all required starter forms.',
      created_at: timestamp(320),
      updated_at: timestamp(300),
    },
    {
      id: employee.id * 100 + 2,
      employee_id: employee.id,
      title: 'Manager welcome session',
      category: 'manager',
      task_type: 'meet_manager',
      assignee_type: 'manager',
      status: employee.employment_status === 'terminated' ? 'skipped' : 'completed',
      sort_order: 20,
      due_date: employee.date_of_joining,
      completed_at: employee.employment_status === 'terminated' ? null : timestamp(290),
      notes: 'Initial expectation-setting conversation.',
      created_at: timestamp(320),
      updated_at: timestamp(290),
    },
    {
      id: employee.id * 100 + 3,
      employee_id: employee.id,
      title: 'Issue workstation and access pack',
      category: 'it',
      task_type: 'setup_equipment',
      assignee_type: 'it_team',
      status: employee.employment_status === 'probation' ? 'in_progress' : 'completed',
      sort_order: 30,
      due_date: employee.date_of_joining,
      completed_at: employee.employment_status === 'probation' ? null : timestamp(285),
      notes: 'Includes laptop, identity provider access, and VPN.',
      created_at: timestamp(320),
      updated_at: timestamp(285),
    },
    {
      id: employee.id * 100 + 4,
      employee_id: employee.id,
      title: 'Compliance learning acknowledgment',
      category: 'compliance',
      task_type: 'read_policy',
      assignee_type: 'employee',
      status: employee.employment_status === 'notice_period' ? 'pending' : 'completed',
      sort_order: 40,
      due_date: employee.date_of_joining,
      completed_at: employee.employment_status === 'notice_period' ? null : timestamp(270),
      notes: 'Must be acknowledged before first payroll cycle.',
      created_at: timestamp(320),
      updated_at: timestamp(270),
    },
  ]
}

function buildDocuments(employee: EmployeeRecord): EmployeeDocumentRecord[] {
  return [
    {
      id: employee.id * 100 + 1,
      employee_id: employee.id,
      document_type: 'Government ID',
      original_file_name: `${employee.employee_code.toLowerCase()}-id-proof.pdf`,
      mime_type: 'application/pdf',
      file_size_bytes: 428_900,
      expiry_date: null,
      notes: 'Verified during onboarding.',
      download_url: '#demo-document-id',
      created_at: timestamp(250),
      updated_at: timestamp(250),
    },
    {
      id: employee.id * 100 + 2,
      employee_id: employee.id,
      document_type: 'Signed offer letter',
      original_file_name: `${employee.employee_code.toLowerCase()}-offer-letter.pdf`,
      mime_type: 'application/pdf',
      file_size_bytes: 512_640,
      expiry_date: null,
      notes: 'Signed copy retained in the employee file.',
      download_url: '#demo-document-offer',
      created_at: timestamp(320),
      updated_at: timestamp(315),
    },
  ]
}

function buildBankAccounts(employee: EmployeeRecord): EmployeeBankAccountCollection {
  return {
    items: [
      {
        id: employee.id * 100 + 1,
        employee_id: employee.id,
        account_holder_name: employee.full_name,
        bank_name: 'State Bank of India',
        branch_name: 'MG Road',
        account_number: '123456789012',
        ifsc_code: 'SBIN0000123',
        routing_number: null,
        iban: null,
        swift_code: null,
        status: 'active',
        is_primary: true,
        verified_at: timestamp(210),
        notes: 'Primary salary account.',
        sensitive_access: 'full',
        created_at: timestamp(230),
        updated_at: timestamp(210),
      },
    ],
    meta: {
      total: 1,
    },
  }
}

function buildAuditHistory(employee: EmployeeRecord): PaginatedAuditLogEntries {
  const items: AuditLogEntry[] = [
    {
      id: employee.id * 1000 + 1,
      event_type: 'employee.record.updated',
      entity_type: 'employee',
      entity_id: String(employee.id),
      ip_address: '127.0.0.1',
      created_at: timestamp(4),
      metadata: {
        source: 'demo-ui',
        changes: ['phone', 'marital_status'],
      },
      user: {
        id: 2,
        name: 'Tenant Administrator',
        email: 'tenant.admin@phoenixhrms.test',
      },
    },
    {
      id: employee.id * 1000 + 2,
      event_type: 'employee.document.uploaded',
      entity_type: 'employee_document',
      entity_id: String(employee.id * 100 + 2),
      ip_address: '127.0.0.1',
      created_at: timestamp(12),
      metadata: {
        document_type: 'Signed offer letter',
      },
      user: {
        id: 2,
        name: 'Tenant Administrator',
        email: 'tenant.admin@phoenixhrms.test',
      },
    },
    {
      id: employee.id * 1000 + 3,
      event_type: 'employee.onboarding_task.updated',
      entity_type: 'employee_onboarding_task',
      entity_id: String(employee.id * 100 + 3),
      ip_address: '127.0.0.1',
      created_at: timestamp(19),
      metadata: {
        status: 'completed',
      },
      user: {
        id: 2,
        name: 'Tenant Administrator',
        email: 'tenant.admin@phoenixhrms.test',
      },
    },
    {
      id: employee.id * 1000 + 4,
      event_type: 'employee.record.created',
      entity_type: 'employee',
      entity_id: String(employee.id),
      ip_address: '127.0.0.1',
      created_at: timestamp(320),
      metadata: {
        employee_code: employee.employee_code,
      },
      user: {
        id: 2,
        name: 'Tenant Administrator',
        email: 'tenant.admin@phoenixhrms.test',
      },
    },
  ]

  return {
    items,
    meta: {
      page: 1,
      per_page: 10,
      total: items.length,
      last_page: 1,
    },
  }
}

function timestamp(hoursAgo: number) {
  return new Date(Date.now() - hoursAgo * 60 * 60 * 1000).toISOString()
}
