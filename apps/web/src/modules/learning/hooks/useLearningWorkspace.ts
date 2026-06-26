import { useCallback, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  completeLearningTarget,
  createLearningAssignment,
  createLearningItem,
  fetchLearningWorkspace,
  updateLearningItem,
} from '../api/learningApi'
import {
  buildDemoLearningWorkspace,
  demoLearningToday,
  plusMonths,
  resolveAudienceRulesForTargeting,
  resolveDemoLearningDueState,
  resolveDemoLearningRenewalPosture,
} from '../data/demoLearningWorkspace'
import type {
  CompleteLearningTargetInput,
  CreateLearningAssignmentInput,
  CreateLearningItemInput,
  LearningAssignmentRecord,
  LearningAssignmentTargetRecord,
  LearningItemRecord,
  LearningWorkspaceData,
  UpdateLearningItemInput,
} from '../types'

const workspaceQueryScope = 'learning-workspace'

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

  return 'The learning action could not be completed right now.'
}

function dedupeLearningItems(items: Array<LearningItemRecord | null | undefined>) {
  const unique = new Map<number, LearningItemRecord>()

  items.forEach((item) => {
    if (item) {
      unique.set(item.id, item)
    }
  })

  return [...unique.values()]
}

function buildAssignmentSummary(
  targets: LearningAssignmentTargetRecord[],
  completionRules: LearningAssignmentRecord['completion_rules'],
) {
  return {
    total_count: targets.length,
    completed_count: targets.filter((target) => target.status === 'completed').length,
    overdue_count: targets.filter((target) => target.due_state === 'overdue').length,
    renewal_overdue_count:
      completionRules.renewal_frequency_months === null
        ? 0
        : targets.filter((target) => target.renewal_posture === 'overdue').length,
  }
}

function reconcileLearningWorkspaceData(current: LearningWorkspaceData) {
  const assignmentMap = new Map<number, LearningAssignmentTargetRecord[]>()

  current.targets.forEach((target) => {
    const assignmentId = target.assignment?.id
    if (assignmentId !== null && assignmentId !== undefined) {
      const existing = assignmentMap.get(assignmentId) ?? []
      existing.push(target)
      assignmentMap.set(assignmentId, existing)
    }
  })

  const assignments = current.assignments.map((assignment) => {
    const targets = assignmentMap.get(assignment.id) ?? []

    return {
      ...assignment,
      targets,
      target_count: targets.length,
      completion_count: targets.filter((target) => target.status === 'completed').length,
      target_summary: buildAssignmentSummary(targets, assignment.completion_rules),
    }
  })

  const myAssignments = current.meta.linked_employee_id === null
    ? []
    : current.targets.filter((target) => target.employee?.id === current.meta.linked_employee_id)

  return {
    ...current,
    assignments,
    myAssignments,
    items: current.meta.can_manage_catalog || current.meta.can_assign_learning
      ? current.items
      : dedupeLearningItems([
          ...assignments.map((assignment) => assignment.item),
          ...current.targets.map((target) => target.item),
          ...myAssignments.map((target) => target.item),
        ]),
  }
}

function applyItemToLearningState(
  current: LearningWorkspaceData,
  learningItemId: number,
  updater: (item: LearningItemRecord) => LearningItemRecord,
) {
  const items = current.items.map((item) => (item.id === learningItemId ? updater(item) : item))

  const assignments = current.assignments.map((assignment) => ({
    ...assignment,
    item: assignment.item?.id === learningItemId ? updater(assignment.item) : assignment.item,
    targets: assignment.targets?.map((target) => ({
      ...target,
      item: target.item?.id === learningItemId ? updater(target.item) : target.item,
    })),
  }))

  const targets = current.targets.map((target) => ({
    ...target,
    item: target.item?.id === learningItemId ? updater(target.item) : target.item,
  }))

  return {
    ...current,
    items,
    assignments,
    targets,
  }
}

export function useLearningWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, LearningWorkspaceData>>({})
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const canViewLearning = permissions.some((permission) =>
    ['learning.view', 'learning.manage', 'learning.assign', 'learning.complete'].includes(permission),
  )
  const canManageCatalog = permissions.includes('learning.manage')
  const canAssignLearning = permissions.includes('learning.assign') || canManageCatalog
  const canCompleteLearning = permissions.includes('learning.complete') || canManageCatalog
  const linkedEmployeeId = snapshot?.user.employee?.id ?? null

  const rawDemoData = demoStates[demoStateKey] ?? buildDemoLearningWorkspace(snapshot)

  const liveQuery = useQuery({
    queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token, linkedEmployeeId],
    queryFn: () =>
      fetchLearningWorkspace(access.apiBaseUrl, access.token, {
        canViewLearning,
        canManageCatalog,
        canAssignLearning,
        canCompleteLearning,
        linkedEmployeeId,
      }),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? rawDemoData : liveQuery.data ?? null

  const refreshLiveQueries = useCallback(async () => {
    await queryClient.invalidateQueries({
      queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token],
    })
  }, [access.apiBaseUrl, access.token, queryClient])

  const mutateDemoState = useCallback(
    async (mutator: (current: LearningWorkspaceData) => LearningWorkspaceData) => {
      await delay(150)
      setDemoStates((current) => {
        const base = current[demoStateKey] ?? buildDemoLearningWorkspace(snapshot)

        return {
          ...current,
          [demoStateKey]: reconcileLearningWorkspaceData(mutator(base)),
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

  const saveLearningItemAction = useCallback(
    async (learningItemId: number | null, payload: CreateLearningItemInput | UpdateLearningItemInput) => {
      await runAction(
        learningItemId === null ? 'Creating learning item' : 'Updating learning item',
        learningItemId === null ? 'Learning item created successfully.' : 'Learning item updated successfully.',
        async () => {
          if (source === 'demo') {
            await mutateDemoState((current) => {
              if (learningItemId === null) {
                const nextId = Math.max(0, ...current.items.map((item) => item.id)) + 1
                const now = new Date().toISOString()

                return {
                  ...current,
                  items: [
                    {
                      id: nextId,
                      code: payload.code ?? '',
                      title: payload.title ?? '',
                      description: payload.description ?? null,
                      category: payload.category ?? 'General',
                      delivery_mode: payload.delivery_mode ?? 'self_paced',
                      duration_minutes: payload.duration_minutes ?? null,
                      requires_completion_evidence: payload.requires_completion_evidence ?? false,
                      renewal_frequency_months: payload.renewal_frequency_months ?? null,
                      default_due_days: payload.default_due_days ?? null,
                      metadata: payload.metadata ?? null,
                      status: payload.status ?? 'draft',
                      created_at: now,
                      updated_at: now,
                    },
                    ...current.items,
                  ],
                }
              }

              return applyItemToLearningState(current, learningItemId, (item) => ({
                ...item,
                ...payload,
                description: payload.description ?? item.description,
                metadata: payload.metadata ?? item.metadata,
                updated_at: new Date().toISOString(),
              }))
            })

            return
          }

          if (learningItemId === null) {
            await createLearningItem(access.apiBaseUrl, access.token, payload as CreateLearningItemInput)
          } else {
            await updateLearningItem(access.apiBaseUrl, access.token, learningItemId, payload as UpdateLearningItemInput)
          }

          await refreshLiveQueries()
        },
      )
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createLearningAssignmentAction = useCallback(
    async (payload: CreateLearningAssignmentInput) => {
      await runAction('Creating learning assignment', 'Learning assignment created successfully.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const item = current.items.find((record) => record.id === payload.learning_item_id)
            if (!item) {
              return current
            }

            const nextAssignmentId = Math.max(0, ...current.assignments.map((assignment) => assignment.id)) + 1
            const nextTargetIdBase = Math.max(0, ...current.targets.map((target) => target.id)) + 1
            const assignedOn = payload.assigned_on ?? demoLearningToday
            const dueOn = payload.due_on ?? null
            const completionRules = {
              requires_completion_evidence: payload.requires_completion_evidence ?? item.requires_completion_evidence,
              renewal_frequency_months: payload.renewal_frequency_months ?? item.renewal_frequency_months,
              default_due_days: payload.default_due_days ?? item.default_due_days,
            }
            const targetEmployees = resolveAudienceRulesForTargeting(payload.audience_type, payload.audience_rules)
            const targets: LearningAssignmentTargetRecord[] = targetEmployees.map((employee, index) => {
              const resolvedDueOn =
                dueOn ??
                (completionRules.default_due_days === null
                  ? null
                  : (() => {
                      const date = new Date(`${assignedOn}T00:00:00`)
                      date.setDate(date.getDate() + completionRules.default_due_days)
                      return date.toISOString().slice(0, 10)
                    })())

              return {
                id: nextTargetIdBase + index,
                assignment: {
                  id: nextAssignmentId,
                  assignment_code: `L-${new Date().getFullYear()}-${String(nextAssignmentId).padStart(3, '0')}`,
                  status: 'active',
                  audience_type: payload.audience_type,
                },
                item,
                employee: {
                  id: employee.id,
                  employee_code: employee.employee_code,
                  full_name: employee.full_name,
                  email: employee.email,
                },
                status: 'assigned',
                completion_progress_percent: 0,
                due_on: resolvedDueOn,
                due_state: resolveDemoLearningDueState(resolvedDueOn, null),
                renewal_due_on: null,
                renewal_posture: resolveDemoLearningRenewalPosture(completionRules.renewal_frequency_months, null, null),
                requires_completion_evidence: completionRules.requires_completion_evidence,
                evidence_present: false,
                completion_notes: null,
                completion_evidence: null,
                completed_at: null,
                completed_by: null,
                assigned_on: assignedOn,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
              }
            })

            const nextAssignment: LearningAssignmentRecord = {
              id: nextAssignmentId,
              assignment_code: `L-${new Date().getFullYear()}-${String(nextAssignmentId).padStart(3, '0')}`,
              item,
              audience_type: payload.audience_type,
              audience_rules: payload.audience_rules,
              assigned_on: assignedOn,
              due_on: dueOn,
              completion_rules: completionRules,
              notes: payload.notes ?? null,
              status: 'active',
              target_count: targets.length,
              completion_count: 0,
              target_summary: buildAssignmentSummary(targets, completionRules),
              targets,
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            }

            return {
              ...current,
              assignments: [nextAssignment, ...current.assignments],
              targets: [...targets, ...current.targets],
            }
          })

          return
        }

        await createLearningAssignment(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const completeLearningTargetAction = useCallback(
    async (targetId: number, payload: CompleteLearningTargetInput) => {
      await runAction('Recording completion', 'Learning completion recorded.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const target = current.targets.find((record) => record.id === targetId)
            if (!target) {
              return current
            }

            const renewalFrequencyMonths =
              current.assignments.find((assignment) => assignment.id === target.assignment?.id)?.completion_rules
                .renewal_frequency_months ?? null
            const completedAt = new Date().toISOString()
            const renewalDueOn =
              renewalFrequencyMonths === null ? null : plusMonths(completedAt.slice(0, 10), renewalFrequencyMonths)

            const updateTarget = (record: LearningAssignmentTargetRecord): LearningAssignmentTargetRecord => {
              if (record.id !== targetId) {
                return record
              }

              return {
                ...record,
                status: 'completed',
                completion_progress_percent: 100,
                due_state: resolveDemoLearningDueState(record.due_on, completedAt),
                renewal_due_on: renewalDueOn,
                renewal_posture: resolveDemoLearningRenewalPosture(
                  renewalFrequencyMonths,
                  completedAt,
                  renewalDueOn,
                ),
                completion_notes: payload.completion_notes ?? record.completion_notes,
                completion_evidence: payload.completion_evidence ?? null,
                evidence_present: Boolean(payload.completion_evidence),
                completed_at: completedAt,
                completed_by: {
                  id: snapshot?.user.id ?? null,
                  name: snapshot?.user.name ?? null,
                },
                updated_at: completedAt,
              }
            }

            return {
              ...current,
              targets: current.targets.map(updateTarget),
            }
          })

          return
        }

        await completeLearningTarget(access.apiBaseUrl, access.token, targetId, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot?.user.id, snapshot?.user.name, source],
  )

  return {
    source,
    snapshot,
    data,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? ((liveQuery.error as Error | null) ?? null) : null,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    canViewLearning,
    canManageCatalog,
    canAssignLearning,
    canCompleteLearning,
    clearActionMessage() {
      setLastActionMessage(null)
      setActionError(null)
    },
    saveLearningItem: saveLearningItemAction,
    createLearningAssignment: createLearningAssignmentAction,
    completeLearningTarget: completeLearningTargetAction,
  }
}
