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
import type { ReleaseReadinessDecisionFormValues } from '../types'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

function createEmptyDecisionForm(targetEnvironment = 'production'): ReleaseReadinessDecisionFormValues {
  return {
    release_window_label: '',
    target_environment: targetEnvironment,
    decision_status: 'conditional',
    summary: '',
    blockers: '',
    artifact_refs: '',
    decision_notes: '',
  }
}

export function OperationsReleaseReadinessPage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [selectedAreaKey, setSelectedAreaKey] = useState<string | null>(null)
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [decisionForm, setDecisionForm] = useState<ReleaseReadinessDecisionFormValues>(
    createEmptyDecisionForm(),
  )

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading go-live readiness...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No go-live readiness workspace is available yet.</p>
  }

  const readiness = workspace.data.releaseReadiness
  const defaultTargetEnvironment = readiness.policy.target_environments[0] ?? 'production'
  const activeTargetEnvironment = decisionForm.target_environment || defaultTargetEnvironment
  const normalizedQuery = search.trim().toLowerCase()
  const filteredAreas = readiness.areas.filter((area) => {
    if (statusFilter !== 'all' && area.status !== statusFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [area.name, area.owner_role, area.summary, area.source]
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })
  const activeAreaKey =
    (selectedAreaKey && readiness.areas.some((area) => area.key === selectedAreaKey) && selectedAreaKey) ||
    filteredAreas[0]?.key ||
    readiness.areas[0]?.key ||
    null
  const selectedArea = readiness.areas.find((area) => area.key === activeAreaKey) ?? null

  async function handleRecordDecision(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()

    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.recordReleaseReadinessDecision({
        ...decisionForm,
        target_environment: activeTargetEnvironment,
      })
      setActionMessage('Release readiness decision recorded.')
      setDecisionForm(createEmptyDecisionForm(defaultTargetEnvironment))
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The release readiness decision could not be recorded.')
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Launch Governance"
          title="Go-Live Readiness"
          description="Review automated gates, recovery evidence, monitoring posture, and accountable blockers before approving a protected launch."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo review' : 'Live review'}</Badge>}
          context={[
            `${readiness.summary.total_area_count} governed readiness area(s)`,
            `${readiness.summary.blocker_count} active blocker(s)`,
            workspace.canManageRelease ? 'Decision workflow enabled' : 'Read only review',
          ]}
          actions={
            <Badge variant={statusBadgeVariant(readiness.recommendation.status)}>
              {formatRecommendationStatus(readiness.recommendation.status)}
            </Badge>
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
                    placeholder="Search readiness areas, owners, or summaries"
                  />
                </WorkspaceField>
                <WorkspaceField label="Area status" compact>
                  <select
                    aria-label="Area status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="ready">Ready</option>
                    <option value="attention">Attention</option>
                    <option value="blocked">Blocked</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageRelease ? (
            <p className="workspace-muted">
              This session can review launch posture and named blockers, but only release-authorized operators can record go or no-go decisions.
            </p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard
              label="Ready areas"
              value={String(readiness.summary.ready_area_count)}
              caption={`${readiness.summary.total_area_count} launch area(s) are governed in this review`}
            />
            <MetricCard
              label="Attention areas"
              value={String(readiness.summary.attention_area_count)}
              caption="Fresh evidence or operator acknowledgement is still pending for these areas"
            />
            <MetricCard
              label="Blocked areas"
              value={String(readiness.summary.blocked_area_count)}
              caption="Any blocked area should hold protected promotion until ownership is clear"
            />
            <MetricCard
              label="Active blockers"
              value={String(readiness.summary.blocker_count)}
              caption="System-derived and decision-recorded blockers stay visible with named owners"
            />
            <MetricCard
              label="Latest decision"
              value={formatRegionalDate(readiness.summary.latest_decision_at, 'Not recorded')}
              caption={`${readiness.summary.decision_count} review decision(s) are currently recorded`}
            />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.08fr)_minmax(22rem,0.92fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Release-readiness checklist</h2>
                  <p className="text-sm text-muted-foreground">Each area rolls up the exact evidence that must stay current before launch approval can proceed.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredAreas.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Area</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Checks</TableHead>
                          <TableHead>Last reviewed</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredAreas.map((area) => (
                          <TableRow key={area.key} className={selectedArea?.key === area.key ? 'bg-primary/5' : undefined}>
                            <TableCell>
                              <span className="ui-table-primary">{area.name}</span>
                              <p className="text-xs text-muted-foreground">
                                {area.owner_role} · {formatAreaSource(area.source)}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={statusBadgeVariant(area.status)}>{formatAreaStatus(area.status)}</Badge>
                            </TableCell>
                            <TableCell>
                              {area.check_count}
                              <p className="text-xs text-muted-foreground">
                                {area.blocking_item_count} blocking · {area.attention_item_count} attention
                              </p>
                            </TableCell>
                            <TableCell>{formatRegionalDateTime(area.last_reviewed_at, 'Not reviewed yet')}</TableCell>
                            <TableCell>
                              <Button
                                size="xs"
                                variant={selectedArea?.key === area.key ? 'primary' : 'secondary'}
                                onClick={() => setSelectedAreaKey(area.key)}
                              >
                                {selectedArea?.key === area.key ? 'Reviewing' : 'Review'}
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No release-readiness areas match this view"
                    copy="Widen the search or change the status filter to inspect more of the go-live checklist."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Recommendation</h2>
                    <p className="text-sm text-muted-foreground">The system recommendation combines readiness evidence with the latest recorded decision.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  <DetailCard
                    label="Current posture"
                    value={formatRecommendationStatus(readiness.recommendation.status)}
                    detail={readiness.recommendation.summary}
                  />
                  <DetailCard
                    label="Target environments"
                    value={readiness.policy.target_environments.join(', ')}
                    detail={readiness.policy.review_cadence}
                  />
                  <DetailCard
                    label="Decision owners"
                    value={readiness.policy.decision_owner_roles.join(', ')}
                    detail="These owner roles are expected to participate in accountable launch review."
                  />
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Latest decision</h2>
                    <p className="text-sm text-muted-foreground">The most recent go or no-go outcome stays visible with summary context and recorded owners.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent>
                  {readiness.latest_decision ? (
                    <article className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                      <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                          <p className="text-base font-semibold text-foreground">{readiness.latest_decision.release_window_label}</p>
                          <p className="text-xs text-muted-foreground">
                            {readiness.latest_decision.target_environment} ·{' '}
                            {formatRegionalDateTime(readiness.latest_decision.decided_at, 'Not dated')}
                          </p>
                        </div>
                        <Badge variant={statusBadgeVariant(readiness.latest_decision.decision_status)}>
                          {formatDecisionStatus(readiness.latest_decision.decision_status)}
                        </Badge>
                      </div>
                      <p className="mt-3 text-sm leading-6 text-muted-foreground">{readiness.latest_decision.summary}</p>
                      <p className="mt-3 text-xs text-muted-foreground">
                        Recorded by {readiness.latest_decision.decided_by_name ?? 'Unknown operator'}
                      </p>
                    </article>
                  ) : (
                    <WorkspaceEmptyState
                      title="No go-live decision recorded yet"
                      copy="Review areas, blockers, and runbooks first, then record the first accountable launch decision."
                    />
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          {selectedArea ? (
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Selected area</h2>
                    <p className="text-sm text-muted-foreground">{selectedArea.summary}</p>
                  </div>
                  <Badge variant={statusBadgeVariant(selectedArea.status)}>
                    {formatAreaStatus(selectedArea.status)}
                  </Badge>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-4">
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                  <DetailCard label="Owner role" value={selectedArea.owner_role} detail={formatAreaSource(selectedArea.source)} />
                  <DetailCard label="Checks" value={String(selectedArea.check_count)} detail={`${selectedArea.blocking_item_count} blocking · ${selectedArea.attention_item_count} attention`} />
                  <DetailCard
                    label="Last reviewed"
                    value={formatRegionalDateTime(selectedArea.last_reviewed_at, 'Not reviewed yet')}
                    detail="Area-level review time tracks the freshest underlying evidence."
                  />
                  <DetailCard
                    label="Artifacts"
                    value={String(selectedArea.artifact_refs.length)}
                    detail="Linked workflow, contract, or runbook references available for operator review."
                  />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">Checklist items</h3>
                        <p className="text-sm text-muted-foreground">These checks roll up into the selected readiness area.</p>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <div className="space-y-3">
                        {selectedArea.items.map((item) => (
                          <article key={item.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                            <div className="flex items-center justify-between gap-3">
                              <div>
                                <p className="text-sm font-semibold text-foreground">{item.label}</p>
                                <p className="text-xs text-muted-foreground">{item.owner_role}</p>
                              </div>
                              <Badge variant={statusBadgeVariant(item.status)}>{formatAreaStatus(item.status)}</Badge>
                            </div>
                            <p className="mt-3 text-sm leading-6 text-muted-foreground">{item.summary}</p>
                            <p className="mt-3 text-xs text-muted-foreground">
                              Last reviewed {formatRegionalDateTime(item.last_reviewed_at, 'Not reviewed yet')}
                            </p>
                          </article>
                        ))}
                      </div>
                    </WorkspaceContent>
                  </WorkspaceSurface>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">Evidence and references</h3>
                        <p className="text-sm text-muted-foreground">Operators can use these evidence requirements and artifacts during go-live review.</p>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent className="space-y-4">
                      <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Evidence requirements</p>
                        <div className="mt-2 space-y-2">
                          {selectedArea.evidence_requirements.map((requirement) => (
                            <p key={requirement} className="rounded-2xl bg-muted/70 px-3 py-2 text-sm text-foreground">
                              {requirement}
                            </p>
                          ))}
                        </div>
                      </div>
                      <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Artifact references</p>
                        <div className="mt-2 space-y-2">
                          {selectedArea.artifact_refs.length ? (
                            selectedArea.artifact_refs.map((artifact) => (
                              <p key={artifact} className="rounded-2xl bg-muted/70 px-3 py-2 text-sm text-foreground">
                                {artifact}
                              </p>
                            ))
                          ) : (
                            <p className="rounded-2xl bg-muted/70 px-3 py-2 text-sm text-muted-foreground">
                              No extra artifacts are linked for this area yet.
                            </p>
                          )}
                        </div>
                      </div>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </div>
              </WorkspaceContent>
            </WorkspaceSurface>
          ) : null}

          <div className="grid gap-4 xl:grid-cols-2">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Active blockers</h2>
                  <p className="text-sm text-muted-foreground">System-derived blockers and decision-recorded blockers stay reviewable with clear owners.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {readiness.blockers.length ? (
                  <div className="space-y-3">
                    {readiness.blockers.map((blocker) => (
                      <article key={blocker.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-3">
                          <div>
                            <p className="text-sm font-semibold text-foreground">{blocker.title}</p>
                            <p className="text-xs text-muted-foreground">
                              {(blocker.area_name ?? 'General')} · {blocker.owner_role}
                            </p>
                          </div>
                          <Badge variant={statusBadgeVariant(blocker.status)}>
                            {formatBlockerStatus(blocker.status)}
                          </Badge>
                        </div>
                        <p className="mt-3 text-sm leading-6 text-muted-foreground">{blocker.summary}</p>
                      </article>
                    ))}
                  </div>
                ) : (
                  <WorkspaceEmptyState
                    title="No blockers are open"
                    copy="The release-readiness checklist is currently clear of unresolved blockers."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Runbooks</h2>
                  <p className="text-sm text-muted-foreground">Launch-response runbooks are visible alongside the checklist so operators are not hunting for procedures mid-launch.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                <div className="space-y-3">
                  {readiness.runbooks.map((runbook) => (
                    <article key={runbook.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                      <p className="text-sm font-semibold text-foreground">{runbook.name}</p>
                      <p className="mt-1 text-xs text-muted-foreground">
                        {runbook.owner_role} · {runbook.path}
                      </p>
                      <p className="mt-3 text-sm leading-6 text-muted-foreground">{runbook.summary}</p>
                      <p className="mt-3 text-xs text-muted-foreground">{runbook.when_to_use}</p>
                    </article>
                  ))}
                </div>
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Decision history</h2>
                <p className="text-sm text-muted-foreground">Recent go-live reviews stay visible with outcome, environment, and named approvers.</p>
              </div>
            </WorkspaceHeader>
            <WorkspaceContent>
              {readiness.decision_history.length ? (
                <div className="space-y-3">
                  {readiness.decision_history.map((decision) => (
                    <article key={decision.id} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                      <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                          <p className="text-sm font-semibold text-foreground">{decision.release_window_label}</p>
                          <p className="text-xs text-muted-foreground">
                            {decision.target_environment} · {formatRegionalDateTime(decision.decided_at, 'Not dated')}
                          </p>
                        </div>
                        <Badge variant={statusBadgeVariant(decision.decision_status)}>
                          {formatDecisionStatus(decision.decision_status)}
                        </Badge>
                      </div>
                      <p className="mt-3 text-sm leading-6 text-muted-foreground">{decision.summary}</p>
                      {decision.decision_notes ? (
                        <p className="mt-3 rounded-2xl bg-muted/70 px-3 py-2 text-sm text-foreground">
                          {decision.decision_notes}
                        </p>
                      ) : null}
                    </article>
                  ))}
                </div>
              ) : (
                <WorkspaceEmptyState
                  title="No launch decisions recorded yet"
                  copy="Once the first go or no-go review is logged, the decision history will stay visible here."
                />
              )}
            </WorkspaceContent>
          </WorkspaceSurface>

          {workspace.canManageRelease ? (
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Record go-live decision</h2>
                  <p className="text-sm text-muted-foreground">Capture the accountable outcome, blockers, and notes so the launch decision stays reviewable later.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                <form className="space-y-4" onSubmit={handleRecordDecision}>
                  <div className="grid gap-4 md:grid-cols-2">
                    <WorkspaceField label="Release window">
                      <Input
                        aria-label="Release window"
                        value={decisionForm.release_window_label}
                        onChange={(event) =>
                          setDecisionForm((current) => ({
                            ...current,
                            release_window_label: event.target.value,
                          }))
                        }
                        placeholder="FY26 payroll launch wave 2"
                      />
                      <FieldErrors errors={fieldErrors.release_window_label} />
                    </WorkspaceField>

                      <WorkspaceField label="Target environment">
                        <select
                          aria-label="Target environment"
                          className={nativeSelectClassName}
                          value={activeTargetEnvironment}
                          onChange={(event) =>
                            setDecisionForm((current) => ({
                              ...current,
                            target_environment: event.target.value,
                          }))
                        }
                      >
                        {readiness.policy.target_environments.map((environment) => (
                          <option key={environment} value={environment}>
                            {environment}
                          </option>
                        ))}
                      </select>
                      <FieldErrors errors={fieldErrors.target_environment} />
                    </WorkspaceField>
                  </div>

                  <div className="grid gap-4 md:grid-cols-2">
                    <WorkspaceField label="Decision">
                      <select
                        aria-label="Decision"
                        className={nativeSelectClassName}
                        value={decisionForm.decision_status}
                        onChange={(event) =>
                          setDecisionForm((current) => ({
                            ...current,
                            decision_status: event.target.value,
                          }))
                        }
                      >
                        <option value="go">Go</option>
                        <option value="conditional">Conditional</option>
                        <option value="no_go">No-go</option>
                      </select>
                      <FieldErrors errors={fieldErrors.decision_status} />
                    </WorkspaceField>

                    <WorkspaceField label="Decision summary">
                      <Input
                        aria-label="Decision summary"
                        value={decisionForm.summary}
                        onChange={(event) =>
                          setDecisionForm((current) => ({
                            ...current,
                            summary: event.target.value,
                          }))
                        }
                        placeholder="Launch is conditionally approved while DR evidence is refreshed."
                      />
                      <FieldErrors errors={fieldErrors.summary} />
                    </WorkspaceField>
                  </div>

                  <WorkspaceField label="Blockers">
                    <Textarea
                      aria-label="Blockers"
                      rows={4}
                      value={decisionForm.blockers}
                      onChange={(event) =>
                        setDecisionForm((current) => ({
                          ...current,
                          blockers: event.target.value,
                        }))
                      }
                      placeholder="backups | platform.super_admin | Regional failover drill remains failed | Rerun scheduled before production cutover"
                    />
                    <p className="text-xs text-muted-foreground">
                      Use one blocker per line in the format `area key | owner role | blocker title | notes`.
                    </p>
                    <FieldErrors errors={fieldErrors.blockers} />
                  </WorkspaceField>

                  <div className="grid gap-4 xl:grid-cols-2">
                    <WorkspaceField label="Artifact references">
                      <Textarea
                        aria-label="Artifact references"
                        rows={4}
                        value={decisionForm.artifact_refs}
                        onChange={(event) =>
                          setDecisionForm((current) => ({
                            ...current,
                            artifact_refs: event.target.value,
                          }))
                        }
                        placeholder="docs/runbooks/release-rollback.md"
                      />
                      <FieldErrors errors={fieldErrors.artifact_refs} />
                    </WorkspaceField>

                    <WorkspaceField label="Decision notes">
                      <Textarea
                        aria-label="Decision notes"
                        rows={4}
                        value={decisionForm.decision_notes}
                        onChange={(event) =>
                          setDecisionForm((current) => ({
                            ...current,
                            decision_notes: event.target.value,
                          }))
                        }
                        placeholder="Capture follow-up actions, owner handoffs, or risk acceptance notes."
                      />
                      <FieldErrors errors={fieldErrors.decision_notes} />
                    </WorkspaceField>
                  </div>

                  <div className="flex justify-end">
                    <Button type="submit" disabled={isSaving}>
                      {isSaving ? 'Recording...' : 'Record decision'}
                    </Button>
                  </div>
                </form>
              </WorkspaceContent>
            </WorkspaceSurface>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <article className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
      <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">{label}</p>
      <p className="mt-2 text-3xl font-semibold text-foreground">{value}</p>
      <p className="mt-2 text-sm leading-6 text-muted-foreground">{caption}</p>
    </article>
  )
}

function DetailCard({ label, value, detail }: { label: string; value: string; detail?: string }) {
  return (
    <article className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
      <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">{label}</p>
      <p className="mt-2 text-base font-semibold text-foreground">{value}</p>
      {detail ? <p className="mt-2 text-sm leading-6 text-muted-foreground">{detail}</p> : null}
    </article>
  )
}

function FieldErrors({ errors }: { errors?: string[] }) {
  if (!errors?.length) {
    return null
  }

  return <p className="mt-2 text-xs text-destructive">{errors.join(' ')}</p>
}

function statusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'ready':
    case 'go':
      return 'success'
    case 'conditional':
    case 'attention':
      return 'warning'
    case 'blocked':
    case 'no_go':
    case 'open':
      return 'danger'
    case 'pending_review':
      return 'info'
    case 'mitigated':
      return 'success'
    case 'accepted':
      return 'neutral'
    default:
      return 'neutral'
  }
}

function formatAreaStatus(status: string) {
  switch (status) {
    case 'ready':
      return 'Ready'
    case 'attention':
      return 'Attention'
    case 'blocked':
      return 'Blocked'
    default:
      return status
  }
}

function formatRecommendationStatus(status: string) {
  switch (status) {
    case 'go':
      return 'Go'
    case 'conditional':
      return 'Conditional'
    case 'no_go':
      return 'No-go'
    case 'pending_review':
      return 'Pending review'
    default:
      return status
  }
}

function formatDecisionStatus(status: string) {
  switch (status) {
    case 'go':
      return 'Go'
    case 'conditional':
      return 'Conditional'
    case 'no_go':
      return 'No-go'
    default:
      return status
  }
}

function formatBlockerStatus(status: string) {
  switch (status) {
    case 'open':
      return 'Open'
    case 'mitigated':
      return 'Mitigated'
    case 'accepted':
      return 'Accepted risk'
    default:
      return status
  }
}

function formatAreaSource(source: string) {
  switch (source) {
    case 'release_gates':
      return 'Release gates'
    case 'resilience_scenarios':
      return 'Recovery evidence'
    case 'observability_services':
      return 'Observability'
    case 'workflow_checks':
      return 'Workflow verification'
    default:
      return source
  }
}

function handleApiError(
  error: unknown,
  setActionError: (message: string | null) => void,
  setFieldErrors: (errors: Record<string, string[]>) => void,
  fallbackMessage: string,
) {
  if (error instanceof ApiRequestError) {
    setFieldErrors(error.fieldErrors)
    setActionError(error.message || fallbackMessage)
    return
  }

  setActionError(error instanceof Error ? error.message : fallbackMessage)
}

const nativeSelectClassName =
  'h-10 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring'
