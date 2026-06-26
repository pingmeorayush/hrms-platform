import { useMemo } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, BookOpenCheck, ClipboardCheck, RotateCcw, ShieldAlert } from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CommandCenterMetricCard, CommandCenterMetricGrid } from '../../../shared/ui/command-center'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
} from '../../../shared/ui/workspace'
import { learningDueStateVariant, learningRenewalVariant, formatLearningDate } from '../utils'
import { useLearningRouteWorkspace } from './useLearningRouteWorkspace'

export function LearningOverviewPage() {
  const workspace = useLearningRouteWorkspace()
  const data = workspace.data

  const metrics = useMemo(() => {
    const items = data?.items ?? []
    const targets = data?.targets ?? []
    const myAssignments = data?.myAssignments ?? []

    return [
      {
        id: 'catalog-items',
        label: workspace.canManageCatalog || workspace.canAssignLearning ? 'Catalog items' : 'Visible learning items',
        value: items.filter((item) => item.status === 'active').length,
        detail: `${items.filter((item) => item.status === 'draft').length} draft or staged next`,
        icon: <BookOpenCheck className="h-4 w-4" />,
        tone: 'info' as const,
      },
      {
        id: 'overdue',
        label: 'Overdue targets',
        value: targets.filter((target) => target.due_state === 'overdue').length,
        detail: `${targets.filter((target) => target.due_state === 'due_today').length} due today`,
        icon: <ShieldAlert className="h-4 w-4" />,
        tone: 'danger' as const,
      },
      {
        id: 'renewal',
        label: 'Renewal pressure',
        value: targets.filter((target) => target.renewal_posture === 'overdue').length,
        detail: `${targets.filter((target) => target.renewal_posture === 'due_today').length} renewal due today`,
        icon: <RotateCcw className="h-4 w-4" />,
        tone: 'warning' as const,
      },
      {
        id: 'my-work',
        label: 'My open work',
        value: myAssignments.filter((target) => target.status !== 'completed').length,
        detail: `${myAssignments.filter((target) => target.status === 'completed').length} already completed`,
        icon: <ClipboardCheck className="h-4 w-4" />,
        tone: 'success' as const,
      },
    ]
  }, [data?.items, data?.myAssignments, data?.targets, workspace.canAssignLearning, workspace.canManageCatalog])

  const attentionItems = useMemo(() => {
    if (!data) {
      return []
    }

    const items: Array<{ id: string; title: string; detail: string; tone: 'info' | 'warning' | 'danger' | 'success' }> = []

    const overdueTarget = data.targets.find((target) => target.due_state === 'overdue')
    if (overdueTarget) {
      items.push({
        id: 'overdue',
        title: `${overdueTarget.employee?.full_name ?? 'A learner'} is overdue on ${overdueTarget.item?.title ?? 'a learning item'}`,
        detail: `The due date was ${formatLearningDate(overdueTarget.due_on)} and still needs a completion decision or follow-up.`,
        tone: 'danger',
      })
    }

    const evidenceGap = data.targets.find((target) => target.requires_completion_evidence && target.status !== 'completed')
    if (evidenceGap) {
      items.push({
        id: 'evidence',
        title: `${evidenceGap.item?.title ?? 'An assignment'} still needs evidence-backed completion`,
        detail: `${evidenceGap.employee?.full_name ?? 'The learner'} cannot complete this item without a valid evidence reference.`,
        tone: 'warning',
      })
    }

    const renewalPressure = data.targets.find((target) => target.renewal_posture === 'overdue')
    if (renewalPressure) {
      items.push({
        id: 'renewal',
        title: `${renewalPressure.item?.title ?? 'A completed item'} is overdue for renewal`,
        detail: `${renewalPressure.employee?.full_name ?? 'The learner'} now needs a re-certification or a fresh acknowledgement cycle.`,
        tone: 'info',
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        title: 'Learning posture looks healthy',
        detail: 'No overdue targets, evidence gaps, or renewal escalations are currently visible in this session.',
        tone: 'success',
      })
    }

    return items.slice(0, 3)
  }, [data])

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading learning workspace" copy="Resolving catalog posture, assignments, and learner progress." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Learning workspace unavailable" copy={workspace.error.message || 'The learning workspace could not be loaded.'} />
  }

  if (!data || !workspace.canViewLearning) {
    return <WorkspaceEmptyState title="Learning workspace unavailable" copy="This session does not currently resolve to learning visibility." />
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Learning"
          title="Learning Operations Center"
          description="Track catalog readiness, compliance pressure, renewal posture, and employee completion evidence from one routed learning workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            workspace.canManageCatalog ? 'Catalog controls live' : 'Learner visibility',
            workspace.canAssignLearning ? 'Assignment controls enabled' : 'Self-service posture',
          ]}
          actions={
            <>
              <Button asChild size="sm">
                <Link to={workspace.canManageCatalog || workspace.canAssignLearning ? '/learning/assignments' : '/learning/my-learning'}>
                  {workspace.canManageCatalog || workspace.canAssignLearning ? 'Open assignments' : 'Open my learning'}
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
              {workspace.canManageCatalog ? (
                <Button asChild variant="secondary" size="sm">
                  <Link to="/learning/catalog">Open catalog</Link>
                </Button>
              ) : null}
            </>
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

          <CommandCenterMetricGrid className="xl:grid-cols-4 2xl:grid-cols-4">
            {metrics.map((metric) => (
              <CommandCenterMetricCard
                key={metric.id}
                label={metric.label}
                value={metric.value}
                delta={metric.detail}
                icon={metric.icon}
                tone={metric.tone}
              />
            ))}
          </CommandCenterMetricGrid>

          <div className="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
            <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="space-y-1">
                <h2 className="text-base font-semibold text-foreground">Attention queue</h2>
                <p className="text-sm text-muted-foreground">
                  The learning items that need the fastest follow-up in the current session.
                </p>
              </div>
              <div className="mt-4 space-y-3">
                {attentionItems.map((item) => (
                  <div key={item.id} className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3">
                    <div className="flex items-center justify-between gap-3">
                      <h3 className="text-sm font-semibold text-foreground">{item.title}</h3>
                      <Badge variant={item.tone}>{item.tone === 'info' ? 'Watch' : item.tone === 'warning' ? 'Needs action' : item.tone === 'danger' ? 'Escalated' : 'Healthy'}</Badge>
                    </div>
                    <p className="mt-2 text-sm text-muted-foreground">{item.detail}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="space-y-1">
                <h2 className="text-base font-semibold text-foreground">Visible learner posture</h2>
                <p className="text-sm text-muted-foreground">
                  Review due-state and renewal-state signals for the first visible assignments in this session.
                </p>
              </div>
              <div className="mt-4 space-y-3">
                {(data.myAssignments.length ? data.myAssignments : data.targets).slice(0, 4).map((target) => (
                  <div key={target.id} className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <div>
                        <p className="text-sm font-semibold text-foreground">{target.item?.title ?? 'Learning assignment'}</p>
                        <p className="text-xs text-muted-foreground">
                          {target.employee?.full_name ?? 'Linked employee'} · due {formatLearningDate(target.due_on)}
                        </p>
                      </div>
                      <div className="flex flex-wrap items-center gap-2">
                        <Badge variant={learningDueStateVariant(target.due_state)}>{target.due_state.replace(/_/g, ' ')}</Badge>
                        <Badge variant={learningRenewalVariant(target.renewal_posture)}>
                          {target.renewal_posture.replace(/_/g, ' ')}
                        </Badge>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
