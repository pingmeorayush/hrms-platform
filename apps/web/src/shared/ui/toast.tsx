import {
  type PropsWithChildren,
  useCallback,
  useMemo,
} from 'react'
import { toast as sonnerToast } from 'sonner'
import { Toaster } from './sonner'
import { ToastContext, type ToastApi, type ToastOptions } from './toast-context'

export function ToastProvider({ children }: PropsWithChildren) {
  const push = useCallback((options: ToastOptions) => {
    const toastOptions = {
      description: options.description,
      duration: options.durationMs ?? 3400,
    }

    switch (options.variant ?? 'info') {
      case 'success':
        sonnerToast.success(options.title, toastOptions)
        break
      case 'error':
        sonnerToast.error(options.title, toastOptions)
        break
      case 'warning':
        sonnerToast.warning(options.title, toastOptions)
        break
      case 'info':
      default:
        sonnerToast.info(options.title, toastOptions)
        break
    }
  }, [])

  const api = useMemo<ToastApi>(
    () => ({
      push,
      success: (title, description) => push({ title, description, variant: 'success' }),
      error: (title, description) => push({ title, description, variant: 'error' }),
      warning: (title, description) => push({ title, description, variant: 'warning' }),
      info: (title, description) => push({ title, description, variant: 'info' }),
    }),
    [push],
  )

  return (
    <ToastContext.Provider value={api}>
      {children}
      <Toaster />
    </ToastContext.Provider>
  )
}
