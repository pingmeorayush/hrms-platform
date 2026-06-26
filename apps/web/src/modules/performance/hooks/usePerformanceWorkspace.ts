import { useCallback, useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  calibratePerformanceReview,
  createPerformanceCompetency,
  createPerformanceGoal,
  createPerformanceReview,
  createPerformanceReviewCycle,
  fetchPerformanceWorkspace,
  finalizePerformanceReview,
  publishPerformanceReview,
  reopenPerformanceReview,
  submitPerformanceReview,
} from '../api/performanceApi'
import { buildDemoPerformanceWorkspace } from '../data/demoPerformanceWorkspace'
import type {
  CalibratePerformanceReviewInput,
  CreatePerformanceCompetencyInput,
  CreatePerformanceGoalInput,
  CreatePerformanceReviewCycleInput,
  CreatePerformanceReviewInput,
  FinalizePerformanceReviewInput,
  PerformanceReviewRecord,
  PerformanceReviewStatus,
  PerformanceWorkspaceData,
  SubmitPerformanceReviewInput,
} from '../types'

const workspaceQueryScope = 'performance-workspace'

function delay(ms: number) {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms)
  })
}

function friendlyActionError(error: unknown) {
  if (error instanceof ApiRequestError) {
    const firstFieldError = Object.values(error.fieldErrors)[0]?.[0]
    return firstFieldError ?? error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'The performance action could not be completed right now.'
}

function resolveActorRole(review: PerformanceReviewRecord, userId: number | null, linkedEmployeeId: number | null, canManage: boolean, canCalibrate: boolean) {
  if (canManage || canCalibrate) {
    return 'hr' as const
  }

  if (linkedEmployeeId !== null && review.employee?.id === linkedEmployeeId) {
    return 'self' as const
  }

  if (linkedEmployeeId !== null && review.manager_employee?.id === linkedEmployeeId) {
    return 'manager' as const
  }

  if (userId !== null && review.reviewer_user_ids.includes(userId)) {
    return 'reviewer' as const
  }

  return null
}

function decorateWorkspaceData(
  data: PerformanceWorkspaceData | null,
  userId: number | null,
  linkedEmployeeId: number | null,
  canManage: boolean,
  canReview: boolean,
  canCalibrate: boolean,
): PerformanceWorkspaceData | null {
  if (!data) {
    return null
  }

  return {
    ...data,
    reviews: data.reviews.map((review) => ({
      ...review,
      actor_role: resolveActorRole(review, userId, linkedEmployeeId, canManage, canCalibrate),
    })),
    meta: {
      ...data.meta,
      can_manage: canManage,
      can_review: canReview,
      can_calibrate: canCalibrate,
      linked_employee_id: linkedEmployeeId,
    },
  }
}

function withUpdatedReview(workspace: PerformanceWorkspaceData, reviewId: number, updater: (review: PerformanceReviewRecord) => PerformanceReviewRecord) {
  return {
    ...workspace,
    reviews: workspace.reviews.map((review) => (review.id === reviewId ? updater(review) : review)),
  }
}

export function usePerformanceWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, PerformanceWorkspaceData>>({})
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const canViewPerformance = permissions.some((permission) =>
    ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'].includes(permission),
  )
  const canManagePerformance = permissions.includes('performance.manage')
  const canReviewPerformance = permissions.includes('performance.review') || canManagePerformance
  const canCalibratePerformance = permissions.includes('performance.calibrate') || canManagePerformance
  const linkedEmployeeId = snapshot?.user.employee?.id ?? null
  const currentUserId = snapshot?.user.id ?? null

  const rawDemoData = demoStates[demoStateKey] ?? buildDemoPerformanceWorkspace(snapshot)

  const liveQuery = useQuery({
    queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token],
    queryFn: () => fetchPerformanceWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const rawData = source === 'demo' ? rawDemoData : liveQuery.data ?? null
  const data = useMemo(
    () =>
      decorateWorkspaceData(
        rawData,
        currentUserId,
        linkedEmployeeId,
        canManagePerformance,
        canReviewPerformance,
        canCalibratePerformance,
      ),
    [rawData, currentUserId, linkedEmployeeId, canManagePerformance, canReviewPerformance, canCalibratePerformance],
  )

  const refreshLiveQueries = useCallback(async () => {
    await queryClient.invalidateQueries({ queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token] })
  }, [access.apiBaseUrl, access.token, queryClient])

  const mutateDemoState = useCallback(
    async (mutator: (current: PerformanceWorkspaceData) => PerformanceWorkspaceData) => {
      await delay(150)
      setDemoStates((current) => {
        const base = current[demoStateKey] ?? buildDemoPerformanceWorkspace(snapshot)

        return {
          ...current,
          [demoStateKey]: mutator(base),
        }
      })
    },
    [demoStateKey, snapshot],
  )

  const runAction = useCallback(
    async (label: string, message: string, action: () => Promise<void>) => {
      setPendingActionLabel(label)
      setActionError(null)
      setLastActionMessage(null)

      try {
        await action()
        setLastActionMessage(message)
      } catch (error) {
        setActionError(friendlyActionError(error))
        throw error
      } finally {
        setPendingActionLabel(null)
      }
    },
    [],
  )

  const createGoalAction = useCallback(
    async (payload: CreatePerformanceGoalInput) => {
      await runAction('Creating goal', 'Goal library entry added.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(0, ...current.goals.map((goal) => goal.id)) + 1
            const owner = current.employees.find((employee) => employee.id === payload.owner_employee_id) ?? null
            const reviewCycle = current.reviewCycles.find((cycle) => cycle.id === payload.performance_review_cycle_id) ?? null

            return {
              ...current,
              goals: [
                {
                  id: nextId,
                  goal_code: payload.goal_code,
                  goal_type: payload.goal_type,
                  title: payload.title,
                  description: payload.description ?? null,
                  review_cycle: reviewCycle
                    ? {
                        id: reviewCycle.id,
                        code: reviewCycle.code,
                        name: reviewCycle.name,
                        status: reviewCycle.status,
                      }
                    : null,
                  owner_employee: owner
                    ? {
                        id: owner.id,
                        employee_code: owner.employee_code,
                        full_name: owner.full_name,
                        email: owner.email,
                      }
                    : null,
                  department: owner?.department ?? null,
                  due_on: payload.due_on,
                  weight_percent: payload.weight_percent,
                  success_metric: payload.success_metric ?? null,
                  status: payload.status,
                  can_edit_configuration: payload.status !== 'archived',
                  created_at: new Date().toISOString(),
                  updated_at: new Date().toISOString(),
                },
                ...current.goals,
              ],
            }
          })
          return
        }

        await createPerformanceGoal(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createCompetencyAction = useCallback(
    async (payload: CreatePerformanceCompetencyInput) => {
      await runAction('Creating competency', 'Competency added to the review library.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(0, ...current.competencies.map((competency) => competency.id)) + 1

            return {
              ...current,
              competencies: [
                {
                  id: nextId,
                  code: payload.code,
                  name: payload.name,
                  category: payload.category,
                  description: payload.description ?? null,
                  scale_definition: payload.scale_definition,
                  status: payload.status,
                  created_at: new Date().toISOString(),
                  updated_at: new Date().toISOString(),
                },
                ...current.competencies,
              ],
            }
          })
          return
        }

        await createPerformanceCompetency(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createReviewCycleAction = useCallback(
    async (payload: CreatePerformanceReviewCycleInput) => {
      await runAction('Creating review cycle', 'Review cycle configuration saved.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(0, ...current.reviewCycles.map((cycle) => cycle.id)) + 1

            return {
              ...current,
              reviewCycles: [
                {
                  id: nextId,
                  code: payload.code,
                  name: payload.name,
                  cycle_type: payload.cycle_type,
                  starts_on: payload.starts_on,
                  ends_on: payload.ends_on,
                  self_review_due_on: payload.self_review_due_on ?? null,
                  manager_review_due_on: payload.manager_review_due_on ?? null,
                  calibration_starts_on: payload.calibration_starts_on ?? null,
                  calibration_ends_on: payload.calibration_ends_on ?? null,
                  publish_on: payload.publish_on ?? null,
                  participant_rules: payload.participant_rules,
                  review_template: payload.review_template,
                  competency_visibility: payload.competency_visibility,
                  status: payload.status,
                  goal_count: 0,
                  can_edit_configuration: payload.status !== 'archived',
                  created_at: new Date().toISOString(),
                  updated_at: new Date().toISOString(),
                },
                ...current.reviewCycles,
              ],
            }
          })
          return
        }

        await createPerformanceReviewCycle(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createReviewAction = useCallback(
    async (payload: CreatePerformanceReviewInput) => {
      await runAction('Launching review', 'Performance review launched.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(0, ...current.reviews.map((review) => review.id)) + 1
            const cycle = current.reviewCycles.find((item) => item.id === payload.performance_review_cycle_id)
            const employee = current.employees.find((item) => item.id === payload.employee_id) ?? null
            const manager = employee?.manager ?? null
            const actorRole = resolveActorRole(
              {
                id: nextId,
                review_cycle: cycle
                  ? {
                      id: cycle.id,
                      code: cycle.code,
                      name: cycle.name,
                      status: cycle.status,
                      self_review_due_on: cycle.self_review_due_on,
                      manager_review_due_on: cycle.manager_review_due_on,
                    }
                  : null,
                employee: employee
                  ? {
                      id: employee.id,
                      employee_code: employee.employee_code,
                      full_name: employee.full_name,
                      email: employee.email,
                    }
                  : null,
                manager_employee: manager,
                reviewer_user_ids: payload.reviewer_user_ids ?? [],
                goal_snapshot: current.goals
                  .filter((goal) => goal.owner_employee?.id === payload.employee_id)
                  .map((goal) => ({
                    id: goal.id,
                    goal_code: goal.goal_code,
                    title: goal.title,
                    description: goal.description,
                    due_on: goal.due_on,
                    weight_percent: goal.weight_percent,
                    success_metric: goal.success_metric,
                    status: goal.status,
                  })),
                competency_snapshot: current.competencies
                  .filter((competency) =>
                    cycle?.competency_visibility.required_competency_ids.includes(competency.id) ?? false,
                  )
                  .map((competency) => ({
                    id: competency.id,
                    code: competency.code,
                    name: competency.name,
                    category: competency.category,
                    scale_definition: competency.scale_definition,
                  })),
                visibility_rules: {
                  employee_can_view_manager_assessment_before_publish:
                    payload.visibility_rules?.employee_can_view_manager_assessment_before_publish ?? false,
                  employee_can_view_peer_feedback_after_publish:
                    payload.visibility_rules?.employee_can_view_peer_feedback_after_publish ?? false,
                  peer_feedback_anonymous_to_employee:
                    payload.visibility_rules?.peer_feedback_anonymous_to_employee ?? true,
                  manager_can_view_peer_feedback: payload.visibility_rules?.manager_can_view_peer_feedback ?? true,
                  reviewer_can_view_other_reviewer_feedback:
                    payload.visibility_rules?.reviewer_can_view_other_reviewer_feedback ?? false,
                },
                status: (payload.launch_immediately ?? true)
                  ? cycle?.participant_rules.reviewers.self_review_required
                    ? 'self_assessment'
                    : 'manager_review'
                  : 'draft',
                actor_role: null,
                submissions: [],
                calibration_payload: null,
                final_payload: null,
                launched_at: payload.launch_immediately ?? true ? new Date().toISOString() : null,
                self_submitted_at: null,
                manager_submitted_at: null,
                calibration_completed_at: null,
                finalized_at: null,
                published_at: null,
                reopened_at: null,
                reopen_count: 0,
                reopened_reason: null,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
              },
              currentUserId,
              linkedEmployeeId,
              canManagePerformance,
              canCalibratePerformance,
            )

            return {
              ...current,
              reviews: [
                {
                  id: nextId,
                  review_cycle: cycle
                    ? {
                        id: cycle.id,
                        code: cycle.code,
                        name: cycle.name,
                        status: cycle.status,
                        self_review_due_on: cycle.self_review_due_on,
                        manager_review_due_on: cycle.manager_review_due_on,
                      }
                    : null,
                  employee: employee
                    ? {
                        id: employee.id,
                        employee_code: employee.employee_code,
                        full_name: employee.full_name,
                        email: employee.email,
                      }
                    : null,
                  manager_employee: manager,
                  reviewer_user_ids: payload.reviewer_user_ids ?? [],
                  goal_snapshot: current.goals
                    .filter((goal) => goal.owner_employee?.id === payload.employee_id)
                    .map((goal) => ({
                      id: goal.id,
                      goal_code: goal.goal_code,
                      title: goal.title,
                      description: goal.description,
                      due_on: goal.due_on,
                      weight_percent: goal.weight_percent,
                      success_metric: goal.success_metric,
                      status: goal.status,
                    })),
                  competency_snapshot: current.competencies
                    .filter((competency) =>
                      cycle?.competency_visibility.required_competency_ids.includes(competency.id) ?? false,
                    )
                    .map((competency) => ({
                      id: competency.id,
                      code: competency.code,
                      name: competency.name,
                      category: competency.category,
                      scale_definition: competency.scale_definition,
                    })),
                  visibility_rules: {
                    employee_can_view_manager_assessment_before_publish:
                      payload.visibility_rules?.employee_can_view_manager_assessment_before_publish ?? false,
                    employee_can_view_peer_feedback_after_publish:
                      payload.visibility_rules?.employee_can_view_peer_feedback_after_publish ?? false,
                    peer_feedback_anonymous_to_employee:
                      payload.visibility_rules?.peer_feedback_anonymous_to_employee ?? true,
                    manager_can_view_peer_feedback: payload.visibility_rules?.manager_can_view_peer_feedback ?? true,
                    reviewer_can_view_other_reviewer_feedback:
                      payload.visibility_rules?.reviewer_can_view_other_reviewer_feedback ?? false,
                  },
                  status: (payload.launch_immediately ?? true)
                    ? cycle?.participant_rules.reviewers.self_review_required
                      ? 'self_assessment'
                      : 'manager_review'
                    : 'draft',
                  actor_role: actorRole,
                  submissions: [],
                  calibration_payload: null,
                  final_payload: null,
                  launched_at: payload.launch_immediately ?? true ? new Date().toISOString() : null,
                  self_submitted_at: null,
                  manager_submitted_at: null,
                  calibration_completed_at: null,
                  finalized_at: null,
                  published_at: null,
                  reopened_at: null,
                  reopen_count: 0,
                  reopened_reason: null,
                  created_at: new Date().toISOString(),
                  updated_at: new Date().toISOString(),
                },
                ...current.reviews,
              ],
            }
          })
          return
        }

        await createPerformanceReview(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [
      access.apiBaseUrl,
      access.token,
      canCalibratePerformance,
      canManagePerformance,
      currentUserId,
      linkedEmployeeId,
      mutateDemoState,
      refreshLiveQueries,
      runAction,
      source,
    ],
  )

  const submitReviewAction = useCallback(
    async (reviewId: number, payload: SubmitPerformanceReviewInput) => {
      await runAction('Saving submission', 'Review input saved.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) =>
            withUpdatedReview(current, reviewId, (review) => {
              const actorRole = resolveActorRole(
                review,
                currentUserId,
                linkedEmployeeId,
                canManagePerformance,
                canCalibratePerformance,
              )

              const roleType = actorRole === 'manager' || actorRole === 'reviewer' ? actorRole : 'self'
              const nextStatus: PerformanceReviewStatus =
                roleType === 'self' ? 'manager_review' : review.reviewer_user_ids.length > 0 ? 'calibration' : 'calibration'

              const nextSubmissionId = Math.max(0, ...review.submissions.map((submission) => submission.id)) + 1
              const submission = {
                id: nextSubmissionId,
                role_type: roleType,
                submitted_by: {
                  id: currentUserId,
                  name: snapshot?.user.name ?? 'Current user',
                  employee_id: linkedEmployeeId,
                },
                is_anonymous_to_current_user: false,
                overall_rating: payload.overall_rating,
                summary: payload.summary,
                confidential_notes: payload.confidential_notes ?? null,
                section_payload: payload.sections,
                competency_payload: payload.competencies ?? [],
                submitted_at: new Date().toISOString(),
              } as PerformanceReviewRecord['submissions'][number]

              const filteredSubmissions = review.submissions.filter((item) => item.role_type !== roleType)

              return {
                ...review,
                status: nextStatus,
                submissions: [...filteredSubmissions, submission],
                self_submitted_at: roleType === 'self' ? submission.submitted_at : review.self_submitted_at,
                manager_submitted_at: roleType === 'manager' ? submission.submitted_at : review.manager_submitted_at,
                updated_at: new Date().toISOString(),
              }
            }),
          )
          return
        }

        await submitPerformanceReview(access.apiBaseUrl, access.token, reviewId, payload)
        await refreshLiveQueries()
      })
    },
    [
      access.apiBaseUrl,
      access.token,
      canCalibratePerformance,
      canManagePerformance,
      currentUserId,
      linkedEmployeeId,
      mutateDemoState,
      refreshLiveQueries,
      runAction,
      snapshot?.user.name,
      source,
    ],
  )

  const calibrateReviewAction = useCallback(
    async (reviewId: number, payload: CalibratePerformanceReviewInput) => {
      await runAction('Calibrating review', 'Calibration notes saved.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) =>
            withUpdatedReview(current, reviewId, (review) => ({
              ...review,
              status: 'calibration',
              calibration_payload: {
                overall_rating: payload.overall_rating,
                summary: payload.summary,
                confidential_notes: payload.confidential_notes ?? null,
                section_adjustments: payload.section_adjustments ?? [],
                competency_adjustments: payload.competency_adjustments ?? [],
              },
              calibration_completed_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            })),
          )
          return
        }

        await calibratePerformanceReview(access.apiBaseUrl, access.token, reviewId, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const finalizeReviewAction = useCallback(
    async (reviewId: number, payload: FinalizePerformanceReviewInput) => {
      await runAction('Finalizing review', 'Review locked into final state.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) =>
            withUpdatedReview(current, reviewId, (review) => ({
              ...review,
              status: 'finalized',
              final_payload: {
                final_rating: payload.final_rating,
                summary: payload.summary,
                employee_visible_summary: payload.employee_visible_summary,
                recommendation: payload.recommendation ?? null,
                finalized_by_user_id: currentUserId ?? 0,
                finalized_by_name: snapshot?.user.name ?? 'Current user',
              },
              finalized_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            })),
          )
          return
        }

        await finalizePerformanceReview(access.apiBaseUrl, access.token, reviewId, payload)
        await refreshLiveQueries()
      })
    },
    [
      access.apiBaseUrl,
      access.token,
      currentUserId,
      mutateDemoState,
      refreshLiveQueries,
      runAction,
      snapshot?.user.name,
      source,
    ],
  )

  const publishReviewAction = useCallback(
    async (reviewId: number) => {
      await runAction('Publishing review', 'Review released to the employee view.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) =>
            withUpdatedReview(current, reviewId, (review) => ({
              ...review,
              status: 'published',
              published_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            })),
          )
          return
        }

        await publishPerformanceReview(access.apiBaseUrl, access.token, reviewId)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const reopenReviewAction = useCallback(
    async (reviewId: number, reason: string) => {
      await runAction('Reopening review', 'Review reopened for another submission round.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) =>
            withUpdatedReview(current, reviewId, (review) => ({
              ...review,
              status: 'reopened',
              reopened_at: new Date().toISOString(),
              reopened_reason: reason,
              reopen_count: review.reopen_count + 1,
              updated_at: new Date().toISOString(),
            })),
          )
          return
        }

        await reopenPerformanceReview(access.apiBaseUrl, access.token, reviewId, reason)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  return {
    data,
    source,
    isLoading: liveEnabled ? liveQuery.isLoading : false,
    error: liveEnabled ? liveQuery.error : null,
    canViewPerformance,
    canManagePerformance,
    canReviewPerformance,
    canCalibratePerformance,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    createGoal: createGoalAction,
    createCompetency: createCompetencyAction,
    createReviewCycle: createReviewCycleAction,
    createReview: createReviewAction,
    submitReview: submitReviewAction,
    calibrateReview: calibrateReviewAction,
    finalizeReview: finalizeReviewAction,
    publishReview: publishReviewAction,
    reopenReview: reopenReviewAction,
  }
}
