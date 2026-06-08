import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  createAttendanceCheckIn,
  createAttendanceCheckOut,
  createAttendanceCorrection,
  fetchAttendanceCorrections,
  fetchAttendanceHistory,
  fetchAttendancePolicy,
  fetchTodayAttendance,
} from '../api/attendanceEmployeeApi'
import {
  buildDemoAttendanceEmployeeWorkspace,
  filterDemoAttendanceHistory,
} from '../data/demoAttendanceEmployee'
import type {
  AttendanceCaptureMetadata,
  AttendanceCaptureSnapshot,
  AttendanceCheckInPayload,
  AttendanceCheckOutPayload,
  AttendanceCorrection,
  AttendanceCorrectionCreatePayload,
  AttendanceCorrectionValueSnapshot,
  AttendanceEmployeeWorkspaceData,
  AttendanceHistoryFilters,
  AttendanceRecord,
  AttendanceUserReference,
  PaginatedAttendanceCorrections,
  PaginatedAttendanceRecords,
} from '../types'

const queryScope = 'attendance-employee-workspace'

export function useAttendanceEmployeeWorkspace(filters: AttendanceHistoryFilters) {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoState, setDemoState] = useState<{
    key: string
    data: AttendanceEmployeeWorkspaceData
  }>(() => ({
    key: demoStateKey,
    data: buildDemoAttendanceEmployeeWorkspace(snapshot),
  }))

  if (demoState.key !== demoStateKey) {
    setDemoState({
      key: demoStateKey,
      data: buildDemoAttendanceEmployeeWorkspace(snapshot),
    })
  }

  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const today = useMemo(() => formatDateForApi(new Date()), [])
  const queryKey = useMemo(
    () =>
      [
        queryScope,
        access.apiBaseUrl,
        access.token,
        filters.dateFrom,
        filters.dateTo,
        filters.primaryStatus,
        filters.state,
        filters.perPage,
      ] as const,
    [
      access.apiBaseUrl,
      access.token,
      filters.dateFrom,
      filters.dateTo,
      filters.primaryStatus,
      filters.state,
      filters.perPage,
    ],
  )

  const historyQuery = useQuery({
    queryKey,
    queryFn: () => fetchAttendanceHistory(access.apiBaseUrl, access.token, filters),
    enabled: liveEnabled,
  })

  const todayQuery = useQuery({
    queryKey: [queryScope, 'today', access.apiBaseUrl, access.token, today],
    queryFn: () => fetchTodayAttendance(access.apiBaseUrl, access.token, today),
    enabled: liveEnabled,
  })

  const correctionsQuery = useQuery({
    queryKey: [queryScope, 'corrections', access.apiBaseUrl, access.token],
    queryFn: () => fetchAttendanceCorrections(access.apiBaseUrl, access.token, { perPage: 20 }),
    enabled: liveEnabled,
  })

  const policyQuery = useQuery({
    queryKey: [queryScope, 'policy', access.apiBaseUrl, access.token],
    queryFn: async () => {
      try {
        return await fetchAttendancePolicy(access.apiBaseUrl, access.token)
      } catch {
        return null
      }
    },
    enabled: liveEnabled,
  })

  const invalidateWorkspace = async () => {
    await queryClient.invalidateQueries({
      queryKey: [queryScope, access.apiBaseUrl, access.token],
    })
    await queryClient.invalidateQueries({
      queryKey: [queryScope, 'today', access.apiBaseUrl, access.token],
    })
    await queryClient.invalidateQueries({
      queryKey: [queryScope, 'corrections', access.apiBaseUrl, access.token],
    })
  }

  const checkInMutation = useMutation({
    mutationFn: (payload: AttendanceCheckInPayload) =>
      createAttendanceCheckIn(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const checkOutMutation = useMutation({
    mutationFn: (payload: AttendanceCheckOutPayload) =>
      createAttendanceCheckOut(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const correctionMutation = useMutation({
    mutationFn: (payload: AttendanceCorrectionCreatePayload) =>
      createAttendanceCorrection(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const canCapture = snapshot ? permissions.includes('attendance.create') : access.mode === 'demo'
  const canRequestCorrection = snapshot
    ? permissions.includes('attendance.correct')
    : access.mode === 'demo'
  const canReviewCorrections = snapshot
    ? permissions.includes('attendance.approve')
    : access.mode === 'demo'

  const demoData = useMemo(() => {
    const baseData = demoState.data

    return {
      ...baseData,
      history: filterDemoAttendanceHistory(baseData.history, filters),
    }
  }, [demoState.data, filters])

  const liveData = useMemo(() => {
    const history = historyQuery.data ?? emptyAttendanceHistory(filters.perPage)
    const corrections = correctionsQuery.data ?? emptyCorrections()
    const todayRecord = todayQuery.data ?? null
    const currentEmployee =
      todayRecord?.employee ??
      history.items[0]?.employee ??
      (snapshot
        ? {
            id: snapshot.user.id,
            employee_code: 'SELF',
            full_name: snapshot.user.name,
            email: snapshot.user.email,
          }
        : null)

    return {
      currentEmployee,
      policy: policyQuery.data ?? null,
      todayRecord,
      history,
      corrections,
    } satisfies AttendanceEmployeeWorkspaceData
  }, [correctionsQuery.data, filters.perPage, historyQuery.data, policyQuery.data, snapshot, todayQuery.data])

  return {
    source,
    snapshot,
    data: source === 'demo' ? demoData : liveData,
    canCapture,
    canRequestCorrection,
    canReviewCorrections,
    isLoading:
      source === 'live'
        ? historyQuery.isLoading || todayQuery.isLoading || correctionsQuery.isLoading
        : false,
    error:
      source === 'live'
        ? ((historyQuery.error as Error | null) ??
          (todayQuery.error as Error | null) ??
          (correctionsQuery.error as Error | null) ??
          null)
        : null,
    isSaving:
      checkInMutation.isPending ||
      checkOutMutation.isPending ||
      correctionMutation.isPending,
    async checkIn(payload: AttendanceCheckInPayload) {
      if (source === 'demo') {
        const record = demoState.data.todayRecord

        if (!record || !demoState.data.currentEmployee) {
          throw new Error('Today’s attendance record is unavailable.')
        }

        if (record.state !== 'not_captured') {
          throw new ApiRequestError(
            'You already have an active attendance capture for today.',
            422,
          )
        }

        const capturedAt = payload.captured_at ?? `${record.attendance_date}T09:41:00+05:30`
        const lateMinutes = calculateLateMinutes(record.calculation.scheduled_start_at, capturedAt)
        const nextRecord: AttendanceRecord = {
          ...record,
          state: 'checked_in',
          calculation: {
            ...record.calculation,
            primary_status: 'incomplete',
            is_late: lateMinutes > 0,
            late_minutes: lateMinutes,
            calculated_at: capturedAt,
          },
          check_in: buildCaptureSnapshot(capturedAt, payload),
          updated_at: capturedAt,
        }

        updateDemoHistory(nextRecord)

        return nextRecord
      }

      return checkInMutation.mutateAsync(payload)
    },
    async checkOut(payload: AttendanceCheckOutPayload) {
      if (source === 'demo') {
        const record = demoState.data.todayRecord
        const policy = demoState.data.policy

        if (!record || !policy) {
          throw new Error('Today’s attendance policy context is unavailable.')
        }

        if (record.state !== 'checked_in' || !record.check_in.at) {
          throw new ApiRequestError(
            'Check-in must be recorded before check-out can be completed.',
            422,
          )
        }

        const checkOutAt = payload.captured_at ?? `${record.attendance_date}T18:44:00+05:30`
        const workedMinutes = calculateWorkedMinutes(
          record.check_in.at,
          checkOutAt,
          record.calculation.break_duration_minutes,
        )
        const scheduledWorkMinutes = record.calculation.scheduled_work_minutes ?? policy.half_day_minutes
        const primaryStatus = workedMinutes < policy.half_day_minutes ? 'half_day' : 'present'
        const overtimeThreshold = policy.overtime_eligible
          ? policy.overtime_after_minutes ?? scheduledWorkMinutes
          : Number.POSITIVE_INFINITY
        const overtimeMinutes = workedMinutes > overtimeThreshold ? workedMinutes - overtimeThreshold : 0
        const earlyDepartureMinutes = calculateEarlyDepartureMinutes(
          record.calculation.scheduled_end_at,
          checkOutAt,
        )
        const nextRecord: AttendanceRecord = {
          ...record,
          state: 'checked_out',
          worked_minutes: workedMinutes,
          calculation: {
            ...record.calculation,
            primary_status: primaryStatus,
            is_half_day: primaryStatus === 'half_day',
            overtime_minutes: overtimeMinutes,
            is_early_departure: earlyDepartureMinutes > 0,
            early_departure_minutes: earlyDepartureMinutes,
            calculated_at: checkOutAt,
          },
          check_out: buildCaptureSnapshot(checkOutAt, payload),
          updated_at: checkOutAt,
        }

        updateDemoHistory(nextRecord)

        return nextRecord
      }

      return checkOutMutation.mutateAsync(payload)
    },
    async submitCorrection(payload: AttendanceCorrectionCreatePayload) {
      if (source === 'demo') {
        if (!payload.reason.trim()) {
          throw new ApiRequestError('A correction reason is required.', 422, {
            reason: ['A correction reason is required.'],
          })
        }

        const targetRecord = demoState.data.history.items.find(
          (record) => record.id === payload.attendance_record_id,
        )

        if (!targetRecord) {
          throw new Error('The selected attendance record could not be found.')
        }

        const hasPendingCorrection = demoState.data.corrections.items.some(
          (correction) =>
            correction.attendance_record_id === targetRecord.id && correction.status === 'pending',
        )

        if (hasPendingCorrection) {
          throw new ApiRequestError(
            'A pending correction already exists for the selected attendance day.',
            422,
          )
        }

        const correctionId = Math.max(8300, ...demoState.data.corrections.items.map((item) => item.id)) + 1
        const requestedBy = snapshot
          ? ({
              id: snapshot.user.id,
              name: snapshot.user.name,
              email: snapshot.user.email,
            } satisfies AttendanceUserReference)
          : null
        const correctedValues: AttendanceCorrectionValueSnapshot = {
          ...buildCorrectionValues(targetRecord),
          check_in_at: payload.corrected.check_in_at ?? targetRecord.check_in.at,
          check_out_at: payload.corrected.check_out_at ?? targetRecord.check_out.at,
        }
        const correction: AttendanceCorrection = {
          id: correctionId,
          status: 'pending',
          reason: payload.reason.trim(),
          attendance_record_id: targetRecord.id,
          employee: targetRecord.employee,
          requested_by: requestedBy,
          latest_action_by: null,
          original_values: buildCorrectionValues(targetRecord),
          corrected_values: correctedValues,
          applied_values: null,
          decision_comment: null,
          workflow: {
            id: correctionId + 1100,
            status: 'waiting',
            current_stage_sequence: 1,
            approval_history: [],
            current_task: null,
          },
          approved_at: null,
          rejected_at: null,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }

        setDemoState((current) => {
          const nextItems = [correction, ...current.data.corrections.items]

          return {
            ...current,
            data: {
              ...current.data,
              corrections: {
                items: nextItems,
                meta: buildPaginationMeta(nextItems.length),
              },
            },
          }
        })

        return correction
      }

      return correctionMutation.mutateAsync(payload)
    },
  }

  function updateDemoHistory(nextRecord: AttendanceRecord) {
    setDemoState((current) => {
      const nextHistoryItems = current.data.history.items
        .map((record) => (record.id === nextRecord.id ? nextRecord : record))
        .sort(sortAttendanceRecords)

      return {
        ...current,
        data: {
          ...current.data,
          todayRecord:
            nextRecord.attendance_date === current.data.todayRecord?.attendance_date
              ? nextRecord
              : current.data.todayRecord,
          history: {
            items: nextHistoryItems,
            meta: buildPaginationMeta(nextHistoryItems.length),
          },
        },
      }
    })
  }
}

function emptyAttendanceHistory(perPage: number): PaginatedAttendanceRecords {
  return {
    items: [],
    meta: buildPaginationMeta(0, perPage),
  }
}

function emptyCorrections(): PaginatedAttendanceCorrections {
  return {
    items: [],
    meta: buildPaginationMeta(0, 20),
  }
}

function buildPaginationMeta(total: number, perPage = 14) {
  return {
    page: 1,
    per_page: perPage,
    total,
    last_page: Math.max(1, Math.ceil(total / Math.max(perPage, 1))),
  }
}

function buildCaptureSnapshot(
  capturedAt: string,
  payload: AttendanceCheckInPayload | AttendanceCheckOutPayload,
): AttendanceCaptureSnapshot {
  const metadata = buildCaptureMetadata(payload)

  return {
    at: capturedAt,
    channel: payload.channel ?? 'web',
    ip_address: '203.110.92.14',
    user_agent:
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36',
    metadata,
  }
}

function buildCaptureMetadata(
  payload: AttendanceCheckInPayload | AttendanceCheckOutPayload,
): AttendanceCaptureMetadata {
  return {
    device: payload.device ?? {
      device_id: 'web-console-01',
      device_name: 'Phoenix Web Console',
      platform: 'Desktop',
      browser: 'Chromium',
      app_version: 'wave-4',
    },
    geolocation: payload.geolocation ?? {
      latitude: 12.9716,
      longitude: 77.5946,
      accuracy_meters: 24,
    },
  }
}

function buildCorrectionValues(record: AttendanceRecord): AttendanceCorrectionValueSnapshot {
  return {
    attendance_date: record.attendance_date,
    check_in_at: record.check_in.at,
    check_out_at: record.check_out.at,
    check_in_channel: record.check_in.channel,
    check_out_channel: record.check_out.channel,
    worked_minutes: record.worked_minutes,
    primary_status: record.calculation.primary_status,
    shift_id: record.shift?.id ?? null,
    shift_roster_id: record.shift_roster_id,
  }
}

function calculateLateMinutes(scheduledStartAt: string | null, capturedAt: string) {
  if (!scheduledStartAt) {
    return 0
  }

  const deltaMinutes = differenceInMinutes(capturedAt, scheduledStartAt)

  return deltaMinutes > 0 ? deltaMinutes : 0
}

function calculateWorkedMinutes(
  checkInAt: string,
  checkOutAt: string,
  breakDurationMinutes: number,
) {
  const grossMinutes = differenceInMinutes(checkOutAt, checkInAt)

  return Math.max(0, grossMinutes - breakDurationMinutes)
}

function calculateEarlyDepartureMinutes(scheduledEndAt: string | null, checkOutAt: string) {
  if (!scheduledEndAt) {
    return 0
  }

  const deltaMinutes = differenceInMinutes(scheduledEndAt, checkOutAt)

  return deltaMinutes > 0 ? deltaMinutes : 0
}

function differenceInMinutes(laterAt: string, earlierAt: string) {
  return Math.round((new Date(laterAt).getTime() - new Date(earlierAt).getTime()) / 60000)
}

function sortAttendanceRecords(left: AttendanceRecord, right: AttendanceRecord) {
  if (left.attendance_date === right.attendance_date) {
    return right.id - left.id
  }

  return right.attendance_date.localeCompare(left.attendance_date)
}

function formatDateForApi(date: Date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}
