import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  fetchAttendanceCorrections,
  fetchAttendanceOperationalReview,
  fetchAttendancePendingExceptions,
  updateAttendanceCorrectionDecision,
} from '../api/attendanceEmployeeApi'
import {
  applyDemoCorrectionDecision,
  type AttendanceReviewDemoState,
  buildDemoAttendanceReviewState,
  deriveDemoAttendanceReviewWorkspace,
} from '../data/demoAttendanceReview'
import type {
  AttendanceCorrectionDecisionPayload,
  AttendanceReviewWorkspaceData,
} from '../types'

const queryScope = 'attendance-review-workspace'
const emptyPermissions: string[] = []

export function useAttendanceReviewWorkspace(windowDate: string) {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? emptyPermissions
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, AttendanceReviewDemoState>>({})
  const demoState = demoStates[demoStateKey] ?? buildDemoAttendanceReviewState(snapshot)

  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const canReview = snapshot ? permissions.includes('attendance.approve') : access.mode === 'demo'
  const liveQueryBase = [queryScope, access.apiBaseUrl, access.token, windowDate] as const

  const operationalReviewQuery = useQuery({
    queryKey: [...liveQueryBase, 'operational-review'],
    queryFn: () => fetchAttendanceOperationalReview(access.apiBaseUrl, access.token, windowDate),
    enabled: liveEnabled && canReview,
  })

  const pendingExceptionsQuery = useQuery({
    queryKey: [...liveQueryBase, 'pending-exceptions'],
    queryFn: () => fetchAttendancePendingExceptions(access.apiBaseUrl, access.token, windowDate),
    enabled: liveEnabled && canReview,
  })

  const correctionsQuery = useQuery({
    queryKey: [queryScope, access.apiBaseUrl, access.token, 'corrections'],
    queryFn: () => fetchAttendanceCorrections(access.apiBaseUrl, access.token, { perPage: 50 }),
    enabled: liveEnabled && canReview,
  })

  const invalidateWorkspace = async () => {
    await queryClient.invalidateQueries({ queryKey: [queryScope, access.apiBaseUrl, access.token] })
  }

  const decisionMutation = useMutation({
    mutationFn: ({
      correctionId,
      payload,
    }: {
      correctionId: number
      payload: AttendanceCorrectionDecisionPayload
    }) =>
      updateAttendanceCorrectionDecision(
        access.apiBaseUrl,
        access.token,
        correctionId,
        payload,
      ),
    onSuccess: invalidateWorkspace,
  })

  const data = useMemo(() => {
    if (source === 'demo') {
      return deriveDemoAttendanceReviewWorkspace(windowDate, demoState)
    }

    return {
      scope:
        snapshot && permissions.includes('attendance.approve') && !permissions.includes('attendance.edit')
          ? 'team'
          : 'tenant',
      windowDate,
      operationalReview:
        operationalReviewQuery.data ??
        ({
          window_date: windowDate,
          summary: {
            total_records: 0,
            present_count: 0,
            absent_count: 0,
            half_day_count: 0,
            incomplete_count: 0,
            holiday_count: 0,
            weekend_count: 0,
            late_count: 0,
            pending_correction_count: 0,
            checked_in_count: 0,
            checked_out_count: 0,
          },
          items: [],
        } satisfies AttendanceReviewWorkspaceData['operationalReview']),
      pendingExceptions:
        pendingExceptionsQuery.data ??
        ({
          window_date: windowDate,
          summary: {
            exception_record_count: 0,
            late_record_count: 0,
            absent_record_count: 0,
            half_day_record_count: 0,
            incomplete_record_count: 0,
            pending_correction_record_count: 0,
            pending_correction_request_count: 0,
          },
          attendance_items: [],
          correction_items: [],
        } satisfies AttendanceReviewWorkspaceData['pendingExceptions']),
      corrections:
        correctionsQuery.data ??
        ({
          items: [],
          meta: {
            page: 1,
            per_page: 50,
            total: 0,
            last_page: 1,
          },
        } satisfies AttendanceReviewWorkspaceData['corrections']),
    } satisfies AttendanceReviewWorkspaceData
  }, [
    correctionsQuery.data,
    demoState,
    operationalReviewQuery.data,
    pendingExceptionsQuery.data,
    permissions,
    snapshot,
    source,
    windowDate,
  ])

  return {
    source,
    data,
    canReview,
    isLoading:
      source === 'live'
        ? operationalReviewQuery.isLoading || pendingExceptionsQuery.isLoading || correctionsQuery.isLoading
        : false,
    error:
      source === 'live'
        ? ((operationalReviewQuery.error as Error | null) ??
          (pendingExceptionsQuery.error as Error | null) ??
          (correctionsQuery.error as Error | null) ??
          null)
        : null,
    isSaving: decisionMutation.isPending,
    async decideCorrection(correctionId: number, payload: AttendanceCorrectionDecisionPayload) {
      if (source === 'demo') {
        const actor = snapshot
          ? {
              id: snapshot.user.id,
              name: snapshot.user.name,
              email: snapshot.user.email,
            }
          : null

        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: applyDemoCorrectionDecision(
            current[demoStateKey] ?? buildDemoAttendanceReviewState(snapshot),
            correctionId,
            payload.action,
            actor,
            payload.comment ?? null,
          ),
        }))

        return
      }

      await decisionMutation.mutateAsync({ correctionId, payload })
    },
  }
}
