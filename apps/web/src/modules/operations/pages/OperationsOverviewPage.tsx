import { Link } from 'react-router-dom'
import { AlertTriangle, FileWarning, LaptopMinimalCheck, ShieldAlert } from 'lucide-react'
import { formatRegionalDate } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import {
  CommandCenterAttentionItem,
  CommandCenterAttentionStrip,
  CommandCenterMetricCard,
  CommandCenterMetricGrid,
} from '../../../shared/ui/command-center'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
} from '../../../shared/ui/workspace'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

export function OperationsOverviewPage() {
  const workspace = useOperationsRouteWorkspace()

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading HR and IT operations...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No operations workspace is available yet.</p>
  }

  const issuedAssets = workspace.data.assets.filter((asset) => asset.status === 'issued')
  const overdueAssets = issuedAssets.filter((asset) => asset.current_assignment && isOverdue(asset.current_assignment.expected_return_date))
  const blockedAssets = workspace.data.assets.filter((asset) => asset.status === 'assigned' || asset.status === 'maintenance')
  const restrictedDocuments = workspace.data.documents.filter((document) => document.visibility_scope === 'confidential')
  const retentionAttention = workspace.data.documents.filter((document) => document.retention_until && isWithinDays(document.retention_until, 30))
  const onboardingRisk = workspace.data.lifecycle.onboarding.filter((record) => record.summary.incomplete_count > 0)
  const offboardingRisk = workspace.data.lifecycle.offboarding.filter((record) => record.summary.incomplete_count > 0)
  const failedIntegrationJobs = workspace.data.integrations.syncJobs.filter((job) => job.monitoring_state === 'failed')
  const pausedSubscriptions = workspace.data.integrations.subscriptions.filter((subscription) => subscription.status === 'paused')
  const failedResilienceScenarios = workspace.data.resilience.scenarios.filter((scenario) => scenario.status === 'failed')
  const attentionResilienceScenarios = workspace.data.resilience.scenarios.filter((scenario) => scenario.status === 'attention')
  const criticalObservabilityServices = workspace.data.observability.services.filter((service) => service.status === 'critical')
  const degradedObservabilityServices = workspace.data.observability.services.filter((service) => service.status === 'degraded')
  const criticalObservabilityAlerts = workspace.data.observability.alerts.filter((alert) => alert.severity === 'sev1')
  const blockedReleaseEnvironments = workspace.data.release.environments.filter((environment) => environment.status === 'blocked')
  const pendingReleaseEnvironments = workspace.data.release.environments.filter((environment) => environment.status === 'pending')
  const attentionItems = [
    blockedAssets[0]
      ? {
          id: 'blocked-asset',
          icon: <LaptopMinimalCheck className="h-4 w-4" />,
          tone: 'warning' as const,
          title: `${blockedAssets[0].asset_tag} needs a handoff decision`,
          detail: blockedAssets[0].notes ?? 'This asset is waiting for assignment or repair resolution.',
          to: '/operations/assets',
        }
      : null,
    overdueAssets[0]
      ? {
          id: 'overdue-return',
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: 'danger' as const,
          title: `${overdueAssets[0].asset_tag} is overdue for return`,
          detail: overdueAssets[0].current_assignment?.employee?.full_name ?? 'Current holder pending',
          to: '/operations/assets',
        }
      : null,
    retentionAttention[0]
      ? {
          id: 'retention',
          icon: <FileWarning className="h-4 w-4" />,
          tone: 'warning' as const,
          title: `${retentionAttention[0].title} is nearing retention review`,
          detail: `Retention date ${formatDate(retentionAttention[0].retention_until)}`,
          to: '/operations/documents',
        }
      : null,
    offboardingRisk[0]
      ? {
          id: 'offboarding',
          icon: <ShieldAlert className="h-4 w-4" />,
          tone: 'danger' as const,
          title: `${offboardingRisk[0].employee.full_name} still has open exit tasks`,
          detail: `${offboardingRisk[0].summary.incomplete_count} item(s) remain in offboarding.`,
          to: '/operations/lifecycle',
        }
      : null,
    failedIntegrationJobs[0]
      ? {
          id: 'integration-failure',
          icon: <ShieldAlert className="h-4 w-4" />,
          tone: 'danger' as const,
          title: `${failedIntegrationJobs[0].event_key} failed downstream delivery`,
          detail: failedIntegrationJobs[0].last_error ?? 'Operator retry is still required.',
          to: '/operations/integrations',
        }
      : null,
    workspace.canViewResilience && (failedResilienceScenarios[0] ?? attentionResilienceScenarios[0])
      ? {
          id: 'resilience-readiness',
          icon: <ShieldAlert className="h-4 w-4" />,
          tone: failedResilienceScenarios[0] ? ('danger' as const) : ('warning' as const),
          title: `${(failedResilienceScenarios[0] ?? attentionResilienceScenarios[0])?.name} needs recovery evidence review`,
          detail:
            (failedResilienceScenarios[0] ?? attentionResilienceScenarios[0])?.blocked_reason ??
            'Recovery validation needs operator follow-up before launch readiness is approved.',
          to: '/operations/resilience',
        }
      : null,
    workspace.canViewObservability && criticalObservabilityAlerts[0]
      ? {
          id: 'observability-critical',
          icon: <ShieldAlert className="h-4 w-4" />,
          tone: 'danger' as const,
          title: criticalObservabilityAlerts[0].title,
          detail: criticalObservabilityAlerts[0].summary,
          to: '/operations/observability',
        }
      : null,
    blockedReleaseEnvironments[0]
      ? {
          id: 'release-blocked',
          icon: <ShieldAlert className="h-4 w-4" />,
          tone: 'danger' as const,
          title: `${blockedReleaseEnvironments[0].name} is blocked by release gates`,
          detail: blockedReleaseEnvironments[0].blocked_reason ?? 'A blocking quality gate still needs recovery.',
          to: '/operations/release',
        }
      : null,
  ].filter((item): item is NonNullable<typeof item> => item !== null)

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Operations"
          title="HR and IT Operations Center"
          description="Keep document governance, asset handoffs, onboarding-offboarding follow-through, observability, and release-readiness posture in one routed workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            workspace.canManageDocuments ? 'Document governance live' : 'Document visibility',
            workspace.canManageAssets ? 'Asset controls live' : 'Asset visibility',
            workspace.canManageLifecycle ? 'Lifecycle controls live' : 'Lifecycle watch',
          ]}
        />

        <WorkspaceContent className="space-y-4">
          <CommandCenterMetricGrid className="xl:grid-cols-4 2xl:grid-cols-8">
            <CommandCenterMetricCard
              label="Document categories"
              value={String(workspace.data.documentCategories.length)}
              delta={`${restrictedDocuments.length} confidential file(s) currently tracked`}
              tone="info"
            />
            <CommandCenterMetricCard
              label="Issued assets"
              value={String(issuedAssets.length)}
              delta={`${overdueAssets.length} return target(s) are overdue`}
              tone="success"
            />
            <CommandCenterMetricCard
              label="Blocked handoffs"
              value={String(blockedAssets.length)}
              delta="Assigned or maintenance assets that still need operator action"
              tone="warning"
            />
            <CommandCenterMetricCard
              label="Lifecycle watch"
              value={String(onboardingRisk.length + offboardingRisk.length)}
              delta={`${offboardingRisk.length} offboarding record(s) still open`}
              tone="danger"
            />
            <CommandCenterMetricCard
              label="Sync failures"
              value={String(failedIntegrationJobs.length)}
              delta={`${pausedSubscriptions.length} subscription(s) are currently paused`}
              tone={failedIntegrationJobs.length ? 'danger' : 'info'}
            />
            {workspace.canViewResilience ? (
              <CommandCenterMetricCard
                label="Recovery readiness"
                value={String(failedResilienceScenarios.length + attentionResilienceScenarios.length)}
                delta={`${workspace.data.resilience.summary.ready_scenario_count} scenario(s) are currently ready`}
                tone={failedResilienceScenarios.length ? 'danger' : attentionResilienceScenarios.length ? 'warning' : 'success'}
              />
            ) : null}
            {workspace.canViewObservability ? (
              <CommandCenterMetricCard
                label="Critical services"
                value={String(criticalObservabilityServices.length)}
                delta={`${degradedObservabilityServices.length} degraded service(s) have routed signals`}
                tone={criticalObservabilityServices.length ? 'danger' : degradedObservabilityServices.length ? 'warning' : 'success'}
              />
            ) : null}
            <CommandCenterMetricCard
              label="Release gate watch"
              value={String(blockedReleaseEnvironments.length)}
              delta={`${pendingReleaseEnvironments.length} promotion stage(s) are pending approval`}
              tone={blockedReleaseEnvironments.length ? 'danger' : pendingReleaseEnvironments.length ? 'warning' : 'success'}
            />
          </CommandCenterMetricGrid>

          {attentionItems.length ? (
            <CommandCenterAttentionStrip title="Needs attention" className="border-[color-mix(in_srgb,var(--warning)_12%,white)]">
              {attentionItems.map((item) => (
                <CommandCenterAttentionItem
                  key={item.id}
                  title={item.title}
                  detail={item.detail}
                  icon={item.icon}
                  tone={item.tone}
                  to={item.to}
                />
              ))}
            </CommandCenterAttentionStrip>
          ) : (
            <WorkspaceEmptyState
              title="Operations posture looks healthy"
              copy="There are no blocked handoffs, overdue returns, or open lifecycle exceptions needing immediate review."
            />
          )}

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Asset pressure</h2>
                  <p className="text-sm text-muted-foreground">Return targets and blocked inventory that need operator follow-up.</p>
                </div>
                <WorkspaceHeaderActions>
                  <Link to="/operations/assets">Open assets</Link>
                </WorkspaceHeaderActions>
              </WorkspaceHeader>
              <WorkspaceContent>
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Asset</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Holder</TableHead>
                        <TableHead>Return target</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {(overdueAssets.length ? overdueAssets : blockedAssets).slice(0, 4).map((asset) => (
                        <TableRow key={asset.id}>
                          <TableCell>
                            <span className="ui-table-primary">{asset.asset_tag}</span>
                            <p className="text-xs text-muted-foreground">{asset.name}</p>
                          </TableCell>
                          <TableCell>
                            <Badge variant={asset.status === 'maintenance' ? 'warning' : asset.status === 'assigned' ? 'warning' : 'danger'}>
                              {formatAssetStatus(asset.status)}
                            </Badge>
                          </TableCell>
                          <TableCell>{asset.current_assignment?.employee?.full_name ?? 'Unassigned'}</TableCell>
                          <TableCell>{asset.current_assignment?.expected_return_date ? formatDate(asset.current_assignment.expected_return_date) : 'No target'}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Lifecycle pressure</h2>
                  <p className="text-sm text-muted-foreground">Onboarding and offboarding records that still carry incomplete work.</p>
                </div>
                <WorkspaceHeaderActions>
                  <Link to="/operations/lifecycle">Open lifecycle</Link>
                </WorkspaceHeaderActions>
              </WorkspaceHeader>
              <WorkspaceContent>
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Employee</TableHead>
                        <TableHead>Lifecycle</TableHead>
                        <TableHead>Progress</TableHead>
                        <TableHead>Open items</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {[...onboardingRisk, ...offboardingRisk].slice(0, 4).map((record) => (
                        <TableRow key={`${record.lifecycle_type}-${record.employee.id}`}>
                          <TableCell>
                            <span className="ui-table-primary">{record.employee.full_name}</span>
                            <p className="text-xs text-muted-foreground">{record.employee.employee_code}</p>
                          </TableCell>
                          <TableCell>{record.lifecycle_type}</TableCell>
                          <TableCell>{record.summary.progress_percentage}%</TableCell>
                          <TableCell>{record.summary.incomplete_count}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Document governance watch</h2>
                <p className="text-sm text-muted-foreground">Retention and visibility posture across repository files now in scope for Sprint 6.</p>
              </div>
              <WorkspaceHeaderActions>
                <Link to="/operations/documents">Open documents</Link>
              </WorkspaceHeaderActions>
            </WorkspaceHeader>
            <WorkspaceContent>
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Document</TableHead>
                      <TableHead>Category</TableHead>
                      <TableHead>Visibility</TableHead>
                      <TableHead>Retention</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {workspace.data.documents.slice(0, 4).map((document) => (
                      <TableRow key={document.id}>
                        <TableCell>
                          <span className="ui-table-primary">{document.title}</span>
                          <p className="text-xs text-muted-foreground">{document.original_file_name}</p>
                        </TableCell>
                        <TableCell>{document.document_category?.name ?? 'Uncategorized'}</TableCell>
                        <TableCell>
                          <Badge variant={document.visibility_scope === 'confidential' ? 'danger' : document.visibility_scope === 'restricted' ? 'warning' : 'neutral'}>
                            {document.visibility_scope}
                          </Badge>
                        </TableCell>
                        <TableCell>{document.retention_until ? formatDate(document.retention_until) : 'No retention set'}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            </WorkspaceContent>
          </WorkspaceSurface>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

function formatAssetStatus(status: string) {
  return status.replace(/_/g, ' ')
}

function isOverdue(value: string | null) {
  if (!value) {
    return false
  }

  return value < new Date().toISOString().slice(0, 10)
}

function isWithinDays(value: string, days: number) {
  const now = new Date()
  const target = new Date(value)
  const diff = target.getTime() - now.getTime()
  const diffDays = diff / (1000 * 60 * 60 * 24)

  return diffDays >= 0 && diffDays <= days
}
