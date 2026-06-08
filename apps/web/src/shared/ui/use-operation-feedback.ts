import { useCallback } from 'react'
import { useConfirm } from './use-confirm'
import { useToast } from './use-toast'

type OperationTone = 'default' | 'warning' | 'danger'

interface ConfirmedActionOptions {
  title: string
  description: string
  confirmLabel?: string
  cancelLabel?: string
  tone?: OperationTone
  successTitle: string
  successDescription?: string
  errorTitle?: string
  action: () => Promise<unknown>
}

export function useOperationFeedback() {
  const confirm = useConfirm()
  const toast = useToast()

  const runConfirmedAction = useCallback(
    async ({
      title,
      description,
      confirmLabel,
      cancelLabel,
      tone,
      successTitle,
      successDescription,
      errorTitle,
      action,
    }: ConfirmedActionOptions) => {
      const approved = await confirm({
        title,
        description,
        confirmLabel,
        cancelLabel,
        tone,
      })

      if (!approved) {
        toast.warning('Action cancelled', 'No changes were applied.')
        return
      }

      try {
        await action()
        toast.success(successTitle, successDescription)
      } catch (caughtError) {
        toast.error(errorTitle ?? 'Action failed', extractErrorMessage(caughtError))
        throw caughtError
      }
    },
    [confirm, toast],
  )

  return {
    confirm,
    toast,
    runConfirmedAction,
  }
}

export function extractErrorMessage(caughtError: unknown) {
  if (caughtError instanceof Error && caughtError.message) {
    return caughtError.message
  }

  return 'An unexpected error occurred.'
}
