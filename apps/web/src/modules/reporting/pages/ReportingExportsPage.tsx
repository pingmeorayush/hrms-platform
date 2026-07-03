import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { Download, FileClock, PlayCircle } from 'lucide-react'
import {
  formatRegionalDateTime,
  formatRegionalNumber,
} from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
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
import type { ReportingExportRecord } from '../types'

type ExportTab = 'all' | 'queued' | 'completed' | 'failed' | 'expired'

const exportTabs: Array<{ id: ExportTab; label: string }> = [
  { id: 'all', label: 'All exports' },
  { id: 'queued', label: 'Queued' },
  { id: 'completed', label: 'Completed' },
  { id: 'failed', label: 'Failed or blocked' },
  { id: 'expired', label: 'Expired' },
]

function formatStatus(record: ReportingExportRecord) {
  if (record.status === 'failed' && (record.last_error ?? '').includes('blocked')) {
    return 'blocked'
  }

  return record.status
}

function statusVariant(status: string) {
  if (status === 'completed') {
    return 'success' as const
  }

  if (status === 'queued' || status === 'processing') {
    return 'warning' as const
  }

  if (status === 'blocked' || status === 'failed') {
    return 'danger' as const
  }

  return 'neutral' as const
}

export function ReportingExportsPage() {
  const workspace = useReportingRouteWorkspace()
  const [activeTab, setActiveTab] = useState<ExportTab>('all')
  const [selectedExportId, setSelectedExportId] = useState<number | null>(null)

  const exports = useMemo(() => workspace.data?.exports ?? [], [workspace.data?.exports])
  const filteredExports = useMemo(() => {
    if (activeTab === 'all') {
      return exports
    }

    if (activeTab === 'failed') {
      return exports.filter((record) => record.status === 'failed')
    }

    return exports.filter((record) => record.status === activeTab)
  }, [activeTab, exports])

  const selectedExport =
    filteredExports.find((record) => record.id === selectedExportId) ??
    exports.find((record) => record.id === selectedExportId) ??
    filteredExports[0] ??
    null

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title="Loading export queue"
        copy="Resolving governed export lifecycle, retention posture, and download availability."
      />
    )
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Export queue unavailable" copy={workspace.error.message} />
  }

  if (!workspace.canViewReporting) {
    return (
      <WorkspaceEmptyState
        title="Export queue unavailable"
        copy="This session does not currently resolve to governed reporting visibility."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Reporting"
          title="Export Queue and Delivery Posture"
          description="Review governed export lifecycle, process queued jobs, handle blocked or expired outputs, and start controlled downloads."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo queue' : 'Live queue'}</Badge>}
          context={[
            `${exports.length} export${exports.length === 1 ? '' : 's'} in scope`,
            `${exports.filter((record) => record.status === 'queued' || record.status === 'processing').length} pending`,
          ]}
          actions={
            <WorkspaceActionsRow>
              <Button asChild size="sm" variant="secondary">
                <Link to="/reporting/explorer">
                  Back to explorer
                </Link>
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

          <WorkspaceTabs aria-label="Reporting export filters">
            {exportTabs.map((tab) => (
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
              {filteredExports.length ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Export</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Dataset</TableHead>
                      <TableHead>Format</TableHead>
                      <TableHead>Rows</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredExports.map((record) => (
                      <TableRow
                        key={record.id}
                        className={record.id === selectedExport?.id ? 'bg-primary/[0.06]' : 'cursor-pointer'}
                        onClick={() => setSelectedExportId(record.id)}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{record.file.name ?? record.export_uuid}</p>
                            <p className="text-xs text-muted-foreground">
                              {formatRegionalDateTime(record.requested_at, 'Request time pending')}
                            </p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant={statusVariant(formatStatus(record))}>{formatStatus(record)}</Badge>
                        </TableCell>
                        <TableCell>{record.dataset?.name ?? 'Dataset pending'}</TableCell>
                        <TableCell>{record.format.toUpperCase()}</TableCell>
                        <TableCell>
                          {typeof (record.counts.exported_row_count ?? record.counts.estimated_row_count) === 'number'
                            ? formatRegionalNumber(record.counts.exported_row_count ?? record.counts.estimated_row_count)
                            : '—'}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <WorkspaceEmptyState
                  title="No exports match the current filter"
                  copy="Request a new export from the explorer or switch the queue filter to inspect a different lifecycle state."
                  className="m-4"
                />
              )}
            </WorkspaceTableShell>

            <div className="space-y-3.5">
              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h2 className="text-base font-semibold text-foreground">Selected export</h2>
                    <p className="text-sm text-muted-foreground">
                      Inspect retention, masking, and delivery posture before processing or downloading.
                    </p>
                  </div>
                  <FileClock className="h-5 w-5 text-muted-foreground" />
                </div>

                {selectedExport ? (
                  <>
                    <div className="mt-4 space-y-1.5">
                      <WorkspaceSummaryRow label="Dataset" value={selectedExport.dataset?.name ?? 'Dataset pending'} />
                      <WorkspaceSummaryRow label="Status" value={formatStatus(selectedExport)} />
                      <WorkspaceSummaryRow label="Format" value={selectedExport.format.toUpperCase()} />
                      <WorkspaceSummaryRow label="Execution" value={selectedExport.execution_mode} />
                      <WorkspaceSummaryRow
                        label="Rows"
                        value={
                          typeof (selectedExport.counts.exported_row_count ?? selectedExport.counts.estimated_row_count) ===
                          'number'
                            ? formatRegionalNumber(
                                selectedExport.counts.exported_row_count ?? selectedExport.counts.estimated_row_count,
                              )
                            : '—'
                        }
                      />
                      <WorkspaceSummaryRow
                        label="Retention"
                        value={formatRegionalDateTime(selectedExport.retention.expires_at, 'Not started')}
                      />
                    </div>
                    {selectedExport.last_error ? (
                      <p className="mt-3 rounded-xl border border-[color-mix(in_srgb,var(--danger)_18%,white)] bg-[color-mix(in_srgb,var(--danger)_8%,white)] px-3 py-2 text-sm text-destructive">
                        {selectedExport.last_error.replace(/_/g, ' ')}
                      </p>
                    ) : null}
                    <WorkspaceActionsRow className="mt-4">
                      {(selectedExport.status === 'queued' || selectedExport.status === 'processing') ? (
                        <Button size="xs" onClick={() => workspace.actions.processExport(selectedExport.id)}>
                          <PlayCircle className="h-3.5 w-3.5" />
                          Process export
                        </Button>
                      ) : null}
                      {selectedExport.file.download_available ? (
                        <Button size="xs" variant="secondary" onClick={() => workspace.actions.downloadExport(selectedExport)}>
                          <Download className="h-3.5 w-3.5" />
                          Download
                        </Button>
                      ) : null}
                    </WorkspaceActionsRow>
                  </>
                ) : (
                  <p className="mt-4 text-sm text-muted-foreground">
                    Select an export to review lifecycle, retention, and masking posture.
                  </p>
                )}
              </div>
            </div>
          </WorkspaceSplit>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
