import type { ComponentPropsWithoutRef } from 'react'
import { cn } from './cn'

export function Textarea({
  className,
  ...props
}: ComponentPropsWithoutRef<'textarea'>) {
  return <textarea className={cn('ui-textarea', className)} {...props} />
}
