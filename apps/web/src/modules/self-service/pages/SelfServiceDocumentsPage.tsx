import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { formatRegionalDate } from '../../../shared/regionalization/formatters'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTableShell,
} from '../../../shared/ui/workspace'
import type { SelfServiceDocumentRecord } from '../types'
import { useSelfServiceRouteWorkspace } from './useSelfServiceRouteWorkspace'

export function SelfServiceDocumentsPage() {
  const workspace = useSelfServiceRouteWorkspace()

  if (workspace.isLoading) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Loading self-service documents"
          copy="We are resolving the linked employee documents and policy acknowledgement queue."
        />
      </WorkspacePage>
    )
  }

  if (workspace.error) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Unable to load self-service documents"
          copy={workspace.error.message}
        />
      </WorkspacePage>
    )
  }

  if (!workspace.data) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="No linked employee profile"
          copy="This session does not resolve to an employee profile yet, so self-service documents are unavailable."
        />
      </WorkspacePage>
    )
  }

  const { items, summary } = workspace.data.documents

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="min-w-0 space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight text-foreground">My documents</h1>
            <p className="max-w-3xl text-sm text-muted-foreground">
              Download approved files, review assigned policy items, and acknowledge the documents that still require action.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={summary.pending_acknowledgement_count ? 'warning' : 'success'}>
              {summary.pending_acknowledgement_count
                ? `${summary.pending_acknowledgement_count} acknowledgement pending`
                : 'All policy items acknowledged'}
            </Badge>
            {summary.hidden_sensitive_count ? (
              <Badge variant="neutral">{summary.hidden_sensitive_count} sensitive item hidden</Badge>
            ) : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Visible items" value={String(summary.total_count)} />
                <WorkspaceSummaryRow label="Download ready" value={String(summary.downloadable_count)} />
                <WorkspaceSummaryRow label="Acknowledged" value={String(summary.acknowledged_count)} />
                <WorkspaceSummaryRow label="Pending" value={String(summary.pending_acknowledgement_count)} />
              </WorkspaceContent>
            </WorkspaceSurface>
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Sensitive items hidden" value={String(summary.hidden_sensitive_count)} />
                <WorkspaceSummaryRow label="Current employee" value={`${workspace.employee?.full_name ?? 'Profile pending'}${workspace.employee ? ` · ${workspace.employee.employee_code}` : ''}`} />
                <WorkspaceSummaryRow label="Mode" value={workspace.source === 'demo' ? 'Demo self-service workspace' : 'Live self-service workspace'} />
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          {workspace.lastActionMessage ? (
            <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
              {workspace.lastActionMessage}
            </div>
          ) : null}

          {workspace.actionError ? (
            <div className="rounded-xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm text-destructive">
              {workspace.actionError}
            </div>
          ) : null}

          {items.length ? (
            <WorkspaceTableShell>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Document</TableHead>
                    <TableHead>Source</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Timeline</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {items.map((document) => (
                    <TableRow key={document.id}>
                      <TableCell>
                        <div className="space-y-1">
                          <p className="font-medium text-foreground">{document.title}</p>
                          <p className="text-xs text-muted-foreground">
                            {[document.subtitle, document.file_name, document.file_size_bytes ? formatBytes(document.file_size_bytes) : null]
                              .filter(Boolean)
                              .join(' · ')}
                          </p>
                          {document.notes ? <p className="text-xs text-muted-foreground">{document.notes}</p> : null}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant="neutral">{sourceLabel(document)}</Badge>
                      </TableCell>
                      <TableCell>
                        <Badge variant={statusVariant(document.status)}>{statusLabel(document.status)}</Badge>
                      </TableCell>
                      <TableCell>
                        <div className="space-y-1 text-sm text-muted-foreground">
                          <p>{document.due_date ? `Due ${formatDate(document.due_date)}` : document.expiry_date ? `Retained until ${formatDate(document.expiry_date)}` : 'No due date'}</p>
                          <p>{document.visibility_scope ? `Visibility ${document.visibility_scope}` : 'Visibility approved'}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="flex flex-wrap items-center gap-2">
                          {document.download_url ? (
                            <Button
                              size="sm"
                              variant="secondary"
                              onClick={() => void workspace.downloadDocument(document)}
                              disabled={workspace.pendingDownloadId === document.source_id}
                            >
                              {workspace.pendingDownloadId === document.source_id ? 'Downloading...' : 'Download'}
                            </Button>
                          ) : null}
                          {document.acknowledge_url ? (
                            <Button
                              size="sm"
                              onClick={() => void workspace.acknowledgeDocument(document)}
                              disabled={workspace.pendingAcknowledgementId === document.source_id}
                            >
                              {workspace.pendingAcknowledgementId === document.source_id ? 'Acknowledging...' : 'Acknowledge policy'}
                            </Button>
                          ) : null}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : (
            <WorkspaceEmptyState
              title="No self-service documents are visible"
              copy="Documents appear here once the linked employee profile has approved employee files, repository files, or policy acknowledgements."
            />
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function sourceLabel(document: SelfServiceDocumentRecord) {
  return {
    policy_acknowledgement: 'Policy',
    employee_document: 'Employee file',
    repository_document: 'Repository',
  }[document.source_type]
}

function statusLabel(status: SelfServiceDocumentRecord['status']) {
  return {
    assigned: 'Pending acknowledgement',
    acknowledged: 'Acknowledged',
    available: 'Available',
  }[status]
}

function statusVariant(status: SelfServiceDocumentRecord['status']) {
  return {
    assigned: 'warning',
    acknowledged: 'success',
    available: 'neutral',
  }[status] as 'warning' | 'success' | 'neutral'
}

function formatBytes(value: number) {
  if (value < 1024) {
    return `${value} B`
  }

  if (value < 1024 * 1024) {
    return `${(value / 1024).toFixed(1)} KB`
  }

  return `${(value / (1024 * 1024)).toFixed(1)} MB`
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}
