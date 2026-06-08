import type { AccessSnapshot } from '../../access/types'
import { buildDemoOrganizationWorkspace } from '../../organization/data/demoOrganizationWorkspace'
import type { EmployeeRecord, EmployeeReference, EmployeeStatus } from '../types'

const employeeStatuses: EmployeeStatus[] = [
  'active',
  'active',
  'probation',
  'active',
  'notice_period',
  'terminated',
]

export function buildDemoEmployees(snapshot: AccessSnapshot | null): EmployeeRecord[] {
  const organization = buildDemoOrganizationWorkspace(snapshot)
  const engineering = organization.departments.find((record) => record.code === 'ENG') ?? organization.departments[0]
  const peopleOps = organization.departments.find((record) => record.code === 'PEO') ?? organization.departments[1]
  const finance = organization.departments.find((record) => record.code === 'FIN') ?? organization.departments[2]
  const engineerDesignation = organization.designations.find((record) => record.code === 'SWE2') ?? organization.designations[0]
  const hrDesignation = organization.designations.find((record) => record.code === 'HRBP') ?? organization.designations[1]
  const primaryLocation = organization.locations[0] ?? null
  const secondaryLocation = organization.locations[1] ?? null
  const platformCostCenter = organization.costCenters[0] ?? null
  const peopleCostCenter = organization.costCenters[1] ?? null

  const managerReference: EmployeeReference = {
    id: 1001,
    employee_code: 'EMP-1001',
    full_name: 'Aarav Raman',
    email: 'aarav.raman@phoenixhrms.test',
  }

  const hrLeadReference: EmployeeReference = {
    id: 1004,
    employee_code: 'EMP-1004',
    full_name: 'Meera Sethi',
    email: 'meera.sethi@phoenixhrms.test',
  }

  return [
    createEmployee({
      id: 1001,
      code: 'EMP-1001',
      firstName: 'Aarav',
      lastName: 'Raman',
      email: 'aarav.raman@phoenixhrms.test',
      phone: '+91 90000 10001',
      department: engineering,
      designation: engineerDesignation,
      manager: null,
      location: primaryLocation,
      costCenter: platformCostCenter,
      employmentType: 'full_time',
      status: employeeStatuses[0],
      joinDate: '2023-04-03',
    }),
    createEmployee({
      id: 1002,
      code: 'EMP-1002',
      firstName: 'Naina',
      lastName: 'Kapoor',
      email: 'naina.kapoor@phoenixhrms.test',
      phone: '+91 90000 10002',
      department: engineering,
      designation: engineerDesignation,
      manager: managerReference,
      location: primaryLocation,
      costCenter: platformCostCenter,
      employmentType: 'full_time',
      status: employeeStatuses[1],
      joinDate: '2024-02-12',
    }),
    createEmployee({
      id: 1003,
      code: 'EMP-1003',
      firstName: 'Rohit',
      lastName: 'Iyer',
      email: 'rohit.iyer@phoenixhrms.test',
      phone: '+91 90000 10003',
      department: engineering,
      designation: engineerDesignation,
      manager: managerReference,
      location: secondaryLocation,
      costCenter: platformCostCenter,
      employmentType: 'full_time',
      status: employeeStatuses[2],
      joinDate: '2026-04-01',
    }),
    createEmployee({
      id: 1004,
      code: 'EMP-1004',
      firstName: 'Meera',
      lastName: 'Sethi',
      email: 'meera.sethi@phoenixhrms.test',
      phone: '+91 90000 10004',
      department: peopleOps,
      designation: hrDesignation,
      manager: null,
      location: primaryLocation,
      costCenter: peopleCostCenter,
      employmentType: 'full_time',
      status: employeeStatuses[3],
      joinDate: '2022-09-19',
    }),
    createEmployee({
      id: 1005,
      code: 'EMP-1005',
      firstName: 'Kabir',
      lastName: 'Malik',
      email: 'kabir.malik@phoenixhrms.test',
      phone: '+91 90000 10005',
      department: peopleOps,
      designation: hrDesignation,
      manager: hrLeadReference,
      location: primaryLocation,
      costCenter: peopleCostCenter,
      employmentType: 'contract',
      status: employeeStatuses[4],
      joinDate: '2024-08-05',
    }),
    createEmployee({
      id: 1006,
      code: 'EMP-1006',
      firstName: 'Sana',
      lastName: 'Dua',
      email: 'sana.dua@phoenixhrms.test',
      phone: '+91 90000 10006',
      department: finance,
      designation: hrDesignation,
      manager: hrLeadReference,
      location: secondaryLocation,
      costCenter: peopleCostCenter,
      employmentType: 'full_time',
      status: employeeStatuses[5],
      joinDate: '2021-06-14',
      terminationReason: 'Role consolidation',
      terminatedAt: '2026-05-14T10:15:00+05:30',
    }),
  ]
}

function createEmployee({
  id,
  code,
  firstName,
  lastName,
  email,
  phone,
  department,
  designation,
  manager,
  location,
  costCenter,
  employmentType,
  status,
  joinDate,
  terminationReason = null,
  terminatedAt = null,
}: {
  id: number
  code: string
  firstName: string
  lastName: string
  email: string
  phone: string
  department: ReturnType<typeof buildDemoOrganizationWorkspace>['departments'][number]
  designation: ReturnType<typeof buildDemoOrganizationWorkspace>['designations'][number]
  manager: EmployeeReference | null
  location: ReturnType<typeof buildDemoOrganizationWorkspace>['locations'][number] | null
  costCenter: ReturnType<typeof buildDemoOrganizationWorkspace>['costCenters'][number] | null
  employmentType: string
  status: EmployeeStatus
  joinDate: string
  terminationReason?: string | null
  terminatedAt?: string | null
}): EmployeeRecord {
  return {
    id,
    employee_code: code,
    first_name: firstName,
    middle_name: null,
    last_name: lastName,
    full_name: `${firstName} ${lastName}`,
    email,
    phone,
    date_of_birth: null,
    gender: null,
    marital_status: null,
    date_of_joining: joinDate,
    employment_type: employmentType,
    employment_status: status,
    termination_reason: terminationReason,
    terminated_at: terminatedAt,
    department,
    designation,
    manager,
    location,
    cost_center: costCenter,
    user_id: null,
    created_at: '2026-01-15T09:00:00+05:30',
    updated_at: '2026-06-02T12:30:00+05:30',
  }
}
