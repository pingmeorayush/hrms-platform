import { useState } from 'react'
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
import { cycleStatusBadgeVariant, formatPerformanceDate, formatPerformanceLabel } from '../utils'

type CycleTab = 'cycles' | 'competencies'

interface CycleFormState {
  code: string
  name: string
  cycle_type: 'annual' | 'half_yearly' | 'quarterly' | 'probation' | 'project'
  starts_on: string
  ends_on: string
  self_review_due_on: string
  manager_review_due_on: string
  calibration_starts_on: string
  calibration_ends_on: string
  publish_on: string
  peer_reviewer_slots: string
  self_review_required: boolean
  manager_review_required: boolean
  allow_hr_reviewer: boolean
  status: 'draft' | 'scheduled' | 'active' | 'archived'
  required_competency_ids: number[]
}

interface CompetencyFormState {
  code: string
  name: string
  category: string
  description: string
  status: 'active' | 'inactive' | 'archived'
}

const cycleTabs: Array<{ id: CycleTab; label: string }> = [
  { id: 'cycles', label: 'Review cycles' },
  { id: 'competencies', label: 'Competencies' },
]

export function PerformanceCyclesPage() {
  const workspace = usePerformanceRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<CycleTab>('cycles')
  const [selectedCycleId, setSelectedCycleId] = useState<number | null>(null)
  const [selectedCompetencyId, setSelectedCompetencyId] = useState<number | null>(null)
  const [cycleForm, setCycleForm] = useState<CycleFormState>({
    code: '',
    name: '',
    cycle_type: 'quarterly',
    starts_on: '',
    ends_on: '',
    self_review_due_on: '',
    manager_review_due_on: '',
    calibration_starts_on: '',
    calibration_ends_on: '',
    publish_on: '',
    peer_reviewer_slots: '0',
    self_review_required: true,
    manager_review_required: true,
    allow_hr_reviewer: true,
    status: 'scheduled',
    required_competency_ids: [],
  })
  const [competencyForm, setCompetencyForm] = useState<CompetencyFormState>({
    code: '',
    name: '',
    category: 'Leadership',
    description: '',
    status: 'active',
  })

  const selectedCycle = data?.reviewCycles.find((cycle) => cycle.id === selectedCycleId) ?? data?.reviewCycles[0] ?? null
  const selectedCompetency =
    data?.competencies.find((competency) => competency.id === selectedCompetencyId) ?? data?.competencies[0] ?? null

  const handleCreateCycle = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!cycleForm.code || !cycleForm.name || !cycleForm.starts_on || !cycleForm.ends_on) {
      return
    }

    const departmentIds = [...new Set(data?.employees.map((employee) => employee.department.id) ?? [])]
    const designationIds = [...new Set(data?.employees.map((employee) => employee.designation.id) ?? [])]

    await workspace.createReviewCycle({
      code: cycleForm.code,
      name: cycleForm.name,
      cycle_type: cycleForm.cycle_type,
      starts_on: cycleForm.starts_on,
      ends_on: cycleForm.ends_on,
      self_review_due_on: cycleForm.self_review_due_on || null,
      manager_review_due_on: cycleForm.manager_review_due_on || null,
      calibration_starts_on: cycleForm.calibration_starts_on || null,
      calibration_ends_on: cycleForm.calibration_ends_on || null,
      publish_on: cycleForm.publish_on || null,
      participant_rules: {
        population: {
          employment_statuses: ['active'],
          employment_types: ['full_time'],
          department_ids: departmentIds,
          designation_ids: designationIds,
        },
        reviewers: {
          self_review_required: cycleForm.self_review_required,
          manager_review_required: cycleForm.manager_review_required,
          peer_reviewer_slots: Number(cycleForm.peer_reviewer_slots),
          allow_hr_reviewer: cycleForm.allow_hr_reviewer,
        },
      },
      review_template: {
        sections: [
          { key: 'impact', label: 'Impact delivered', weight_percent: 40, required: true },
          { key: 'collaboration', label: 'Collaboration', weight_percent: 30, required: true },
          { key: 'growth', label: 'Growth posture', weight_percent: 30, required: true },
        ],
        rating_scale: {
          min: 1,
          max: 5,
        },
      },
      competency_visibility: {
        enabled: true,
        visible_to_employee: true,
        visible_to_manager: true,
        visible_to_hr: true,
        required_competency_ids: cycleForm.required_competency_ids,
      },
      status: cycleForm.status,
    })

    setCycleForm({
      code: '',
      name: '',
      cycle_type: 'quarterly',
      starts_on: '',
      ends_on: '',
      self_review_due_on: '',
      manager_review_due_on: '',
      calibration_starts_on: '',
      calibration_ends_on: '',
      publish_on: '',
      peer_reviewer_slots: '0',
      self_review_required: true,
      manager_review_required: true,
      allow_hr_reviewer: true,
      status: 'scheduled',
      required_competency_ids: [],
    })
  }

  const handleCreateCompetency = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!competencyForm.code || !competencyForm.name) {
      return
    }

    await workspace.createCompetency({
      code: competencyForm.code,
      name: competencyForm.name,
      category: competencyForm.category,
      description: competencyForm.description || null,
      scale_definition: {
        min_rating: 1,
        max_rating: 5,
        labels: [
          { value: 1, label: 'Needs support' },
          { value: 2, label: 'Developing' },
          { value: 3, label: 'Consistent' },
          { value: 4, label: 'Strong' },
          { value: 5, label: 'Role model' },
        ],
      },
      status: competencyForm.status,
    })

    setCompetencyForm({
      code: '',
      name: '',
      category: 'Leadership',
      description: '',
      status: 'active',
    })
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading review cycles" copy="Resolving cycle timing, competencies, and review template posture." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Review cycles unavailable" copy={workspace.error.message || 'The review cycle workspace could not be loaded.'} />
  }

  if (!data || !workspace.canViewPerformance) {
    return <WorkspaceEmptyState title="Review cycles unavailable" copy="This session does not currently resolve to performance cycle visibility." />
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Cycle and competency setup</Badge>
            <CardTitle>Review-cycle and competency baseline</CardTitle>
            <CardDescription>
              Keep timing, participant rules, review-template posture, and competency visibility explicit before employees and managers begin submissions.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo cycle posture' : 'Live cycle posture'}
            </Badge>
            {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
            {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Performance cycle tabs">
            {cycleTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                isActive={activeTab === tab.id}
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          {activeTab === 'cycles' ? (
            <WorkspaceSplit
              primary={(
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Cycle</TableHead>
                        <TableHead>Window</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {data.reviewCycles.map((cycle) => (
                        <TableRow key={cycle.id} onClick={() => setSelectedCycleId(cycle.id)} className="cursor-pointer">
                          <TableCell>
                            <div className="space-y-1">
                              <div className="font-medium text-foreground">{cycle.name}</div>
                              <div className="text-xs text-muted-foreground">{cycle.code}</div>
                            </div>
                          </TableCell>
                          <TableCell>{formatPerformanceDate(cycle.starts_on)} → {formatPerformanceDate(cycle.ends_on)}</TableCell>
                          <TableCell>
                            <Badge variant={cycleStatusBadgeVariant(cycle.status)}>{formatPerformanceLabel(cycle.status)}</Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              )}
              secondary={(
                <div className="space-y-4">
                  {selectedCycle ? (
                    <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                      <div className="flex items-center justify-between gap-3">
                        <div className="space-y-1">
                          <h2 className="text-base font-semibold text-foreground">{selectedCycle.name}</h2>
                          <p className="text-sm text-muted-foreground">{selectedCycle.code} · {formatPerformanceLabel(selectedCycle.cycle_type)}</p>
                        </div>
                        <Badge variant={cycleStatusBadgeVariant(selectedCycle.status)}>{formatPerformanceLabel(selectedCycle.status)}</Badge>
                      </div>
                      <dl className="mt-4 space-y-3 text-sm">
                        <div>
                          <dt className="font-medium text-foreground">Review windows</dt>
                          <dd className="text-muted-foreground">
                            Self due {formatPerformanceDate(selectedCycle.self_review_due_on)} · Manager due {formatPerformanceDate(selectedCycle.manager_review_due_on)}
                          </dd>
                        </div>
                        <div>
                          <dt className="font-medium text-foreground">Calibration and publish</dt>
                          <dd className="text-muted-foreground">
                            Calibration {formatPerformanceDate(selectedCycle.calibration_starts_on)} → {formatPerformanceDate(selectedCycle.calibration_ends_on)} · Publish {formatPerformanceDate(selectedCycle.publish_on)}
                          </dd>
                        </div>
                        <div>
                          <dt className="font-medium text-foreground">Participants</dt>
                          <dd className="text-muted-foreground">
                            Self review {selectedCycle.participant_rules.reviewers.self_review_required ? 'enabled' : 'disabled'} · Manager review {selectedCycle.participant_rules.reviewers.manager_review_required ? 'enabled' : 'disabled'} · Peer slots {selectedCycle.participant_rules.reviewers.peer_reviewer_slots}
                          </dd>
                        </div>
                        <div>
                          <dt className="font-medium text-foreground">Template sections</dt>
                          <dd className="space-y-1 text-muted-foreground">
                            {selectedCycle.review_template.sections.map((section) => (
                              <div key={section.key}>
                                {section.label} · {section.weight_percent}% · {section.required ? 'Required' : 'Optional'}
                              </div>
                            ))}
                          </dd>
                        </div>
                      </dl>
                    </div>
                  ) : null}

                  {workspace.canManagePerformance ? (
                    <form onSubmit={handleCreateCycle} className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                      <div className="space-y-1">
                        <h2 className="text-base font-semibold text-foreground">Create review cycle</h2>
                        <p className="text-sm text-muted-foreground">
                          Launch a cycle quickly with a standard three-section template and explicit review timing.
                        </p>
                      </div>
                      <div className="mt-4 grid gap-3 sm:grid-cols-2">
                        <WorkspaceField>
                          <span>Cycle code</span>
                          <Input value={cycleForm.code} onChange={(event) => setCycleForm((current) => ({ ...current, code: event.target.value.toUpperCase() }))} placeholder="FY26-Q4" />
                        </WorkspaceField>
                        <WorkspaceField>
                        <span>Status</span>
                        <SelectField
                          label="Status"
                          value={cycleForm.status}
                          onChange={(value) => setCycleForm((current) => ({ ...current, status: value as CycleFormState['status'] }))}
                          options={[
                            ['scheduled', 'Scheduled'],
                            ['draft', 'Draft'],
                            ['active', 'Active'],
                              ['archived', 'Archived'],
                            ]}
                          />
                        </WorkspaceField>
                        <WorkspaceField className="sm:col-span-2">
                          <span>Name</span>
                          <Input value={cycleForm.name} onChange={(event) => setCycleForm((current) => ({ ...current, name: event.target.value }))} placeholder="FY26 Q4 Review Cycle" />
                        </WorkspaceField>
                        <WorkspaceField>
                        <span>Cycle type</span>
                        <SelectField
                          label="Cycle type"
                          value={cycleForm.cycle_type}
                          onChange={(value) => setCycleForm((current) => ({ ...current, cycle_type: value as CycleFormState['cycle_type'] }))}
                          options={[
                            ['annual', 'Annual'],
                            ['half_yearly', 'Half yearly'],
                            ['quarterly', 'Quarterly'],
                              ['probation', 'Probation'],
                              ['project', 'Project'],
                            ]}
                          />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Peer reviewer slots</span>
                          <Input type="number" min="0" max="5" value={cycleForm.peer_reviewer_slots} onChange={(event) => setCycleForm((current) => ({ ...current, peer_reviewer_slots: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Starts on</span>
                          <Input type="date" value={cycleForm.starts_on} onChange={(event) => setCycleForm((current) => ({ ...current, starts_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Ends on</span>
                          <Input type="date" value={cycleForm.ends_on} onChange={(event) => setCycleForm((current) => ({ ...current, ends_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Self review due</span>
                          <Input type="date" value={cycleForm.self_review_due_on} onChange={(event) => setCycleForm((current) => ({ ...current, self_review_due_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Manager review due</span>
                          <Input type="date" value={cycleForm.manager_review_due_on} onChange={(event) => setCycleForm((current) => ({ ...current, manager_review_due_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Calibration starts</span>
                          <Input type="date" value={cycleForm.calibration_starts_on} onChange={(event) => setCycleForm((current) => ({ ...current, calibration_starts_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Calibration ends</span>
                          <Input type="date" value={cycleForm.calibration_ends_on} onChange={(event) => setCycleForm((current) => ({ ...current, calibration_ends_on: event.target.value }))} />
                        </WorkspaceField>
                        <WorkspaceField className="sm:col-span-2">
                          <span>Publish on</span>
                          <Input type="date" value={cycleForm.publish_on} onChange={(event) => setCycleForm((current) => ({ ...current, publish_on: event.target.value }))} />
                        </WorkspaceField>
                        <div className="sm:col-span-2 grid gap-2 rounded-2xl border border-line/70 bg-panel/70 px-3 py-3 text-sm text-muted-foreground">
                          <label className="flex items-center gap-2">
                            <input type="checkbox" checked={cycleForm.self_review_required} onChange={(event) => setCycleForm((current) => ({ ...current, self_review_required: event.target.checked }))} />
                            Self review required
                          </label>
                          <label className="flex items-center gap-2">
                            <input type="checkbox" checked={cycleForm.manager_review_required} onChange={(event) => setCycleForm((current) => ({ ...current, manager_review_required: event.target.checked }))} />
                            Manager review required
                          </label>
                          <label className="flex items-center gap-2">
                            <input type="checkbox" checked={cycleForm.allow_hr_reviewer} onChange={(event) => setCycleForm((current) => ({ ...current, allow_hr_reviewer: event.target.checked }))} />
                            HR reviewer can participate
                          </label>
                        </div>
                        <div className="sm:col-span-2 rounded-2xl border border-line/70 bg-panel/70 px-3 py-3">
                          <div className="text-sm font-medium text-foreground">Required competencies</div>
                          <div className="mt-2 grid gap-2 sm:grid-cols-2">
                            {data.competencies.filter((competency) => competency.status === 'active').map((competency) => (
                              <label key={competency.id} className="flex items-center gap-2 text-sm text-muted-foreground">
                                <input
                                  type="checkbox"
                                  checked={cycleForm.required_competency_ids.includes(competency.id)}
                                  onChange={(event) =>
                                    setCycleForm((current) => ({
                                      ...current,
                                      required_competency_ids: event.target.checked
                                        ? [...current.required_competency_ids, competency.id]
                                        : current.required_competency_ids.filter((id) => id !== competency.id),
                                    }))
                                  }
                                />
                                {competency.name}
                              </label>
                            ))}
                          </div>
                        </div>
                      </div>
                      <div className="mt-4 flex justify-end">
                        <Button type="submit" disabled={workspace.pendingActionLabel !== null}>Create cycle</Button>
                      </div>
                    </form>
                  ) : null}
                </div>
              )}
            />
          ) : (
            <WorkspaceSplit
              primary={(
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Competency</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {data.competencies.map((competency) => (
                        <TableRow key={competency.id} onClick={() => setSelectedCompetencyId(competency.id)} className="cursor-pointer">
                          <TableCell>
                            <div className="space-y-1">
                              <div className="font-medium text-foreground">{competency.name}</div>
                              <div className="text-xs text-muted-foreground">{competency.code}</div>
                            </div>
                          </TableCell>
                          <TableCell>{competency.category}</TableCell>
                          <TableCell>
                            <Badge variant={competency.status === 'active' ? 'success' : competency.status === 'archived' ? 'neutral' : 'warning'}>
                              {formatPerformanceLabel(competency.status)}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              )}
              secondary={(
                <div className="space-y-4">
                  {selectedCompetency ? (
                    <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                      <div className="space-y-1">
                        <h2 className="text-base font-semibold text-foreground">{selectedCompetency.name}</h2>
                        <p className="text-sm text-muted-foreground">{selectedCompetency.code} · {selectedCompetency.category}</p>
                      </div>
                      <p className="mt-4 text-sm text-muted-foreground">{selectedCompetency.description ?? 'No competency description has been recorded yet.'}</p>
                      <div className="mt-4 space-y-2">
                        {selectedCompetency.scale_definition.labels.map((label) => (
                          <div key={label.value} className="flex items-center justify-between rounded-xl border border-line/70 bg-panel/70 px-3 py-2 text-sm">
                            <span className="font-medium text-foreground">Rating {label.value}</span>
                            <span className="text-muted-foreground">{label.label}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  ) : null}

                  {workspace.canManagePerformance ? (
                    <form onSubmit={handleCreateCompetency} className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                      <div className="space-y-1">
                        <h2 className="text-base font-semibold text-foreground">Create competency</h2>
                        <p className="text-sm text-muted-foreground">
                          Add a competency with the standard 1–5 scale so review cycles can immediately depend on it.
                        </p>
                      </div>
                      <div className="mt-4 grid gap-3 sm:grid-cols-2">
                        <WorkspaceField>
                          <span>Code</span>
                          <Input value={competencyForm.code} onChange={(event) => setCompetencyForm((current) => ({ ...current, code: event.target.value.toUpperCase() }))} placeholder="LEAD-01" />
                        </WorkspaceField>
                        <WorkspaceField>
                        <span>Status</span>
                        <SelectField
                          label="Status"
                          value={competencyForm.status}
                          onChange={(value) => setCompetencyForm((current) => ({ ...current, status: value as CompetencyFormState['status'] }))}
                          options={[
                            ['active', 'Active'],
                            ['inactive', 'Inactive'],
                            ['archived', 'Archived'],
                            ]}
                          />
                        </WorkspaceField>
                        <WorkspaceField className="sm:col-span-2">
                          <span>Name</span>
                          <Input value={competencyForm.name} onChange={(event) => setCompetencyForm((current) => ({ ...current, name: event.target.value }))} placeholder="Strategic communication" />
                        </WorkspaceField>
                        <WorkspaceField>
                          <span>Category</span>
                          <Input value={competencyForm.category} onChange={(event) => setCompetencyForm((current) => ({ ...current, category: event.target.value }))} placeholder="Leadership" />
                        </WorkspaceField>
                        <WorkspaceField className="sm:col-span-2">
                          <span>Description</span>
                          <Textarea value={competencyForm.description} onChange={(event) => setCompetencyForm((current) => ({ ...current, description: event.target.value }))} rows={3} />
                        </WorkspaceField>
                      </div>
                      <div className="mt-4 flex justify-end">
                        <Button type="submit" disabled={workspace.pendingActionLabel !== null}>Create competency</Button>
                      </div>
                    </form>
                  ) : null}
                </div>
              )}
            />
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
