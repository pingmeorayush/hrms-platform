import type { ComponentPropsWithoutRef, PropsWithChildren } from 'react'
import { cn } from './cn'

export function Card({
  className,
  ...props
}: ComponentPropsWithoutRef<'section'>) {
  return <section className={cn('ui-card', className)} {...props} />
}

export function CardHeader({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__header', className)} {...props} />
}

export function CardContent({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__content', className)} {...props} />
}

export function CardTitle({
  className,
  ...props
}: ComponentPropsWithoutRef<'h2'>) {
  return <h2 className={cn('ui-card__title', className)} {...props} />
}

export function CardDescription({
  className,
  ...props
}: ComponentPropsWithoutRef<'p'>) {
  return <p className={cn('ui-card__description', className)} {...props} />
}

export function CardFooter({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('ui-card__footer', className)} {...props} />
}

export function CardGrid({ children }: PropsWithChildren) {
  return <div className="ui-card-grid">{children}</div>
}
