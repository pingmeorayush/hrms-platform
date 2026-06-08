import {
  type PropsWithChildren,
  useCallback,
  useMemo,
  useState,
} from 'react'
import { AlertTriangle, ShieldAlert } from 'lucide-react'
import { Button } from './button'
import {
  ConfirmContext,
  type ConfirmHandler,
  type ConfirmOptions,
} from './confirm-context'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from './alert-dialog'

export function ConfirmProvider({ children }: PropsWithChildren) {
  const [pending, setPending] = useState<(ConfirmOptions & { resolve: (value: boolean) => void }) | null>(null)

  const confirm = useCallback<ConfirmHandler>((options) => {
    return new Promise((resolve) => {
      setPending({ ...options, resolve })
    })
  }, [])

  const value = useMemo(() => confirm, [confirm])

  function close(result: boolean) {
    pending?.resolve(result)
    setPending(null)
  }

  return (
    <ConfirmContext.Provider value={value}>
      {children}
      <AlertDialog open={Boolean(pending)} onOpenChange={(nextOpen) => (nextOpen ? undefined : close(false))}>
        <AlertDialogContent size="sm">
          <AlertDialogHeader>
            <AlertDialogTitle>{pending?.title ?? 'Confirm action'}</AlertDialogTitle>
            <AlertDialogDescription>{pending?.description}</AlertDialogDescription>
          </AlertDialogHeader>
          {pending?.tone === 'warning' ? (
            <div className="ui-confirm-note ui-confirm-note--warning">
              <AlertTriangle className="h-4 w-4" />
              <span>Review this change before continuing.</span>
            </div>
          ) : null}
          {pending?.tone === 'danger' ? (
            <div className="ui-confirm-note ui-confirm-note--danger">
              <ShieldAlert className="h-4 w-4" />
              <span>This action changes operational records and should be verified.</span>
            </div>
          ) : null}
          <AlertDialogFooter>
            <AlertDialogCancel asChild>
              <Button variant="secondary" onClick={() => close(false)}>
                {pending?.cancelLabel ?? 'Cancel'}
              </Button>
            </AlertDialogCancel>
            <AlertDialogAction asChild>
              <Button
                variant={pending?.tone === 'danger' ? 'danger' : 'primary'}
                onClick={() => close(true)}
              >
                {pending?.confirmLabel ?? 'Confirm'}
              </Button>
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </ConfirmContext.Provider>
  )
}
