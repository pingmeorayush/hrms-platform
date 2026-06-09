import { Badge } from '../../../shared/ui/badge'
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
import { useSelfServiceRouteWorkspace } from './useSelfServiceRouteWorkspace'

export function SelfServiceAssetsPage() {
  const workspace = useSelfServiceRouteWorkspace()

  if (workspace.isLoading) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Loading assigned assets"
          copy="We are resolving the linked employee asset assignments and current handover posture."
        />
      </WorkspacePage>
    )
  }

  if (workspace.error) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Unable to load assigned assets"
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
          copy="This session does not resolve to an employee profile yet, so assigned assets are unavailable."
        />
      </WorkspacePage>
    )
  }

  const { items, summary } = workspace.data.assets

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="min-w-0 space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight text-foreground">Assigned assets</h1>
            <p className="max-w-3xl text-sm text-muted-foreground">
              Review the devices and issued equipment currently linked to this employee profile, including handover context and return expectations.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={summary.overdue_count ? 'warning' : 'success'}>
              {summary.overdue_count ? `${summary.overdue_count} overdue` : 'Return windows healthy'}
            </Badge>
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Active assets" value={String(summary.active_count)} />
                <WorkspaceSummaryRow label="Issued" value={String(summary.issued_count)} />
                <WorkspaceSummaryRow label="Assigned" value={String(summary.assigned_count)} />
                <WorkspaceSummaryRow label="Overdue returns" value={String(summary.overdue_count)} />
              </WorkspaceContent>
            </WorkspaceSurface>
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow label="Current employee" value={`${workspace.employee?.full_name ?? 'Profile pending'}${workspace.employee ? ` · ${workspace.employee.employee_code}` : ''}`} />
                <WorkspaceSummaryRow label="Mode" value={workspace.source === 'demo' ? 'Demo self-service workspace' : 'Live self-service workspace'} />
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          {items.length ? (
            <WorkspaceTableShell>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Asset</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Assignment</TableHead>
                    <TableHead>Return window</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {items.map((asset) => (
                    <TableRow key={asset.id}>
                      <TableCell>
                        <div className="space-y-1">
                          <p className="font-medium text-foreground">{asset.name}</p>
                          <p className="text-xs text-muted-foreground">
                            {[asset.asset_tag, asset.manufacturer, asset.model_name].filter(Boolean).join(' · ')}
                          </p>
                          {asset.notes ? <p className="text-xs text-muted-foreground">{asset.notes}</p> : null}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant={asset.assignment?.status === 'issued' ? 'success' : 'neutral'}>
                          {asset.assignment?.status ?? asset.status}
                        </Badge>
                      </TableCell>
                      <TableCell>{asset.category?.name ?? 'Uncategorized'}</TableCell>
                      <TableCell>
                        <div className="space-y-1 text-sm text-muted-foreground">
                          <p>{asset.assignment?.assigned_at ? `Assigned ${formatDate(asset.assignment.assigned_at)}` : 'Assignment date pending'}</p>
                          <p>{asset.assignment?.issue_notes ?? asset.assignment?.assignment_notes ?? 'No assignment notes recorded'}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="space-y-1">
                          <p className="text-sm text-foreground">
                            {asset.assignment?.expected_return_date ? formatDate(asset.assignment.expected_return_date) : 'No return target'}
                          </p>
                          <p className="text-xs text-muted-foreground">{asset.assignment?.due_state.replace(/_/g, ' ') ?? 'No due state'}</p>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : (
            <WorkspaceEmptyState
              title="No assigned assets are visible"
              copy="Assigned devices and issued equipment appear here once the linked employee profile has an active asset handoff."
            />
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not available'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}
