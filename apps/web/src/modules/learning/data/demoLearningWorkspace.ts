import type { AccessSnapshot } from '../../access/types'
import type { EmployeeRecord, EmployeeReference } from '../../employees/types'
import type { OrganizationMasterRecord } from '../../organization/types'
import type {
  LearningAssignmentRecord,
  LearningAssignmentTargetRecord,
  LearningAudienceRules,
  LearningCompletionRules,
  LearningItemRecord,
  LearningWorkspaceData,
} from '../types'

const DEMO_TODAY = '2026-06-11'

function createDepartment(id: number, code: string, name: string): OrganizationMasterRecord {
  return {
    id,
    code,
    name,
    description: null,
    status: 'active',
    created_at: '2026-05-01T09:00:00+05:30',
    updated_at: '2026-05-01T09:00:00+05:30',
  }
}

function createDesignation(id: number, code: string, name: string): OrganizationMasterRecord {
  return {
    id,
    code,
    name,
    description: null,
    status: 'active',
    created_at: '2026-05-01T09:00:00+05:30',
    updated_at: '2026-05-01T09:00:00+05:30',
  }
}

function employeeReference(record: EmployeeRecord): EmployeeReference {
  return {
    id: record.id,
    employee_code: record.employee_code,
    full_name: record.full_name,
    email: record.email,
  }
}

const engineeringDepartment = createDepartment(11, 'ENG', 'Engineering')
const peopleDepartment = createDepartment(12, 'PEOPLE', 'People Operations')
const productDepartment = createDepartment(13, 'PROD', 'Product')

const engineeringManagerDesignation = createDesignation(21, 'ENG-MGR', 'Engineering Manager')
const hrBusinessPartnerDesignation = createDesignation(22, 'HRBP', 'HR Business Partner')
const seniorEngineerDesignation = createDesignation(23, 'SWE-3', 'Senior Software Engineer')
const productManagerDesignation = createDesignation(24, 'PM', 'Product Manager')

const employees: EmployeeRecord[] = [
  {
    id: 2101,
    employee_code: 'PAY-2101',
    first_name: 'Aman',
    middle_name: null,
    last_name: 'Verma',
    full_name: 'Aman Verma',
    email: 'aman.verma@phoenixhrms.test',
    phone: '+91 900000001',
    date_of_birth: '1989-03-14',
    gender: 'male',
    marital_status: 'married',
    date_of_joining: '2021-02-08',
    employment_type: 'full_time',
    employment_status: 'active',
    termination_reason: null,
    terminated_at: null,
    department: engineeringDepartment,
    designation: engineeringManagerDesignation,
    manager: null,
    location: null,
    cost_center: null,
    user_id: 3,
    created_at: '2026-01-02T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 2102,
    employee_code: 'PAY-2102',
    first_name: 'Nisha',
    middle_name: null,
    last_name: 'Rao',
    full_name: 'Nisha Rao',
    email: 'nisha.rao@phoenixhrms.test',
    phone: '+91 900000002',
    date_of_birth: '1990-11-20',
    gender: 'female',
    marital_status: 'single',
    date_of_joining: '2020-07-13',
    employment_type: 'full_time',
    employment_status: 'active',
    termination_reason: null,
    terminated_at: null,
    department: peopleDepartment,
    designation: hrBusinessPartnerDesignation,
    manager: null,
    location: null,
    cost_center: null,
    user_id: 2,
    created_at: '2026-01-02T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 2103,
    employee_code: 'PAY-2103',
    first_name: 'Kabir',
    middle_name: null,
    last_name: 'Malik',
    full_name: 'Kabir Malik',
    email: 'kabir.malik@phoenixhrms.test',
    phone: '+91 900000003',
    date_of_birth: '1996-08-02',
    gender: 'male',
    marital_status: 'single',
    date_of_joining: '2024-01-15',
    employment_type: 'full_time',
    employment_status: 'active',
    termination_reason: null,
    terminated_at: null,
    department: engineeringDepartment,
    designation: seniorEngineerDesignation,
    manager: {
      id: 2101,
      employee_code: 'PAY-2101',
      full_name: 'Aman Verma',
      email: 'aman.verma@phoenixhrms.test',
    },
    location: null,
    cost_center: null,
    user_id: 4,
    created_at: '2026-01-02T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 2106,
    employee_code: 'PAY-2106',
    first_name: 'Sonia',
    middle_name: null,
    last_name: 'Menon',
    full_name: 'Sonia Menon',
    email: 'sonia.menon@phoenixhrms.test',
    phone: '+91 900000006',
    date_of_birth: '1994-04-05',
    gender: 'female',
    marital_status: 'single',
    date_of_joining: '2023-05-09',
    employment_type: 'full_time',
    employment_status: 'active',
    termination_reason: null,
    terminated_at: null,
    department: productDepartment,
    designation: productManagerDesignation,
    manager: {
      id: 2102,
      employee_code: 'PAY-2102',
      full_name: 'Nisha Rao',
      email: 'nisha.rao@phoenixhrms.test',
    },
    location: null,
    cost_center: null,
    user_id: 6,
    created_at: '2026-01-02T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
]

const items: LearningItemRecord[] = [
  {
    id: 901,
    code: 'SEC-2026',
    title: 'Security Awareness 2026',
    description: 'Annual security and phishing refresher for all active employees.',
    category: 'Compliance',
    delivery_mode: 'self_paced',
    duration_minutes: 45,
    requires_completion_evidence: false,
    renewal_frequency_months: 12,
    default_due_days: 30,
    metadata: {
      provider: 'Phoenix Academy',
    },
    status: 'active',
    created_at: '2026-05-10T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 902,
    code: 'CODE-SEC',
    title: 'Secure Coding Essentials',
    description: 'Required engineering refresh that captures evidence-backed completion.',
    category: 'Technical',
    delivery_mode: 'blended',
    duration_minutes: 90,
    requires_completion_evidence: true,
    renewal_frequency_months: 6,
    default_due_days: 14,
    metadata: {
      provider: 'Phoenix Academy',
      audience: 'Engineering',
    },
    status: 'active',
    created_at: '2026-05-15T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 903,
    code: 'DATA-ACK',
    title: 'Data Privacy Handbook',
    description: 'Document acknowledgement with signed receipt evidence stored in the DMS.',
    category: 'Compliance',
    delivery_mode: 'document_acknowledgement',
    duration_minutes: 25,
    requires_completion_evidence: true,
    renewal_frequency_months: 12,
    default_due_days: 7,
    metadata: {
      document_version: '2026.1',
    },
    status: 'active',
    created_at: '2026-05-25T09:00:00+05:30',
    updated_at: '2026-06-01T10:00:00+05:30',
  },
  {
    id: 904,
    code: 'LEAD-101',
    title: 'Coaching Feedback Foundations',
    description: 'Virtual session for managers before the mid-year review cycle opens.',
    category: 'Leadership',
    delivery_mode: 'virtual_session',
    duration_minutes: 60,
    requires_completion_evidence: false,
    renewal_frequency_months: null,
    default_due_days: 21,
    metadata: {
      facilitator: 'People Development',
    },
    status: 'active',
    created_at: '2026-05-28T09:00:00+05:30',
    updated_at: '2026-06-02T10:00:00+05:30',
  },
]

type LearningAssignmentSeed = Omit<LearningAssignmentRecord, 'target_count' | 'completion_count' | 'target_summary' | 'targets'>

const assignments: LearningAssignmentSeed[] = [
  {
    id: 1001,
    assignment_code: 'L-2026-001',
    item: items[0],
    audience_type: 'all_active',
    audience_rules: {},
    assigned_on: '2026-06-01',
    due_on: '2026-06-30',
    completion_rules: {
      requires_completion_evidence: false,
      renewal_frequency_months: 12,
      default_due_days: 30,
    },
    notes: 'Complete before the Q3 access-review freeze.',
    status: 'active',
    created_at: '2026-06-01T09:00:00+05:30',
    updated_at: '2026-06-01T09:00:00+05:30',
  },
  {
    id: 1002,
    assignment_code: 'L-2026-002',
    item: items[1],
    audience_type: 'department',
    audience_rules: {
      department_ids: [engineeringDepartment.id],
    },
    assigned_on: '2026-06-04',
    due_on: '2026-06-18',
    completion_rules: {
      requires_completion_evidence: true,
      renewal_frequency_months: 6,
      default_due_days: 14,
    },
    notes: 'Engineering baseline for secure release reviews.',
    status: 'active',
    created_at: '2026-06-04T09:00:00+05:30',
    updated_at: '2026-06-04T09:00:00+05:30',
  },
  {
    id: 1003,
    assignment_code: 'L-2026-003',
    item: items[2],
    audience_type: 'employee',
    audience_rules: {
      employee_ids: [2103],
    },
    assigned_on: '2025-06-03',
    due_on: '2025-06-10',
    completion_rules: {
      requires_completion_evidence: true,
      renewal_frequency_months: 12,
      default_due_days: 7,
    },
    notes: 'Re-acknowledge before handling production customer data.',
    status: 'active',
    created_at: '2025-06-03T09:00:00+05:30',
    updated_at: '2026-06-01T09:00:00+05:30',
  },
  {
    id: 1004,
    assignment_code: 'L-2026-004',
    item: items[3],
    audience_type: 'designation',
    audience_rules: {
      designation_ids: [engineeringManagerDesignation.id],
    },
    assigned_on: '2026-06-06',
    due_on: '2026-06-22',
    completion_rules: {
      requires_completion_evidence: false,
      renewal_frequency_months: null,
      default_due_days: 21,
    },
    notes: 'Required before goal calibration workshops begin.',
    status: 'active',
    created_at: '2026-06-06T09:00:00+05:30',
    updated_at: '2026-06-06T09:00:00+05:30',
  },
]

const targets: LearningAssignmentTargetRecord[] = [
  {
    id: 1101,
    assignment: {
      id: 1001,
      assignment_code: 'L-2026-001',
      status: 'active',
      audience_type: 'all_active',
    },
    item: items[0],
    employee: employeeReference(employees[0]),
    status: 'completed',
    completion_progress_percent: 100,
    due_on: '2026-06-30',
    due_state: 'completed',
    renewal_due_on: '2027-06-02',
    renewal_posture: 'current',
    requires_completion_evidence: false,
    evidence_present: false,
    completion_notes: 'Completed in the June security window.',
    completion_evidence: null,
    completed_at: '2026-06-02T10:30:00+05:30',
    completed_by: {
      id: 3,
      name: 'Manager Reviewer',
    },
    assigned_on: '2026-06-01',
    created_at: '2026-06-01T09:00:00+05:30',
    updated_at: '2026-06-02T10:30:00+05:30',
  },
  {
    id: 1102,
    assignment: {
      id: 1001,
      assignment_code: 'L-2026-001',
      status: 'active',
      audience_type: 'all_active',
    },
    item: items[0],
    employee: employeeReference(employees[1]),
    status: 'completed',
    completion_progress_percent: 100,
    due_on: '2026-06-30',
    due_state: 'completed',
    renewal_due_on: '2027-06-03',
    renewal_posture: 'current',
    requires_completion_evidence: false,
    evidence_present: false,
    completion_notes: 'Completed as part of people-ops annual controls.',
    completion_evidence: null,
    completed_at: '2026-06-03T14:00:00+05:30',
    completed_by: {
      id: 2,
      name: 'Tenant Administrator',
    },
    assigned_on: '2026-06-01',
    created_at: '2026-06-01T09:00:00+05:30',
    updated_at: '2026-06-03T14:00:00+05:30',
  },
  {
    id: 1103,
    assignment: {
      id: 1001,
      assignment_code: 'L-2026-001',
      status: 'active',
      audience_type: 'all_active',
    },
    item: items[0],
    employee: employeeReference(employees[2]),
    status: 'assigned',
    completion_progress_percent: 0,
    due_on: '2026-06-30',
    due_state: 'upcoming',
    renewal_due_on: null,
    renewal_posture: 'pending_initial_completion',
    requires_completion_evidence: false,
    evidence_present: false,
    completion_notes: null,
    completion_evidence: null,
    completed_at: null,
    completed_by: null,
    assigned_on: '2026-06-01',
    created_at: '2026-06-01T09:00:00+05:30',
    updated_at: '2026-06-01T09:00:00+05:30',
  },
  {
    id: 1104,
    assignment: {
      id: 1001,
      assignment_code: 'L-2026-001',
      status: 'active',
      audience_type: 'all_active',
    },
    item: items[0],
    employee: employeeReference(employees[3]),
    status: 'assigned',
    completion_progress_percent: 0,
    due_on: '2026-06-30',
    due_state: 'upcoming',
    renewal_due_on: null,
    renewal_posture: 'pending_initial_completion',
    requires_completion_evidence: false,
    evidence_present: false,
    completion_notes: null,
    completion_evidence: null,
    completed_at: null,
    completed_by: null,
    assigned_on: '2026-06-01',
    created_at: '2026-06-01T09:00:00+05:30',
    updated_at: '2026-06-01T09:00:00+05:30',
  },
  {
    id: 1105,
    assignment: {
      id: 1002,
      assignment_code: 'L-2026-002',
      status: 'active',
      audience_type: 'department',
    },
    item: items[1],
    employee: employeeReference(employees[0]),
    status: 'assigned',
    completion_progress_percent: 0,
    due_on: '2026-06-18',
    due_state: 'upcoming',
    renewal_due_on: null,
    renewal_posture: 'pending_initial_completion',
    requires_completion_evidence: true,
    evidence_present: false,
    completion_notes: null,
    completion_evidence: null,
    completed_at: null,
    completed_by: null,
    assigned_on: '2026-06-04',
    created_at: '2026-06-04T09:00:00+05:30',
    updated_at: '2026-06-04T09:00:00+05:30',
  },
  {
    id: 1106,
    assignment: {
      id: 1002,
      assignment_code: 'L-2026-002',
      status: 'active',
      audience_type: 'department',
    },
    item: items[1],
    employee: employeeReference(employees[2]),
    status: 'assigned',
    completion_progress_percent: 0,
    due_on: '2026-06-18',
    due_state: 'upcoming',
    renewal_due_on: null,
    renewal_posture: 'pending_initial_completion',
    requires_completion_evidence: true,
    evidence_present: false,
    completion_notes: null,
    completion_evidence: null,
    completed_at: null,
    completed_by: null,
    assigned_on: '2026-06-04',
    created_at: '2026-06-04T09:00:00+05:30',
    updated_at: '2026-06-04T09:00:00+05:30',
  },
  {
    id: 1107,
    assignment: {
      id: 1003,
      assignment_code: 'L-2026-003',
      status: 'active',
      audience_type: 'employee',
    },
    item: items[2],
    employee: employeeReference(employees[2]),
    status: 'completed',
    completion_progress_percent: 100,
    due_on: '2025-06-10',
    due_state: 'completed',
    renewal_due_on: '2026-06-10',
    renewal_posture: 'overdue',
    requires_completion_evidence: true,
    evidence_present: true,
    completion_notes: 'Signed acknowledgement stored in the employee record.',
    completion_evidence: {
      type: 'document_receipt',
      reference: 'DATA-ACK-KM-2025',
      notes: 'Signature captured during secure onboarding.',
    },
    completed_at: '2025-06-10T12:00:00+05:30',
    completed_by: {
      id: 4,
      name: 'Employee Viewer',
    },
    assigned_on: '2025-06-03',
    created_at: '2025-06-03T09:00:00+05:30',
    updated_at: '2025-06-10T12:00:00+05:30',
  },
  {
    id: 1108,
    assignment: {
      id: 1004,
      assignment_code: 'L-2026-004',
      status: 'active',
      audience_type: 'designation',
    },
    item: items[3],
    employee: employeeReference(employees[0]),
    status: 'assigned',
    completion_progress_percent: 0,
    due_on: '2026-06-22',
    due_state: 'upcoming',
    renewal_due_on: null,
    renewal_posture: 'not_configured',
    requires_completion_evidence: false,
    evidence_present: false,
    completion_notes: null,
    completion_evidence: null,
    completed_at: null,
    completed_by: null,
    assigned_on: '2026-06-06',
    created_at: '2026-06-06T09:00:00+05:30',
    updated_at: '2026-06-06T09:00:00+05:30',
  },
]

function learningTargetSummary(visibleTargets: LearningAssignmentTargetRecord[], completionRules: LearningCompletionRules) {
  return {
    total_count: visibleTargets.length,
    completed_count: visibleTargets.filter((target) => target.status === 'completed').length,
    overdue_count: visibleTargets.filter((target) => target.due_state === 'overdue').length,
    renewal_overdue_count:
      completionRules.renewal_frequency_months === null
        ? 0
        : visibleTargets.filter((target) => target.renewal_posture === 'overdue').length,
  }
}

function learningMeta(snapshot: AccessSnapshot | null | undefined) {
  const permissions = snapshot?.user.permissions ?? []

  return {
    can_view_learning: permissions.some((permission) =>
      ['learning.view', 'learning.manage', 'learning.assign', 'learning.complete'].includes(permission),
    ),
    can_manage_catalog: permissions.includes('learning.manage'),
    can_assign_learning: permissions.includes('learning.assign') || permissions.includes('learning.manage'),
    can_complete_learning: permissions.includes('learning.complete') || permissions.includes('learning.manage'),
    linked_employee_id: snapshot?.user.employee?.id ?? null,
  }
}

function resolveVisibleEmployeeIds(snapshot: AccessSnapshot | null | undefined, canAdmin: boolean) {
  if (canAdmin) {
    return employees.map((employee) => employee.id)
  }

  const linkedEmployeeId = snapshot?.user.employee?.id ?? null
  if (linkedEmployeeId === null) {
    return []
  }

  const directReportIds = employees
    .filter((employee) => employee.manager?.id === linkedEmployeeId)
    .map((employee) => employee.id)

  return [linkedEmployeeId, ...directReportIds]
}

function assignmentTargetsFor(assignmentId: number, visibleTargets: LearningAssignmentTargetRecord[]) {
  return visibleTargets.filter((target) => target.assignment?.id === assignmentId)
}

function uniqueItemsFromTargets(input: LearningAssignmentTargetRecord[]) {
  const unique = new Map<number, LearningItemRecord>()

  input.forEach((target) => {
    if (target.item) {
      unique.set(target.item.id, target.item)
    }
  })

  return [...unique.values()]
}

function uniqueDepartmentsFor(employeeIds: number[]) {
  const visibleDepartments = employees
    .filter((employee) => employeeIds.includes(employee.id))
    .map((employee) => employee.department)
  const unique = new Map<number, OrganizationMasterRecord>()

  visibleDepartments.forEach((department) => {
    unique.set(department.id, department)
  })

  return [...unique.values()]
}

function uniqueDesignationsFor(employeeIds: number[]) {
  const visibleDesignations = employees
    .filter((employee) => employeeIds.includes(employee.id))
    .map((employee) => employee.designation)
  const unique = new Map<number, OrganizationMasterRecord>()

  visibleDesignations.forEach((designation) => {
    unique.set(designation.id, designation)
  })

  return [...unique.values()]
}

export function buildDemoLearningWorkspace(snapshot: AccessSnapshot | null | undefined): LearningWorkspaceData {
  const meta = learningMeta(snapshot)
  const canAdmin = meta.can_manage_catalog || meta.can_assign_learning
  const visibleEmployeeIds = resolveVisibleEmployeeIds(snapshot, canAdmin)
  const visibleTargets = targets.filter((target) => {
    const employeeId = target.employee?.id

    return employeeId !== null && employeeId !== undefined && visibleEmployeeIds.includes(employeeId)
  })
  const myAssignments = meta.linked_employee_id === null
    ? []
    : visibleTargets.filter((target) => target.employee?.id === meta.linked_employee_id)
  const visibleAssignmentIds = new Set(visibleTargets.map((target) => target.assignment?.id).filter(Boolean) as number[])
  const scopedAssignments = assignments
    .filter((assignment) => canAdmin || visibleAssignmentIds.has(assignment.id))
    .map((assignment) => {
      const assignmentTargets = assignmentTargetsFor(assignment.id, visibleTargets)
      const targetCount = assignmentTargets.length
      const completionCount = assignmentTargets.filter((target) => target.status === 'completed').length

      return {
        ...assignment,
        target_count: targetCount,
        completion_count: completionCount,
        target_summary: learningTargetSummary(assignmentTargets, assignment.completion_rules),
        targets: assignmentTargets,
      }
    })

  return {
    items: canAdmin ? items : uniqueItemsFromTargets([...visibleTargets, ...myAssignments]),
    assignments: scopedAssignments,
    targets: visibleTargets,
    myAssignments,
    employees: canAdmin ? employees : employees.filter((employee) => visibleEmployeeIds.includes(employee.id)),
    departments: canAdmin ? [engineeringDepartment, peopleDepartment, productDepartment] : uniqueDepartmentsFor(visibleEmployeeIds),
    designations: canAdmin
      ? [engineeringManagerDesignation, hrBusinessPartnerDesignation, seniorEngineerDesignation, productManagerDesignation]
      : uniqueDesignationsFor(visibleEmployeeIds),
    meta,
  }
}

export const demoLearningToday = DEMO_TODAY

export function resolveDemoLearningDueState(dueOn: string | null, completedAt: string | null) {
  if (completedAt) {
    return 'completed' as const
  }

  if (!dueOn) {
    return 'no_due_date' as const
  }

  if (dueOn < DEMO_TODAY) {
    return 'overdue' as const
  }

  if (dueOn === DEMO_TODAY) {
    return 'due_today' as const
  }

  return 'upcoming' as const
}

export function resolveDemoLearningRenewalPosture(
  renewalFrequencyMonths: number | null,
  completedAt: string | null,
  renewalDueOn: string | null,
) {
  if (renewalFrequencyMonths === null) {
    return 'not_configured' as const
  }

  if (!completedAt || !renewalDueOn) {
    return 'pending_initial_completion' as const
  }

  if (renewalDueOn < DEMO_TODAY) {
    return 'overdue' as const
  }

  if (renewalDueOn === DEMO_TODAY) {
    return 'due_today' as const
  }

  return 'current' as const
}

export function formatLearningRenewalDate(assignedOn: string, defaultDueDays: number | null) {
  if (defaultDueDays === null) {
    return null
  }

  const base = new Date(`${assignedOn}T00:00:00`)
  base.setDate(base.getDate() + defaultDueDays)

  return base.toISOString().slice(0, 10)
}

export function plusMonths(dateValue: string, months: number) {
  const result = new Date(`${dateValue}T00:00:00`)
  result.setMonth(result.getMonth() + months)

  return result.toISOString().slice(0, 10)
}

export function resolveAudienceRulesForTargeting(
  audienceType: LearningAssignmentRecord['audience_type'],
  audienceRules: LearningAudienceRules,
) {
  switch (audienceType) {
    case 'employee':
      return employees.filter((employee) => audienceRules.employee_ids?.includes(employee.id))
    case 'department':
      return employees.filter((employee) => audienceRules.department_ids?.includes(employee.department.id))
    case 'designation':
      return employees.filter((employee) => audienceRules.designation_ids?.includes(employee.designation.id))
    case 'all_active':
    default:
      return employees.filter((employee) => employee.employment_status === 'active')
  }
}
