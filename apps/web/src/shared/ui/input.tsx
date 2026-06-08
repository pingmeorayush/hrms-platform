import type { ComponentPropsWithoutRef } from 'react'
import { cn } from './cn'

export function Input({
  className,
  ...props
}: ComponentPropsWithoutRef<'input'>) {
  return (
    <input
      className={cn(
        'ui-input flex h-10 w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground shadow-none outline-none transition-colors placeholder:text-muted-foreground focus-visible:ring-4 focus-visible:ring-ring/40 disabled:cursor-not-allowed disabled:opacity-60',
        className,
      )}
      {...props}
    />
  )
}
