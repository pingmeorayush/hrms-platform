import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceTabButton,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { ApiRequestError } from '../../../shared/api/http'
import { useOperationsRouteWorkspace } from './useOperationsRouteWorkspace'

export function OperationsLifecyclePage() {
  const workspace = useOperationsRouteWorkspace()
  const [actionError, setActionError] = useState<string | null>(null)
  const [actionMessage, setActionMessage] = useState<string | null>(null)
  const selectedEmployee = useMemo(
    () =>
      workspace.selectedLifecycleStatuses.find(
        (record) => record.employee.id === workspace.selectedLifecycleEmployeeId,
      ) ?? null,
    [workspace.selectedLifecycleEmployeeId, workspace.selectedLifecycleStatuses],
  )

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading lifecycle operations...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!workspace.data) {
    return <p className="workspace-muted">No lifecycle operations workspace is available yet.</p>
  }

  async function handleTaskStatusUpdate(taskId: number, status: string) {
    if (!selectedEmployee) {
      return
    }

    setActionError(null)
    setActionMessage(null)

    try {
      await workspace.updateLifecycleTask(selectedEmployee.employee.id, taskId, status)
      setActionMessage('Lifecycle task updated.')
    } catch (error) {
      if (error instanceof ApiRequestError) {
        setActionError(error.message)
      } else if (error instanceof Error) {
        setActionError(error.message)
      } else {
        setActionError('The lifecycle task could not be updated.')
      }
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Lifecycle Operations"
          title="Lifecycle Operations"
          description="Review employee-level progress, then move individual tasks forward without leaving the operations module."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            `${workspace.selectedLifecycleStatuses.length} employee record(s) in scope`,
            workspace.selectedLifecycleType,
            workspace.canManageLifecycle ? 'Lifecycle updates enabled' : 'Lifecycle view restricted',
          ]}
        />

        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Lifecycle types">
            <WorkspaceTabButton
              active={workspace.selectedLifecycleType === 'onboarding'}
              onClick={() => workspace.setSelectedLifecycleType('onboarding')}
            >
              Onboarding
            </WorkspaceTabButton>
            <WorkspaceTabButton
              active={workspace.selectedLifecycleType === 'offboarding'}
              onClick={() => workspace.setSelectedLifecycleType('offboarding')}
            >
              Offboarding
            </WorkspaceTabButton>
          </WorkspaceTabs>

          {actionMessage ? <p className="text-sm font-medium text-emerald-700">{actionMessage}</p> : null}
          {actionError ? <p className="workspace-error">{actionError}</p> : null}
          {!workspace.canManageLifecycle ? (
            <p className="workspace-muted">This section is read only unless the session carries `employee.manage`.</p>
          ) : null}

          <div className="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Employee progress</h2>
                  <p className="text-sm text-muted-foreground">Each employee row stays filtered to records that still need lifecycle follow-through.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {workspace.selectedLifecycleStatuses.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Employee</TableHead>
                          <TableHead>Progress</TableHead>
                          <TableHead>Open items</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {workspace.selectedLifecycleStatuses.map((record) => (
                          <TableRow key={`${record.lifecycle_type}-${record.employee.id}`}>
                            <TableCell>
                              <span className="ui-table-primary">{record.employee.full_name}</span>
                              <p className="text-xs text-muted-foreground">{record.employee.department ?? 'Department pending'} · {record.employee.designation ?? 'Designation pending'}</p>
                            </TableCell>
                            <TableCell>{record.summary.progress_percentage}%</TableCell>
                            <TableCell>
                              <Badge variant={record.summary.incomplete_count > 1 ? 'warning' : 'neutral'}>
                                {record.summary.incomplete_count}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              <Button
                                size="xs"
                                variant={workspace.selectedLifecycleEmployeeId === record.employee.id ? 'primary' : 'secondary'}
                                onClick={() => workspace.setSelectedLifecycleEmployeeId(record.employee.id)}
                              >
                                {workspace.selectedLifecycleEmployeeId === record.employee.id ? 'Selected' : 'Review'}
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title={`No ${workspace.selectedLifecycleType} tasks need attention`}
                    copy="Switch the lifecycle type or wait for new incomplete records to enter the queue."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">
                    {selectedEmployee ? `${selectedEmployee.employee.full_name} · task detail` : 'Lifecycle task detail'}
                  </h2>
                  <p className="text-sm text-muted-foreground">
                    Open task details, due states, and approval posture stay visible here for the selected employee.
                  </p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent>
                {workspace.isLifecycleLoading ? <p className="workspace-muted">Loading lifecycle tasks...</p> : null}
                {workspace.lifecycleError ? <p className="workspace-error">{workspace.lifecycleError.message}</p> : null}

                {workspace.selectedLifecycleTasks?.items.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Task</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Due</TableHead>
                          <TableHead>Assignee</TableHead>
                          <TableHead>Action</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {workspace.selectedLifecycleTasks.items.map((task) => (
                          <TableRow key={task.id}>
                            <TableCell>
                              <span className="ui-table-primary">{task.title}</span>
                              <p className="text-xs text-muted-foreground">{task.category} · {task.task_type ?? 'general'}</p>
                              {task.notes ? <p className="mt-1 text-xs text-muted-foreground">{task.notes}</p> : null}
                            </TableCell>
                            <TableCell>
                              <Badge variant={taskStatusVariant(task.status)}>{task.status.replace(/_/g, ' ')}</Badge>
                              {task.requires_approval ? <p className="mt-1 text-xs text-muted-foreground">Approval routed</p> : null}
                            </TableCell>
                            <TableCell>
                              <Badge variant={dueVariant(task.due_state)}>{task.due_state.replace(/_/g, ' ')}</Badge>
                              <p className="mt-1 text-xs text-muted-foreground">{task.due_date ? formatDate(task.due_date) : 'No due date'}</p>
                            </TableCell>
                            <TableCell>{task.assigned_to_user_name ?? task.assignee_type.replace(/_/g, ' ')}</TableCell>
                            <TableCell>
                              {workspace.canManageLifecycle ? (
                                <div className="flex flex-wrap gap-1.5">
                                  {task.status !== 'in_progress' ? (
                                    <Button size="xs" variant="secondary" onClick={() => handleTaskStatusUpdate(task.id, 'in_progress')}>
                                      Start
                                    </Button>
                                  ) : null}
                                  {task.status !== 'completed' ? (
                                    <Button size="xs" variant="secondary" onClick={() => handleTaskStatusUpdate(task.id, 'completed')}>
                                      Complete
                                    </Button>
                                  ) : null}
                                  {task.status !== 'skipped' ? (
                                    <Button size="xs" variant="secondary" onClick={() => handleTaskStatusUpdate(task.id, 'skipped')}>
                                      Skip
                                    </Button>
                                  ) : null}
                                </div>
                              ) : (
                                <span className="text-xs text-muted-foreground">Read only</span>
                              )}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title={selectedEmployee ? 'No visible task details yet' : 'Select an employee'}
                    copy={
                      selectedEmployee
                        ? 'Task detail appears here once the selected employee has visible lifecycle records.'
                        : 'Choose a lifecycle record from the left to inspect its current checklist and approval posture.'
                    }
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'No due date'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function taskStatusVariant(status: string) {
  if (status === 'completed') {
    return 'success'
  }

  if (status === 'awaiting_approval' || status === 'changes_requested' || status === 'rejected') {
    return 'warning'
  }

  return 'neutral'
}

function dueVariant(state: string) {
  if (state === 'overdue') {
    return 'danger'
  }

  if (state === 'due_today' || state === 'upcoming') {
    return 'warning'
  }

  if (state === 'closed') {
    return 'success'
  }

  return 'neutral'
}
