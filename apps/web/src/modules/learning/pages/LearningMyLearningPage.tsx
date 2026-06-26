import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { Input } from '../../../shared/ui/input'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { formatLearningDate, learningDueStateVariant, learningRenewalVariant, learningDeliveryModeLabel } from '../utils'
import { useLearningRouteWorkspace } from './useLearningRouteWorkspace'

type LearnerTab = 'actionable' | 'completed' | 'renewals'

interface CompletionFormState {
  completion_notes: string
  completion_evidence_type: string
  completion_evidence_reference: string
  completion_evidence_notes: string
}

const learnerTabs: Array<{ id: LearnerTab; label: string }> = [
  { id: 'actionable', label: 'Actionable now' },
  { id: 'completed', label: 'Completed' },
  { id: 'renewals', label: 'Renewals' },
]

function defaultCompletionForm(): CompletionFormState {
  return {
    completion_notes: '',
    completion_evidence_type: '',
    completion_evidence_reference: '',
    completion_evidence_notes: '',
  }
}

export function LearningMyLearningPage() {
  const workspace = useLearningRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<LearnerTab>('actionable')
  const [selectedTargetId, setSelectedTargetId] = useState<number | null>(null)
  const [form, setForm] = useState<CompletionFormState>(defaultCompletionForm())

  const filteredTargets = useMemo(() => {
    if (!data) {
      return []
    }

    return data.myAssignments.filter((target) => {
      if (activeTab === 'completed') {
        return target.status === 'completed'
      }

      if (activeTab === 'renewals') {
        return target.renewal_posture === 'overdue' || target.renewal_posture === 'due_today'
      }

      return target.status !== 'completed'
    })
  }, [activeTab, data])

  const selectedTarget =
    filteredTargets.find((target) => target.id === selectedTargetId) ??
    data?.myAssignments.find((target) => target.id === selectedTargetId) ??
    filteredTargets.find((target) => target.status !== 'completed') ??
    filteredTargets[0] ??
    null

  const handleCompleteTarget = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!selectedTarget || selectedTarget.status === 'completed') {
      return
    }

    await workspace.completeLearningTarget(selectedTarget.id, {
      completion_notes: form.completion_notes.trim() || null,
      completion_evidence: selectedTarget.requires_completion_evidence
        ? {
            type: form.completion_evidence_type.trim(),
            reference: form.completion_evidence_reference.trim(),
            notes: form.completion_evidence_notes.trim() || null,
          }
        : null,
    })

    setForm(defaultCompletionForm())
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading my learning" copy="Resolving the linked employee profile and assigned learning posture." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="My learning unavailable" copy={workspace.error.message || 'The learning workspace could not be loaded.'} />
  }

  if (!data || !workspace.canViewLearning) {
    return <WorkspaceEmptyState title="My learning unavailable" copy="This session does not currently resolve to learning visibility." />
  }

  if (data.meta.linked_employee_id === null) {
    return (
      <WorkspaceEmptyState
        title="No linked employee learning profile"
        copy="This session can view learning posture, but it does not resolve to an employee profile that can open personal learning assignments."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Learner self service</Badge>
            <CardTitle>My learning dashboard</CardTitle>
            <CardDescription>
              Review assigned learning items, overdue posture, renewal pressure, and evidence-backed completion from one routed learner workspace.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo learner surface' : 'Live learner surface'}
            </Badge>
            {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
            {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="My learning tabs">
            {learnerTabs.map((tab) => (
              <WorkspaceTabButton key={tab.id} isActive={activeTab === tab.id} onClick={() => setActiveTab(tab.id)}>
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          {data.myAssignments.length === 0 ? (
            <WorkspaceEmptyState
              title="No learning items assigned"
              copy="The linked employee profile does not currently have active learning work in this session."
            />
          ) : (
            <WorkspaceSplit
              primary={(
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Learning item</TableHead>
                        <TableHead>Due</TableHead>
                        <TableHead>Due state</TableHead>
                        <TableHead>Renewal</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {filteredTargets.map((target) => (
                        <TableRow
                          key={target.id}
                          className={target.id === selectedTarget?.id ? 'bg-primary/[0.06]' : 'cursor-pointer'}
                          onClick={() => setSelectedTargetId(target.id)}
                        >
                          <TableCell>
                            <div className="space-y-1">
                              <p className="font-medium text-foreground">{target.item?.title ?? 'Learning assignment'}</p>
                              <p className="text-xs text-muted-foreground">{target.item?.code ?? 'No code'}</p>
                            </div>
                          </TableCell>
                          <TableCell>{formatLearningDate(target.due_on)}</TableCell>
                          <TableCell>
                            <Badge variant={learningDueStateVariant(target.due_state)}>{target.due_state.replace(/_/g, ' ')}</Badge>
                          </TableCell>
                          <TableCell>
                            <Badge variant={learningRenewalVariant(target.renewal_posture)}>
                              {target.renewal_posture.replace(/_/g, ' ')}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              )}
              secondary={(
                <div className="space-y-3.5">
                  <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                    <div className="space-y-1">
                      <h2 className="text-base font-semibold text-foreground">Selected learning item</h2>
                      <p className="text-sm text-muted-foreground">
                        Review delivery posture, due-state, and evidence requirements before marking completion.
                      </p>
                    </div>
                    {selectedTarget ? (
                      <div className="mt-4 space-y-1.5">
                        <WorkspaceSummaryRow label="Title" value={selectedTarget.item?.title ?? 'Learning item'} />
                        <WorkspaceSummaryRow label="Delivery" value={selectedTarget.item ? learningDeliveryModeLabel(selectedTarget.item.delivery_mode) : 'Not set'} />
                        <WorkspaceSummaryRow label="Due on" value={formatLearningDate(selectedTarget.due_on)} />
                        <WorkspaceSummaryRow label="Renewal due" value={formatLearningDate(selectedTarget.renewal_due_on)} />
                        <WorkspaceSummaryRow label="Evidence" value={selectedTarget.requires_completion_evidence ? 'Required' : 'Optional'} />
                        <WorkspaceSummaryRow label="Completion" value={selectedTarget.status === 'completed' ? 'Completed' : 'Pending'} />
                      </div>
                    ) : (
                      <p className="mt-4 text-sm text-muted-foreground">Select a learning assignment to review its completion posture.</p>
                    )}
                  </div>
                </div>
              )}
            />
          )}

          {selectedTarget && selectedTarget.status !== 'completed' && workspace.canCompleteLearning ? (
            <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="space-y-1">
                <h2 className="text-base font-semibold text-foreground">Record completion</h2>
                <p className="text-sm text-muted-foreground">
                  Mark the selected item as complete and attach evidence if the assignment requires it.
                </p>
              </div>
              <form className="mt-4 grid gap-3 md:grid-cols-2" onSubmit={handleCompleteTarget}>
                <WorkspaceField label="Completion notes" className="md:col-span-2">
                  <Textarea
                    value={form.completion_notes}
                    onChange={(event) => setForm((current) => ({ ...current, completion_notes: event.target.value }))}
                    rows={4}
                  />
                </WorkspaceField>
                {selectedTarget.requires_completion_evidence ? (
                  <>
                    <WorkspaceField label="Evidence type">
                      <Input
                        value={form.completion_evidence_type}
                        onChange={(event) => setForm((current) => ({ ...current, completion_evidence_type: event.target.value }))}
                      />
                    </WorkspaceField>
                    <WorkspaceField label="Evidence reference">
                      <Input
                        value={form.completion_evidence_reference}
                        onChange={(event) => setForm((current) => ({ ...current, completion_evidence_reference: event.target.value }))}
                      />
                    </WorkspaceField>
                    <WorkspaceField label="Evidence notes" className="md:col-span-2">
                      <Textarea
                        value={form.completion_evidence_notes}
                        onChange={(event) => setForm((current) => ({ ...current, completion_evidence_notes: event.target.value }))}
                        rows={3}
                      />
                    </WorkspaceField>
                  </>
                ) : null}
                <div className="md:col-span-2 flex flex-wrap items-center gap-2">
                  <Button type="submit">Complete learning item</Button>
                  <Button type="button" variant="secondary" onClick={() => setForm(defaultCompletionForm())}>
                    Reset
                  </Button>
                </div>
              </form>
            </div>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
