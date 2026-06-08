import { createContext } from 'react'

type ToastVariant = 'success' | 'error' | 'warning' | 'info'

export interface ToastItem {
  id: number
  title: string
  description?: string
  variant: ToastVariant
  durationMs: number
}

export interface ToastOptions {
  title: string
  description?: string
  variant?: ToastVariant
  durationMs?: number
}

export interface ToastApi {
  push: (options: ToastOptions) => void
  success: (title: string, description?: string) => void
  error: (title: string, description?: string) => void
  warning: (title: string, description?: string) => void
  info: (title: string, description?: string) => void
}

const defaultToastApi: ToastApi = {
  push: () => undefined,
  success: () => undefined,
  error: () => undefined,
  warning: () => undefined,
  info: () => undefined,
}

export const ToastContext = createContext<ToastApi>(defaultToastApi)
