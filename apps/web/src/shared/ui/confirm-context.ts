import { createContext } from 'react'

export type ConfirmTone = 'default' | 'warning' | 'danger'

export interface ConfirmOptions {
  title: string
  description: string
  confirmLabel?: string
  cancelLabel?: string
  tone?: ConfirmTone
}

export type ConfirmHandler = (options: ConfirmOptions) => Promise<boolean>

export const ConfirmContext = createContext<ConfirmHandler>(async () => true)
