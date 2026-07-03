import { Link } from 'react-router-dom'
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

export function OperationsObservabilityPage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('all')
  const [selectedServiceKey, setSelectedServiceKey] = useState<string | null>(null)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading observability baseline...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No observability workspace is available yet.</p>
  }

  const observability = workspace.data.observability
  const normalizedQuery = search.trim().toLowerCase()
  const filteredServices = observability.services.filter((service) => {
    if (statusFilter !== 'all' && service.status !== statusFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [service.name, service.category, service.owner_role, service.summary].join(' ').toLowerCase().includes(normalizedQuery)
  })
  const selectedService =
    filteredServices.find((service) => service.key === selectedServiceKey) ??
    observability.services.find((service) => service.key === selectedServiceKey) ??
    filteredServices[0] ??
    observability.services[0] ??
    null
  const selectedSignals = selectedService
    ? observability.signals.filter((signal) => signal.service_key === selectedService.key)
    : []
  const selectedAlerts = selectedService
    ? observability.alerts.filter((alert) => alert.service_key === selectedService.key)
    : []
  const selectedCoverage = selectedService
    ? [...observability.coverage.workflows, ...observability.coverage.integrations, ...observability.coverage.release_critical].filter((item) =>
        selectedService.signal_keys.some((signalKey) => item.signal_keys.includes(signalKey)),
      )
    : []

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Observability"
          title="Operational Observability Baseline"
          description="Review live service-health indicators, routed alerts, workflow SLA pressure, and release-critical monitoring coverage from one governed operator workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo telemetry' : 'Live telemetry'}</Badge>}
          context={[
            `${observability.telemetry.health_endpoint} health endpoint`,
            `${observability.alert_routes.length} routed alert lane(s)`,
            workspace.canManageObservability ? 'Operator escalation controls' : 'Read only review',
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
                    placeholder="Search services, owner roles, or summaries"
                  />
                </WorkspaceField>
                <WorkspaceField label="Service status" compact>
                  <select
                    aria-label="Service status"
                    className={nativeSelectClassName}
                    value={statusFilter}
                    onChange={(event) => setStatusFilter(event.target.value)}
                  >
                    <option value="all">All statuses</option>
                    <option value="healthy">Healthy</option>
                    <option value="degraded">Degraded</option>
                    <option value="critical">Critical</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {!workspace.canManageObservability ? (
            <p className="workspace-muted">
              This session can review service health and routed alerts, but alert-lane ownership still belongs to approved observability operators.
            </p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard
              label="Active alerts"
              value={String(observability.summary.active_alert_count)}
              caption={`${observability.summary.routed_alert_count} alert(s) currently resolve to an approved route`}
            />
            <MetricCard
              label="Critical services"
              value={String(observability.summary.critical_service_count)}
              caption="Any critical service should be reviewed before protected promotion or payroll cutover continues."
            />
            <MetricCard
              label="Degraded services"
              value={String(observability.summary.degraded_service_count)}
              caption={`${observability.summary.healthy_service_count} healthy service(s) are still within baseline`}
            />
            <MetricCard
              label="Workflow coverage"
              value={String(observability.summary.monitored_workflow_count)}
              caption="Release-critical workflow lanes that are explicitly monitored"
            />
            <MetricCard
              label="Integration coverage"
              value={String(observability.summary.monitored_integration_count)}
              caption="Approved integration domains currently mapped into this telemetry baseline"
            />
            <MetricCard
              label="Release-critical watch"
              value={String(observability.summary.release_critical_coverage_count)}
              caption={`${observability.telemetry.required_release_workflows.length} release workflow(s) are expected in the baseline`}
            />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(24rem,0.85fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Service health</h2>
                  <p className="text-sm text-muted-foreground">Each service exposes the routed alert count, live telemetry signals, and current operational posture.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {filteredServices.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Service</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Signals</TableHead>
                          <TableHead>Alerts</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredServices.map((service) => (
                          <TableRow key={service.key} className={selectedService?.key === service.key ? 'bg-primary/5' : undefined}>
                            <TableCell>
                              <span className="ui-table-primary">{service.name}</span>
                              <p className="text-xs text-muted-foreground">
                                {service.category} · {service.owner_role}
                              </p>
                            </TableCell>
                            <TableCell>
                              <Badge variant={serviceStatusBadgeVariant(service.status)}>{formatServiceStatus(service.status)}</Badge>
                            </TableCell>
                            <TableCell>{service.metric_count}</TableCell>
                            <TableCell>{service.alert_count}</TableCell>
                            <TableCell>
                              <Button
                                size="xs"
                                variant={selectedService?.key === service.key ? 'primary' : 'secondary'}
                                onClick={() => setSelectedServiceKey(service.key)}
                              >
                                {selectedService?.key === service.key ? 'Reviewing' : 'Review'}
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No services match this view"
                    copy="Widen the search or change the status filter to inspect more of the observability baseline."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Alert routing</h2>
                    <p className="text-sm text-muted-foreground">Approved severity lanes define who responds first and how quickly the incident should escalate.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent>
                  <div className="space-y-3">
                    {observability.alert_routes.map((route) => (
                      <article key={route.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-3">
                          <div>
                            <p className="text-sm font-semibold text-foreground">{route.name}</p>
                            <p className="text-xs text-muted-foreground">{route.owner_team}</p>
                          </div>
                          <Badge variant={severityBadgeVariant(route.severity)}>{route.severity.toUpperCase()}</Badge>
                        </div>
                        <p className="mt-3 text-sm text-muted-foreground">
                          Respond within {route.initial_response_minutes} minute(s) and escalate after {route.escalation_minutes} minute(s).
                        </p>
                        <div className="mt-3 flex flex-wrap gap-2">
                          {route.channels.map((channel) => (
                            <Badge key={channel} variant="neutral">
                              {channel}
                            </Badge>
                          ))}
                        </div>
                      </article>
                    ))}
                  </div>
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Telemetry baseline</h2>
                    <p className="text-sm text-muted-foreground">Core endpoint and logging assumptions stay visible next to the routed alert contract.</p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  <DetailCard label="Health endpoint" value={observability.telemetry.health_endpoint} />
                  <DetailCard label="Default log channel" value={observability.telemetry.default_log_channel} />
                  <DetailCard
                    label="Slack alert channel"
                    value={observability.telemetry.slack_alert_channel ?? 'Not configured'}
                    detail={`Refresh target ${observability.telemetry.dashboard_refresh_minutes} minute(s)`}
                  />
                  <DetailCard
                    label="Required release workflows"
                    value={observability.telemetry.required_release_workflows.join(', ') || 'None configured'}
                  />
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          {selectedService ? (
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Selected service</h2>
                    <p className="text-sm text-muted-foreground">{selectedService.summary}</p>
                  </div>
                  <Badge variant={serviceStatusBadgeVariant(selectedService.status)}>{formatServiceStatus(selectedService.status)}</Badge>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-4">
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                  <DetailCard label="Owner role" value={selectedService.owner_role} detail={selectedService.category} />
                  <DetailCard label="Signals" value={String(selectedService.metric_count)} />
                  <DetailCard label="Alerts" value={String(selectedService.alert_count)} />
                  <DetailCard label="Status" value={formatServiceStatus(selectedService.status)} />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <h3 className="text-base font-semibold text-foreground">Signals</h3>
                        <p className="text-sm text-muted-foreground">Thresholds, routing, and drill-ins for the live telemetry mapped to this service.</p>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      {selectedSignals.length ? (
                        <div className="space-y-3">
                          {selectedSignals.map((signal) => (
                            <article key={signal.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                              <div className="flex items-center justify-between gap-3">
                                <div>
                                  <p className="text-sm font-semibold text-foreground">{signal.name}</p>
                                  <p className="text-xs text-muted-foreground">
                                    {signal.owner_role} · observed {formatDate(signal.observed_at)}
                                  </p>
                                </div>
                                <Badge variant={signalStatusBadgeVariant(signal.status)}>{formatSignalStatus(signal.status)}</Badge>
                              </div>
                              <p className="mt-3 text-sm leading-6 text-muted-foreground">{signal.summary}</p>
                              <div className="mt-3 flex flex-wrap gap-3 text-sm text-foreground">
                                <span>
                                  Value <strong>{signal.value}</strong> {signal.unit}
                                </span>
                                <span>Threshold {signal.threshold ?? 'None'}</span>
                                <span>{signal.route_name ?? 'No active route'}</span>
                              </div>
                              <div className="mt-3 flex flex-wrap gap-2">
                                {signal.route_channels.map((channel) => (
                                  <Badge key={channel} variant="neutral">
                                    {channel}
                                  </Badge>
                                ))}
                              </div>
                              <div className="mt-3">
                                <Link to={signal.drill_in_path} className="text-sm font-medium text-primary hover:underline">
                                  {signal.drill_in_label}
                                </Link>
                              </div>
                            </article>
                          ))}
                        </div>
                      ) : (
                        <WorkspaceEmptyState
                          title="No live signals are attached"
                          copy="This service is represented in the baseline, but it currently has no direct numeric alert signals attached."
                        />
                      )}
                    </WorkspaceContent>
                  </WorkspaceSurface>

                  <div className="space-y-4">
                    <WorkspaceSurface>
                      <WorkspaceHeader compact>
                        <div className="space-y-1">
                          <h3 className="text-base font-semibold text-foreground">Active alerts</h3>
                          <p className="text-sm text-muted-foreground">Signals currently routed to response lanes for this service.</p>
                        </div>
                      </WorkspaceHeader>
                      <WorkspaceContent>
                        {selectedAlerts.length ? (
                          <div className="space-y-3">
                            {selectedAlerts.map((alert) => (
                              <article key={alert.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                                <div className="flex items-center justify-between gap-3">
                                  <div>
                                    <p className="text-sm font-semibold text-foreground">{alert.title}</p>
                                    <p className="text-xs text-muted-foreground">
                                      {alert.service_name} · started {formatDate(alert.started_at)}
                                    </p>
                                  </div>
                                  <Badge variant={severityBadgeVariant(alert.severity)}>{alert.severity.toUpperCase()}</Badge>
                                </div>
                                <p className="mt-3 text-sm leading-6 text-muted-foreground">{alert.summary}</p>
                                <div className="mt-3 flex flex-wrap gap-2">
                                  {alert.channels.map((channel) => (
                                    <Badge key={channel} variant="neutral">
                                      {channel}
                                    </Badge>
                                  ))}
                                </div>
                              </article>
                            ))}
                          </div>
                        ) : (
                          <WorkspaceEmptyState
                            title="No active alerts for this service"
                            copy="The selected service currently has no routed alert conditions."
                          />
                        )}
                      </WorkspaceContent>
                    </WorkspaceSurface>

                    <WorkspaceSurface>
                      <WorkspaceHeader compact>
                        <div className="space-y-1">
                          <h3 className="text-base font-semibold text-foreground">Coverage</h3>
                          <p className="text-sm text-muted-foreground">Release-critical workflows, integrations, or release lanes that inherit this service’s telemetry.</p>
                        </div>
                      </WorkspaceHeader>
                      <WorkspaceContent>
                        {selectedCoverage.length ? (
                          <div className="space-y-3">
                            {selectedCoverage.map((item) => (
                              <article key={item.key} className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
                                <div className="flex items-center justify-between gap-3">
                                  <div>
                                    <p className="text-sm font-semibold text-foreground">{item.name}</p>
                                    <p className="text-xs text-muted-foreground">
                                      {formatCoverageArea(item.area)} · {item.owner_role}
                                    </p>
                                  </div>
                                  <Badge variant={coverageStateBadgeVariant(item.coverage_state)}>{item.coverage_state}</Badge>
                                </div>
                                <p className="mt-3 text-sm leading-6 text-muted-foreground">{item.summary}</p>
                                <div className="mt-3 flex flex-wrap gap-3 text-sm text-foreground">
                                  <span>
                                    Monitored <strong>{item.monitored_entity_count}</strong>
                                  </span>
                                  <span>
                                    Issues <strong>{item.issue_count}</strong>
                                  </span>
                                </div>
                              </article>
                            ))}
                          </div>
                        ) : (
                          <WorkspaceEmptyState
                            title="No linked coverage lanes"
                            copy="This service currently does not own a workflow, integration, or release-critical coverage card in the baseline."
                          />
                        )}
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>
                </div>
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

function serviceStatusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'healthy':
      return 'success'
    case 'degraded':
      return 'warning'
    case 'critical':
      return 'danger'
    default:
      return 'info'
  }
}

function signalStatusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'healthy':
      return 'success'
    case 'warning':
      return 'warning'
    case 'critical':
      return 'danger'
    default:
      return 'info'
  }
}

function coverageStateBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'monitored':
      return 'success'
    case 'attention':
      return 'warning'
    default:
      return 'info'
  }
}

function severityBadgeVariant(severity: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (severity) {
    case 'sev1':
      return 'danger'
    case 'sev2':
      return 'warning'
    case 'sev3':
      return 'info'
    default:
      return 'neutral'
  }
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

function formatServiceStatus(status: string) {
  return status === 'degraded' ? 'Degraded' : status === 'critical' ? 'Critical' : 'Healthy'
}

function formatSignalStatus(status: string) {
  return status === 'warning' ? 'Warning' : status === 'critical' ? 'Critical' : 'Healthy'
}

function formatCoverageArea(area: string) {
  return area.replace(/_/g, ' ')
}

const nativeSelectClassName =
  'flex h-10 w-full rounded-2xl border border-border/70 bg-background px-3 text-sm text-foreground shadow-sm outline-none transition focus:border-primary/60 focus:ring-2 focus:ring-primary/15'
