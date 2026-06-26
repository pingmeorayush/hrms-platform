import { useMemo } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, BriefcaseBusiness, FileCheck2, KanbanSquare, UserRoundCheck } from 'lucide-react'
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
import { useRecruitmentRouteWorkspace } from './useRecruitmentRouteWorkspace'
import { formatRecruitmentDate, formatRecruitmentLabel } from '../utils'

export function RecruitmentOverviewPage() {
  const workspace = useRecruitmentRouteWorkspace()
  const requisitions = useMemo(() => workspace.data?.requisitions ?? [], [workspace.data?.requisitions])
  const candidates = useMemo(() => workspace.data?.candidates ?? [], [workspace.data?.candidates])
  const interviews = useMemo(() => workspace.data?.interviews ?? [], [workspace.data?.interviews])
  const offers = useMemo(() => workspace.data?.offers ?? [], [workspace.data?.offers])
  const handoffs = useMemo(() => workspace.data?.handoffs ?? [], [workspace.data?.handoffs])

  const metrics = useMemo(
    () => [
      {
        id: 'approved-requisitions',
        label: 'Approved requisitions',
        value: requisitions.filter((item) => item.status === 'approved').length,
        detail: `${requisitions.filter((item) => item.status === 'submitted').length} awaiting approval`,
        icon: <BriefcaseBusiness className="h-4 w-4" />,
        tone: 'info' as const,
      },
      {
        id: 'active-candidates',
        label: 'Active candidates',
        value: candidates.filter((item) => item.status === 'active').length,
        detail: `${candidates.filter((item) => item.current_stage === 'interview').length} in interview`,
        icon: <KanbanSquare className="h-4 w-4" />,
        tone: 'success' as const,
      },
      {
        id: 'offer-pressure',
        label: 'Offer pressure',
        value: offers.filter((item) => ['submitted', 'approved', 'sent'].includes(item.status)).length,
        detail: `${offers.filter((item) => item.status === 'accepted').length} accepted`,
        icon: <FileCheck2 className="h-4 w-4" />,
        tone: 'warning' as const,
      },
      {
        id: 'hire-handoffs',
        label: 'Hire handoffs',
        value: handoffs.length,
        detail: `${handoffs.filter((item) => item.status === 'onboarding_queued').length} queued into onboarding`,
        icon: <UserRoundCheck className="h-4 w-4" />,
        tone: 'neutral' as const,
      },
    ],
    [candidates, handoffs, offers, requisitions],
  )

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      title: string
      detail: string
      tone: 'warning' | 'danger' | 'info' | 'success'
      path: string
    }> = []

    const pendingApproval = requisitions.find((item) => item.status === 'submitted')
    if (pendingApproval) {
      items.push({
        id: 'pending-requisition',
        title: `${pendingApproval.requisition_code} is waiting on approval`,
        detail: `${pendingApproval.title} is submitted and blocking candidate intake until approval is resolved.`,
        tone: 'warning',
        path: '/recruitment/requisitions',
      })
    }

    const acceptedWithoutHandoff = offers.find((offer) => offer.status === 'accepted' && !offer.hire_handoff)
    if (acceptedWithoutHandoff) {
      items.push({
        id: 'accepted-offer',
        title: `${acceptedWithoutHandoff.offer_code} needs hire handoff`,
        detail: 'An accepted offer exists without employee conversion or onboarding queue activation.',
        tone: 'danger',
        path: `/recruitment/candidates/${acceptedWithoutHandoff.candidate?.id ?? ''}`,
      })
    }

    const pendingFeedback = interviews.find((interview) => interview.status === 'scheduled' && !interview.feedback)
    if (pendingFeedback) {
      items.push({
        id: 'pending-feedback',
        title: `${pendingFeedback.interview_code} is scheduled without scorecard closure`,
        detail: `${pendingFeedback.candidate?.full_name ?? 'Candidate'} still needs feedback capture after the interview round.`,
        tone: 'info',
        path: `/recruitment/candidates/${pendingFeedback.candidate?.id ?? ''}`,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        title: 'Recruitment posture looks healthy',
        detail: 'There are no submitted requisitions, accepted-offer gaps, or scheduled interview follow-ups currently flagged.',
        tone: 'success',
        path: '/recruitment/overview',
      })
    }

    return items.slice(0, 3)
  }, [interviews, offers, requisitions])

  const stageSummary = useMemo(() => {
    const stageCounts = new Map<string, number>()
    candidates.forEach((candidate) => {
      stageCounts.set(candidate.current_stage, (stageCounts.get(candidate.current_stage) ?? 0) + 1)
    })

    return [...stageCounts.entries()].sort((left, right) => right[1] - left[1])
  }, [candidates])

  const upcomingInterviews = useMemo(
    () =>
      interviews
        .filter((interview) => interview.status === 'scheduled')
        .sort((left, right) => `${left.scheduled_start_at ?? ''}`.localeCompare(`${right.scheduled_start_at ?? ''}`))
        .slice(0, 4),
    [interviews],
  )

  const expiringOffers = useMemo(
    () =>
      offers
        .filter((offer) => ['approved', 'sent'].includes(offer.status))
        .sort((left, right) => `${left.expires_on ?? ''}`.localeCompare(`${right.expires_on ?? ''}`))
        .slice(0, 4),
    [offers],
  )

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading recruitment workspace" copy="Resolving requisitions, pipeline, interviews, offers, and handoffs." />
  }

  if (workspace.error) {
    return (
      <WorkspaceEmptyState
        title="Recruitment workspace unavailable"
        copy={workspace.error.message || 'The recruiter operations view could not be loaded.'}
      />
    )
  }

  if (!workspace.data || !workspace.canViewRecruitment) {
    return (
      <WorkspaceEmptyState
        title="Recruitment workspace unavailable"
        copy="This session does not currently resolve to recruitment visibility or management access."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Recruitment"
          title="Recruitment Operations Center"
          description="Track hiring demand, pipeline movement, pending approvals, and accepted-offer handoff posture from one talent workspace."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            workspace.canManageRecruitment ? 'Pipeline controls live' : 'Recruiter visibility',
            workspace.canApproveRecruitment ? 'Approval controls enabled' : 'Follow-up posture',
          ]}
          actions={
            <>
              <Button asChild size="sm">
                <Link to="/recruitment/requisitions">
                  Review requisitions
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="secondary" size="sm">
                <Link to="/recruitment/candidates">Open candidate pipeline</Link>
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

          <div className="grid gap-4 xl:grid-cols-[1.25fr_0.9fr]">
            <div className="space-y-3">
              <div className="rounded-[1rem] border border-line/80 bg-white/92 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h3 className="text-base font-semibold text-foreground">Attention queue</h3>
                    <p className="text-sm text-muted-foreground">
                      Surface approvals, offer follow-through, and interview follow-up before they become stale.
                    </p>
                  </div>
                  <Badge variant="warning">{attentionItems.length} items</Badge>
                </div>
                <div className="mt-3 space-y-2.5">
                  {attentionItems.map((item) => (
                    <Link
                      key={item.id}
                      to={item.path}
                      className="flex flex-col gap-1 rounded-xl border border-line/80 bg-panel-soft/70 px-3 py-3 transition hover:border-line-strong hover:bg-panel"
                    >
                      <div className="flex items-center gap-2">
                        <Badge variant={item.tone === 'danger' ? 'danger' : item.tone === 'warning' ? 'warning' : item.tone === 'info' ? 'info' : 'success'}>
                          {formatRecruitmentLabel(item.tone)}
                        </Badge>
                        <span className="font-semibold text-foreground">{item.title}</span>
                      </div>
                      <p className="text-sm text-muted-foreground">{item.detail}</p>
                    </Link>
                  ))}
                </div>
              </div>

              <div className="rounded-[1rem] border border-line/80 bg-white/92 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h3 className="text-base font-semibold text-foreground">Pipeline distribution</h3>
                    <p className="text-sm text-muted-foreground">
                      Candidate flow by current stage across the visible recruitment scope.
                    </p>
                  </div>
                  <Button asChild variant="secondary" size="sm">
                    <Link to="/recruitment/candidates">Open board</Link>
                  </Button>
                </div>
                <div className="mt-3 grid gap-2 sm:grid-cols-2">
                  {stageSummary.map(([stage, count]) => (
                    <div key={stage} className="rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                      <div className="flex items-center justify-between">
                        <Badge variant="neutral">{formatRecruitmentLabel(stage)}</Badge>
                        <span className="text-lg font-semibold text-foreground">{count}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            <div className="space-y-3">
              <div className="rounded-[1rem] border border-line/80 bg-white/92 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <h3 className="text-base font-semibold text-foreground">Upcoming interviews</h3>
                <div className="mt-3 space-y-2.5">
                  {upcomingInterviews.length ? (
                    upcomingInterviews.map((interview) => (
                      <Link
                        key={interview.id}
                        to={`/recruitment/candidates/${interview.candidate?.id ?? ''}`}
                        className="block rounded-xl border border-line/80 bg-panel-soft/70 px-3 py-3 transition hover:border-line-strong hover:bg-panel"
                      >
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-semibold text-foreground">{interview.candidate?.full_name ?? 'Candidate'}</span>
                          <Badge variant="info">{formatRecruitmentLabel(interview.interview_type)}</Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                          {formatRecruitmentDate(interview.scheduled_start_at)} · {interview.interviewer?.name ?? 'Interviewer pending'}
                        </p>
                      </Link>
                    ))
                  ) : (
                    <p className="text-sm text-muted-foreground">No scheduled interviews are currently visible in this scope.</p>
                  )}
                </div>
              </div>

              <div className="rounded-[1rem] border border-line/80 bg-white/92 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <h3 className="text-base font-semibold text-foreground">Offer pressure</h3>
                <div className="mt-3 space-y-2.5">
                  {expiringOffers.length ? (
                    expiringOffers.map((offer) => (
                      <Link
                        key={offer.id}
                        to={`/recruitment/candidates/${offer.candidate?.id ?? ''}`}
                        className="block rounded-xl border border-line/80 bg-panel-soft/70 px-3 py-3 transition hover:border-line-strong hover:bg-panel"
                      >
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-semibold text-foreground">{offer.offer_code}</span>
                          <Badge variant="warning">{formatRecruitmentLabel(offer.status)}</Badge>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                          {offer.candidate?.full_name ?? 'Candidate'} · expires {formatRecruitmentDate(offer.expires_on)}
                        </p>
                      </Link>
                    ))
                  ) : (
                    <p className="text-sm text-muted-foreground">No approved or sent offers are currently close to expiry.</p>
                  )}
                </div>
              </div>
            </div>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
