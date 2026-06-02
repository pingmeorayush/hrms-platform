import type { ComponentPropsWithoutRef } from 'react'
import { cn } from './cn'

type BadgeVariant = 'neutral' | 'info' | 'success' | 'warning'

interface BadgeProps extends ComponentPropsWithoutRef<'span'> {
  variant?: BadgeVariant
}

export function Badge({ className, variant = 'neutral', ...props }: BadgeProps) {
  return <span className={cn('ui-badge', `ui-badge--${variant}`, className)} {...props} />
}
