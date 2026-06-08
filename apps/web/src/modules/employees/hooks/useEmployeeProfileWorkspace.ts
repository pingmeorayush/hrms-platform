import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import type { AccessSnapshot } from '../../access/types'
import { fetchOrganizationWorkspace } from '../../organization/api/organizationApi'
import { buildDemoOrganizationWorkspace } from '../../organization/data/demoOrganizationWorkspace'
import type { OrganizationWorkspaceData } from '../../organization/types'
import { fetchEmployeeDetail, fetchEmployeeDirectory } from '../api/employeesApi'
import {
  createEmployeeAddress,
  createEmployeeContact,
  createEmployeeEmergencyContact,
  createEmployeeOnboardingTask,
  downloadEmployeeDocument,
  fetchEmployeeAddresses,
  fetchEmployeeAuditHistory,
  fetchEmployeeBankAccounts,
  fetchEmployeeContacts,
  fetchEmployeeDocuments,
  fetchEmployeeEmergencyContacts,
  fetchEmployeeOnboarding,
  recordEmployeePromotion,
  recordEmployeeTermination,
  recordEmployeeTransfer,
  updateEmployeeAddress,
  updateEmployeeContact,
  updateEmployeeEmergencyContact,
  updateEmployeeOnboardingTask,
  updateEmployeeProfile,
  uploadEmployeeDocument,
  type EmployeeAddressPayload,
  type EmployeeContactPayload,
  type EmployeeDocumentUploadPayload,
  type EmployeeEmergencyContactPayload,
  type EmployeeOnboardingTaskPayload,
  type EmployeeProfileUpdatePayload,
  type EmployeePromotionPayload,
  type EmployeeTerminationPayload,
  type EmployeeTransferPayload,
} from '../api/employeeProfileApi'
import { buildDemoEmployees } from '../data/demoEmployees'
import { buildDemoEmployeeWorkspace, summarizeOnboarding } from '../data/demoEmployeeProfiles'
import type {
  AuditLogEntry,
  EmployeeAddressRecord,
  EmployeeContactRecord,
  EmployeeDocumentRecord,
  EmployeeEmergencyContactRecord,
  EmployeeOnboardingData,
  EmployeeOnboardingTaskRecord,
  EmployeeProfileWorkspaceData,
  EmployeeRecord,
  PaginatedAuditLogEntries,
} from '../types'

type OrganizationOptions = Pick<
  OrganizationWorkspaceData,
  'departments' | 'designations' | 'locations' | 'costCenters'
>

const managerQueryFilters = {
  search: '',
  employmentStatus: '',
  departmentId: '',
  designationId: '',
  managerId: '',
  page: 1,
  perPage: 200,
} as const

const emptyAuditHistory: PaginatedAuditLogEntries = {
  items: [],
  meta: {
    page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  },
}

export function useEmployeeProfileWorkspace(employeeId: number | null) {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const demoEmployees = useMemo(() => buildDemoEmployees(snapshot), [snapshot])
  const demoOrganizationWorkspace = useMemo(
    () => buildDemoOrganizationWorkspace(snapshot),
    [snapshot],
  )
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}:${employeeId ?? 'none'}`
  const [demoState, setDemoState] = useState<{
    key: string
    data: EmployeeProfileWorkspaceData | null
  }>(() => ({
    key: demoStateKey,
    data: buildDemoEmployeeWorkspace(snapshot, employeeId),
  }))
  const [isDemoSaving, setIsDemoSaving] = useState(false)

  if (demoState.key !== demoStateKey) {
    setDemoState({
      key: demoStateKey,
      data: buildDemoEmployeeWorkspace(snapshot, employeeId),
    })
  }

  const demoData = demoState.data

  function setDemoData(
    value:
      | EmployeeProfileWorkspaceData
      | null
      | ((current: EmployeeProfileWorkspaceData | null) => EmployeeProfileWorkspaceData | null),
  ) {
    setDemoState((current) => {
      const baseData =
        current.key === demoStateKey ? current.data : buildDemoEmployeeWorkspace(snapshot, employeeId)
      const nextData = typeof value === 'function' ? value(baseData) : value

      return {
        key: demoStateKey,
        data: nextData,
      }
    })
  }

  const canManage = snapshot
    ? snapshot.user.permissions.includes('employee.manage')
    : access.mode === 'demo'
  const canViewBank = snapshot
    ? snapshot.user.permissions.some((permission) =>
        ['employee.bank.view', 'employee.bank.manage'].includes(permission),
      )
    : access.mode === 'demo'
  const canManageBank = snapshot
    ? snapshot.user.permissions.includes('employee.bank.manage')
    : access.mode === 'demo'
  const canViewAudit = snapshot
    ? snapshot.user.permissions.some((permission) =>
        ['audit.view', 'employee.view', 'employee.manage'].includes(permission),
      )
    : access.mode === 'demo'
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0 && employeeId !== null

  const employeeQuery = useQuery({
    queryKey: ['employee-profile', 'employee', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeDetail(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const contactsQuery = useQuery({
    queryKey: ['employee-profile', 'contacts', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeContacts(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const addressesQuery = useQuery({
    queryKey: ['employee-profile', 'addresses', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeAddresses(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const emergencyContactsQuery = useQuery({
    queryKey: ['employee-profile', 'emergency-contacts', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeEmergencyContacts(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const onboardingQuery = useQuery({
    queryKey: ['employee-profile', 'onboarding', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeOnboarding(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const documentsQuery = useQuery({
    queryKey: ['employee-profile', 'documents', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeDocuments(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const bankAccountsQuery = useQuery({
    queryKey: ['employee-profile', 'bank-accounts', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeBankAccounts(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled && canViewBank,
  })

  const auditHistoryQuery = useQuery({
    queryKey: ['employee-profile', 'audit-history', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeAuditHistory(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled && canViewAudit,
  })

  const managerOptionsQuery = useQuery({
    queryKey: ['employee-profile', 'managers', access.apiBaseUrl, access.token],
    queryFn: () => fetchEmployeeDirectory(access.apiBaseUrl, access.token, managerQueryFilters),
    enabled: liveEnabled,
    staleTime: 300_000,
  })

  const organizationOptionsQuery = useQuery({
    queryKey: ['employee-profile', 'organization-options', access.apiBaseUrl, access.token],
    queryFn: () => fetchOrganizationWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
    staleTime: 300_000,
  })

  const employeeKey = ['employee-profile', 'employee', access.apiBaseUrl, access.token, employeeId] as const
  const contactsKey = ['employee-profile', 'contacts', access.apiBaseUrl, access.token, employeeId] as const
  const addressesKey = ['employee-profile', 'addresses', access.apiBaseUrl, access.token, employeeId] as const
  const emergencyContactsKey = [
    'employee-profile',
    'emergency-contacts',
    access.apiBaseUrl,
    access.token,
    employeeId,
  ] as const
  const onboardingKey = ['employee-profile', 'onboarding', access.apiBaseUrl, access.token, employeeId] as const
  const documentsKey = ['employee-profile', 'documents', access.apiBaseUrl, access.token, employeeId] as const

  const updateProfileMutation = useMutation({
    mutationFn: (payload: EmployeeProfileUpdatePayload) =>
      updateEmployeeProfile(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (employee) => {
      queryClient.setQueryData<EmployeeRecord>(employeeKey, employee)
    },
  })

  const upsertContactMutation = useMutation({
    mutationFn: ({
      contactId,
      payload,
    }: {
      contactId?: number
      payload: EmployeeContactPayload
    }) =>
      contactId
        ? updateEmployeeContact(
            access.apiBaseUrl,
            access.token,
            requireEmployeeId(employeeId),
            contactId,
            payload,
          )
        : createEmployeeContact(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (record) => {
      queryClient.setQueryData<EmployeeContactRecord[]>(contactsKey, (current = []) =>
        upsertRecord(current, record),
      )
    },
  })

  const upsertAddressMutation = useMutation({
    mutationFn: ({
      addressId,
      payload,
    }: {
      addressId?: number
      payload: EmployeeAddressPayload
    }) =>
      addressId
        ? updateEmployeeAddress(
            access.apiBaseUrl,
            access.token,
            requireEmployeeId(employeeId),
            addressId,
            payload,
          )
        : createEmployeeAddress(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (record) => {
      queryClient.setQueryData<EmployeeAddressRecord[]>(addressesKey, (current = []) =>
        upsertRecord(current, record),
      )
    },
  })

  const upsertEmergencyContactMutation = useMutation({
    mutationFn: ({
      emergencyContactId,
      payload,
    }: {
      emergencyContactId?: number
      payload: EmployeeEmergencyContactPayload
    }) =>
      emergencyContactId
        ? updateEmployeeEmergencyContact(
            access.apiBaseUrl,
            access.token,
            requireEmployeeId(employeeId),
            emergencyContactId,
            payload,
          )
        : createEmployeeEmergencyContact(
            access.apiBaseUrl,
            access.token,
            requireEmployeeId(employeeId),
            payload,
          ),
    onSuccess: (record) => {
      queryClient.setQueryData<EmployeeEmergencyContactRecord[]>(emergencyContactsKey, (current = []) =>
        upsertRecord(current, record),
      )
    },
  })

  const upsertOnboardingTaskMutation = useMutation({
    mutationFn: ({
      taskId,
      payload,
    }: {
      taskId?: number
      payload: EmployeeOnboardingTaskPayload
    }) =>
      taskId
        ? updateEmployeeOnboardingTask(
            access.apiBaseUrl,
            access.token,
            requireEmployeeId(employeeId),
            taskId,
            payload,
          )
        : createEmployeeOnboardingTask(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (record) => {
      queryClient.setQueryData<EmployeeOnboardingData>(onboardingKey, (current) => {
        const items = upsertRecord(current?.items ?? [], record, compareBySortOrder)

        return {
          items,
          summary: summarizeOnboarding(items),
        }
      })
    },
  })

  const uploadDocumentMutation = useMutation({
    mutationFn: (payload: EmployeeDocumentUploadPayload) =>
      uploadEmployeeDocument(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (record) => {
      queryClient.setQueryData<EmployeeDocumentRecord[]>(documentsKey, (current = []) =>
        upsertRecord(current, record, compareByUpdatedAt),
      )
    },
  })

  const transferMutation = useMutation({
    mutationFn: (payload: EmployeeTransferPayload) =>
      recordEmployeeTransfer(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (employee) => {
      queryClient.setQueryData<EmployeeRecord>(employeeKey, employee)
    },
  })

  const promotionMutation = useMutation({
    mutationFn: (payload: EmployeePromotionPayload) =>
      recordEmployeePromotion(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (employee) => {
      queryClient.setQueryData<EmployeeRecord>(employeeKey, employee)
    },
  })

  const terminationMutation = useMutation({
    mutationFn: (payload: EmployeeTerminationPayload) =>
      recordEmployeeTermination(access.apiBaseUrl, access.token, requireEmployeeId(employeeId), payload),
    onSuccess: (employee) => {
      queryClient.setQueryData<EmployeeRecord>(employeeKey, employee)
    },
  })

  const liveData = useMemo(() => {
    if (
      !employeeQuery.data ||
      !contactsQuery.data ||
      !addressesQuery.data ||
      !emergencyContactsQuery.data ||
      !onboardingQuery.data ||
      !documentsQuery.data
    ) {
      return null
    }

    if (canViewBank && !bankAccountsQuery.data) {
      return null
    }

    if (canViewAudit && !auditHistoryQuery.data) {
      return null
    }

    return {
      employee: employeeQuery.data,
      contacts: contactsQuery.data,
      addresses: addressesQuery.data,
      emergencyContacts: emergencyContactsQuery.data,
      onboarding: onboardingQuery.data,
      documents: documentsQuery.data,
      bankAccounts: canViewBank ? bankAccountsQuery.data ?? null : null,
      auditHistory: canViewAudit ? auditHistoryQuery.data ?? emptyAuditHistory : emptyAuditHistory,
    } satisfies EmployeeProfileWorkspaceData
  }, [
    addressesQuery.data,
    auditHistoryQuery.data,
    bankAccountsQuery.data,
    canViewAudit,
    canViewBank,
    contactsQuery.data,
    documentsQuery.data,
    emergencyContactsQuery.data,
    employeeQuery.data,
    onboardingQuery.data,
  ])

  const managerOptions = useMemo(() => {
    const records =
      source === 'demo'
        ? demoEmployees
        : (managerOptionsQuery.data?.items ?? []).filter((record) => record.id !== employeeId)

    return [...records].sort((left, right) => left.full_name.localeCompare(right.full_name))
  }, [demoEmployees, employeeId, managerOptionsQuery.data?.items, source])

  const organizationOptions = useMemo<OrganizationOptions | null>(() => {
    if (source === 'demo') {
      return {
        departments: demoOrganizationWorkspace.departments,
        designations: demoOrganizationWorkspace.designations,
        locations: demoOrganizationWorkspace.locations,
        costCenters: demoOrganizationWorkspace.costCenters,
      }
    }

    if (!organizationOptionsQuery.data) {
      return null
    }

    return {
      departments: organizationOptionsQuery.data.departments,
      designations: organizationOptionsQuery.data.designations,
      locations: organizationOptionsQuery.data.locations,
      costCenters: organizationOptionsQuery.data.costCenters,
    }
  }, [demoOrganizationWorkspace, organizationOptionsQuery.data, source])

  const data = source === 'demo' ? demoData : liveData

  return {
    data,
    source,
    snapshot,
    managerOptions,
    organizationOptions,
    canManage,
    canViewBank,
    canManageBank,
    canViewAudit,
    isLoading:
      source === 'live'
        ? employeeQuery.isLoading ||
          contactsQuery.isLoading ||
          addressesQuery.isLoading ||
          emergencyContactsQuery.isLoading ||
          onboardingQuery.isLoading ||
          documentsQuery.isLoading ||
          managerOptionsQuery.isLoading ||
          organizationOptionsQuery.isLoading ||
          (canViewBank && bankAccountsQuery.isLoading) ||
          (canViewAudit && auditHistoryQuery.isLoading)
        : false,
    error:
      source === 'live'
        ? firstDefinedError(
            employeeQuery.error,
            contactsQuery.error,
            addressesQuery.error,
            emergencyContactsQuery.error,
            onboardingQuery.error,
            documentsQuery.error,
            bankAccountsQuery.error,
            auditHistoryQuery.error,
            managerOptionsQuery.error,
            organizationOptionsQuery.error,
          )
        : null,
    isSaving:
      isDemoSaving ||
      updateProfileMutation.isPending ||
      upsertContactMutation.isPending ||
      upsertAddressMutation.isPending ||
      upsertEmergencyContactMutation.isPending ||
      upsertOnboardingTaskMutation.isPending ||
      uploadDocumentMutation.isPending ||
      transferMutation.isPending ||
      promotionMutation.isPending ||
      terminationMutation.isPending,
    async saveProfile(payload: EmployeeProfileUpdatePayload) {
      if (source === 'demo') {
        return runDemoMutation(setIsDemoSaving, async () => {
          let updatedEmployee: EmployeeRecord | null = null

          setDemoData((current) => {
            if (!current) {
              return current
            }

            updatedEmployee = applyDemoProfileUpdate(current.employee, payload)

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, current.employee.id, 'employee.record.updated', {
                  fields: Object.keys(payload),
                }),
              ),
              employee: updatedEmployee,
            }
          })

          return updatedEmployee
        })
      }

      return updateProfileMutation.mutateAsync(payload)
    },
    async saveContact(contactId: number | undefined, payload: EmployeeContactPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current || employeeId === null) {
              return current
            }

            const record = buildDemoContactRecord(employeeId, current.contacts, contactId, payload)

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, employeeId, contactId ? 'employee.contact.updated' : 'employee.contact.created', {
                  type: record.type,
                  value: record.value,
                }),
              ),
              contacts: upsertRecord(current.contacts, record),
            }
          })
        })
        return
      }

      await upsertContactMutation.mutateAsync({ contactId, payload })
    },
    async saveAddress(addressId: number | undefined, payload: EmployeeAddressPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current || employeeId === null) {
              return current
            }

            const record = buildDemoAddressRecord(employeeId, current.addresses, addressId, payload)

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, employeeId, addressId ? 'employee.address.updated' : 'employee.address.created', {
                  type: record.type,
                  city: record.city,
                }),
              ),
              addresses: upsertRecord(current.addresses, record),
            }
          })
        })
        return
      }

      await upsertAddressMutation.mutateAsync({ addressId, payload })
    },
    async saveEmergencyContact(
      emergencyContactId: number | undefined,
      payload: EmployeeEmergencyContactPayload,
    ) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current || employeeId === null) {
              return current
            }

            const record = buildDemoEmergencyContactRecord(
              employeeId,
              current.emergencyContacts,
              emergencyContactId,
              payload,
            )

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(
                  snapshot,
                  employeeId,
                  emergencyContactId
                    ? 'employee.emergency_contact.updated'
                    : 'employee.emergency_contact.created',
                  {
                    name: record.name,
                    relationship: record.relationship,
                  },
                ),
              ),
              emergencyContacts: upsertRecord(current.emergencyContacts, record),
            }
          })
        })
        return
      }

      await upsertEmergencyContactMutation.mutateAsync({ emergencyContactId, payload })
    },
    async saveOnboardingTask(taskId: number | undefined, payload: EmployeeOnboardingTaskPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current || employeeId === null) {
              return current
            }

            const record = buildDemoOnboardingTaskRecord(employeeId, current.onboarding.items, taskId, payload)
            const items = upsertRecord(current.onboarding.items, record, compareBySortOrder)

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(
                  snapshot,
                  employeeId,
                  taskId ? 'employee.onboarding_task.updated' : 'employee.onboarding_task.created',
                  {
                    title: record.title,
                    status: record.status,
                  },
                ),
              ),
              onboarding: {
                items,
                summary: summarizeOnboarding(items),
              },
            }
          })
        })
        return
      }

      await upsertOnboardingTaskMutation.mutateAsync({ taskId, payload })
    },
    async uploadDocument(payload: EmployeeDocumentUploadPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current || employeeId === null) {
              return current
            }

            const record = buildDemoDocumentRecord(employeeId, current.documents, payload)

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, employeeId, 'employee.document.uploaded', {
                  document_type: record.document_type,
                  original_file_name: record.original_file_name,
                }),
              ),
              documents: upsertRecord(current.documents, record, compareByUpdatedAt),
            }
          })
        })
        return
      }

      await uploadDocumentMutation.mutateAsync(payload)
    },
    async triggerTransfer(payload: EmployeeTransferPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current) {
              return current
            }

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, current.employee.id, 'employee.record.transferred', {
                  effective_date: payload.effective_date,
                  department_id: payload.department_id,
                  manager_id: payload.manager_id ?? null,
                }),
              ),
              employee: applyDemoTransfer(current.employee, payload, managerOptions, organizationOptions),
            }
          })
        })
        return
      }

      await transferMutation.mutateAsync(payload)
    },
    async triggerPromotion(payload: EmployeePromotionPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current) {
              return current
            }

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, current.employee.id, 'employee.record.promoted', {
                  effective_date: payload.effective_date,
                  designation_id: payload.designation_id,
                  manager_id: payload.manager_id ?? null,
                }),
              ),
              employee: applyDemoPromotion(current.employee, payload, managerOptions, organizationOptions),
            }
          })
        })
        return
      }

      await promotionMutation.mutateAsync(payload)
    },
    async triggerTermination(payload: EmployeeTerminationPayload) {
      if (source === 'demo') {
        await runDemoMutation(setIsDemoSaving, async () => {
          setDemoData((current) => {
            if (!current) {
              return current
            }

            return {
              ...current,
              auditHistory: prependAuditEntry(
                current.auditHistory,
                buildDemoAuditEntry(snapshot, current.employee.id, 'employee.record.terminated', {
                  termination_date: payload.termination_date,
                  reason: payload.reason.trim(),
                }),
              ),
              employee: {
                ...current.employee,
                employment_status: 'terminated',
                termination_reason: payload.reason.trim(),
                terminated_at: payload.termination_date,
                updated_at: new Date().toISOString(),
              },
            }
          })
        })
        return
      }

      await terminationMutation.mutateAsync(payload)
    },
    async downloadDocument(document: EmployeeDocumentRecord) {
      if (source === 'demo') {
        if (typeof window !== 'undefined' && document.download_url) {
          window.open(document.download_url, '_blank', 'noopener,noreferrer')
        }
        return
      }

      await downloadEmployeeDocument(
        access.apiBaseUrl,
        access.token,
        requireEmployeeId(employeeId),
        document.id,
        document.original_file_name,
      )
    },
  }
}

function requireEmployeeId(employeeId: number | null) {
  if (employeeId === null) {
    throw new Error('Employee workspace requires a valid employee id.')
  }

  return employeeId
}

async function runDemoMutation<T>(setIsSaving: (value: boolean) => void, task: () => Promise<T>) {
  setIsSaving(true)

  try {
    return await task()
  } finally {
    setIsSaving(false)
  }
}

function firstDefinedError(...errors: unknown[]) {
  return (errors.find((error): error is Error => error instanceof Error) ?? null) as Error | null
}

function prependAuditEntry(
  auditHistory: PaginatedAuditLogEntries,
  entry: AuditLogEntry,
): PaginatedAuditLogEntries {
  const items = [entry, ...auditHistory.items]

  return {
    items,
    meta: {
      ...auditHistory.meta,
      total: items.length,
      last_page: 1,
    },
  }
}

function buildDemoAuditEntry(
  snapshot: AccessSnapshot | null,
  employeeId: number,
  eventType: string,
  metadata?: unknown,
): AuditLogEntry {
  return {
    id: Date.now(),
    event_type: eventType,
    entity_type: 'employee',
    entity_id: String(employeeId),
    ip_address: '127.0.0.1',
    created_at: new Date().toISOString(),
    metadata: toAuditMetadata(metadata),
    user: snapshot
      ? {
          id: snapshot.user.id,
          name: snapshot.user.name,
          email: snapshot.user.email,
        }
      : null,
  }
}

function toAuditMetadata(metadata: unknown): Record<string, unknown> {
  if (!metadata || typeof metadata !== 'object' || Array.isArray(metadata)) {
    return {}
  }

  return Object.fromEntries(Object.entries(metadata as Record<string, unknown>))
}

function upsertRecord<T extends { id: number }>(
  records: T[],
  nextRecord: T,
  compare: (left: T, right: T) => number = compareById,
) {
  const hasRecord = records.some((record) => record.id === nextRecord.id)
  const nextRecords = hasRecord
    ? records.map((record) => (record.id === nextRecord.id ? nextRecord : record))
    : [...records, nextRecord]

  return [...nextRecords].sort(compare)
}

function compareById<T extends { id: number }>(left: T, right: T) {
  return left.id - right.id
}

function compareBySortOrder<
  T extends {
    sort_order: number
    id: number
  },
>(left: T, right: T) {
  return left.sort_order - right.sort_order || left.id - right.id
}

function compareByUpdatedAt<
  T extends {
    id: number
    updated_at: string | null
    created_at: string | null
  },
>(left: T, right: T) {
  const leftTime = left.updated_at ?? left.created_at ?? ''
  const rightTime = right.updated_at ?? right.created_at ?? ''

  return rightTime.localeCompare(leftTime) || left.id - right.id
}

function nextId<T extends { id: number }>(records: T[]) {
  return records.reduce((maxId, record) => Math.max(maxId, record.id), 0) + 1
}

function applyDemoProfileUpdate(employee: EmployeeRecord, payload: EmployeeProfileUpdatePayload): EmployeeRecord {
  const firstName = payload.first_name?.trim() ?? employee.first_name
  const middleName = payload.middle_name === undefined ? employee.middle_name : normalizeNullableString(payload.middle_name)
  const lastName = payload.last_name?.trim() ?? employee.last_name

  return {
    ...employee,
    first_name: firstName,
    middle_name: middleName,
    last_name: lastName,
    full_name: [firstName, middleName, lastName].filter(Boolean).join(' '),
    email: payload.email?.trim() ?? employee.email,
    phone: payload.phone === undefined ? employee.phone : normalizeNullableString(payload.phone),
    date_of_birth:
      payload.date_of_birth === undefined
        ? employee.date_of_birth
        : normalizeNullableString(payload.date_of_birth),
    gender: payload.gender === undefined ? employee.gender : normalizeNullableString(payload.gender),
    marital_status:
      payload.marital_status === undefined
        ? employee.marital_status
        : normalizeNullableString(payload.marital_status),
    employment_type: payload.employment_type?.trim() ?? employee.employment_type,
    user_id: payload.user_id === undefined ? employee.user_id : payload.user_id,
    updated_at: new Date().toISOString(),
  }
}

function applyDemoTransfer(
  employee: EmployeeRecord,
  payload: EmployeeTransferPayload,
  managers: EmployeeRecord[],
  organizationOptions: OrganizationOptions | null,
): EmployeeRecord {
  return {
    ...employee,
    department:
      organizationOptions?.departments.find((record) => record.id === payload.department_id) ??
      employee.department,
    manager:
      payload.manager_id === undefined
        ? employee.manager
        : managers.find((record) => record.id === payload.manager_id)
            ? buildEmployeeReference(managers.find((record) => record.id === payload.manager_id) ?? null)
            : null,
    location:
      payload.location_id === undefined
        ? employee.location
        : organizationOptions?.locations.find((record) => record.id === payload.location_id) ?? null,
    cost_center:
      payload.cost_center_id === undefined
        ? employee.cost_center
        : organizationOptions?.costCenters.find((record) => record.id === payload.cost_center_id) ?? null,
    updated_at: new Date().toISOString(),
  }
}

function applyDemoPromotion(
  employee: EmployeeRecord,
  payload: EmployeePromotionPayload,
  managers: EmployeeRecord[],
  organizationOptions: OrganizationOptions | null,
): EmployeeRecord {
  return {
    ...applyDemoTransfer(employee, payload, managers, organizationOptions),
    designation:
      organizationOptions?.designations.find((record) => record.id === payload.designation_id) ??
      employee.designation,
    updated_at: new Date().toISOString(),
  }
}

function buildEmployeeReference(employee: EmployeeRecord | null) {
  if (!employee) {
    return null
  }

  return {
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
  }
}

function buildDemoContactRecord(
  employeeId: number,
  contacts: EmployeeContactRecord[],
  contactId: number | undefined,
  payload: EmployeeContactPayload,
): EmployeeContactRecord {
  const existingRecord = contactId ? contacts.find((record) => record.id === contactId) ?? null : null
  const timestamp = new Date().toISOString()

  return {
    id: existingRecord?.id ?? nextId(contacts),
    employee_id: employeeId,
    type: payload.type,
    label: normalizeNullableString(payload.label),
    value: payload.value.trim(),
    is_primary: payload.is_primary ?? existingRecord?.is_primary ?? false,
    status: payload.status ?? existingRecord?.status ?? 'active',
    notes: normalizeNullableString(payload.notes),
    created_at: existingRecord?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildDemoAddressRecord(
  employeeId: number,
  addresses: EmployeeAddressRecord[],
  addressId: number | undefined,
  payload: EmployeeAddressPayload,
): EmployeeAddressRecord {
  const existingRecord = addressId ? addresses.find((record) => record.id === addressId) ?? null : null
  const timestamp = new Date().toISOString()

  return {
    id: existingRecord?.id ?? nextId(addresses),
    employee_id: employeeId,
    type: payload.type,
    address_line_1: payload.address_line_1.trim(),
    address_line_2: normalizeNullableString(payload.address_line_2),
    city: payload.city.trim(),
    state: normalizeNullableString(payload.state),
    country: payload.country.trim(),
    postal_code: payload.postal_code.trim(),
    notes: normalizeNullableString(payload.notes),
    created_at: existingRecord?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildDemoEmergencyContactRecord(
  employeeId: number,
  contacts: EmployeeEmergencyContactRecord[],
  emergencyContactId: number | undefined,
  payload: EmployeeEmergencyContactPayload,
): EmployeeEmergencyContactRecord {
  const existingRecord = emergencyContactId
    ? contacts.find((record) => record.id === emergencyContactId) ?? null
    : null
  const timestamp = new Date().toISOString()

  return {
    id: existingRecord?.id ?? nextId(contacts),
    employee_id: employeeId,
    name: payload.name.trim(),
    relationship: payload.relationship.trim(),
    phone_number: payload.phone_number.trim(),
    email: normalizeNullableString(payload.email),
    address: normalizeNullableString(payload.address),
    priority: payload.priority ?? null,
    notes: normalizeNullableString(payload.notes),
    created_at: existingRecord?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildDemoOnboardingTaskRecord(
  employeeId: number,
  items: EmployeeOnboardingTaskRecord[],
  taskId: number | undefined,
  payload: EmployeeOnboardingTaskPayload,
): EmployeeOnboardingTaskRecord {
  const existingRecord = taskId ? items.find((record) => record.id === taskId) ?? null : null
  const timestamp = new Date().toISOString()
  const status = payload.status ?? existingRecord?.status ?? 'pending'

  return {
    id: existingRecord?.id ?? nextId(items),
    employee_id: employeeId,
    title: payload.title?.trim() || existingRecord?.title || 'New onboarding task',
    category: payload.category ?? existingRecord?.category ?? 'other',
    task_type:
      payload.task_type === undefined
        ? (existingRecord?.task_type ?? null)
        : payload.task_type,
    assignee_type: payload.assignee_type ?? existingRecord?.assignee_type ?? 'employee',
    status,
    sort_order: payload.sort_order ?? existingRecord?.sort_order ?? (items.length + 1) * 10,
    due_date:
      payload.due_date === undefined ? (existingRecord?.due_date ?? null) : normalizeNullableString(payload.due_date),
    completed_at:
      status === 'completed'
        ? existingRecord?.completed_at ?? timestamp
        : status === 'skipped'
          ? existingRecord?.completed_at ?? null
          : null,
    notes: normalizeNullableString(payload.notes ?? existingRecord?.notes ?? null),
    created_at: existingRecord?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildDemoDocumentRecord(
  employeeId: number,
  documents: EmployeeDocumentRecord[],
  payload: EmployeeDocumentUploadPayload,
): EmployeeDocumentRecord {
  const timestamp = new Date().toISOString()

  return {
    id: nextId(documents),
    employee_id: employeeId,
    document_type: payload.document_type.trim(),
    original_file_name: payload.file.name,
    mime_type: payload.file.type || 'application/octet-stream',
    file_size_bytes: payload.file.size,
    expiry_date: normalizeNullableString(payload.expiry_date),
    notes: normalizeNullableString(payload.notes),
    download_url: '#demo-document-download',
    created_at: timestamp,
    updated_at: timestamp,
  }
}

function normalizeNullableString(value: string | null | undefined) {
  const nextValue = value?.trim()
  return nextValue ? nextValue : null
}
