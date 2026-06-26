import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceFilters,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { SelectField } from '../../../shared/ui/select-field'
import { useRecruitmentRouteWorkspace } from './useRecruitmentRouteWorkspace'
import type { RecruitmentJobRequisitionRecord, RecruitmentRequisitionFilters } from '../types'
import {
  formatRecruitmentDate,
  formatRecruitmentLabel,
  requisitionStatusBadgeVariant,
} from '../utils'

const statusOptions: Array<[string, string]> = [
  ['', 'All statuses'],
  ['draft', 'Draft'],
  ['submitted', 'Submitted'],
  ['approved', 'Approved'],
  ['changes_requested', 'Changes requested'],
  ['rejected', 'Rejected'],
  ['on_hold', 'On hold'],
  ['closed', 'Closed'],
]

const priorityOptions: Array<[string, string]> = [
  ['', 'All priorities'],
  ['low', 'Low'],
  ['medium', 'Medium'],
  ['high', 'High'],
  ['critical', 'Critical'],
]

export function RecruitmentRequisitionsPage() {
  const workspace = useRecruitmentRouteWorkspace()
  const [filters, setFilters] = useState<RecruitmentRequisitionFilters>({
    status: '',
    priority: '',
    q: '',
  })
  const [selectedRequisitionId, setSelectedRequisitionId] = useState<number | null>(null)
  const [comment, setComment] = useState('')

  const requisitions = useMemo(() => workspace.data?.requisitions ?? [], [workspace.data?.requisitions])

  const filteredRequisitions = useMemo(() => {
    const query = filters.q.trim().toLowerCase()

    return requisitions.filter((requisition) => {
      const matchesStatus = !filters.status || requisition.status === filters.status
      const matchesPriority = !filters.priority || requisition.priority === filters.priority
      const matchesQuery =
        query.length === 0 ||
        [
          requisition.requisition_code,
          requisition.title,
          requisition.department?.name,
          requisition.recruiter?.name,
          requisition.hiring_manager?.full_name,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()
          .includes(query)

      return matchesStatus && matchesPriority && matchesQuery
    })
  }, [filters, requisitions])

  const selectedRequisition =
    filteredRequisitions.find((requisition) => requisition.id === selectedRequisitionId) ??
    filteredRequisitions[0] ??
    null

  async function handleAction(requisition: RecruitmentJobRequisitionRecord, action: string) {
    await workspace.actions.updateRequisitionStatus(requisition.id, action, comment)
    setComment('')
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading requisitions" copy="Resolving requisition workflow posture and recruiter-owned demand." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Requisitions unavailable" copy={workspace.error.message || 'Unable to resolve requisitions.'} />
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Recruitment requisitions</Badge>
            <h1 className="text-xl font-semibold text-foreground">Hiring demand and approval posture</h1>
            <p className="text-sm text-muted-foreground">
              Review role demand, headcount context, and workflow-backed requisition status before pipeline volume grows.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Button asChild size="sm" variant="secondary">
              <Link to="/recruitment/candidates">Open pipeline board</Link>
            </Button>
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <WorkspaceFilters>
                <WorkspaceField label="Search" compact>
                  <Input
                    value={filters.q}
                    onChange={(event) => setFilters((current) => ({ ...current, q: event.target.value }))}
                    placeholder="Search requisition, recruiter, or hiring manager"
                  />
                </WorkspaceField>
                <SelectField
                  label="Status"
                  value={filters.status}
                  options={statusOptions}
                  compact
                  onChange={(value) =>
                    setFilters((current) => ({ ...current, status: value as RecruitmentRequisitionFilters['status'] }))
                  }
                />
                <SelectField
                  label="Priority"
                  value={filters.priority}
                  options={priorityOptions}
                  compact
                  onChange={(value) =>
                    setFilters((current) => ({ ...current, priority: value as RecruitmentRequisitionFilters['priority'] }))
                  }
                />
              </WorkspaceFilters>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

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

          {!filteredRequisitions.length ? (
            <WorkspaceEmptyState
              title="No requisitions match the current filters"
              copy="Clear the filters or switch personas to review a different recruitment scope."
            />
          ) : (
            <div className="grid gap-4 xl:grid-cols-[1.35fr_0.95fr]">
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Requisition</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Priority</TableHead>
                      <TableHead>Recruiter</TableHead>
                      <TableHead>Target start</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredRequisitions.map((requisition) => (
                      <TableRow
                        key={requisition.id}
                        className="cursor-pointer"
                        data-state={selectedRequisition?.id === requisition.id ? 'selected' : undefined}
                        onClick={() => setSelectedRequisitionId(requisition.id)}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <div className="font-semibold text-foreground">{requisition.title}</div>
                            <div className="text-xs text-muted-foreground">
                              {requisition.requisition_code} · {requisition.department?.name ?? 'Department pending'}
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant={requisitionStatusBadgeVariant(requisition.status)}>
                            {formatRecruitmentLabel(requisition.status)}
                          </Badge>
                        </TableCell>
                        <TableCell>{formatRecruitmentLabel(requisition.priority)}</TableCell>
                        <TableCell>{requisition.recruiter?.name ?? 'Unassigned'}</TableCell>
                        <TableCell>{formatRecruitmentDate(requisition.target_start_date)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>

              {selectedRequisition ? (
                <div className="rounded-[1.1rem] border border-line/80 bg-white/95 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                  <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge variant={requisitionStatusBadgeVariant(selectedRequisition.status)}>
                        {formatRecruitmentLabel(selectedRequisition.status)}
                      </Badge>
                      <Badge variant="neutral">{selectedRequisition.requisition_code}</Badge>
                      <Badge variant="subtle">{formatRecruitmentLabel(selectedRequisition.priority)}</Badge>
                    </div>
                    <div>
                      <h2 className="text-lg font-semibold text-foreground">{selectedRequisition.title}</h2>
                      <p className="text-sm text-muted-foreground">
                        {selectedRequisition.department?.name ?? 'Department pending'} ·{' '}
                        {selectedRequisition.designation?.name ?? 'Role pending'} ·{' '}
                        {selectedRequisition.openings_count} opening(s)
                      </p>
                    </div>
                  </div>

                  <dl className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                    <InfoRow label="Recruiter" value={selectedRequisition.recruiter?.name ?? 'Unassigned'} />
                    <InfoRow label="Hiring manager" value={selectedRequisition.hiring_manager?.full_name ?? 'Pending'} />
                    <InfoRow label="Target start" value={formatRecruitmentDate(selectedRequisition.target_start_date)} />
                    <InfoRow label="Hiring type" value={formatRecruitmentLabel(selectedRequisition.hiring_type)} />
                    <InfoRow label="Employment type" value={formatRecruitmentLabel(selectedRequisition.employment_type)} />
                    <InfoRow label="Workflow stage" value={selectedRequisition.workflow?.tasks?.find((task) => task.status === 'pending')?.stage_name ?? 'No active task'} />
                  </dl>

                  <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                    <div className="text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">Justification</div>
                    <p className="mt-1 text-sm text-foreground">{selectedRequisition.justification}</p>
                    {selectedRequisition.notes ? (
                      <>
                        <div className="mt-3 text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">Notes</div>
                        <p className="mt-1 text-sm text-muted-foreground">{selectedRequisition.notes}</p>
                      </>
                    ) : null}
                  </div>

                  {(workspace.canManageRecruitment || workspace.canApproveRecruitment) && selectedRequisition.status !== 'closed' ? (
                    <div className="mt-4 space-y-3">
                      <WorkspaceField label="Comment for workflow or close action">
                        <Textarea
                          value={comment}
                          onChange={(event) => setComment(event.target.value)}
                          placeholder="Add approval rationale, hold context, or close notes."
                          className="min-h-24"
                        />
                      </WorkspaceField>
                      <div className="flex flex-wrap gap-2">
                        {selectedRequisition.can_submit && workspace.canManageRecruitment ? (
                          <Button size="sm" onClick={() => handleAction(selectedRequisition, 'submit')}>
                            Submit
                          </Button>
                        ) : null}
                        {selectedRequisition.status === 'submitted' && workspace.canApproveRecruitment ? (
                          <>
                            <Button size="sm" onClick={() => handleAction(selectedRequisition, 'approve')}>
                              Approve
                            </Button>
                            <Button size="sm" variant="secondary" onClick={() => handleAction(selectedRequisition, 'request_changes')}>
                              Request changes
                            </Button>
                            <Button size="sm" variant="danger" onClick={() => handleAction(selectedRequisition, 'reject')}>
                              Reject
                            </Button>
                          </>
                        ) : null}
                        {selectedRequisition.can_put_on_hold && workspace.canManageRecruitment ? (
                          <Button size="sm" variant="secondary" onClick={() => handleAction(selectedRequisition, 'put_on_hold')}>
                            Put on hold
                          </Button>
                        ) : null}
                        {selectedRequisition.can_resume && workspace.canManageRecruitment ? (
                          <Button size="sm" variant="secondary" onClick={() => handleAction(selectedRequisition, 'resume')}>
                            Resume
                          </Button>
                        ) : null}
                        {selectedRequisition.can_close && workspace.canManageRecruitment ? (
                          <Button size="sm" variant="ghost" onClick={() => handleAction(selectedRequisition, 'close')}>
                            Close requisition
                          </Button>
                        ) : null}
                      </div>
                    </div>
                  ) : null}
                </div>
              ) : null}
            </div>
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <dt className="text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</dt>
      <dd className="mt-1 text-sm text-foreground">{value}</dd>
    </div>
  )
}
