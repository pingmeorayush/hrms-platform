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
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { usePerformanceRouteWorkspace } from './usePerformanceRouteWorkspace'
import { formatPerformanceDate, formatPerformanceLabel, goalStatusBadgeVariant } from '../utils'

type GoalTab = 'all' | 'focus' | 'archived'

interface GoalFormState {
  goal_code: string
  title: string
  description: string
  owner_employee_id: string
  performance_review_cycle_id: string
  due_on: string
  weight_percent: string
  success_metric_measure_type: string
  success_metric_target_value: string
  success_metric_unit: string
  success_metric_notes: string
  status: 'draft' | 'active' | 'archived'
}

const goalTabs: Array<{ id: GoalTab; label: string }> = [
  { id: 'all', label: 'All visible goals' },
  { id: 'focus', label: 'My focus' },
  { id: 'archived', label: 'Archived' },
]

export function PerformanceGoalsPage() {
  const workspace = usePerformanceRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<GoalTab>('focus')
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedGoalId, setSelectedGoalId] = useState<number | null>(null)
  const [form, setForm] = useState<GoalFormState>({
    goal_code: '',
    title: '',
    description: '',
    owner_employee_id: '',
    performance_review_cycle_id: '',
    due_on: '',
    weight_percent: '25',
    success_metric_measure_type: 'percentage',
    success_metric_target_value: '',
    success_metric_unit: '',
    success_metric_notes: '',
    status: 'active',
  })

  const filteredGoals = useMemo(() => {
    if (!data) {
      return []
    }

    const linkedEmployeeId = data.meta.linked_employee_id
    const base = activeTab === 'archived'
      ? data.goals.filter((goal) => goal.status === 'archived')
      : activeTab === 'focus'
        ? data.goals.filter((goal) => linkedEmployeeId ? goal.owner_employee?.id === linkedEmployeeId : goal.status === 'active')
        : data.goals

    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return base
    }

    return base.filter((goal) =>
      [goal.goal_code, goal.title, goal.owner_employee?.full_name ?? '', goal.department?.name ?? '']
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [activeTab, data, searchTerm])

  const selectedGoal =
    filteredGoals.find((goal) => goal.id === selectedGoalId) ??
    data?.goals.find((goal) => goal.id === selectedGoalId) ??
    filteredGoals[0] ??
    null

  const handleCreateGoal = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!form.goal_code || !form.title || !form.owner_employee_id || !form.due_on) {
      return
    }

    await workspace.createGoal({
      goal_code: form.goal_code,
      goal_type: 'library',
      title: form.title,
      description: form.description || null,
      owner_employee_id: Number(form.owner_employee_id),
      performance_review_cycle_id: form.performance_review_cycle_id ? Number(form.performance_review_cycle_id) : null,
      department_id:
        data?.employees.find((employee) => employee.id === Number(form.owner_employee_id))?.department.id ?? null,
      due_on: form.due_on,
      weight_percent: Number(form.weight_percent),
      success_metric: form.success_metric_target_value
        ? {
            measure_type: form.success_metric_measure_type || null,
            target_value: form.success_metric_target_value,
            unit: form.success_metric_unit || null,
            notes: form.success_metric_notes || null,
          }
        : null,
      status: form.status,
    })

    setForm({
      goal_code: '',
      title: '',
      description: '',
      owner_employee_id: '',
      performance_review_cycle_id: '',
      due_on: '',
      weight_percent: '25',
      success_metric_measure_type: 'percentage',
      success_metric_target_value: '',
      success_metric_unit: '',
      success_metric_notes: '',
      status: 'active',
    })
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading goals" copy="Resolving assigned goals, review-cycle context, and weight posture." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Goals unavailable" copy={workspace.error.message || 'The goals workspace could not be loaded.'} />
  }

  if (!data || !workspace.canViewPerformance) {
    return <WorkspaceEmptyState title="Goals unavailable" copy="This session does not currently resolve to performance goal visibility." />
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Goals and delivery focus</Badge>
            <CardTitle>Goals and ownership workspace</CardTitle>
            <CardDescription>
              Review goal libraries, track due dates and weight balance, and surface employee focus areas before the review cycle moves into calibration.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo goal posture' : 'Live goal posture'}
            </Badge>
            {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
            {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Performance goal tabs">
            {goalTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                isActive={activeTab === tab.id}
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          <WorkspaceField>
            <span>Search goals</span>
            <Input
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
              placeholder="Search by code, title, owner, or department"
            />
          </WorkspaceField>

          <WorkspaceSplit
            primary={(
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Goal</TableHead>
                      <TableHead>Owner</TableHead>
                      <TableHead>Due</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredGoals.map((goal) => (
                      <TableRow key={goal.id} onClick={() => setSelectedGoalId(goal.id)} className="cursor-pointer">
                        <TableCell>
                          <div className="space-y-1">
                            <div className="font-medium text-foreground">{goal.title}</div>
                            <div className="text-xs text-muted-foreground">{goal.goal_code}</div>
                          </div>
                        </TableCell>
                        <TableCell>{goal.owner_employee?.full_name ?? 'Unassigned'}</TableCell>
                        <TableCell>{formatPerformanceDate(goal.due_on)}</TableCell>
                        <TableCell>
                          <Badge variant={goalStatusBadgeVariant(goal.status)}>{formatPerformanceLabel(goal.status)}</Badge>
                        </TableCell>
                      </TableRow>
                    ))}
                    {!filteredGoals.length ? (
                      <TableRow>
                        <TableCell colSpan={4} className="text-sm text-muted-foreground">
                          No goals match the current focus and search filters.
                        </TableCell>
                      </TableRow>
                    ) : null}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            )}
            secondary={(
              <div className="space-y-4">
                {selectedGoal ? (
                  <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                    <div className="space-y-1">
                      <div className="flex items-center justify-between gap-3">
                        <h2 className="text-base font-semibold text-foreground">{selectedGoal.title}</h2>
                        <Badge variant={goalStatusBadgeVariant(selectedGoal.status)}>{formatPerformanceLabel(selectedGoal.status)}</Badge>
                      </div>
                      <p className="text-sm text-muted-foreground">{selectedGoal.goal_code}</p>
                    </div>
                    <dl className="mt-4 space-y-3 text-sm">
                      <div>
                        <dt className="font-medium text-foreground">Owner</dt>
                        <dd className="text-muted-foreground">{selectedGoal.owner_employee?.full_name ?? 'Unassigned'}</dd>
                      </div>
                      <div>
                        <dt className="font-medium text-foreground">Review cycle</dt>
                        <dd className="text-muted-foreground">{selectedGoal.review_cycle?.name ?? 'Library only'}</dd>
                      </div>
                      <div>
                        <dt className="font-medium text-foreground">Weight and due date</dt>
                        <dd className="text-muted-foreground">
                          {selectedGoal.weight_percent}% · {formatPerformanceDate(selectedGoal.due_on)}
                        </dd>
                      </div>
                      <div>
                        <dt className="font-medium text-foreground">Success metric</dt>
                        <dd className="text-muted-foreground">
                          {selectedGoal.success_metric
                            ? `${selectedGoal.success_metric.measure_type ?? 'metric'} · ${selectedGoal.success_metric.target_value ?? 'Target pending'} ${selectedGoal.success_metric.unit ?? ''}`.trim()
                            : 'No explicit metric configured'}
                        </dd>
                      </div>
                      <div>
                        <dt className="font-medium text-foreground">Narrative</dt>
                        <dd className="text-muted-foreground">{selectedGoal.description ?? 'No supporting narrative was recorded for this goal yet.'}</dd>
                      </div>
                    </dl>
                  </div>
                ) : null}

                {workspace.canManagePerformance ? (
                  <form onSubmit={handleCreateGoal} className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                    <div className="space-y-1">
                      <h2 className="text-base font-semibold text-foreground">Create goal library entry</h2>
                      <p className="text-sm text-muted-foreground">
                        Add a goal record that managers and HR can immediately pull into the active cycle posture.
                      </p>
                    </div>
                    <div className="mt-4 grid gap-3 sm:grid-cols-2">
                      <WorkspaceField>
                        <span>Goal code</span>
                        <Input value={form.goal_code} onChange={(event) => setForm((current) => ({ ...current, goal_code: event.target.value.toUpperCase() }))} placeholder="ENG-QUALITY-02" />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Status</span>
                        <SelectField
                          label="Status"
                          value={form.status}
                          onChange={(value) => setForm((current) => ({ ...current, status: value as GoalFormState['status'] }))}
                          options={[
                            ['active', 'Active'],
                            ['draft', 'Draft'],
                            ['archived', 'Archived'],
                          ]}
                        />
                      </WorkspaceField>
                      <WorkspaceField className="sm:col-span-2">
                        <span>Title</span>
                        <Input value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} placeholder="Reduce regression escape rate" />
                      </WorkspaceField>
                      <WorkspaceField className="sm:col-span-2">
                        <span>Description</span>
                        <Textarea value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} rows={3} />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Owner employee</span>
                        <SelectField
                          label="Owner employee"
                          value={form.owner_employee_id}
                          onChange={(value) => setForm((current) => ({ ...current, owner_employee_id: value }))}
                          options={[
                            { value: '', label: 'Select owner' },
                            ...data.employees.map((employee) => ({
                              value: String(employee.id),
                              label: `${employee.full_name} · ${employee.employee_code}`,
                            })),
                          ]}
                        />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Review cycle</span>
                        <SelectField
                          label="Review cycle"
                          value={form.performance_review_cycle_id}
                          onChange={(value) => setForm((current) => ({ ...current, performance_review_cycle_id: value }))}
                          options={[
                            { value: '', label: 'Library only' },
                            ...data.reviewCycles.map((cycle) => ({
                              value: String(cycle.id),
                              label: `${cycle.name} · ${formatPerformanceLabel(cycle.status)}`,
                            })),
                          ]}
                        />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Due date</span>
                        <Input type="date" value={form.due_on} onChange={(event) => setForm((current) => ({ ...current, due_on: event.target.value }))} />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Weight percent</span>
                        <Input type="number" min="1" max="100" step="0.01" value={form.weight_percent} onChange={(event) => setForm((current) => ({ ...current, weight_percent: event.target.value }))} />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Measure type</span>
                        <Input value={form.success_metric_measure_type} onChange={(event) => setForm((current) => ({ ...current, success_metric_measure_type: event.target.value }))} placeholder="percentage" />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Target value</span>
                        <Input value={form.success_metric_target_value} onChange={(event) => setForm((current) => ({ ...current, success_metric_target_value: event.target.value }))} placeholder="25" />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Unit</span>
                        <Input value={form.success_metric_unit} onChange={(event) => setForm((current) => ({ ...current, success_metric_unit: event.target.value }))} placeholder="%" />
                      </WorkspaceField>
                      <WorkspaceField className="sm:col-span-2">
                        <span>Metric notes</span>
                        <Textarea value={form.success_metric_notes} onChange={(event) => setForm((current) => ({ ...current, success_metric_notes: event.target.value }))} rows={2} />
                      </WorkspaceField>
                    </div>
                    <div className="mt-4 flex justify-end">
                      <Button type="submit" disabled={workspace.pendingActionLabel !== null}>Create goal</Button>
                    </div>
                  </form>
                ) : null}
              </div>
            )}
          />
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
