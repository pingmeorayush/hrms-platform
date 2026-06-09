import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { acknowledgeSelfServicePolicy, downloadSelfServiceDocument, fetchSelfServiceWorkspace } from '../api/selfServiceApi'
import { buildDemoSelfServiceWorkspace } from '../data/demoSelfServiceWorkspace'
import type { SelfServiceDocumentRecord, SelfServiceWorkspaceData } from '../types'

const queryScope = 'self-service-workspace'

function cloneWorkspaceData(data: SelfServiceWorkspaceData | null) {
  return data ? structuredClone(data) : null
}

export function useSelfServiceWorkspace() {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const queryClient = useQueryClient()
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = String(snapshot?.user.id ?? 'anonymous')
  const [demoStates, setDemoStates] = useState<Record<string, SelfServiceWorkspaceData | null>>({})
  const [pendingAcknowledgementId, setPendingAcknowledgementId] = useState<number | null>(null)
  const [pendingDownloadId, setPendingDownloadId] = useState<number | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoSelfServiceWorkspace(snapshot ?? null)
  const queryKey = useMemo(() => [queryScope, access.apiBaseUrl, access.token] as const, [access.apiBaseUrl, access.token])

  const liveQuery = useQuery({
    queryKey,
    queryFn: () => fetchSelfServiceWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  const acknowledgeMutation = useMutation({
    mutationFn: async (document: SelfServiceDocumentRecord) => {
      if (document.source_type !== 'policy_acknowledgement' || !document.acknowledge_url) {
        throw new Error('This document is not awaiting acknowledgement.')
      }

      setActionError(null)
      setLastActionMessage(null)
      setPendingAcknowledgementId(document.source_id)

      if (source === 'demo') {
        const nextData = acknowledgeDemoPolicy(cloneWorkspaceData(demoStates[demoStateKey] ?? demoData), document.source_id)

        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextData,
        }))

        return {
          message: 'Policy acknowledgement recorded successfully.',
        }
      }

      await acknowledgeSelfServicePolicy(
        access.apiBaseUrl,
        access.token,
        document.source_id,
        'Acknowledged from the self-service workspace.',
      )

      return {
        message: 'Policy acknowledgement recorded successfully.',
      }
    },
    onSuccess: async (result) => {
      if (source === 'live') {
        await queryClient.invalidateQueries({ queryKey })
      }

      setLastActionMessage(result.message)
    },
    onError: (error) => {
      setActionError(resolveActionError(error, 'The policy acknowledgement could not be recorded.'))
    },
    onSettled: () => {
      setPendingAcknowledgementId(null)
    },
  })

  async function handleDownload(document: SelfServiceDocumentRecord) {
    if (!document.download_url) {
      return
    }

    setActionError(null)
    setLastActionMessage(null)
    setPendingDownloadId(document.source_id)

    try {
      if (source === 'demo') {
        setLastActionMessage(`Demo download ready for ${document.file_name ?? document.title}.`)
        return
      }

      await downloadSelfServiceDocument(
        access.apiBaseUrl,
        access.token,
        document.download_url,
        document.file_name ?? `${document.title}.pdf`,
      )

      setLastActionMessage(`${document.title} downloaded successfully.`)
    } catch (error) {
      setActionError(resolveActionError(error, 'The document download failed.'))
    } finally {
      setPendingDownloadId(null)
    }
  }

  return {
    source,
    data,
    employee: data?.employee ?? null,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? (liveQuery.error as Error | null) ?? null : null,
    hasLinkedProfile: data !== null,
    lastActionMessage,
    actionError,
    pendingAcknowledgementId,
    pendingDownloadId,
    acknowledgeDocument: (document: SelfServiceDocumentRecord) => acknowledgeMutation.mutateAsync(document),
    downloadDocument: handleDownload,
  }
}

function acknowledgeDemoPolicy(
  data: SelfServiceWorkspaceData | null,
  policyAcknowledgementId: number,
) {
  if (!data) {
    return data
  }

  const nextItems = data.documents.items.map((item) => {
    if (item.source_type !== 'policy_acknowledgement' || item.source_id !== policyAcknowledgementId) {
      return item
    }

    return {
      ...item,
      status: 'acknowledged' as const,
      action_required: false,
      acknowledge_url: null,
      updated_at: new Date().toISOString(),
    }
  })

  return {
    ...data,
    documents: {
      ...data.documents,
      summary: {
        ...data.documents.summary,
        pending_acknowledgement_count: nextItems.filter((item) => item.status === 'assigned').length,
        acknowledged_count: nextItems.filter((item) => item.status === 'acknowledged').length,
      },
      items: nextItems,
    },
  }
}

function resolveActionError(error: unknown, fallbackMessage: string) {
  if (error instanceof ApiRequestError) {
    return error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return fallbackMessage
}
