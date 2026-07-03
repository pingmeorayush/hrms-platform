import { useState } from 'react'
import { formatRegionalDate } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
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
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

export function OperationsReleasePage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [selectedGateKey, setSelectedGateKey] = useState<string | null>(null)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading release-quality gates...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No release-quality workspace is available yet.</p>
  }

  const release = workspace.data.release
  const normalizedQuery = search.trim().toLowerCase()
  const filteredGates = release.gates.filter((gate) => {
    if (statusFilter !== 'all' && gate.status !== statusFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [gate.name, gate.category, gate.workflow_name, gate.owner_role, gate.summary]
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })
  const selectedGate =
    filteredGates.find((gate) => gate.key === selectedGateKey) ??
    release.gates.find((gate) => gate.key === selectedGateKey) ??
    filteredGates[0] ??
    release.gates[0] ??
    null
  const failingEnvironments = release.environments.filter((environment) => environment.status === 'blocked')

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Release Engineering"
          title="Release Engineering Baseline"
          description="Review blocking CI gates, dependency-security posture, contract validation, and protected-environment promotion policy from one governed operator surface."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo baseline' : 'Live baseline'}</Badge>}
          context={[
            `Protected branch ${release.policy.protected_branch}`,
            `${release.policy.required_workflow_names.length} required workflow(s)`,
            workspace.canManageRelease ? 'Release operators configured' : 'Read only review',
          ]}
        />

        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <div className="flex flex-1 flex-wrap items-end gap-2.5">
                <WorkspaceField label="Search">
                  <Input
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search gates, workflows, owner roles, or summaries"
                  />
                </WorkspaceField>
                <WorkspaceField label="Gate status" compact>
                  <select
                    aria-label="Gate status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="passing">Passing</option>
                    <option value="pending">Pending</option>
                    <option value="warning">Warning</option>
                    <option value="blocked">Blocked</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {!workspace.canManageRelease ? (
            <p className="workspace-muted">This session can review release posture, but protected-environment promotion still requires a release-authorized operator.</p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard
              label="Blocking gates"
              value={String(release.summary.blocking_gate_count)}
              caption="Any non-passing blocking gate prevents protected promotion."
            />
            <MetricCard
              label="Passing gates"
              value={String(release.summary.passing_gate_count)}
              caption={`${release.summary.total_gate_count} total governed gates are modeled in the baseline`}
            />
            <MetricCard
              label="Pending promotions"
              value={String(release.summary.pending_gate_count)}
              caption="Pending posture usually means manual approval is still required after automated checks pass."
            />
            <MetricCard
              label="Blocked environments"
              value={String(release.summary.blocked_environment_count)}
              caption={`${release.summary.protected_environment_count} protected environment(s) are tracked`}
            />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Release gates</h2>
                  <p className="text-sm text-muted-foreground">Each blocking check is visible with workflow ownership, status, and last-run evidence.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredGates.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Gate</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Workflow</TableHead>
                          <TableHead>Last run</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredGates.map((gate) => (
                          <TableRow key={gate.key} className={selectedGate?.key === gate.key ? 'bg-primary/5' : undefined}>
                            <TableCell>
                              <span className="ui-table-primary">{gate.name}</span>
                              <p className="text-xs text-muted-foreground">
                                {gate.category} · {gate.owner_role}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={statusBadgeVariant(gate.status)}>{gate.status}</Badge>
                            </TableCell>
                            <TableCell>
                              {gate.workflow_name}
                              <p className="text-xs text-muted-foreground">{gate.workflow_path}</p>
                            </TableCell>
                            <TableCell>{formatDate(gate.last_run_at)}</TableCell>
                            <TableCell>
                              <Button
                                size="xs"
                                variant={selectedGate?.key === gate.key ? 'primary' : 'secondary'}
                                onClick={() => setSelectedGateKey(gate.key)}
                              >
                                {selectedGate?.key === gate.key ? 'Reviewing' : 'Review'}
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No release gates match this view"
                    copy="Widen the search or change the status filter to inspect more of the release baseline."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Promotion posture</h2>
                    <p className="text-sm text-muted-foreground">Protected environments inherit the blocking status of their required gates.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent>
                  <div className="space-y-3">
                    {release.environments.map((environment) => (
                      <article key={environment.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-3">
                          <div>
                            <p className="text-sm font-semibold text-foreground">{environment.name}</p>
                            <p className="text-xs text-muted-foreground">
                              {environment.required_gate_count} required gate(s)
                            </p>
                          </div>
                          <Badge variant={statusBadgeVariant(environment.status)}>{environment.status}</Badge>
                        </div>
                        <p className="mt-3 text-sm text-muted-foreground">
                          {environment.blocked_reason ?? 'All required blocking gates are currently passing.'}
                        </p>
                      </article>
                    ))}
                  </div>
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Policy baseline</h2>
                    <p className="text-sm text-muted-foreground">Protected-branch policy and review ownership are part of the same operator contract.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  <DetailCard label="Protected branch" value={release.policy.protected_branch} />
                  <DetailCard label="Required workflows" value={release.policy.required_workflow_names.join(', ')} />
                  <DetailCard label="Reviewer roles" value={release.policy.reviewer_roles.join(', ')} />
                  <p className="text-sm leading-6 text-muted-foreground">{release.policy.promotion_rule}</p>
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          {selectedGate ? (
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Selected gate</h2>
                    <p className="text-sm text-muted-foreground">Review the exact checks, artifact references, and environment scope that make this gate promotion-blocking.</p>
                  </div>
                  <Badge variant={statusBadgeVariant(selectedGate.status)}>{selectedGate.status}</Badge>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-4">
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                  <DetailCard label="Workflow" value={selectedGate.workflow_name} detail={selectedGate.workflow_path} />
                  <DetailCard label="Owner role" value={selectedGate.owner_role} detail={selectedGate.category} />
                  <DetailCard label="Checks" value={String(selectedGate.check_count)} detail={`${selectedGate.failing_check_count} non-passing check(s)`} />
                  <DetailCard label="Last run" value={formatDate(selectedGate.last_run_at)} detail={selectedGate.blocking ? 'Blocking gate' : 'Advisory gate'} />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">Gate checks</h3>
                        <p className="text-sm text-muted-foreground">{selectedGate.summary}</p>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <div className="space-y-3">
                        {selectedGate.checks.map((check) => (
                          <article key={check.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                            <div className="flex items-center justify-between gap-3">
                              <p className="text-sm font-semibold text-foreground">{check.label}</p>
                              <Badge variant={statusBadgeVariant(check.status)}>{check.status}</Badge>
                            </div>
                            <p className="mt-2 rounded-2xl bg-slate-950/95 px-3 py-2 text-xs text-slate-100">{check.command}</p>
                          </article>
                        ))}
                      </div>
                    </WorkspaceContent>
                  </WorkspaceSurface>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">Evidence and scope</h3>
                        <p className="text-sm text-muted-foreground">These are the workflow and contract artifacts operators can review before approving promotion.</p>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent className="space-y-4">
                      <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Required for</p>
                        <div className="mt-2 flex flex-wrap gap-2">
                          {selectedGate.required_for.map((requirement) => (
                            <Badge key={requirement} variant="neutral">
                              {requirement.replace(/_/g, ' ')}
                            </Badge>
                          ))}
                        </div>
                      </div>
                      <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Artifact references</p>
                        <div className="mt-2 space-y-2">
                          {selectedGate.artifact_refs.map((artifact) => (
                            <p key={artifact} className="rounded-2xl bg-muted/70 px-3 py-2 text-sm text-foreground">
                              {artifact}
                            </p>
                          ))}
                        </div>
                      </div>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </div>
              </WorkspaceContent>
            </WorkspaceSurface>
          ) : null}

          {failingEnvironments.length ? (
            <p className="workspace-error">
              {failingEnvironments.length} protected environment(s) are blocked right now and require gate recovery before promotion can continue.
            </p>
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

function statusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'passing':
      return 'success'
    case 'pending':
      return 'warning'
    case 'blocked':
      return 'danger'
    case 'warning':
      return 'warning'
    default:
      return 'info'
  }
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

const nativeSelectClassName =
  'flex h-10 w-full rounded-2xl border border-border/70 bg-background px-3 text-sm text-foreground shadow-sm outline-none transition focus:border-primary/60 focus:ring-2 focus:ring-primary/15'
