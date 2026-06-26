import { useMemo } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, Gauge, Goal, Layers3, Target } from 'lucide-react'
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
import { usePerformanceRouteWorkspace } from './usePerformanceRouteWorkspace'
import { formatActorRole, formatPerformanceDate, formatPerformanceLabel } from '../utils'

export function PerformanceOverviewPage() {
  const workspace = usePerformanceRouteWorkspace()
  const data = workspace.data

  const metrics = useMemo(() => {
    const goals = data?.goals ?? []
    const cycles = data?.reviewCycles ?? []
    const reviews = data?.reviews ?? []

    return [
      {
        id: 'active-cycles',
        label: 'Active cycles',
        value: cycles.filter((cycle) => cycle.status === 'active').length,
        detail: `${cycles.filter((cycle) => cycle.status === 'scheduled').length} scheduled next`,
        icon: <Layers3 className="h-4 w-4" />,
        tone: 'info' as const,
      },
      {
        id: 'active-goals',
        label: 'Visible goals',
        value: goals.filter((goal) => goal.status === 'active').length,
        detail: `${goals.filter((goal) => goal.due_on <= '2026-06-30').length} due this cycle`,
        icon: <Goal className="h-4 w-4" />,
        tone: 'success' as const,
      },
      {
        id: 'pending-reviews',
        label: 'Actionable reviews',
        value: reviews.filter((review) => ['self_assessment', 'manager_review', 'reopened'].includes(review.status)).length,
        detail: `${reviews.filter((review) => review.status === 'calibration').length} queued for calibration`,
        icon: <Target className="h-4 w-4" />,
        tone: 'warning' as const,
      },
      {
        id: 'released-reviews',
        label: 'Published reviews',
        value: reviews.filter((review) => review.status === 'published').length,
        detail: `${reviews.filter((review) => review.status === 'finalized').length} locked and awaiting release`,
        icon: <Gauge className="h-4 w-4" />,
        tone: 'neutral' as const,
      },
    ]
  }, [data?.goals, data?.reviewCycles, data?.reviews])

  const attentionItems = useMemo(() => {
    if (!data) {
      return []
    }

    const items: Array<{ id: string; tone: 'warning' | 'danger' | 'info' | 'success'; title: string; detail: string }> = []

    const selfAssessment = data.reviews.find((review) => review.status === 'self_assessment')
    if (selfAssessment) {
      items.push({
        id: 'self-review',
        tone: 'warning',
        title: `${selfAssessment.employee?.full_name ?? 'A review'} still needs self-assessment`,
        detail: `The ${selfAssessment.review_cycle?.name ?? 'active cycle'} window is still waiting on employee input before manager review begins.`,
      })
    }

    const calibration = data.reviews.find((review) => review.status === 'calibration')
    if (calibration) {
      items.push({
        id: 'calibration',
        tone: 'info',
        title: `${calibration.employee?.full_name ?? 'A review'} is ready for calibration`,
        detail: `${calibration.review_cycle?.name ?? 'The selected cycle'} now has the manager evidence needed for alignment and finalization.`,
      })
    }

    const reopened = data.reviews.find((review) => review.status === 'reopened')
    if (reopened) {
      items.push({
        id: 'reopened',
        tone: 'danger',
        title: `${reopened.employee?.full_name ?? 'A review'} was reopened`,
        detail: reopened.reopened_reason ?? 'The final review state was reopened and now needs controlled follow-up.',
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        tone: 'success',
        title: 'Performance posture looks healthy',
        detail: 'No self-review bottlenecks, calibration queues, or reopened review risks are currently flagged in this session.',
      })
    }

    return items.slice(0, 3)
  }, [data])

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading performance workspace" copy="Resolving goals, review cycles, competencies, and review posture." />
  }

  if (workspace.error) {
    return (
      <WorkspaceEmptyState
        title="Performance workspace unavailable"
        copy={workspace.error.message || 'The performance workspace could not be loaded.'}
      />
    )
  }

  if (!data || !workspace.canViewPerformance) {
    return (
      <WorkspaceEmptyState
        title="Performance workspace unavailable"
        copy="This session does not currently resolve to performance visibility or review access."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Performance"
          title="Performance Operations Center"
          description="Track review-cycle timing, employee goal delivery, self and manager review pressure, and final calibration readiness from one routed talent workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            workspace.canManagePerformance ? 'Cycle controls live' : 'Review visibility',
            workspace.canCalibratePerformance ? 'Calibration enabled' : 'Calibration watch',
          ]}
          actions={
            <>
              <Button asChild size="sm">
                <Link to="/performance/reviews">
                  Open reviews
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="secondary" size="sm">
                <Link to="/performance/goals">Review goals</Link>
              </Button>
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

          <div className="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="space-y-1">
                <h2 className="text-base font-semibold text-foreground">Attention queue</h2>
                <p className="text-sm text-muted-foreground">
                  The current review states that deserve the fastest follow-up for this session.
                </p>
              </div>
              <div className="mt-4 space-y-3">
                {attentionItems.map((item) => (
                  <div key={item.id} className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3">
                    <div className="flex items-center justify-between gap-3">
                      <h3 className="text-sm font-semibold text-foreground">{item.title}</h3>
                      <Badge variant={item.tone === 'danger' ? 'danger' : item.tone === 'warning' ? 'warning' : item.tone === 'success' ? 'success' : 'info'}>
                        {formatPerformanceLabel(item.tone)}
                      </Badge>
                    </div>
                    <p className="mt-2 text-sm text-muted-foreground">{item.detail}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="space-y-1">
                <h2 className="text-base font-semibold text-foreground">Current review posture</h2>
                <p className="text-sm text-muted-foreground">
                  Focus on due dates, actor context, and final publication posture across the visible review set.
                </p>
              </div>
              <div className="mt-4 space-y-3">
                {data.reviews.slice(0, 4).map((review) => (
                  <div key={review.id} className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <div>
                        <p className="text-sm font-semibold text-foreground">{review.employee?.full_name ?? 'Performance review'}</p>
                        <p className="text-xs text-muted-foreground">
                          {review.review_cycle?.name ?? 'Review cycle'} · {formatActorRole(review.actor_role)}
                        </p>
                      </div>
                      <Badge variant="neutral">{formatPerformanceLabel(review.status)}</Badge>
                    </div>
                    <p className="mt-2 text-sm text-muted-foreground">
                      Self due {formatPerformanceDate(review.review_cycle?.self_review_due_on)} · Manager due{' '}
                      {formatPerformanceDate(review.review_cycle?.manager_review_due_on)}
                    </p>
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
