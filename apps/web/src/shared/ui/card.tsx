import type { ComponentPropsWithoutRef, PropsWithChildren } from 'react'
import { cn } from './cn'

export function Card({
  className,
  ...props
}: ComponentPropsWithoutRef<'section'>) {
  return (
    <section
      className={cn(
        'ui-card rounded-xl border border-line bg-card text-card-foreground shadow-[var(--shadow-sm)]',
        className,
      )}
      {...props}
    />
  )
}

export function CardHeader({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__header flex flex-col gap-1.5 p-4', className)} {...props} />
}

export function CardContent({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__content p-4 pt-0', className)} {...props} />
}

export function CardTitle({
  className,
  ...props
}: ComponentPropsWithoutRef<'h2'>) {
  return <h2 className={cn('ui-card__title ui-type-card-title', className)} {...props} />
}

export function CardDescription({
  className,
  ...props
}: ComponentPropsWithoutRef<'p'>) {
  return <p className={cn('ui-card__description ui-type-body text-muted-foreground', className)} {...props} />
}

export function CardFooter({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__footer flex items-center gap-2.5 p-4 pt-0', className)} {...props} />
}

export function CardGrid({ children, className }: PropsWithChildren<{ className?: string }>) {
  return <div className={cn('ui-card-grid grid gap-3.5', className)}>{children}</div>
}
