import { useState, type FormEvent } from 'react'
import { ApiRequestError } from '../../../shared/api/http'
import { formatRegionalDate, formatRegionalDateTime } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import type { ResilienceValidationRunFormValues } from '../types'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

const emptyValidationForm: ResilienceValidationRunFormValues = {
  scenario_key: '',
  status: 'passed',
  recovery_point_actual_minutes: '',
  recovery_time_actual_minutes: '',
  evidence_refs: '',
  notes: '',
}

export function OperationsResiliencePage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [selectedScenarioKey, setSelectedScenarioKey] = useState<string | null>(null)
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [validationForm, setValidationForm] = useState<ResilienceValidationRunFormValues>(emptyValidationForm)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading backup and recovery readiness...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No resilience workspace is available yet.</p>
  }

  const resilience = workspace.data.resilience
  const normalizedQuery = search.trim().toLowerCase()
  const filteredScenarios = resilience.scenarios.filter((scenario) => {
    if (statusFilter !== 'all' && scenario.status !== statusFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [scenario.name, scenario.scenario_type, scenario.environment, scenario.owner_role, scenario.summary]
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })
  const activeScenarioKey =
    (selectedScenarioKey &&
      resilience.scenarios.some((scenario) => scenario.key === selectedScenarioKey) &&
      selectedScenarioKey) ||
    filteredScenarios[0]?.key ||
    resilience.scenarios[0]?.key ||
    null
  const selectedScenario = resilience.scenarios.find((scenario) => scenario.key === activeScenarioKey) ?? null
  const activeValidationScenarioKey = validationForm.scenario_key || activeScenarioKey || ''
  const selectedRuns = selectedScenario
    ? resilience.validation_runs.filter((run) => run.scenario_key === selectedScenario.key)
    : []

  async function handleLogValidation(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!selectedScenario && !activeValidationScenarioKey) {
      return
    }

    const scenarioKey = activeValidationScenarioKey

    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.logResilienceValidation({
        ...validationForm,
        scenario_key: scenarioKey,
      })
      setActionMessage('Validation run recorded.')
      setValidationForm({
        ...emptyValidationForm,
        scenario_key: scenarioKey,
      })
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The validation run could not be recorded.')
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Resilience"
          title="Backup and Recovery Readiness"
          description="Track backup execution, restore validation evidence, and disaster-recovery drill outcomes from one governed operator workspace before launch readiness is approved."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo readiness' : 'Live readiness'}</Badge>}
          context={[
            `${resilience.summary.total_scenario_count} governed scenario(s)`,
            `${resilience.summary.validation_run_count} logged validation run(s)`,
            workspace.canManageResilience ? 'Operator evidence capture enabled' : 'Read only review',
          ]}
          actions={
            workspace.canManageResilience ? (
              <Badge variant="success">Validation logging available</Badge>
            ) : (
              <Badge variant="warning">Read only in this session</Badge>
            )
          }
        />

        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <div className="flex flex-1 flex-wrap items-end gap-2.5">
                <WorkspaceField label="Search">
                  <Input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search scenarios, environments, or owner roles"
                  />
                </WorkspaceField>
                <WorkspaceField label="Scenario status" compact>
                  <select
                    aria-label="Scenario status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="ready">Ready</option>
                    <option value="attention">Attention</option>
                    <option value="failed">Failed</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageResilience ? (
            <p className="workspace-muted">
              This session can review recovery posture and evidence history, but only approved resilience operators can log validation outcomes.
            </p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard
              label="Ready scenarios"
              value={String(resilience.summary.ready_scenario_count)}
              caption={`${resilience.summary.total_scenario_count} total scenario(s) are governed in this baseline`}
            />
            <MetricCard
              label="Attention scenarios"
              value={String(resilience.summary.attention_scenario_count)}
              caption="Scenarios waiting on first evidence, remediation, or a fresh rerun"
            />
            <MetricCard
              label="Failed scenarios"
              value={String(resilience.summary.failed_scenario_count)}
              caption="Any failed drill should block launch readiness until retested"
            />
            <MetricCard
              label="Overdue validations"
              value={String(resilience.summary.overdue_scenario_count)}
              caption="Successful evidence is older than the agreed cadence for these scenarios"
            />
            <MetricCard
              label="Latest validation"
              value={formatRegionalDate(resilience.summary.latest_validation_at, 'Not started')}
              caption={`${resilience.summary.validation_run_count} run(s) are currently recorded`}
            />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(24rem,0.9fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Recovery scenarios</h2>
                  <p className="text-sm text-muted-foreground">Each scenario carries an objective, validation cadence, evidence list, and current readiness state.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredScenarios.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Scenario</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Cadence</TableHead>
                          <TableHead>Latest validation</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredScenarios.map((scenario) => (
                          <TableRow key={scenario.key} className={selectedScenario?.key === scenario.key ? 'bg-primary/5' : undefined}>
                            <TableCell>
                              <span className="ui-table-primary">{scenario.name}</span>
                              <p className="text-xs text-muted-foreground">
                                {formatScenarioType(scenario.scenario_type)} · {scenario.environment}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={scenarioStatusBadgeVariant(scenario.status)}>
                                {formatScenarioStatus(scenario.status)}
                              </Badge>
                            </TableCell>
                            <TableCell>{scenario.cadence_days} day(s)</TableCell>
                            <TableCell>
                              {formatRegionalDateTime(scenario.last_validated_at, 'Not validated yet')}
                              <p className="text-xs text-muted-foreground">
                                {scenario.overdue
                                  ? 'Evidence is overdue'
                                  : `Next due ${formatRegionalDate(scenario.next_validation_due_at, 'Pending')}`}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Button
                                size="xs"
                                variant={selectedScenario?.key === scenario.key ? 'primary' : 'secondary'}
                                onClick={() => {
                                  setSelectedScenarioKey(scenario.key)
                                  setValidationForm((current) => ({
                                    ...current,
                                    scenario_key: scenario.key,
                                  }))
                                }}
                              >
                                {selectedScenario?.key === scenario.key ? 'Reviewing' : 'Review'}
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No resilience scenarios match this view"
                    copy="Widen the search or change the status filter to inspect more of the recovery-readiness baseline."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Recovery policy</h2>
                    <p className="text-sm text-muted-foreground">Baseline regions, cadence, retention, and encryption expectations stay visible next to scenario evidence.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  <DetailCard label="Regions" value={`${resilience.policy.primary_region} -> ${resilience.policy.secondary_region}`} />
                  <DetailCard
                    label="Default targets"
                    value={`RPO ${resilience.policy.default_rpo_minutes} min · RTO ${resilience.policy.default_rto_minutes} min`}
                    detail={resilience.policy.dr_drill_cadence}
                  />
                  <DetailCard label="Backup cadence" value={resilience.policy.backup_cadence} />
                  <DetailCard label="Retention policy" value={resilience.policy.retention_policy} detail={resilience.policy.encryption_posture} />
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Selected scenario</h2>
                    <p className="text-sm text-muted-foreground">Review the current validation posture, objectives, and required evidence before logging the next outcome.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-4">
                  {selectedScenario ? (
                    <>
                      <div className="space-y-2">
                        <div className="flex flex-wrap items-center gap-2">
                          <h3 className="text-base font-semibold text-foreground">{selectedScenario.name}</h3>
                          <Badge variant={scenarioStatusBadgeVariant(selectedScenario.status)}>
                            {formatScenarioStatus(selectedScenario.status)}
                          </Badge>
                          <Badge variant="neutral">{formatScenarioType(selectedScenario.scenario_type)}</Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">{selectedScenario.summary}</p>
                        {selectedScenario.blocked_reason ? (
                          <p className="rounded-2xl border border-warning/30 bg-warning/10 px-3 py-2 text-sm text-foreground">
                            {selectedScenario.blocked_reason}
                          </p>
                        ) : null}
                      </div>

                      <div className="grid gap-3 sm:grid-cols-2">
                        <DetailCard
                          label="Objective"
                          value={`RPO ${selectedScenario.recovery_point_objective_minutes} min`}
                          detail={`RTO ${selectedScenario.recovery_time_objective_minutes} min`}
                        />
                        <DetailCard
                          label="Validation cadence"
                          value={`${selectedScenario.cadence_days} day(s)`}
                          detail={`Next due ${formatRegionalDate(selectedScenario.next_validation_due_at, 'Pending')}`}
                        />
                        <DetailCard
                          label="Owner"
                          value={selectedScenario.owner_role}
                          detail={selectedScenario.environment}
                        />
                        <DetailCard
                          label="Latest run"
                          value={selectedScenario.latest_run ? formatScenarioStatus(selectedScenario.latest_run.status) : 'Not recorded'}
                          detail={formatRegionalDateTime(selectedScenario.last_validated_at, 'No validation yet')}
                        />
                      </div>

                      <div className="space-y-2">
                        <p className="text-sm font-semibold text-foreground">Evidence requirements</p>
                        <div className="flex flex-wrap gap-2">
                          {selectedScenario.evidence_requirements.map((item) => (
                            <Badge key={item} variant="neutral">
                              {item}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    </>
                  ) : (
                    <WorkspaceEmptyState
                      title="No scenario selected"
                      copy="Choose a scenario from the table to review its recovery posture and evidence requirements."
                    />
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(22rem,0.9fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Recent validation runs</h2>
                  <p className="text-sm text-muted-foreground">Logged outcomes remain reviewable together with actual recovery timing and operator evidence.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {resilience.validation_runs.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Scenario</TableHead>
                          <TableHead>Outcome</TableHead>
                          <TableHead>Actuals</TableHead>
                          <TableHead>Evidence</TableHead>
                          <TableHead>Notes</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {resilience.validation_runs.slice(0, 6).map((run) => (
                          <TableRow key={run.id}>
                            <TableCell>
                              <span className="ui-table-primary">{run.scenario_name}</span>
                              <p className="text-xs text-muted-foreground">
                                {formatRegionalDateTime(run.completed_at ?? run.started_at, 'Pending')} · {run.executed_by_name ?? 'Unknown operator'}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={validationOutcomeBadgeVariant(run.status)}>{formatValidationOutcome(run.status)}</Badge>
                            </TableCell>
                            <TableCell>
                              RPO {run.recovery_point_actual_minutes ?? '—'} min
                              <p className="text-xs text-muted-foreground">RTO {run.recovery_time_actual_minutes ?? '—'} min</p>
                            </TableCell>
                            <TableCell>{run.evidence_refs.length ? run.evidence_refs.join(', ') : 'Evidence pending'}</TableCell>
                            <TableCell>{run.notes ?? 'No notes recorded'}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No validation runs recorded yet"
                    copy="Log the first backup or recovery validation to make launch-readiness evidence reviewable."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Disaster-recovery runbook</h2>
                    <p className="text-sm text-muted-foreground">Roles, sequencing, and evidence expectations remain explicit before launch review.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  {resilience.runbook.map((step) => (
                    <article key={step.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                      <div className="flex items-center justify-between gap-3">
                        <p className="text-sm font-semibold text-foreground">
                          Step {step.sequence}: {step.name}
                        </p>
                        <Badge variant="neutral">{step.owner_role}</Badge>
                      </div>
                      <p className="mt-2 text-sm text-muted-foreground">{step.objective}</p>
                      <div className="mt-3 flex flex-wrap gap-2">
                        {step.evidence_requirements.map((item) => (
                          <Badge key={item} variant="neutral">
                            {item}
                          </Badge>
                        ))}
                      </div>
                    </article>
                  ))}
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Log validation</h2>
                    <p className="text-sm text-muted-foreground">Capture the latest scenario outcome so recovery readiness stays current and reviewable.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent>
                  {workspace.canManageResilience ? (
                    <form className="space-y-3" onSubmit={handleLogValidation}>
                      <WorkspaceField label="Scenario">
                        <select
                          aria-label="Scenario"
                          className={nativeSelectClassName}
                          value={activeValidationScenarioKey}
                          onChange={(event) => {
                            setSelectedScenarioKey(event.target.value)
                            setValidationForm((current) => ({
                              ...current,
                              scenario_key: event.target.value,
                            }))
                          }}
                        >
                          {resilience.scenarios.map((scenario) => (
                            <option key={scenario.key} value={scenario.key}>
                              {scenario.name}
                            </option>
                          ))}
                        </select>
                      </WorkspaceField>
                      <WorkspaceField label="Outcome">
                        <select
                          aria-label="Outcome"
                          className={nativeSelectClassName}
                          value={validationForm.status}
                          onChange={(event) =>
                            setValidationForm((current) => ({
                              ...current,
                              status: event.target.value,
                            }))
                          }
                        >
                          <option value="passed">Passed</option>
                          <option value="issues_found">Issues found</option>
                          <option value="failed">Failed</option>
                          <option value="in_progress">In progress</option>
                        </select>
                      </WorkspaceField>
                      <div className="grid gap-3 sm:grid-cols-2">
                        <WorkspaceField label="Actual RPO (min)">
                          <Input
                            type="number"
                            min="0"
                            value={validationForm.recovery_point_actual_minutes}
                            onChange={(event) =>
                              setValidationForm((current) => ({
                                ...current,
                                recovery_point_actual_minutes: event.target.value,
                              }))
                            }
                            placeholder="e.g. 25"
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Actual RTO (min)">
                          <Input
                            type="number"
                            min="0"
                            value={validationForm.recovery_time_actual_minutes}
                            onChange={(event) =>
                              setValidationForm((current) => ({
                                ...current,
                                recovery_time_actual_minutes: event.target.value,
                              }))
                            }
                            placeholder="e.g. 90"
                          />
                        </WorkspaceField>
                      </div>
                      <WorkspaceField label="Evidence">
                        <Textarea
                          aria-label="Evidence"
                          value={validationForm.evidence_refs}
                          onChange={(event) =>
                            setValidationForm((current) => ({
                              ...current,
                              evidence_refs: event.target.value,
                            }))
                          }
                          placeholder="One evidence reference per line or comma-separated"
                        />
                        {fieldErrors.evidence_refs?.length ? (
                          <p className="mt-1 text-xs text-rose-700">{fieldErrors.evidence_refs[0]}</p>
                        ) : null}
                      </WorkspaceField>
                      <WorkspaceField label="Notes">
                        <Textarea
                          aria-label="Notes"
                          value={validationForm.notes}
                          onChange={(event) =>
                            setValidationForm((current) => ({
                              ...current,
                              notes: event.target.value,
                            }))
                          }
                          placeholder="Summarize the result, blockers, or remediation path."
                        />
                      </WorkspaceField>
                      <Button type="submit" variant="primary" disabled={isSaving}>
                        {isSaving ? 'Logging...' : 'Log validation'}
                      </Button>
                    </form>
                  ) : (
                    <p className="workspace-muted">
                      Validation outcomes can be reviewed here, but recording new evidence still requires `resilience.manage`.
                    </p>
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          {selectedScenario && selectedRuns.length ? (
            <p className="text-xs text-muted-foreground">
              {selectedRuns.length} validation run(s) are currently linked to {selectedScenario.name}.
            </p>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function formatScenarioStatus(status: string) {
  switch (status) {
    case 'ready':
      return 'Ready'
    case 'attention':
      return 'Attention'
    case 'failed':
      return 'Failed'
    default:
      return status
  }
}

function formatValidationOutcome(status: string) {
  switch (status) {
    case 'issues_found':
      return 'Issues found'
    case 'in_progress':
      return 'In progress'
    case 'passed':
      return 'Passed'
    case 'failed':
      return 'Failed'
    default:
      return status
  }
}

function formatScenarioType(type: string) {
  switch (type) {
    case 'disaster_recovery':
      return 'Disaster recovery'
    default:
      return type.charAt(0).toUpperCase() + type.slice(1)
  }
}

function scenarioStatusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'neutral' {
  switch (status) {
    case 'ready':
      return 'success'
    case 'failed':
      return 'danger'
    case 'attention':
      return 'warning'
    default:
      return 'neutral'
  }
}

function validationOutcomeBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'neutral' {
  switch (status) {
    case 'passed':
      return 'success'
    case 'failed':
      return 'danger'
    case 'issues_found':
      return 'warning'
    default:
      return 'neutral'
  }
}

function MetricCard({
  label,
  value,
  caption,
}: {
  label: string
  value: string
  caption: string
}) {
  return (
    <article className="rounded-3xl border border-border/70 bg-card/95 p-5 shadow-sm">
      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">{label}</p>
      <p className="mt-3 text-3xl font-semibold text-foreground">{value}</p>
      <p className="mt-2 text-sm text-muted-foreground">{caption}</p>
    </article>
  )
}

function DetailCard({
  label,
  value,
  detail,
}: {
  label: string
  value: string
  detail?: string
}) {
  return (
    <article className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
      <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">{label}</p>
      <p className="mt-2 text-sm font-medium text-foreground">{value}</p>
      {detail ? <p className="mt-1 text-xs text-muted-foreground">{detail}</p> : null}
    </article>
  )
}

function handleApiError(
  error: unknown,
  setMessage: (value: string | null) => void,
  setFieldErrors: (value: Record<string, string[]>) => void,
  fallbackMessage: string,
) {
  if (error instanceof ApiRequestError) {
    setMessage(error.message)
    setFieldErrors(error.fieldErrors)
    return
  }

  if (error instanceof Error) {
    setMessage(error.message)
    setFieldErrors({})
    return
  }

  setMessage(fallbackMessage)
  setFieldErrors({})
}

const nativeSelectClassName =
  'ui-select h-11 rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground shadow-none outline-none transition-colors focus-visible:ring-4 focus-visible:ring-ring/40'
