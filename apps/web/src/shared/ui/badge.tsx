import type { VariantProps } from 'class-variance-authority'
import type { ComponentPropsWithoutRef } from 'react'
import { cn } from './cn'
import { badgeVariants } from './badge.variants'

interface BadgeProps extends ComponentPropsWithoutRef<'span'>, VariantProps<typeof badgeVariants> {}

export type BadgeVariant = NonNullable<BadgeProps['variant']>

export function Badge({ className, variant = 'neutral', ...props }: BadgeProps) {
  return <span className={cn(badgeVariants({ variant }), className)} {...props} />
}
