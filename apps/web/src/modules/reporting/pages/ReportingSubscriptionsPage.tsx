import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { BellRing, PlayCircle, RefreshCw, ShieldAlert } from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { useReportingRouteWorkspace } from './useReportingRouteWorkspace'
import type {
  ReportingExportFormat,
  ReportingSubscriptionFrequency,
  ReportingSubscriptionRecord,
  ReportingSubscriptionStatus,
} from '../types'

type SubscriptionTab = 'all' | ReportingSubscriptionStatus
type SubscriptionSourceType = 'saved_view' | 'dataset'

interface SubscriptionFormState {
  sourceType: SubscriptionSourceType
  savedReportViewId: string
  datasetKey: string
  name: string
  description: string
  frequency: ReportingSubscriptionFrequency
  exportFormat: ReportingExportFormat
  timezone: string
  timeOfDay: string
  weekday: string
  dayOfMonth: string
}

const subscriptionTabs: Array<{ id: SubscriptionTab; label: string }> = [
  { id: 'all', label: 'All subscriptions' },
  { id: 'active', label: 'Active' },
  { id: 'paused', label: 'Paused' },
  { id: 'blocked', label: 'Blocked' },
  { id: 'revoked', label: 'Revoked' },
]

function defaultSubscriptionForm(): SubscriptionFormState {
  return {
    sourceType: 'saved_view',
    savedReportViewId: '',
    datasetKey: '',
    name: '',
    description: '',
    frequency: 'weekly',
    exportFormat: 'csv',
    timezone: 'Asia/Kolkata',
    timeOfDay: '09:00',
    weekday: '1',
    dayOfMonth: '1',
  }
}

function statusVariant(status: ReportingSubscriptionStatus) {
  if (status === 'active') {
    return 'success' as const
  }

  if (status === 'paused') {
    return 'warning' as const
  }

  if (status === 'blocked') {
    return 'danger' as const
  }

  return 'neutral' as const
}

function domainLabel(domain: string | null | undefined) {
  if (!domain) {
    return 'Unassigned'
  }

  return domain.replace(/_/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase())
}

function formatDateTime(value: string | null) {
  if (!value) {
    return 'Not scheduled'
  }

  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleString()
}

function sourceLabel(subscription: ReportingSubscriptionRecord) {
  if (subscription.source.saved_view?.name) {
    return `${subscription.source.saved_view.name} · saved view`
  }

  if (subscription.source.dataset?.name) {
    return `${subscription.source.dataset.name} · dataset`
  }

  return 'Source unavailable'
}

export function ReportingSubscriptionsPage() {
  const workspace = useReportingRouteWorkspace()
  const [activeTab, setActiveTab] = useState<SubscriptionTab>('all')
  const [selectedSubscriptionId, setSelectedSubscriptionId] = useState<number | null>(null)
  const [form, setForm] = useState<SubscriptionFormState>(defaultSubscriptionForm())

  const subscriptions = useMemo(() => workspace.data?.subscriptions ?? [], [workspace.data?.subscriptions])
  const savedViews = useMemo(() => workspace.data?.savedViews ?? [], [workspace.data?.savedViews])
  const datasets = useMemo(() => workspace.data?.datasets ?? [], [workspace.data?.datasets])

  const filteredSubscriptions = useMemo(() => {
    if (activeTab === 'all') {
      return subscriptions
    }

    return subscriptions.filter((subscription) => subscription.status === activeTab)
  }, [activeTab, subscriptions])

  const selectedSubscription =
    filteredSubscriptions.find((subscription) => subscription.id === selectedSubscriptionId) ??
    subscriptions.find((subscription) => subscription.id === selectedSubscriptionId) ??
    filteredSubscriptions[0] ??
    null

  const savedViewOptions = useMemo(
    () =>
      savedViews.map(
        (view) => [String(view.id), `${view.name} · ${view.dataset?.name ?? 'Dataset unavailable'}`] as [string, string],
      ),
    [savedViews],
  )

  const datasetOptions = useMemo(
    () =>
      datasets.map(
        (dataset) => [dataset.key, `${dataset.name} · ${domainLabel(dataset.domain)}`] as [string, string],
      ),
    [datasets],
  )

  async function handleCreateSubscription() {
    if (!form.name.trim()) {
      return
    }

    const scheduleConfig: {
      time_of_day: string
      weekday?: number
      day_of_month?: number
    } = {
      time_of_day: form.timeOfDay,
    }

    if (form.frequency === 'weekly') {
      scheduleConfig.weekday = Number(form.weekday || '1')
    }

    if (form.frequency === 'monthly') {
      scheduleConfig.day_of_month = Number(form.dayOfMonth || '1')
    }

    await workspace.actions.createSubscription({
      ...(form.sourceType === 'saved_view' && form.savedReportViewId
        ? { saved_report_view_id: Number(form.savedReportViewId) }
        : { dataset_key: form.datasetKey }),
      name: form.name.trim(),
      description: form.description.trim() || null,
      delivery_channel: 'in_app_notification',
      delivery_target: 'owner_only',
      export_format: form.exportFormat,
      frequency: form.frequency,
      timezone: form.timezone,
      schedule_config: scheduleConfig,
    })

    setForm(defaultSubscriptionForm())
  }

  async function handlePauseOrResume(subscription: ReportingSubscriptionRecord) {
    await workspace.actions.updateSubscription(subscription.id, {
      status: subscription.status === 'active' ? 'paused' : 'active',
    })
  }

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title="Loading subscription center"
        copy="Resolving recurring delivery posture, blocked-state visibility, and governed source options."
      />
    )
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Subscription center unavailable" copy={workspace.error.message} />
  }

  if (!workspace.canViewReporting) {
    return (
      <WorkspaceEmptyState
        title="Subscription center unavailable"
        copy="This session does not currently resolve to governed reporting visibility."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Reporting"
          title="Subscription Center"
          description="Manage recurring report delivery from approved datasets or saved views, inspect blocked-state posture, and manually trigger governed refreshes when needed."
          badge={
            <Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>
              {workspace.source === 'demo' ? 'Demo subscriptions' : 'Live subscriptions'}
            </Badge>
          }
          context={[
            `${subscriptions.length} subscription${subscriptions.length === 1 ? '' : 's'} in scope`,
            `${subscriptions.filter((subscription) => subscription.status === 'blocked').length} blocked`,
          ]}
          actions={
            <WorkspaceActionsRow>
              <Button asChild size="sm" variant="secondary">
                <Link to="/reporting/explorer">Open explorer</Link>
              </Button>
              <Button asChild size="sm" variant="secondary">
                <Link to="/reporting/exports">Export queue</Link>
              </Button>
            </WorkspaceActionsRow>
          }
        />

        <WorkspaceContent className="space-y-4">
          {workspace.pendingActionLabel ? (
            <div className="rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm text-foreground">
              {workspace.pendingActionLabel}…
            </div>
          ) : null}
          {workspace.lastActionMessage ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--success)_22%,white)] bg-[color-mix(in_srgb,var(--success)_10%,white)] px-3 py-2 text-sm text-success">
              {workspace.lastActionMessage}
            </div>
          ) : null}
          {workspace.actionError ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--danger)_22%,white)] bg-[color-mix(in_srgb,var(--danger)_10%,white)] px-3 py-2 text-sm text-destructive">
              {workspace.actionError}
            </div>
          ) : null}

          <WorkspaceTabs aria-label="Reporting subscription filters">
            {subscriptionTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                isActive={activeTab === tab.id}
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)]">
            <WorkspaceTableShell>
              {filteredSubscriptions.length ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Subscription</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Source</TableHead>
                      <TableHead>Frequency</TableHead>
                      <TableHead>Next delivery</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredSubscriptions.map((subscription) => (
                      <TableRow
                        key={subscription.id}
                        className={subscription.id === selectedSubscription?.id ? 'bg-primary/[0.06]' : 'cursor-pointer'}
                        onClick={() => setSelectedSubscriptionId(subscription.id)}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{subscription.name}</p>
                            <p className="text-xs text-muted-foreground">
                              {subscription.owner?.name ?? 'Owner unavailable'}
                            </p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant={statusVariant(subscription.status)}>{subscription.status}</Badge>
                        </TableCell>
                        <TableCell>{sourceLabel(subscription)}</TableCell>
                        <TableCell>{subscription.schedule.frequency}</TableCell>
                        <TableCell>{formatDateTime(subscription.schedule.next_delivery_at)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <WorkspaceEmptyState
                  title="No subscriptions match the current filter"
                  copy="Create a recurring delivery from a saved view or dataset, or switch the status filter to inspect a different segment."
                  className="m-4"
                />
              )}
            </WorkspaceTableShell>

            <div className="space-y-3.5">
              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h2 className="text-base font-semibold text-foreground">Selected subscription</h2>
                    <p className="text-sm text-muted-foreground">
                      Review delivery posture, validation state, and the last governed dispatch event.
                    </p>
                  </div>
                  <BellRing className="h-5 w-5 text-muted-foreground" />
                </div>

                {selectedSubscription ? (
                  <>
                    <div className="mt-4 space-y-1.5">
                      <WorkspaceSummaryRow label="Source" value={sourceLabel(selectedSubscription)} />
                      <WorkspaceSummaryRow label="Status" value={selectedSubscription.status} />
                      <WorkspaceSummaryRow label="Frequency" value={selectedSubscription.schedule.frequency} />
                      <WorkspaceSummaryRow label="Timezone" value={selectedSubscription.schedule.timezone} />
                      <WorkspaceSummaryRow label="Export format" value={selectedSubscription.delivery.export_format.toUpperCase()} />
                      <WorkspaceSummaryRow label="Next delivery" value={formatDateTime(selectedSubscription.schedule.next_delivery_at)} />
                      <WorkspaceSummaryRow
                        label="Last delivery"
                        value={selectedSubscription.last_delivery.delivered_at ? formatDateTime(selectedSubscription.last_delivery.delivered_at) : selectedSubscription.last_delivery.status ?? 'No delivery yet'}
                      />
                    </div>
                    {selectedSubscription.validation.reason ? (
                      <p className="mt-3 rounded-xl border border-[color-mix(in_srgb,var(--warning)_22%,white)] bg-[color-mix(in_srgb,var(--warning)_10%,white)] px-3 py-2 text-sm text-warning">
                        {selectedSubscription.validation.reason}
                      </p>
                    ) : null}
                    {selectedSubscription.last_delivery.error ? (
                      <p className="mt-3 rounded-xl border border-[color-mix(in_srgb,var(--danger)_18%,white)] bg-[color-mix(in_srgb,var(--danger)_8%,white)] px-3 py-2 text-sm text-destructive">
                        {selectedSubscription.last_delivery.error.replace(/_/g, ' ')}
                      </p>
                    ) : null}
                    <WorkspaceActionsRow className="mt-4">
                      {selectedSubscription.status !== 'revoked' ? (
                        <Button
                          size="xs"
                          variant="secondary"
                          onClick={() => handlePauseOrResume(selectedSubscription)}
                        >
                          <RefreshCw className="h-3.5 w-3.5" />
                          {selectedSubscription.status === 'active' ? 'Pause' : 'Resume'}
                        </Button>
                      ) : null}
                      {selectedSubscription.status !== 'revoked' ? (
                        <Button
                          size="xs"
                          onClick={() => workspace.actions.deliverSubscription(selectedSubscription.id)}
                        >
                          <PlayCircle className="h-3.5 w-3.5" />
                          Deliver now
                        </Button>
                      ) : null}
                      {selectedSubscription.status !== 'revoked' ? (
                        <Button
                          size="xs"
                          variant="ghost"
                          onClick={() => workspace.actions.revokeSubscription(selectedSubscription.id)}
                        >
                          Revoke
                        </Button>
                      ) : null}
                    </WorkspaceActionsRow>
                  </>
                ) : (
                  <p className="mt-4 text-sm text-muted-foreground">
                    Select a subscription to inspect owner scope, delivery history, and blocked-state posture.
                  </p>
                )}
              </div>

              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-start justify-between gap-3">
                  <div className="space-y-1">
                    <h2 className="text-base font-semibold text-foreground">Create subscription</h2>
                    <p className="text-sm text-muted-foreground">
                      Start a recurring governed delivery from a reusable saved view or a directly selected dataset.
                    </p>
                  </div>
                  <ShieldAlert className="h-5 w-5 text-muted-foreground" />
                </div>

                <div className="mt-4 space-y-3">
                  <SelectField
                    label="Source type"
                    value={form.sourceType}
                    options={[
                      ['saved_view', 'Saved view'],
                      ['dataset', 'Dataset'],
                    ]}
                    onChange={(value) =>
                      setForm((current) => ({
                        ...current,
                        sourceType: (value || 'saved_view') as SubscriptionSourceType,
                      }))
                    }
                  />
                  {form.sourceType === 'saved_view' ? (
                    <SelectField
                      label="Saved view"
                      value={form.savedReportViewId}
                      placeholder="Choose a saved view"
                      options={savedViewOptions}
                      onChange={(value) =>
                        setForm((current) => ({
                          ...current,
                          savedReportViewId: value,
                        }))
                      }
                    />
                  ) : (
                    <SelectField
                      label="Dataset"
                      value={form.datasetKey}
                      placeholder="Choose a dataset"
                      options={datasetOptions}
                      onChange={(value) =>
                        setForm((current) => ({
                          ...current,
                          datasetKey: value,
                        }))
                      }
                    />
                  )}
                  <WorkspaceField label="Name">
                    <Input
                      value={form.name}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          name: event.target.value,
                        }))
                      }
                      placeholder="Monday hiring pipeline brief"
                    />
                  </WorkspaceField>
                  <WorkspaceField label="Description">
                    <Input
                      value={form.description}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          description: event.target.value,
                        }))
                      }
                      placeholder="Who consumes this and why"
                    />
                  </WorkspaceField>
                  <div className="grid gap-3 md:grid-cols-2">
                    <SelectField
                      label="Frequency"
                      value={form.frequency}
                      options={[
                        ['daily', 'Daily'],
                        ['weekly', 'Weekly'],
                        ['monthly', 'Monthly'],
                      ]}
                      onChange={(value) =>
                        setForm((current) => ({
                          ...current,
                          frequency: (value || 'weekly') as ReportingSubscriptionFrequency,
                        }))
                      }
                    />
                    <SelectField
                      label="Export format"
                      value={form.exportFormat}
                      options={[
                        ['csv', 'CSV'],
                        ['json', 'JSON'],
                      ]}
                      onChange={(value) =>
                        setForm((current) => ({
                          ...current,
                          exportFormat: (value || 'csv') as ReportingExportFormat,
                        }))
                      }
                    />
                  </div>
                  <div className="grid gap-3 md:grid-cols-2">
                    <WorkspaceField label="Timezone">
                      <Input
                        value={form.timezone}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            timezone: event.target.value,
                          }))
                        }
                        placeholder="Asia/Kolkata"
                      />
                    </WorkspaceField>
                    <WorkspaceField label="Time of day">
                      <Input
                        value={form.timeOfDay}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            timeOfDay: event.target.value,
                          }))
                        }
                        placeholder="09:00"
                      />
                    </WorkspaceField>
                  </div>
                  {form.frequency === 'weekly' ? (
                    <SelectField
                      label="Weekday"
                      value={form.weekday}
                      options={[
                        ['1', 'Monday'],
                        ['2', 'Tuesday'],
                        ['3', 'Wednesday'],
                        ['4', 'Thursday'],
                        ['5', 'Friday'],
                      ]}
                      onChange={(value) =>
                        setForm((current) => ({
                          ...current,
                          weekday: value || '1',
                        }))
                      }
                    />
                  ) : null}
                  {form.frequency === 'monthly' ? (
                    <WorkspaceField label="Day of month">
                      <Input
                        value={form.dayOfMonth}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            dayOfMonth: event.target.value,
                          }))
                        }
                        placeholder="1"
                      />
                    </WorkspaceField>
                  ) : null}
                  <Button
                    onClick={handleCreateSubscription}
                    disabled={
                      !form.name.trim() ||
                      (form.sourceType === 'saved_view'
                        ? !form.savedReportViewId
                        : !form.datasetKey)
                    }
                  >
                    Create subscription
                  </Button>
                </div>
              </div>
            </div>
          </WorkspaceSplit>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
