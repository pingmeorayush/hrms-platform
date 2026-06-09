import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import type { EmployeeRecord } from '../../employees/types'
import type {
  OperationsAssetCategoryRecord,
  OperationsAssetRecord,
  OperationsDocumentCategoryRecord,
  OperationsDocumentRecord,
  OperationsLifecycleTaskCollection,
  OperationsLifecycleTaskRecord,
  OperationsLifecycleType,
  OperationsWorkspaceData,
} from '../types'

export function buildDemoOperationsWorkspace(snapshot: AccessSnapshot | null): OperationsWorkspaceData {
  const employees = buildDemoEmployees(snapshot)
  const documentCategories = buildDocumentCategories()
  const documents = buildRepositoryDocuments(documentCategories, employees)
  const assetCategories = buildAssetCategories()
  const assets = buildAssets(assetCategories, employees)
  const lifecycleTaskDetails = buildLifecycleTaskDetails(employees)

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

function pastDay(days: number) {
  return daysAgo(days).slice(0, 10)
}

function futureDay(days: number) {
  const date = new Date()
  date.setDate(date.getDate() + days)
  return isoFromDate(date).slice(0, 10)
}
