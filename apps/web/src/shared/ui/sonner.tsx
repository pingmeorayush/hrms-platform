import type { ComponentProps } from 'react'
import { Toaster as Sonner } from 'sonner'

type ToasterProps = ComponentProps<typeof Sonner>

export function Toaster(props: ToasterProps) {
  return (
    <Sonner
      closeButton
      position="top-right"
      richColors
      toastOptions={{
        classNames: {
          toast:
            'rounded-xl border border-line bg-panel text-foreground shadow-[var(--shadow-md)]',
          title: 'text-sm font-semibold',
          description: 'text-sm text-muted-foreground',
          closeButton:
            'border border-line bg-panel text-muted-foreground hover:bg-panel-soft hover:text-foreground',
          actionButton:
            'bg-primary text-primary-foreground hover:bg-[color-mix(in_srgb,var(--primary)_92%,black)]',
          cancelButton:
            'border border-line bg-panel-soft text-foreground hover:bg-panel-tint',
        },
      }}
      {...props}
    />
  )
}
