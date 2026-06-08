import type { FormEvent, ReactNode } from 'react'
import { useMemo, useState } from 'react'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import { WorkspaceEmptyState, WorkspaceField, WorkspacePillRow, WorkspaceTableShell, WorkspaceToolbar, WorkspaceToolbarRow, WorkspaceToolbarSummary } from '../../../shared/ui/workspace'
import { useLeaveWorkspace } from '../hooks/useLeaveWorkspace'
import type {
  LeaveAccrualFrequency,
  LeaveCalendarFilters,
  LeavePolicyFormValues,
  LeavePolicyRecord,
  LeaveRequestStatus,
  LeaveTypeCategory,
  LeaveTypeFormValues,
  LeaveTypeRecord,
} from '../types'

type LeaveAdminTab = 'types' | 'policies' | 'calendar'

const leaveAdminTabs: Array<{ id: LeaveAdminTab; label: string }> = [
  { id: 'types', label: 'Leave types' },
  { id: 'policies', label: 'Policy rules' },
  { id: 'calendar', label: 'Leave calendar' },
]

const emptyLeaveTypeForm: LeaveTypeFormValues = {
  code: '',
  name: '',
  category: 'earned',
  description: '',
  is_paid: true,
  requires_approval: true,
  allows_half_day: true,
  color_token: '#0972d3',
  status: 'active',
}

const emptyPolicyForm: LeavePolicyFormValues = {
  leave_type_id: '',
  annual_allowance_days: '0',
  opening_balance_days: '0',
  accrual_frequency: 'none',
  carry_forward_limit_days: '0',
  encashment_limit_days: '0',
  max_consecutive_days: '1',
  min_notice_days: '0',
  requires_documentation_after_days: '',
  applicable_department_id: '',
  applicable_location_id: '',
  status: 'active',
}

const categoryOptions: Array<[LeaveTypeCategory, string]> = [
  ['earned', 'Earned'],
  ['casual', 'Casual'],
  ['sick', 'Sick'],
  ['optional', 'Optional holiday'],
  ['unpaid', 'Unpaid'],
]

const accrualOptions: Array<[LeaveAccrualFrequency, string]> = [
  ['monthly', 'Monthly'],
  ['quarterly', 'Quarterly'],
  ['annual', 'Annual'],
  ['none', 'No accrual'],
]

export function LeaveAdminWorkspace() {
  const workspace = useLeaveWorkspace()

  return <LeaveAdminWorkspaceView workspace={workspace} />
}

export function LeaveAdminWorkspaceView({
  workspace,
}: {
  workspace: ReturnType<typeof useLeaveWorkspace>
}) {
  const {
    data,
    canManagePolicy,
    isLoading,
    error,
    isSaving,
    saveLeaveType,
    saveLeavePolicy,
  } = workspace
  const [activeTab, setActiveTab] = useState<LeaveAdminTab>('types')
  const [selectedLeaveTypeId, setSelectedLeaveTypeId] = useState<number | null>(null)
  const [selectedPolicyId, setSelectedPolicyId] = useState<number | null>(null)
  const [isLeaveTypeModalOpen, setIsLeaveTypeModalOpen] = useState(false)
  const [isPolicyModalOpen, setIsPolicyModalOpen] = useState(false)
  const [filters, setFilters] = useState<LeaveCalendarFilters>({
    status: '',
    departmentId: '',
    locationId: '',
  })
  const { runConfirmedAction } = useOperationFeedback()

  const selectedLeaveType = useMemo(
    () => data?.leaveTypes.find((record) => record.id === selectedLeaveTypeId) ?? null,
    [data?.leaveTypes, selectedLeaveTypeId],
  )
  const selectedPolicy = useMemo(
    () => data?.policies.find((record) => record.id === selectedPolicyId) ?? null,
    [data?.policies, selectedPolicyId],
  )

  const filteredCalendarEntries = useMemo(() => {
    if (!data) {
      return []
    }

    return data.calendarEntries.filter((entry) => {
      const matchesStatus = !filters.status || entry.status === filters.status
      const matchesDepartment =
        !filters.departmentId || entry.department.id === Number(filters.departmentId)
      const matchesLocation =
        !filters.locationId || entry.location?.id === Number(filters.locationId)

      return matchesStatus && matchesDepartment && matchesLocation
    })
  }, [data, filters])

  const approvedLeaveCount = useMemo(
    () => data?.calendarEntries.filter((entry) => entry.status === 'approved').length ?? 0,
    [data],
  )
  const activePolicyCount = useMemo(
    () => data?.policies.filter((record) => record.status === 'active').length ?? 0,
    [data],
  )

  return (
    <div className="workspace-stack">
      {isLoading ? <p className="workspace-muted">Loading leave administration...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      {data ? (
        <>
          <Card className="workspace-collection">
            <CardHeader className="workspace-collection__header">
              <div>
                <CardTitle>Leave policy administration</CardTitle>
                <CardDescription>
                  Configure leave types, maintain balance rules, and review the organization leave calendar.
                </CardDescription>
              </div>
              <WorkspacePillRow>
                <Badge variant="subtle">{activePolicyCount} active policies</Badge>
                <Badge variant="subtle">{approvedLeaveCount} approved leave</Badge>
                <Badge variant={canManagePolicy ? 'success' : 'warning'}>
                  {canManagePolicy ? 'Setup controls enabled' : 'Permission limited'}
                </Badge>
              </WorkspacePillRow>
            </CardHeader>
            <CardContent className="workspace-collection__content">
              <div className="workspace-collection-tabs" role="tablist" aria-label="Leave administration sections">
                {leaveAdminTabs.map((tab) => (
                  <button
                    key={tab.id}
                    type="button"
                    role="tab"
                    aria-selected={activeTab === tab.id}
                    className={`workspace-collection-tabs__button${
                      activeTab === tab.id ? ' workspace-collection-tabs__button--active' : ''
                    }`}
                    onClick={() => setActiveTab(tab.id)}
                  >
                    {tab.label}
                  </button>
                ))}
              </div>
            </CardContent>
          </Card>

          {!canManagePolicy ? (
            <Card className="workspace-detail-card">
              <CardHeader>
                <CardTitle>Admin actions are permission restricted</CardTitle>
                <CardDescription>
                  Leave policy setup requires leave-policy or HR-admin permissions in the current session. Approvers without admin access can still use this route to inspect calendar context alongside their review queue.
                </CardDescription>
              </CardHeader>
            </Card>
          ) : null}

          {activeTab === 'types' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Leave types</CardTitle>
                  <CardDescription>
                    Define the leave categories employees and managers can request, review, and approve.
                  </CardDescription>
                </div>
                <Button
                  variant="primary"
                  size="sm"
                  onClick={() => {
                    setSelectedLeaveTypeId(null)
                    setIsLeaveTypeModalOpen(true)
                  }}
                >
                  New leave type
                </Button>
              </CardHeader>
              <CardContent>
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Leave type</TableHead>
                        <TableHead>Summary</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Updated</TableHead>
                        <TableHead className="w-[132px] text-right">Action</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {data.leaveTypes.map((record) => (
                        <TableRow key={record.id}>
                          <TableCell className="align-top">
                            <div className="ui-table-stack">
                              <span className="ui-table-primary">{record.name}</span>
                              <span className="ui-table-secondary">{record.code}</span>
                            </div>
                          </TableCell>
                          <TableCell className="align-top">
                            <div className="ui-table-stack">
                              <span className="ui-table-primary">
                                {formatLeaveCategory(record.category)} · {record.is_paid ? 'Paid' : 'Unpaid'}
                              </span>
                              <span className="ui-table-secondary">
                                {record.allows_half_day ? 'Half day allowed' : 'Full day only'}
                                {' · '}
                                {record.requires_approval ? 'Approval required' : 'Auto approved'}
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="align-top">
                            <div className="ui-table-badge-row">
                              <Badge variant="subtle">{record.status}</Badge>
                              <Badge variant="subtle">
                                {record.requires_approval ? 'Approval required' : 'Auto approved'}
                              </Badge>
                            </div>
                          </TableCell>
                          <TableCell className="ui-table-body-muted align-top">
                            {formatDate(record.updated_at)}
                          </TableCell>
                          <TableCell className="ui-table-action-cell align-top text-right">
                            <Button
                              variant="secondary"
                              size="sm"
                              onClick={() => {
                                setSelectedLeaveTypeId(record.id)
                                setIsLeaveTypeModalOpen(true)
                              }}
                            >
                              Edit
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              </CardContent>
            </Card>
          ) : null}

          {activeTab === 'policies' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Balance and accrual rules</CardTitle>
                  <CardDescription>
                    Keep annual allowance, accrual, carry-forward, and documentation rules close to the leave type they govern.
                  </CardDescription>
                </div>
                <Button
                  variant="primary"
                  size="sm"
                  onClick={() => {
                    setSelectedPolicyId(null)
                    setIsPolicyModalOpen(true)
                  }}
                >
                  New policy
                </Button>
              </CardHeader>
              <CardContent>
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Policy</TableHead>
                        <TableHead>Rules</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Updated</TableHead>
                        <TableHead className="w-[132px] text-right">Action</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {data.policies.map((record) => (
                        <TableRow key={record.id}>
                          <TableCell className="align-top">
                            <div className="ui-table-stack">
                              <span className="ui-table-primary">{record.leave_type.name}</span>
                              <span className="ui-table-secondary">
                                {record.annual_allowance_days} day(s)
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="align-top">
                            <div className="ui-table-stack">
                              <span className="ui-table-primary">
                                {formatAccrual(record.accrual_frequency)} · Carry forward {record.carry_forward_limit_days} days
                              </span>
                              <span className="ui-table-secondary">
                                Notice {record.min_notice_days} day(s) · {record.applicable_department?.name ?? 'All departments'}
                              </span>
                            </div>
                          </TableCell>
                          <TableCell className="align-top">
                            <div className="ui-table-badge-row">
                              <Badge variant="subtle">{record.status}</Badge>
                              <Badge variant="subtle">
                                {record.applicable_department?.name ?? 'All departments'}
                              </Badge>
                            </div>
                          </TableCell>
                          <TableCell className="ui-table-body-muted align-top">
                            {formatDate(record.updated_at)}
                          </TableCell>
                          <TableCell className="ui-table-action-cell align-top text-right">
                            <Button
                              variant="secondary"
                              size="sm"
                              onClick={() => {
                                setSelectedPolicyId(record.id)
                                setIsPolicyModalOpen(true)
                              }}
                            >
                              Edit
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              </CardContent>
            </Card>
          ) : null}

          {activeTab === 'calendar' ? (
            <>
              <Card className="workspace-detail-card">
                <CardHeader className="workspace-collection__header">
                  <div>
                    <CardTitle>Organization leave calendar</CardTitle>
                    <CardDescription>
                      Review approved and in-flight leave at organization scope with one shared filter toolbar.
                    </CardDescription>
                  </div>
                  <WorkspacePillRow>
                    <Badge variant="subtle">{filteredCalendarEntries.length} shown</Badge>
                  </WorkspacePillRow>
                </CardHeader>
                <CardContent>
                  <WorkspaceToolbar>
                    <WorkspaceToolbarRow className="items-end">
                      <div className="workspace-form-grid">
                        <SelectField
                          label="Status"
                          value={filters.status}
                          onChange={(value) =>
                            setFilters((current) => ({ ...current, status: value as LeaveRequestStatus | '' }))
                          }
                          options={[
                            ['', 'All statuses'],
                            ['approved', 'Approved'],
                            ['pending', 'Pending'],
                            ['changes_requested', 'Changes requested'],
                            ['rejected', 'Rejected'],
                            ['cancelled', 'Cancelled'],
                          ]}
                        />
                        <SelectField
                          label="Department"
                          value={filters.departmentId}
                          onChange={(value) => setFilters((current) => ({ ...current, departmentId: value }))}
                          options={[
                            ['', 'All departments'] as [string, string],
                            ...data.departments.map((record) => [String(record.id), record.name] as [string, string]),
                          ]}
                        />
                        <SelectField
                          label="Location"
                          value={filters.locationId}
                          onChange={(value) => setFilters((current) => ({ ...current, locationId: value }))}
                          options={[
                            ['', 'All locations'] as [string, string],
                            ...data.locations.map((record) => [String(record.id), record.name] as [string, string]),
                          ]}
                        />
                      </div>
                    </WorkspaceToolbarRow>
                    <WorkspaceToolbarSummary>
                      <strong>{filteredCalendarEntries.length} records in view</strong>
                      <span className="text-sm text-muted-foreground">
                        Filter by status, department, or location to inspect a different slice of leave activity.
                      </span>
                    </WorkspaceToolbarSummary>
                  </WorkspaceToolbar>
                </CardContent>
              </Card>

              <Card className="workspace-detail-card">
                <CardHeader>
                  <CardTitle>Calendar records</CardTitle>
                  <CardDescription>
                    Review leave coverage across the tenant without extra summary cards.
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  {filteredCalendarEntries.length ? (
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Employee</TableHead>
                            <TableHead>Leave window</TableHead>
                            <TableHead>Leave type</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Scope</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {filteredCalendarEntries.map((entry) => (
                            <TableRow key={entry.id}>
                              <TableCell className="align-top">
                                <div className="ui-table-stack">
                                  <span className="ui-table-primary">{entry.employee.full_name}</span>
                                  <span className="ui-table-secondary">{entry.employee.employee_code}</span>
                                </div>
                              </TableCell>
                              <TableCell className="align-top">
                                <div className="ui-table-stack">
                                  <span className="ui-table-primary">
                                    {formatDate(entry.start_date)} to {formatDate(entry.end_date)}
                                  </span>
                                  <span className="ui-table-secondary">{entry.total_days} day(s)</span>
                                </div>
                              </TableCell>
                              <TableCell className="align-top">
                                <div className="ui-table-status-stack">
                                  <Badge variant="subtle">{entry.leave_type.name}</Badge>
                                  <span className="ui-table-secondary">{entry.reason}</span>
                                </div>
                              </TableCell>
                              <TableCell className="align-top">
                                <div className="ui-table-stack">
                                  <span className="ui-table-primary">{formatLeaveStatus(entry.status)}</span>
                                  <span className="ui-table-secondary">{entry.leave_type.code}</span>
                                </div>
                              </TableCell>
                              <TableCell className="ui-table-body-muted align-top">
                                {entry.department.name}
                                {entry.location ? ` · ${entry.location.name}` : ''}
                              </TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>
                  ) : (
                    <EmptyState
                      title="No calendar entries match these filters"
                      copy="Adjust status or scope filters to inspect another slice of leave activity."
                    />
                  )}
                </CardContent>
              </Card>
            </>
          ) : null}
        </>
      ) : null}

      {data ? (
        <>
          <Modal
            open={isLeaveTypeModalOpen}
            title={selectedLeaveType ? `Edit ${selectedLeaveType.name}` : 'Create leave type'}
            description="Define requestable leave types without leaving the collection view."
            onClose={() => setIsLeaveTypeModalOpen(false)}
          >
            <LeaveTypeEditor
              key={`leave-type:${selectedLeaveTypeId ?? 'new'}`}
              leaveType={selectedLeaveType}
              canManage={canManagePolicy}
              isSaving={isSaving}
              onSave={(values) =>
                runConfirmedAction({
                  title: selectedLeaveType ? `Save ${selectedLeaveType.name}?` : 'Create leave type?',
                  description: selectedLeaveType
                    ? 'Review the leave-type changes before saving them to the admin workspace.'
                    : 'Create this leave type for future request and approval flows.',
                  confirmLabel: selectedLeaveType ? 'Save leave type' : 'Create leave type',
                  tone: selectedLeaveType ? 'warning' : 'default',
                  successTitle: selectedLeaveType ? 'Leave type updated' : 'Leave type created',
                  successDescription: 'Leave type changes are available in the collection.',
                  errorTitle: 'Unable to save leave type',
                  action: async () => {
                    await saveLeaveType(selectedLeaveType?.id, values)
                    setIsLeaveTypeModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isPolicyModalOpen}
            title={selectedPolicy ? `Edit ${selectedPolicy.leave_type.name}` : 'Create balance rule'}
            description="Keep policy-rule changes inside a focused modal workflow."
            size="lg"
            onClose={() => setIsPolicyModalOpen(false)}
          >
            <LeavePolicyEditor
              key={`leave-policy:${selectedPolicyId ?? 'new'}:${data.leaveTypes.length}`}
              policy={selectedPolicy}
              leaveTypes={data.leaveTypes}
              departments={data.departments}
              locations={data.locations}
              canManage={canManagePolicy}
              isSaving={isSaving}
              onSave={(values) =>
                runConfirmedAction({
                  title: selectedPolicy ? `Save ${selectedPolicy.leave_type.name} policy?` : 'Create balance rule?',
                  description: selectedPolicy
                    ? 'Review the updated allowance and accrual rules before saving.'
                    : 'Create this policy rule so employee balances and requests can reuse it.',
                  confirmLabel: selectedPolicy ? 'Save policy' : 'Create policy',
                  tone: selectedPolicy ? 'warning' : 'default',
                  successTitle: selectedPolicy ? 'Policy updated' : 'Policy created',
                  successDescription: 'Balance-rule changes are now available in leave admin.',
                  errorTitle: 'Unable to save policy',
                  action: async () => {
                    await saveLeavePolicy(selectedPolicy?.id, values)
                    setIsPolicyModalOpen(false)
                  },
                })
              }
            />
          </Modal>
        </>
      ) : null}
    </div>
  )
}

function LeaveTypeEditor({
  leaveType,
  canManage,
  isSaving,
  onSave,
}: {
  leaveType: LeaveTypeRecord | null
  canManage: boolean
  isSaving: boolean
  onSave: (values: LeaveTypeFormValues) => Promise<unknown>
}) {
  const [values, setValues] = useState<LeaveTypeFormValues>(
    leaveType
      ? {
          code: leaveType.code,
          name: leaveType.name,
          category: leaveType.category,
          description: leaveType.description ?? '',
          is_paid: leaveType.is_paid,
          requires_approval: leaveType.requires_approval,
          allows_half_day: leaveType.allows_half_day,
          color_token: leaveType.color_token,
          status: leaveType.status,
        }
      : emptyLeaveTypeForm,
  )
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setMessage(null)
    setFieldErrors({})

    if (!values.code.trim() || !values.name.trim()) {
      setError('Code and name are required.')
      return
    }

    try {
      await onSave(values)
      setMessage(leaveType ? 'Leave type updated successfully.' : 'Leave type created successfully.')
    } catch (caughtError) {
      const nextError = caughtError as Error
      setError(nextError.message)

      if (nextError instanceof ApiRequestError) {
        setFieldErrors(nextError.fieldErrors)
      }
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      <div className="workspace-form-grid">
        <Field label="Code" error={fieldErrors.code?.[0]}>
          <Input
            value={values.code}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, code: event.target.value }))}
          />
        </Field>
        <Field label="Name" error={fieldErrors.name?.[0]}>
          <Input
            value={values.name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Category"
          value={values.category}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, category: value as LeaveTypeCategory }))}
          options={categoryOptions}
        />
        <Field label="Color token">
          <Input
            value={values.color_token}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, color_token: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Paid leave"
          value={values.is_paid ? 'yes' : 'no'}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, is_paid: value === 'yes' }))}
          options={[
            ['yes', 'Paid'],
            ['no', 'Unpaid'],
          ]}
        />
        <SelectField
          label="Approval path"
          value={values.requires_approval ? 'approval' : 'auto'}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({ ...current, requires_approval: value === 'approval' }))
          }
          options={[
            ['approval', 'Approval required'],
            ['auto', 'Auto approved'],
          ]}
        />
        <SelectField
          label="Half-day requests"
          value={values.allows_half_day ? 'yes' : 'no'}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, allows_half_day: value === 'yes' }))}
          options={[
            ['yes', 'Allowed'],
            ['no', 'Not allowed'],
          ]}
        />
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, status: value as LeaveTypeRecord['status'] }))}
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <Field label="Description" error={fieldErrors.description?.[0]}>
        <Textarea
          rows={4}
          value={values.description}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, description: event.target.value }))}
        />
      </Field>

      <FormNotice error={error} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {leaveType ? 'Save leave type' : 'Create leave type'}
        </Button>
      </div>
    </form>
  )
}

function LeavePolicyEditor({
  policy,
  leaveTypes,
  departments,
  locations,
  canManage,
  isSaving,
  onSave,
}: {
  policy: LeavePolicyRecord | null
  leaveTypes: LeaveTypeRecord[]
  departments: Array<{ id: number; name: string }>
  locations: Array<{ id: number; name: string }>
  canManage: boolean
  isSaving: boolean
  onSave: (values: LeavePolicyFormValues) => Promise<unknown>
}) {
  const [values, setValues] = useState<LeavePolicyFormValues>(
    policy
      ? {
          leave_type_id: String(policy.leave_type_id),
          annual_allowance_days: String(policy.annual_allowance_days),
          opening_balance_days: String(policy.opening_balance_days),
          accrual_frequency: policy.accrual_frequency,
          carry_forward_limit_days: String(policy.carry_forward_limit_days),
          encashment_limit_days: String(policy.encashment_limit_days),
          max_consecutive_days: String(policy.max_consecutive_days),
          min_notice_days: String(policy.min_notice_days),
          requires_documentation_after_days:
            policy.requires_documentation_after_days === null
              ? ''
              : String(policy.requires_documentation_after_days),
          applicable_department_id: policy.applicable_department ? String(policy.applicable_department.id) : '',
          applicable_location_id: policy.applicable_location ? String(policy.applicable_location.id) : '',
          status: policy.status,
        }
      : emptyPolicyForm,
  )
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setMessage(null)
    setFieldErrors({})

    if (!values.leave_type_id) {
      setError('Choose a leave type before saving policy rules.')
      return
    }

    const numericFields = [
      values.annual_allowance_days,
      values.opening_balance_days,
      values.carry_forward_limit_days,
      values.encashment_limit_days,
      values.max_consecutive_days,
      values.min_notice_days,
    ]

    if (numericFields.some((value) => value.trim() === '')) {
      setError('Annual allowance, balance, carry-forward, encashment, notice, and max consecutive values are required.')
      return
    }

    try {
      await onSave(values)
      setMessage(policy ? 'Leave policy updated successfully.' : 'Leave policy created successfully.')
    } catch (caughtError) {
      const nextError = caughtError as Error
      setError(nextError.message)

      if (nextError instanceof ApiRequestError) {
        setFieldErrors(nextError.fieldErrors)
      }
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      <div className="workspace-form-grid">
        <SelectField
          label="Leave type"
          value={values.leave_type_id}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, leave_type_id: value }))}
          options={[
            ['', 'Select leave type'] as [string, string],
            ...leaveTypes.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
          error={fieldErrors.leave_type_id?.[0]}
        />
        <SelectField
          label="Accrual frequency"
          value={values.accrual_frequency}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({ ...current, accrual_frequency: value as LeaveAccrualFrequency }))
          }
          options={accrualOptions}
        />
        <Field label="Annual allowance" error={fieldErrors.annual_allowance_days?.[0]}>
          <Input
            type="number"
            value={values.annual_allowance_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, annual_allowance_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Opening balance" error={fieldErrors.opening_balance_days?.[0]}>
          <Input
            type="number"
            value={values.opening_balance_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, opening_balance_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Carry-forward limit" error={fieldErrors.carry_forward_limit_days?.[0]}>
          <Input
            type="number"
            value={values.carry_forward_limit_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, carry_forward_limit_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Encashment limit" error={fieldErrors.encashment_limit_days?.[0]}>
          <Input
            type="number"
            value={values.encashment_limit_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, encashment_limit_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Max consecutive days" error={fieldErrors.max_consecutive_days?.[0]}>
          <Input
            type="number"
            value={values.max_consecutive_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, max_consecutive_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Minimum notice days" error={fieldErrors.min_notice_days?.[0]}>
          <Input
            type="number"
            value={values.min_notice_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, min_notice_days: event.target.value }))
            }
          />
        </Field>
        <Field label="Documentation after (days)" error={fieldErrors.requires_documentation_after_days?.[0]}>
          <Input
            type="number"
            value={values.requires_documentation_after_days}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({
                ...current,
                requires_documentation_after_days: event.target.value,
              }))
            }
          />
        </Field>
        <SelectField
          label="Department scope"
          value={values.applicable_department_id}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, applicable_department_id: value }))}
          options={[
            ['', 'All departments'] as [string, string],
            ...departments.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Location scope"
          value={values.applicable_location_id}
          disabled={!canManage || isSaving}
          onChange={(value) => setValues((current) => ({ ...current, applicable_location_id: value }))}
          options={[
            ['', 'All locations'] as [string, string],
            ...locations.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          onChange={(value) =>
            setValues((current) => ({ ...current, status: value as LeavePolicyRecord['status'] }))
          }
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <FormNotice error={error} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {policy ? 'Save policy rules' : 'Create policy rules'}
        </Button>
      </div>
    </form>
  )
}

function SelectField({
  label,
  value,
  options,
  onChange,
  disabled = false,
  error,
}: {
  label: string
  value: string
  options: Array<[string, string]>
  onChange: (value: string) => void
  disabled?: boolean
  error?: string
}) {
  return (
    <AppSelectField
      label={label}
      value={value}
      options={options}
      onChange={onChange}
      disabled={disabled}
      error={error}
      compact
    />
  )
}

function Field({
  label,
  children,
  error,
}: {
  label: string
  children: ReactNode
  error?: string
}) {
  return <WorkspaceField label={label} error={error}>{children}</WorkspaceField>
}

function EmptyState({ title, copy }: { title: string; copy: string }) {
  return <WorkspaceEmptyState title={title} copy={copy} />
}

function FormNotice({ error, message }: { error: string | null; message: string | null }) {
  return (
    <>
      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}
    </>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not updated'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function formatLeaveCategory(category: LeaveTypeCategory) {
  switch (category) {
    case 'earned':
      return 'Earned'
    case 'optional':
      return 'Optional holiday'
    case 'unpaid':
      return 'Unpaid'
    case 'casual':
      return 'Casual'
    case 'sick':
    default:
      return 'Sick'
  }
}

function formatAccrual(value: LeaveAccrualFrequency) {
  switch (value) {
    case 'monthly':
      return 'Monthly accrual'
    case 'quarterly':
      return 'Quarterly accrual'
    case 'annual':
      return 'Annual reset'
    case 'none':
    default:
      return 'No accrual'
  }
}

function formatLeaveStatus(status: LeaveRequestStatus) {
  return status.charAt(0).toUpperCase() + status.slice(1)
}
