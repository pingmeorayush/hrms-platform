import type { ComponentPropsWithoutRef, PropsWithChildren, ReactNode } from 'react'
import { Star } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Button } from './button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './card'
import { cn } from './cn'

type MetricTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'

const metricToneClasses: Record<MetricTone, string> = {
  neutral: 'border-line/80 bg-white/78 text-muted-foreground',
  info: 'border-primary/16 bg-primary/[0.08] text-primary',
  success: 'border-emerald-500/16 bg-emerald-500/[0.08] text-emerald-700 dark:text-emerald-300',
  warning: 'border-amber-500/18 bg-amber-500/[0.09] text-amber-700 dark:text-amber-300',
  danger: 'border-destructive/16 bg-destructive/[0.08] text-destructive',
}

export function CommandCenterMetricGrid({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn('grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6', className)}
      {...props}
    />
  )
}

export function CommandCenterMetricCard({
  label,
  value,
  delta,
  caption,
  icon,
  tone = 'neutral',
  className,
}: {
  label: ReactNode
  value: ReactNode
  delta?: ReactNode
  caption?: ReactNode
  icon?: ReactNode
  tone?: MetricTone
  className?: string
}) {
  return (
    <Card
      className={cn(
        'relative overflow-hidden rounded-[1.2rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(248,251,255,0.985)_100%)] shadow-[0_14px_30px_rgba(15,23,42,0.06)]',
        className,
      )}
    >
      <CardContent className="flex items-start justify-between gap-4 p-4">
        <div className="min-w-0 space-y-2">
          <p className="ui-type-label text-text-subtle">{label}</p>
          <div className="space-y-1">
            <strong className="ui-type-page-title block text-[2rem] leading-none text-foreground">{value}</strong>
            {delta ? <p className="ui-type-caption text-muted-foreground">{delta}</p> : null}
            {caption ? <p className="ui-type-caption text-text-subtle">{caption}</p> : null}
          </div>
        </div>
        {icon ? (
          <span
            className={cn(
              'grid h-11 w-11 shrink-0 place-items-center rounded-2xl border shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]',
              metricToneClasses[tone],
            )}
          >
            {icon}
          </span>
        ) : null}
      </CardContent>
    </Card>
  )
}

export function CommandCenterLayout({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('grid gap-4 xl:grid-cols-[minmax(0,1fr)_19rem]', className)} {...props} />
}

export function CommandCenterMain({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('min-w-0 space-y-4', className)} {...props} />
}

export function CommandCenterRail({
  className,
  ...props
}: ComponentPropsWithoutRef<'aside'>) {
  return <aside className={cn('space-y-4', className)} {...props} />
}

export function CommandCenterPanel({
  title,
  description,
  actions,
  className,
  children,
}: PropsWithChildren<{
  title: ReactNode
  description?: ReactNode
  actions?: ReactNode
  className?: string
}>) {
  return (
    <Card className={cn('rounded-[1.2rem] border-line/80 shadow-[0_14px_30px_rgba(15,23,42,0.05)]', className)}>
      <CardHeader className="flex flex-row items-start justify-between gap-3 border-b border-line/80 bg-[linear-gradient(180deg,rgba(252,253,255,0.98)_0%,rgba(245,248,252,0.98)_100%)]">
        <div className="min-w-0 space-y-1">
          <CardTitle>{title}</CardTitle>
          {description ? <CardDescription>{description}</CardDescription> : null}
        </div>
        {actions ? <div className="ml-auto flex shrink-0 items-center gap-2 self-start">{actions}</div> : null}
      </CardHeader>
      <CardContent className="p-0">{children}</CardContent>
    </Card>
  )
}

export function CommandCenterAttentionStrip({
  title,
  action,
  className,
  children,
}: PropsWithChildren<{
  title?: ReactNode
  action?: ReactNode
  className?: string
}>) {
  return (
    <CommandCenterPanel
      title={title ?? 'Needs attention'}
      actions={action}
      className={className}
    >
      <div className="divide-y divide-line/80 xl:divide-y-0 xl:divide-x xl:grid xl:grid-cols-2 2xl:grid-cols-4">
        {children}
      </div>
    </CommandCenterPanel>
  )
}

export function CommandCenterAttentionItem({
  title,
  detail,
  meta,
  icon,
  tone = 'neutral',
  to,
  pinned = false,
  onTogglePinned,
  pinLabel,
  actions,
  className,
}: {
  title: ReactNode
  detail?: ReactNode
  meta?: ReactNode
  icon?: ReactNode
  tone?: MetricTone
  to?: string
  pinned?: boolean
  onTogglePinned?: () => void
  pinLabel?: string
  actions?: ReactNode
  className?: string
}) {
  const content = (
    <div className="min-w-0 flex-1 space-y-1">
      <p className="ui-type-body-strong text-foreground">{title}</p>
      {detail ? <p className="ui-type-body text-muted-foreground">{detail}</p> : null}
      {meta ? <p className="ui-type-caption text-text-subtle">{meta}</p> : null}
    </div>
  )

  return (
    <div className={cn('flex items-start gap-3 px-4 py-4', className)}>
      {icon ? (
        <span
          className={cn(
            'mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-2xl border shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]',
            metricToneClasses[tone],
          )}
        >
          {icon}
        </span>
      ) : null}
      {to ? (
        <Link
          to={to}
          className="min-w-0 flex-1 rounded-xl px-1 py-0.5 transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ring/35"
        >
          {content}
        </Link>
      ) : (
        content
      )}
      {actions || onTogglePinned ? (
        <div className="ml-auto flex shrink-0 items-center gap-1 pl-2">
          {actions}
          {onTogglePinned ? (
            <Button
              type="button"
              variant="ghost"
              size="sm"
              className="h-8 w-8 rounded-full p-0"
              onClick={onTogglePinned}
              aria-label={pinLabel ?? (pinned ? 'Unpin attention item' : 'Pin attention item')}
            >
              <Star
                className={cn('h-4 w-4', pinned ? 'fill-amber-400 text-amber-500' : 'text-muted-foreground')}
              />
            </Button>
          ) : null}
        </div>
      ) : null}
    </div>
  )
}

export function CommandCenterActivityList({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('divide-y divide-line/80', className)} {...props} />
}

export function CommandCenterActivityItem({
  title,
  detail,
  meta,
  icon,
  tone = 'neutral',
  to,
  pinned = false,
  onTogglePinned,
  pinLabel,
  actions,
  className,
}: {
  title: ReactNode
  detail?: ReactNode
  meta?: ReactNode
  icon?: ReactNode
  tone?: MetricTone
  to?: string
  pinned?: boolean
  onTogglePinned?: () => void
  pinLabel?: string
  actions?: ReactNode
  className?: string
}) {
  const content = (
    <div className="min-w-0 flex-1 space-y-0.5">
      <p className="ui-type-body-strong text-foreground">{title}</p>
      {detail ? <p className="ui-type-body text-muted-foreground">{detail}</p> : null}
      {meta ? <p className="ui-type-caption text-text-subtle">{meta}</p> : null}
    </div>
  )

  return (
    <div className={cn('flex items-start gap-3 px-4 py-4', className)}>
      {icon ? (
        <span
          className={cn(
            'mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-2xl border shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]',
            metricToneClasses[tone],
          )}
        >
          {icon}
        </span>
      ) : null}
      {to ? (
        <Link
          to={to}
          className="min-w-0 flex-1 rounded-xl px-1 py-0.5 transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ring/35"
        >
          {content}
        </Link>
      ) : (
        content
      )}
      {actions || onTogglePinned ? (
        <div className="ml-auto flex shrink-0 items-center gap-1 pl-2">
          {actions}
          {onTogglePinned ? (
            <Button
              type="button"
              variant="ghost"
              size="sm"
              className="h-8 w-8 rounded-full p-0"
              onClick={onTogglePinned}
              aria-label={pinLabel ?? (pinned ? 'Unpin activity' : 'Pin activity')}
            >
              <Star
                className={cn('h-4 w-4', pinned ? 'fill-amber-400 text-amber-500' : 'text-muted-foreground')}
              />
            </Button>
          ) : null}
        </div>
      ) : null}
    </div>
  )
}

export function CommandCenterInsightGrid({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('grid gap-4 xl:grid-cols-3', className)} {...props} />
}

export function CommandCenterInsightCard({
  title,
  description,
  className,
  children,
}: PropsWithChildren<{
  title: ReactNode
  description?: ReactNode
  className?: string
}>) {
  return (
    <Card
      className={cn(
        'rounded-[1.2rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(248,251,255,0.985)_100%)] shadow-[0_14px_30px_rgba(15,23,42,0.05)]',
        className,
      )}
    >
      <CardHeader className="space-y-1 border-b border-line/80 bg-[linear-gradient(180deg,rgba(252,253,255,0.98)_0%,rgba(245,248,252,0.98)_100%)]">
        <CardTitle>{title}</CardTitle>
        {description ? <CardDescription>{description}</CardDescription> : null}
      </CardHeader>
      <CardContent className="space-y-2.5 p-4">{children}</CardContent>
    </Card>
  )
}
