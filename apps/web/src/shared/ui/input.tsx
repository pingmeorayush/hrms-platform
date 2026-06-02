import type { ComponentPropsWithoutRef } from 'react'
import { cn } from './cn'

export function Input({
  className,
  ...props
}: ComponentPropsWithoutRef<'input'>) {
  return <input className={cn('ui-input', className)} {...props} />
}
