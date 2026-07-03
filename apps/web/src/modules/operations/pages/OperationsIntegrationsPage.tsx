import { useState, type FormEvent } from 'react'
import { formatRegionalDate } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
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
import { ApiRequestError } from '../../../shared/api/http'
import type {
  IntegrationConnectionFormValues,
  IntegrationDispatchFormValues,
  IntegrationSubscriptionFormValues,
} from '../types'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

const emptyConnectionForm: IntegrationConnectionFormValues = {
  system_key: 'identity_directory',
  name: '',
  direction: 'outbound',
  status: 'active',
  auth_mode: 'hmac_sha256',
  endpoint_url: '',
  description: '',
  scopes: '',
}

const emptySubscriptionForm: IntegrationSubscriptionFormValues = {
  integration_connection_id: '',
  event_key: 'employee.updated',
  direction: 'outbound',
  status: 'active',
  endpoint_url: '',
  secret: '',
  custom_headers: '{\n  "X-Partner-Key": "phoenix-demo"\n}',
  filter_rules: '{\n  "entity_types": ["employee"]\n}',
}

const emptyDispatchForm: IntegrationDispatchFormValues = {
  event_key: 'employee.updated',
  entity_type: 'employee',
  entity_id: '',
  payload: '{\n  "employee_code": "EMP-1005",\n  "status": "active"\n}',
}

export function OperationsIntegrationsPage() {
  const workspace = useOperationsRouteWorkspace()
  const [search, setSearch] = useState('')
  const [stateFilter, setStateFilter] = useState('all')
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [selectedJobId, setSelectedJobId] = useState<number | null>(null)
  const [isConnectionModalOpen, setIsConnectionModalOpen] = useState(false)
  const [isSubscriptionModalOpen, setIsSubscriptionModalOpen] = useState(false)
  const [isDispatchModalOpen, setIsDispatchModalOpen] = useState(false)
  const [connectionForm, setConnectionForm] = useState<IntegrationConnectionFormValues>(emptyConnectionForm)
  const [subscriptionForm, setSubscriptionForm] = useState<IntegrationSubscriptionFormValues>(emptySubscriptionForm)
  const [dispatchForm, setDispatchForm] = useState<IntegrationDispatchFormValues>(emptyDispatchForm)

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading integration operations...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No integration operations workspace is available yet.</p>
  }

  const integrationData = workspace.data.integrations
  const normalizedQuery = search.trim().toLowerCase()
  const filteredJobs = integrationData.syncJobs.filter((job) => {
    if (stateFilter !== 'all' && job.monitoring_state !== stateFilter) {
      return false
    }

    if (!normalizedQuery) {
      return true
    }

    return [job.event_key, job.system_key, job.entity_id ?? '', job.last_error ?? '', job.connection?.name ?? '']
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })

  const activeConnections = integrationData.connections.filter((connection) => connection.status === 'active')
  const activeSubscriptions = integrationData.subscriptions.filter((subscription) => subscription.status === 'active')
  const failedJobs = integrationData.syncJobs.filter((job) => job.monitoring_state === 'failed')
  const queuedJobs = integrationData.syncJobs.filter((job) => job.monitoring_state === 'queued')
  const retriedJobs = integrationData.syncJobs.filter((job) => job.monitoring_state === 'retried')
  const pausedSubscriptions = integrationData.subscriptions.filter((subscription) => subscription.status === 'paused')
  const availableSystems = integrationData.systems.reduce<Record<string, string>>((accumulator, system) => {
    accumulator[system.key] = system.name
    return accumulator
  }, {})
  const selectedJob = filteredJobs.find((job) => job.id === selectedJobId) ?? filteredJobs[0] ?? null

  async function handleCreateConnection(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.saveIntegrationConnection(connectionForm)
      setActionMessage('Integration connection created.')
      setConnectionForm(emptyConnectionForm)
      setIsConnectionModalOpen(false)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The integration connection could not be created.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleCreateSubscription(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.saveIntegrationSubscription(subscriptionForm)
      setActionMessage('Webhook subscription created.')
      setSubscriptionForm({
        ...emptySubscriptionForm,
        integration_connection_id: subscriptionForm.integration_connection_id,
      })
      setIsSubscriptionModalOpen(false)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The webhook subscription could not be created.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleDispatchEvent(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSaving(true)
    setActionMessage(null)
    setActionError(null)
    setFieldErrors({})

    try {
      await workspace.triggerIntegrationEvent(dispatchForm)
      setActionMessage('Integration event dispatched.')
      setDispatchForm(emptyDispatchForm)
      setIsDispatchModalOpen(false)
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The integration event could not be dispatched.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleRetryJob(jobId: number) {
    setIsSaving(true)
    setSelectedJobId(jobId)
    setActionMessage(null)
    setActionError(null)

    try {
      await workspace.retryFailedIntegrationJob(jobId)
      setActionMessage('Sync job retried successfully.')
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The sync job could not be retried.')
    } finally {
      setIsSaving(false)
    }
  }

  async function handleProcessJob(jobId: number) {
    setIsSaving(true)
    setSelectedJobId(jobId)
    setActionMessage(null)
    setActionError(null)

    try {
      await workspace.processQueuedIntegrationJob(jobId)
      setActionMessage('Sync job processed successfully.')
    } catch (error) {
      handleApiError(error, setActionError, setFieldErrors, 'The sync job could not be processed.')
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Integration Operations"
          title="Integration Operations"
          description="Monitor approved webhook posture, dispatch governed outbound events, and retry failed sync jobs without leaving the operations workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            `${integrationData.connections.length} connection(s) tracked`,
            `${integrationData.syncJobMeta.total} sync job(s) visible`,
            workspace.canManageIntegrations ? 'Operator controls live' : 'Read only session',
          ]}
          actions={
            workspace.canManageIntegrations ? (
              <>
                <Button size="xs" variant="secondary" onClick={() => setIsConnectionModalOpen(true)}>
                  Add connection
                </Button>
                <Button
                  size="xs"
                  variant="secondary"
                  onClick={() => {
                    setSubscriptionForm((current) => ({
                      ...current,
                      integration_connection_id:
                        current.integration_connection_id ||
                        String(integrationData.connections[0]?.id ?? ''),
                    }))
                    setIsSubscriptionModalOpen(true)
                  }}
                >
                  Add subscription
                </Button>
                <Button size="xs" variant="primary" onClick={() => setIsDispatchModalOpen(true)}>
                  Dispatch event
                </Button>
              </>
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
                    placeholder="Search systems, events, entity ids, or error text"
                  />
                </WorkspaceField>
                <WorkspaceField label="Monitor state" compact>
                  <select
                    aria-label="Monitor state"
                    className={nativeSelectClassName}
                    value={stateFilter}
                    onChange={(event) => setStateFilter(event.target.value)}
                  >
                    <option value="all">All states</option>
                    <option value="failed">Failed</option>
                    <option value="queued">Queued</option>
                    <option value="running">Running</option>
                    <option value="retried">Retried</option>
                    <option value="completed">Completed</option>
                  </select>
                </WorkspaceField>
              </div>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageIntegrations ? (
            <p className="workspace-muted">This session can inspect integration posture, but connection changes, outbound dispatches, and retries stay restricted without `integration.manage`.</p>
          ) : null}

          <div className="organization-metric-grid">
            <MetricCard label="Active connections" value={String(activeConnections.length)} caption={`${integrationData.connections.length} governed bridge(s) currently configured`} />
            <MetricCard label="Active subscriptions" value={String(activeSubscriptions.length)} caption={`${pausedSubscriptions.length} subscription(s) are currently paused`} />
            <MetricCard label="Failed jobs" value={String(failedJobs.length)} caption="Jobs requiring operator review before downstream delivery can recover" />
            <MetricCard label="Retry posture" value={String(retriedJobs.length)} caption={`${queuedJobs.length} job(s) are still queued for processing`} />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Connections</h2>
                  <p className="text-sm text-muted-foreground">Operator-approved external systems and their current direction or status posture.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {integrationData.connections.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Connection</TableHead>
                          <TableHead>Direction</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Subscriptions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {integrationData.connections.map((connection) => (
                          <TableRow key={connection.id}>
                            <TableCell>
                              <span className="ui-table-primary">{connection.name}</span>
                              <p className="text-xs text-muted-foreground">{availableSystems[connection.system_key] ?? connection.system_key}</p>
                            </TableCell>
                            <TableCell>{connection.direction}</TableCell>
                            <TableCell>
                              <Badge variant={connection.status === 'active' ? 'success' : connection.status === 'paused' ? 'warning' : 'neutral'}>
                                {connection.status}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              {connection.active_subscription_count ?? 0}
                              <p className="text-xs text-muted-foreground">
                                {connection.last_synced_at ? `Last sync ${formatDate(connection.last_synced_at)}` : 'No sync completed yet'}
                              </p>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No integration connections are configured"
                    copy="Create the first connection to start governing webhook subscriptions and sync-job monitoring."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Webhook subscriptions</h2>
                  <p className="text-sm text-muted-foreground">Review event bindings, secret posture, and recent delivery or receipt timestamps.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {integrationData.subscriptions.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Event</TableHead>
                          <TableHead>Direction</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Activity</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {integrationData.subscriptions.map((subscription) => (
                          <TableRow key={subscription.id}>
                            <TableCell>
                              <span className="ui-table-primary">{subscription.event_key}</span>
                              <p className="text-xs text-muted-foreground">{subscription.connection?.name ?? 'Unknown connection'}</p>
                            </TableCell>
                            <TableCell>{subscription.direction}</TableCell>
                            <TableCell>
                              <Badge variant={subscription.status === 'active' ? 'success' : subscription.status === 'paused' ? 'warning' : 'neutral'}>
                                {subscription.status}
                              </Badge>
                              <p className="mt-1 text-xs text-muted-foreground">{subscription.secret_preview ?? 'Secret hidden'}</p>
                            </TableCell>
                            <TableCell>
                              {subscription.last_delivery_at ? `Delivered ${formatDate(subscription.last_delivery_at)}` : 'No outbound delivery'}
                              <p className="text-xs text-muted-foreground">
                                {subscription.last_received_at ? `Received ${formatDate(subscription.last_received_at)}` : 'No inbound receipt'}
                              </p>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No webhook subscriptions exist yet"
                    copy="Bind an approved event to a connection so the monitored sync-job queue has a governed source of truth."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Sync-job monitor</h2>
                <p className="text-sm text-muted-foreground">Queued, failed, retried, and completed jobs stay visible here for operator follow-through.</p>
              </div>
            </WorkspaceHeader>
            <WorkspaceContent>
              {filteredJobs.length ? (
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Event</TableHead>
                        <TableHead>System</TableHead>
                        <TableHead>State</TableHead>
                        <TableHead>Attempts</TableHead>
                        <TableHead>Latest outcome</TableHead>
                        <TableHead>Action</TableHead>
                      </TableRow>
                    </TableHeader>
                      <TableBody>
                        {filteredJobs.map((job) => (
                        <TableRow key={job.id} className={selectedJob?.id === job.id ? 'bg-primary/5' : undefined}>
                          <TableCell>
                            <span className="ui-table-primary">{job.event_key}</span>
                            <p className="text-xs text-muted-foreground">{job.entity_id ?? job.job_uuid}</p>
                          </TableCell>
                          <TableCell>
                            {availableSystems[job.system_key] ?? job.system_key}
                            <p className="text-xs text-muted-foreground">{job.direction}</p>
                          </TableCell>
                          <TableCell>
                            <Badge variant={job.monitoring_state === 'failed' ? 'danger' : job.monitoring_state === 'queued' ? 'warning' : job.monitoring_state === 'retried' ? 'info' : 'success'}>
                              {job.monitoring_state}
                            </Badge>
                          </TableCell>
                          <TableCell>{job.attempts_count}</TableCell>
                          <TableCell>
                            {job.last_error ?? 'Delivery healthy'}
                            <p className="text-xs text-muted-foreground">
                              {job.last_attempt_at ? `Last attempt ${formatDate(job.last_attempt_at)}` : 'No attempt recorded yet'}
                            </p>
                          </TableCell>
                          <TableCell>
                            <div className="flex flex-wrap gap-2">
                              <Button
                                size="xs"
                                variant={selectedJob?.id === job.id ? 'primary' : 'secondary'}
                                onClick={() => setSelectedJobId(job.id)}
                              >
                                {selectedJob?.id === job.id ? 'Reviewing' : 'Review'}
                              </Button>
                              {workspace.canManageIntegrations && job.status === 'queued' ? (
                                <Button
                                  size="xs"
                                  variant="secondary"
                                  disabled={isSaving}
                                  onClick={() => handleProcessJob(job.id)}
                                >
                                  Process
                                </Button>
                              ) : null}
                              {workspace.canManageIntegrations && job.can_retry ? (
                                <Button
                                  size="xs"
                                  variant="secondary"
                                  disabled={isSaving}
                                  onClick={() => handleRetryJob(job.id)}
                                >
                                  Retry
                                </Button>
                              ) : null}
                              <span className="text-xs text-muted-foreground">
                                {describeJobAction(job, workspace.canManageIntegrations)}
                              </span>
                            </div>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              ) : (
                <WorkspaceEmptyState
                  title="No sync jobs match this view"
                  copy="Widen the search or monitor-state filter to inspect more integration history."
                />
              )}
            </WorkspaceContent>
          </WorkspaceSurface>

          {selectedJob ? (
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Selected sync job</h2>
                    <p className="text-sm text-muted-foreground">Inspect payloads, headers, state transitions, and retry evidence before taking the next operator action.</p>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge
                      variant={
                        selectedJob.monitoring_state === 'failed'
                          ? 'danger'
                          : selectedJob.monitoring_state === 'queued'
                            ? 'warning'
                            : selectedJob.monitoring_state === 'retried'
                              ? 'info'
                              : 'success'
                      }
                    >
                      {selectedJob.monitoring_state}
                    </Badge>
                    {workspace.canManageIntegrations && selectedJob.status === 'queued' ? (
                      <Button
                        size="xs"
                        variant="primary"
                        disabled={isSaving}
                        onClick={() => handleProcessJob(selectedJob.id)}
                      >
                        Process selected job
                      </Button>
                    ) : null}
                    {workspace.canManageIntegrations && selectedJob.can_retry ? (
                      <Button
                        size="xs"
                        variant="primary"
                        disabled={isSaving}
                        onClick={() => handleRetryJob(selectedJob.id)}
                      >
                        Retry selected job
                      </Button>
                    ) : null}
                    {!workspace.canManageIntegrations ? <Badge variant="warning">Manage permission required</Badge> : null}
                  </div>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-4">
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                  <DetailCard
                    label="Connection"
                    value={selectedJob.connection?.name ?? 'Unknown connection'}
                    detail={`${availableSystems[selectedJob.system_key] ?? selectedJob.system_key} · ${selectedJob.direction}`}
                  />
                  <DetailCard
                    label="Subscription"
                    value={selectedJob.subscription?.subscription_key ?? 'No subscription key'}
                    detail={selectedJob.subscription?.event_key ?? 'Event binding unavailable'}
                  />
                  <DetailCard
                    label="Trigger"
                    value={selectedJob.trigger_source}
                    detail={selectedJob.entity_id ?? selectedJob.job_uuid}
                  />
                  <DetailCard
                    label="Attempts"
                    value={String(selectedJob.attempts_count)}
                    detail={selectedJob.last_error ?? 'No unresolved delivery error'}
                  />
                </div>

                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                  <DetailCard label="Queued" value={formatDate(selectedJob.queued_at)} />
                  <DetailCard label="Started" value={formatDate(selectedJob.started_at)} />
                  <DetailCard label="Completed" value={formatDate(selectedJob.completed_at)} />
                  <DetailCard label="Failed" value={formatDate(selectedJob.failed_at)} />
                  <DetailCard label="Retried" value={formatDate(selectedJob.retried_at)} />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                  <JsonPreview title="Request payload" value={selectedJob.request_payload} />
                  <JsonPreview title="Response payload" value={selectedJob.response_payload} />
                  <JsonPreview title="Request headers" value={selectedJob.request_headers} />
                  <JsonPreview title="Response headers" value={selectedJob.response_headers} />
                </div>

                <section className="space-y-3">
                  <div className="space-y-1">
                    <h3 className="text-base font-semibold text-foreground">Error history</h3>
                    <p className="text-sm text-muted-foreground">Each delivery failure stays visible here with attempt numbers and any captured request or response evidence.</p>
                  </div>
                  {selectedJob.errors.length ? (
                    <div className="grid gap-3">
                      {selectedJob.errors.map((error) => (
                        <article key={error.id} className="rounded-3xl border border-border/70 bg-card/90 p-4 shadow-sm">
                          <div className="flex flex-wrap items-center gap-2">
                            <p className="text-sm font-semibold text-foreground">Attempt {error.attempt_number}</p>
                            <Badge variant={error.resolved_at ? 'success' : 'danger'}>
                              {error.resolved_at ? 'Resolved' : 'Open'}
                            </Badge>
                            {error.error_code ? <Badge variant="neutral">{error.error_code}</Badge> : null}
                          </div>
                          <p className="mt-2 text-sm text-foreground">{error.error_message}</p>
                          <p className="mt-1 text-xs text-muted-foreground">
                            Occurred {formatDate(error.occurred_at)}
                            {error.resolved_at ? ` · Resolved ${formatDate(error.resolved_at)}` : ''}
                          </p>
                          <div className="mt-3 grid gap-3 lg:grid-cols-2">
                            <JsonPreview title="Error request" value={error.request_payload} />
                            <JsonPreview title="Error response" value={error.response_payload} />
                          </div>
                        </article>
                      ))}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">No delivery errors are recorded for this job yet.</p>
                  )}
                </section>
              </WorkspaceContent>
            </WorkspaceSurface>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isConnectionModalOpen}
        title="Create integration connection"
        description="Register a governed external system before webhooks and sync jobs can attach to it."
        onClose={() => setIsConnectionModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleCreateConnection}>
          <WorkspaceField label="System" error={fieldErrors.system_key?.[0]}>
            <select
              aria-label="Integration system"
              className={nativeSelectClassName}
              value={connectionForm.system_key}
              onChange={(event) => setConnectionForm((current) => ({ ...current, system_key: event.target.value }))}
            >
              {integrationData.systems.map((system) => (
                <option key={system.key} value={system.key}>
                  {system.name}
                </option>
              ))}
            </select>
          </WorkspaceField>
          <WorkspaceField label="Connection name" error={fieldErrors.name?.[0]}>
            <Input value={connectionForm.name} onChange={(event) => setConnectionForm((current) => ({ ...current, name: event.target.value }))} />
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Direction" error={fieldErrors.direction?.[0]}>
              <select
                aria-label="Connection direction"
                className={nativeSelectClassName}
                value={connectionForm.direction}
                onChange={(event) => setConnectionForm((current) => ({ ...current, direction: event.target.value }))}
              >
                <option value="inbound">Inbound</option>
                <option value="outbound">Outbound</option>
                <option value="bidirectional">Bidirectional</option>
              </select>
            </WorkspaceField>
            <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
              <select
                aria-label="Connection status"
                className={nativeSelectClassName}
                value={connectionForm.status}
                onChange={(event) => setConnectionForm((current) => ({ ...current, status: event.target.value }))}
              >
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="paused">Paused</option>
              </select>
            </WorkspaceField>
          </div>
          <WorkspaceField label="Auth mode" error={fieldErrors.auth_mode?.[0]}>
            <select
              aria-label="Connection auth mode"
              className={nativeSelectClassName}
              value={connectionForm.auth_mode}
              onChange={(event) => setConnectionForm((current) => ({ ...current, auth_mode: event.target.value }))}
            >
              <option value="hmac_sha256">HMAC SHA256</option>
              <option value="bearer">Bearer</option>
              <option value="none">None</option>
            </select>
          </WorkspaceField>
          <WorkspaceField label="Endpoint URL" error={fieldErrors.endpoint_url?.[0]}>
            <Input value={connectionForm.endpoint_url} onChange={(event) => setConnectionForm((current) => ({ ...current, endpoint_url: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Scopes" error={fieldErrors.scopes?.[0]}>
            <Input
              value={connectionForm.scopes}
              onChange={(event) => setConnectionForm((current) => ({ ...current, scopes: event.target.value }))}
              placeholder="employee.profile, employee.lifecycle"
            />
          </WorkspaceField>
          <WorkspaceField label="Description" error={fieldErrors.description?.[0]}>
            <Textarea rows={3} value={connectionForm.description} onChange={(event) => setConnectionForm((current) => ({ ...current, description: event.target.value }))} />
          </WorkspaceField>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsConnectionModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Saving...' : 'Create connection'}
            </Button>
          </div>
        </form>
      </Modal>

      <Modal
        open={isSubscriptionModalOpen}
        title="Create webhook subscription"
        description="Bind an approved event to a governed connection so operator-visible sync jobs can be generated."
        onClose={() => setIsSubscriptionModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleCreateSubscription}>
          <WorkspaceField label="Connection" error={fieldErrors.integration_connection_id?.[0]}>
            <select
              aria-label="Subscription connection"
              className={nativeSelectClassName}
              value={subscriptionForm.integration_connection_id}
              onChange={(event) => setSubscriptionForm((current) => ({ ...current, integration_connection_id: event.target.value }))}
            >
              <option value="">Select connection</option>
              {integrationData.connections.map((connection) => (
                <option key={connection.id} value={String(connection.id)}>
                  {connection.name}
                </option>
              ))}
            </select>
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Event" error={fieldErrors.event_key?.[0]}>
              <select
                aria-label="Subscription event"
                className={nativeSelectClassName}
                value={subscriptionForm.event_key}
                onChange={(event) => setSubscriptionForm((current) => ({ ...current, event_key: event.target.value }))}
              >
                {integrationData.events.map((eventRecord) => (
                  <option key={eventRecord.key} value={eventRecord.key}>
                    {eventRecord.key}
                  </option>
                ))}
              </select>
            </WorkspaceField>
            <WorkspaceField label="Direction" error={fieldErrors.direction?.[0]}>
              <select
                aria-label="Subscription direction"
                className={nativeSelectClassName}
                value={subscriptionForm.direction}
                onChange={(event) => setSubscriptionForm((current) => ({ ...current, direction: event.target.value }))}
              >
                <option value="outbound">Outbound</option>
                <option value="inbound">Inbound</option>
              </select>
            </WorkspaceField>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
              <select
                aria-label="Subscription status"
                className={nativeSelectClassName}
                value={subscriptionForm.status}
                onChange={(event) => setSubscriptionForm((current) => ({ ...current, status: event.target.value }))}
              >
                <option value="active">Active</option>
                <option value="paused">Paused</option>
                <option value="disabled">Disabled</option>
              </select>
            </WorkspaceField>
            <WorkspaceField label="Secret" error={fieldErrors.secret?.[0]}>
              <Input value={subscriptionForm.secret} onChange={(event) => setSubscriptionForm((current) => ({ ...current, secret: event.target.value }))} />
            </WorkspaceField>
          </div>
          <WorkspaceField label="Endpoint URL" error={fieldErrors.endpoint_url?.[0]}>
            <Input value={subscriptionForm.endpoint_url} onChange={(event) => setSubscriptionForm((current) => ({ ...current, endpoint_url: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Custom headers (JSON)" error={fieldErrors.custom_headers?.[0]}>
            <Textarea rows={4} value={subscriptionForm.custom_headers} onChange={(event) => setSubscriptionForm((current) => ({ ...current, custom_headers: event.target.value }))} />
          </WorkspaceField>
          <WorkspaceField label="Filter rules (JSON)" error={fieldErrors.filter_rules?.[0]}>
            <Textarea rows={4} value={subscriptionForm.filter_rules} onChange={(event) => setSubscriptionForm((current) => ({ ...current, filter_rules: event.target.value }))} />
          </WorkspaceField>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsSubscriptionModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Saving...' : 'Create subscription'}
            </Button>
          </div>
        </form>
      </Modal>

      <Modal
        open={isDispatchModalOpen}
        title="Dispatch integration event"
        description="Trigger an approved outbound event so matching active subscriptions enter the monitored sync queue."
        onClose={() => setIsDispatchModalOpen(false)}
      >
        <form className="space-y-3" onSubmit={handleDispatchEvent}>
          <WorkspaceField label="Event" error={fieldErrors.event_key?.[0]}>
            <select
              aria-label="Dispatch event key"
              className={nativeSelectClassName}
              value={dispatchForm.event_key}
              onChange={(event) => setDispatchForm((current) => ({ ...current, event_key: event.target.value }))}
            >
              {integrationData.events
                .filter((eventRecord) => eventRecord.directions.includes('outbound'))
                .map((eventRecord) => (
                  <option key={eventRecord.key} value={eventRecord.key}>
                    {eventRecord.key}
                  </option>
                ))}
            </select>
          </WorkspaceField>
          <div className="grid gap-3 sm:grid-cols-2">
            <WorkspaceField label="Entity type" error={fieldErrors.entity_type?.[0]}>
              <Input value={dispatchForm.entity_type} onChange={(event) => setDispatchForm((current) => ({ ...current, entity_type: event.target.value }))} />
            </WorkspaceField>
            <WorkspaceField label="Entity id" error={fieldErrors.entity_id?.[0]}>
              <Input value={dispatchForm.entity_id} onChange={(event) => setDispatchForm((current) => ({ ...current, entity_id: event.target.value }))} />
            </WorkspaceField>
          </div>
          <WorkspaceField label="Payload (JSON)" error={fieldErrors.payload?.[0]}>
            <Textarea rows={7} value={dispatchForm.payload} onChange={(event) => setDispatchForm((current) => ({ ...current, payload: event.target.value }))} />
          </WorkspaceField>
          <div className="flex justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => setIsDispatchModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isSaving}>
              {isSaving ? 'Dispatching...' : 'Dispatch event'}
            </Button>
          </div>
        </form>
      </Modal>
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

function JsonPreview({ title, value }: { title: string; value: Record<string, unknown> }) {
  return (
    <article className="rounded-3xl border border-border/70 bg-card/95 p-4 shadow-sm">
      <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">{title}</p>
      <pre className="mt-3 overflow-x-auto rounded-2xl bg-slate-950/95 p-4 text-xs leading-6 text-slate-100">
        {formatJsonValue(value)}
      </pre>
    </article>
  )
}

function describeJobAction(
  job: { status: string; can_retry: boolean },
  canManageIntegrations: boolean,
) {
  if (job.status === 'queued') {
    return canManageIntegrations ? 'Ready for operator processing' : 'Manage permission required'
  }

  if (job.can_retry) {
    return canManageIntegrations ? 'Retry available' : 'Manage permission required'
  }

  return 'No action required'
}

function formatJsonValue(value: Record<string, unknown>) {
  if (!Object.keys(value).length) {
    return '{}'
  }

  return JSON.stringify(value, null, 2)
}

function handleApiError(
  error: unknown,
  setActionError: (value: string | null) => void,
  setFieldErrors: (value: Record<string, string[]>) => void,
  fallbackMessage: string,
) {
  if (error instanceof ApiRequestError) {
    setActionError(error.message)
    setFieldErrors(error.fieldErrors)
    return
  }

  if (error instanceof Error) {
    setActionError(error.message)
    setFieldErrors({})
    return
  }

  setActionError(fallbackMessage)
  setFieldErrors({})
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

const nativeSelectClassName =
  'flex h-10 w-full rounded-2xl border border-border/70 bg-background px-3 text-sm text-foreground shadow-sm outline-none transition focus:border-primary/60 focus:ring-2 focus:ring-primary/15'
