import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceFilters,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { SelectField } from '../../../shared/ui/select-field'
import { useRecruitmentRouteWorkspace } from './useRecruitmentRouteWorkspace'
import type { RecruitmentCandidateFilters, RecruitmentCandidateRecord } from '../types'
import {
  candidateStageBadgeVariant,
  candidateStatusBadgeVariant,
  formatRecruitmentDate,
  formatRecruitmentLabel,
  nextCandidateStage,
  recruitmentCandidateStages,
} from '../utils'

const stageOptions: Array<[string, string]> = [
  ['', 'All stages'],
  ...recruitmentCandidateStages.map((stage) => [stage, formatRecruitmentLabel(stage)] as [string, string]),
]
const statusOptions: Array<[string, string]> = [
  ['', 'All statuses'],
  ['active', 'Active'],
  ['hired', 'Hired'],
  ['rejected', 'Rejected'],
  ['withdrawn', 'Withdrawn'],
]

export function RecruitmentCandidatesPage() {
  const workspace = useRecruitmentRouteWorkspace()
  const [filters, setFilters] = useState<RecruitmentCandidateFilters>({
    stage: '',
    status: '',
    requisitionId: '',
    q: '',
  })

  const candidates = useMemo(() => workspace.data?.candidates ?? [], [workspace.data?.candidates])
  const requisitions = useMemo(() => workspace.data?.requisitions ?? [], [workspace.data?.requisitions])

  const filteredCandidates = useMemo(() => {
    const query = filters.q.trim().toLowerCase()

    return candidates.filter((candidate) => {
      const matchesStage = !filters.stage || candidate.current_stage === filters.stage
      const matchesStatus = !filters.status || candidate.status === filters.status
      const matchesRequisition = !filters.requisitionId || String(candidate.requisition?.id ?? '') === filters.requisitionId
      const matchesQuery =
        query.length === 0 ||
        [
          candidate.candidate_code,
          candidate.full_name,
          candidate.email,
          candidate.current_company,
          candidate.current_title,
          candidate.requisition?.title,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase()
          .includes(query)

      return matchesStage && matchesStatus && matchesRequisition && matchesQuery
    })
  }, [candidates, filters])

  const groupedCandidates = useMemo(() => {
    return recruitmentCandidateStages.map((stage) => ({
      stage,
      items: filteredCandidates.filter((candidate) => candidate.current_stage === stage),
    }))
  }, [filteredCandidates])

  async function handleQuickAdvance(candidate: RecruitmentCandidateRecord) {
    const nextStage = nextCandidateStage(candidate.current_stage)

    if (!nextStage) {
      return
    }

    await workspace.actions.moveCandidateStage(candidate.id, nextStage, `Quick moved to ${formatRecruitmentLabel(nextStage)} from the board.`)
  }

  if (workspace.isLoading) {
    return <WorkspaceEmptyState title="Loading candidates" copy="Resolving pipeline, stage posture, and recruiter scope." />
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Candidates unavailable" copy={workspace.error.message || 'Unable to resolve the candidate pipeline.'} />
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Candidate pipeline</Badge>
            <h1 className="text-xl font-semibold text-foreground">Pipeline board and movement controls</h1>
            <p className="text-sm text-muted-foreground">
              Review stage distribution, open candidate details, and move visible profiles forward without losing audit-ready context.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Button asChild size="sm">
              <Link to="/recruitment/requisitions">
                Requisition posture
                <ArrowRight className="h-4 w-4" />
              </Link>
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
                    placeholder="Search candidate, company, or requisition"
                  />
                </WorkspaceField>
                <SelectField
                  label="Stage"
                  value={filters.stage}
                  options={stageOptions}
                  compact
                  onChange={(value) =>
                    setFilters((current) => ({ ...current, stage: value as RecruitmentCandidateFilters['stage'] }))
                  }
                />
                <SelectField
                  label="Status"
                  value={filters.status}
                  options={statusOptions}
                  compact
                  onChange={(value) =>
                    setFilters((current) => ({ ...current, status: value as RecruitmentCandidateFilters['status'] }))
                  }
                />
                <SelectField
                  label="Requisition"
                  value={filters.requisitionId}
                  options={[
                    ['', 'All requisitions'],
                    ...requisitions.map((requisition) => [String(requisition.id), `${requisition.requisition_code} · ${requisition.title}`] as [string, string]),
                  ]}
                  compact
                  onChange={(value) => setFilters((current) => ({ ...current, requisitionId: value }))}
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

          {!filteredCandidates.length ? (
            <WorkspaceEmptyState
              title="No candidates match the current filters"
              copy="Clear the board filters or switch to another recruitment persona to inspect a different pipeline scope."
            />
          ) : (
            <div className="grid gap-4 xl:grid-cols-3 2xl:grid-cols-4">
              {groupedCandidates.map((column) => (
                <section
                  key={column.stage}
                  className="rounded-[1.1rem] border border-line/80 bg-white/92 p-3.5 shadow-[0_14px_28px_rgba(15,23,42,0.05)]"
                >
                  <div className="flex items-center justify-between gap-2">
                    <div>
                      <div className="text-sm font-semibold text-foreground">{formatRecruitmentLabel(column.stage)}</div>
                      <div className="text-xs text-muted-foreground">{column.items.length} candidate(s)</div>
                    </div>
                    <Badge variant={candidateStageBadgeVariant(column.stage)}>{column.items.length}</Badge>
                  </div>
                  <div className="mt-3 space-y-3">
                    {column.items.length ? (
                      column.items.map((candidate) => {
                        const nextStage = nextCandidateStage(candidate.current_stage)
                        return (
                          <div
                            key={candidate.id}
                            className="rounded-xl border border-line/80 bg-panel-soft/70 px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.74)]"
                          >
                            <div className="flex items-start justify-between gap-3">
                              <div className="min-w-0">
                                <Link
                                  to={`/recruitment/candidates/${candidate.id}`}
                                  className="font-semibold text-foreground transition hover:text-primary"
                                >
                                  {candidate.full_name}
                                </Link>
                                <p className="mt-1 text-xs text-muted-foreground">
                                  {candidate.current_title ?? 'Title pending'} · {candidate.current_company ?? 'Company pending'}
                                </p>
                              </div>
                              <Badge variant={candidateStatusBadgeVariant(candidate.status)}>
                                {formatRecruitmentLabel(candidate.status)}
                              </Badge>
                            </div>

                            <div className="mt-3 space-y-1 text-xs text-muted-foreground">
                              <div>{candidate.requisition?.requisition_code ?? 'REQ pending'} · {candidate.requisition?.title ?? 'Requisition pending'}</div>
                              <div>Updated {formatRecruitmentDate(candidate.stage_entered_at)}</div>
                              <div>{candidate.resume_count ?? candidate.resumes?.length ?? 0} resume version(s)</div>
                            </div>

                            <div className="mt-3 flex flex-wrap gap-2">
                              <Button asChild size="xs" variant="secondary">
                                <Link to={`/recruitment/candidates/${candidate.id}`}>Open detail</Link>
                              </Button>
                              {nextStage && workspace.canManageRecruitment ? (
                                <Button size="xs" onClick={() => handleQuickAdvance(candidate)}>
                                  Move to {formatRecruitmentLabel(nextStage)}
                                </Button>
                              ) : null}
                            </div>
                          </div>
                        )
                      })
                    ) : (
                      <div className="rounded-xl border border-dashed border-line bg-panel-soft/55 px-3 py-4 text-sm text-muted-foreground">
                        No candidates are currently in this stage.
                      </div>
                    )}
                  </div>
                </section>
              ))}
            </div>
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
