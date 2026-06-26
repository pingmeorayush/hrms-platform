import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import type { CreateLearningAssignmentInput, LearningAudienceType } from '../types'
import { learningAudienceLabel, learningDueStateVariant, learningRenewalVariant, formatLearningDate } from '../utils'
import { useLearningRouteWorkspace } from './useLearningRouteWorkspace'

type AssignmentTab = 'all' | 'overdue' | 'renewals'

interface AssignmentFormState {
  learning_item_id: string
  audience_type: LearningAudienceType
  audience_value: string
  due_on: string
  requires_completion_evidence: 'inherit' | 'yes' | 'no'
  renewal_frequency_months: string
  default_due_days: string
  notes: string
}

const assignmentTabs: Array<{ id: AssignmentTab; label: string }> = [
  { id: 'all', label: 'All assignments' },
  { id: 'overdue', label: 'Overdue pressure' },
  { id: 'renewals', label: 'Renewal pressure' },
]

function defaultAssignmentForm(): AssignmentFormState {
  return {
    learning_item_id: '',
    audience_type: 'employee',
    audience_value: '',
    due_on: '',
    requires_completion_evidence: 'inherit',
    renewal_frequency_months: '',
    default_due_days: '',
    notes: '',
  }
}

export function LearningAssignmentsPage() {
  const workspace = useLearningRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<AssignmentTab>('all')
  const [selectedAssignmentId, setSelectedAssignmentId] = useState<number | null>(null)
  const [form, setForm] = useState<AssignmentFormState>(defaultAssignmentForm())

  const filteredAssignments = useMemo(() => {
    if (!data) {
      return []
    }

    return data.assignments.filter((assignment) => {
      if (activeTab === 'overdue') {
        return (assignment.target_summary?.overdue_count ?? 0) > 0
      }

      if (activeTab === 'renewals') {
        return (assignment.target_summary?.renewal_overdue_count ?? 0) > 0
      }

      return true
    })
  }, [activeTab, data])

  const selectedAssignment =
    filteredAssignments.find((assignment) => assignment.id === selectedAssignmentId) ??
    data?.assignments.find((assignment) => assignment.id === selectedAssignmentId) ??
    filteredAssignments[0] ??
    null

  const audienceValueOptions = useMemo(() => {
    if (!data) {
      return []
    }

    switch (form.audience_type) {
      case 'department':
        return data.departments.map((department) => [String(department.id), department.name] as [string, string])
      case 'designation':
        return data.designations.map((designation) => [String(designation.id), designation.name] as [string, string])
      case 'employee':
        return data.employees.map((employee) => [String(employee.id), `${employee.full_name} · ${employee.employee_code}`] as [string, string])
      default:
        return [['', 'All active employees']] as [string, string][]
    }
  }, [data, form.audience_type])

  const handleCreateAssignment = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!form.learning_item_id) {
      return
    }

    const payload: CreateLearningAssignmentInput = {
      learning_item_id: Number(form.learning_item_id),
      audience_type: form.audience_type,
      audience_rules:
        form.audience_type === 'employee'
          ? { employee_ids: form.audience_value ? [Number(form.audience_value)] : [] }
          : form.audience_type === 'department'
            ? { department_ids: form.audience_value ? [Number(form.audience_value)] : [] }
            : form.audience_type === 'designation'
              ? { designation_ids: form.audience_value ? [Number(form.audience_value)] : [] }
              : {},
      due_on: form.due_on || null,
      requires_completion_evidence:
        form.requires_completion_evidence === 'inherit'
          ? null
          : form.requires_completion_evidence === 'yes',
      renewal_frequency_months: form.renewal_frequency_months ? Number(form.renewal_frequency_months) : null,
      default_due_days: form.default_due_days ? Number(form.default_due_days) : null,
      notes: form.notes.trim() || null,
    }

    await workspace.createLearningAssignment(payload)
    setForm(defaultAssignmentForm())
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading assignments" copy="Resolving assignment targeting, due-state posture, and visible learner targets." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Assignments unavailable" copy={workspace.error.message || 'The learning assignments workspace could not be loaded.'} />
  }

  if (!data || !workspace.canViewLearning) {
    return <WorkspaceEmptyState title="Assignments unavailable" copy="This session does not currently resolve to learning visibility." />
  }

  if (!workspace.canManageCatalog && !workspace.canAssignLearning) {
    return (
      <WorkspaceEmptyState
        title="Assignments unavailable"
        copy="This route is limited to sessions that can assign or administer learning."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Assignment operations</Badge>
            <CardTitle>Assignment and compliance queue</CardTitle>
            <CardDescription>
              Track audience targeting, due-date pressure, renewal posture, and visible employee-level completion state from one routed admin queue.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo assignment surface' : 'Live assignment surface'}
            </Badge>
            {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
            {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Learning assignment tabs">
            {assignmentTabs.map((tab) => (
              <WorkspaceTabButton key={tab.id} isActive={activeTab === tab.id} onClick={() => setActiveTab(tab.id)}>
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          <WorkspaceSplit
            primary={(
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Assignment</TableHead>
                      <TableHead>Audience</TableHead>
                      <TableHead>Due</TableHead>
                      <TableHead>Targets</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredAssignments.map((assignment) => (
                      <TableRow
                        key={assignment.id}
                        className={assignment.id === selectedAssignment?.id ? 'bg-primary/[0.06]' : 'cursor-pointer'}
                        onClick={() => setSelectedAssignmentId(assignment.id)}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{assignment.item?.title ?? 'Assignment'}</p>
                            <p className="text-xs text-muted-foreground">{assignment.assignment_code}</p>
                          </div>
                        </TableCell>
                        <TableCell>{learningAudienceLabel(assignment.audience_type)}</TableCell>
                        <TableCell>{formatLearningDate(assignment.due_on)}</TableCell>
                        <TableCell>{assignment.target_count}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            )}
            secondary={(
              <div className="space-y-3.5">
                <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                  <div className="space-y-1">
                    <h2 className="text-base font-semibold text-foreground">Selected assignment detail</h2>
                    <p className="text-sm text-muted-foreground">
                      Review the visible target posture before expanding assignment coverage.
                    </p>
                  </div>
                  {selectedAssignment ? (
                    <div className="mt-4 space-y-1.5">
                      <WorkspaceSummaryRow label="Assignment code" value={selectedAssignment.assignment_code} />
                      <WorkspaceSummaryRow label="Audience" value={learningAudienceLabel(selectedAssignment.audience_type)} />
                      <WorkspaceSummaryRow label="Due on" value={formatLearningDate(selectedAssignment.due_on)} />
                      <WorkspaceSummaryRow label="Target count" value={selectedAssignment.target_count} />
                      <WorkspaceSummaryRow label="Completed" value={selectedAssignment.completion_count} />
                      <WorkspaceSummaryRow label="Overdue" value={selectedAssignment.target_summary?.overdue_count ?? 0} />
                      <WorkspaceSummaryRow
                        label="Renewal overdue"
                        value={selectedAssignment.target_summary?.renewal_overdue_count ?? 0}
                      />
                    </div>
                  ) : (
                    <p className="mt-4 text-sm text-muted-foreground">Select an assignment to inspect the visible target population.</p>
                  )}
                </div>

                {selectedAssignment?.targets?.length ? (
                  <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                    <div className="space-y-1">
                      <h2 className="text-base font-semibold text-foreground">Visible target sample</h2>
                      <p className="text-sm text-muted-foreground">
                        The first visible learners and their compliance posture for this assignment.
                      </p>
                    </div>
                    <div className="mt-4 space-y-3">
                      {selectedAssignment.targets.slice(0, 4).map((target) => (
                        <div key={target.id} className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3">
                          <div className="flex flex-wrap items-center justify-between gap-2">
                            <div>
                              <p className="text-sm font-semibold text-foreground">{target.employee?.full_name ?? 'Learner'}</p>
                              <p className="text-xs text-muted-foreground">
                                Due {formatLearningDate(target.due_on)} · {target.employee?.employee_code ?? 'No code'}
                              </p>
                            </div>
                            <div className="flex flex-wrap items-center gap-2">
                              <Badge variant={learningDueStateVariant(target.due_state)}>{target.due_state.replace(/_/g, ' ')}</Badge>
                              <Badge variant={learningRenewalVariant(target.renewal_posture)}>
                                {target.renewal_posture.replace(/_/g, ' ')}
                              </Badge>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                ) : null}
              </div>
            )}
          />

          <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
            <div className="space-y-1">
              <h2 className="text-base font-semibold text-foreground">Create learning assignment</h2>
              <p className="text-sm text-muted-foreground">
                Issue a new compliance or enablement assignment with explicit audience targeting and due-state expectations.
              </p>
            </div>
            <form className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3" onSubmit={handleCreateAssignment}>
              <SelectField
                label="Learning item"
                value={form.learning_item_id}
                options={[
                  ['', 'Select learning item'],
                  ...data.items
                    .filter((item) => item.status === 'active')
                    .map((item) => [String(item.id), `${item.title} · ${item.code}`] as [string, string]),
                ]}
                onChange={(value) => setForm((current) => ({ ...current, learning_item_id: value }))}
              />
              <SelectField
                label="Audience type"
                value={form.audience_type}
                options={[
                  ['employee', 'Employee'],
                  ['department', 'Department'],
                  ['designation', 'Designation'],
                  ['all_active', 'All active employees'],
                ]}
                onChange={(value) =>
                  setForm((current) => ({
                    ...current,
                    audience_type: value as LearningAudienceType,
                    audience_value: '',
                  }))
                }
              />
              <SelectField
                label="Audience value"
                value={form.audience_value}
                options={audienceValueOptions}
                onChange={(value) => setForm((current) => ({ ...current, audience_value: value }))}
                disabled={form.audience_type === 'all_active'}
              />
              <WorkspaceField label="Due on">
                <Input value={form.due_on} type="date" onChange={(event) => setForm((current) => ({ ...current, due_on: event.target.value }))} />
              </WorkspaceField>
              <SelectField
                label="Evidence posture"
                value={form.requires_completion_evidence}
                options={[
                  ['inherit', 'Inherit from item'],
                  ['yes', 'Require evidence'],
                  ['no', 'Evidence optional'],
                ]}
                onChange={(value) =>
                  setForm((current) => ({ ...current, requires_completion_evidence: value as AssignmentFormState['requires_completion_evidence'] }))
                }
              />
              <WorkspaceField label="Renewal months">
                <Input
                  value={form.renewal_frequency_months}
                  onChange={(event) => setForm((current) => ({ ...current, renewal_frequency_months: event.target.value }))}
                />
              </WorkspaceField>
              <WorkspaceField label="Default due days" className="md:col-span-2 xl:col-span-1">
                <Input
                  value={form.default_due_days}
                  onChange={(event) => setForm((current) => ({ ...current, default_due_days: event.target.value }))}
                />
              </WorkspaceField>
              <WorkspaceField label="Notes" className="md:col-span-2 xl:col-span-2">
                <Textarea value={form.notes} onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))} rows={4} />
              </WorkspaceField>
              <div className="md:col-span-2 xl:col-span-3 flex flex-wrap items-center gap-2">
                <Button type="submit">Create learning assignment</Button>
                <Button type="button" variant="secondary" onClick={() => setForm(defaultAssignmentForm())}>
                  Reset form
                </Button>
              </div>
            </form>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
