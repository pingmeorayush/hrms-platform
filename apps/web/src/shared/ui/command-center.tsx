import type { ComponentPropsWithoutRef, PropsWithChildren, ReactNode } from 'react'
import { Star } from 'lucide-react'
import { Link } from 'react-router-dom'
import { Button } from './button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './card'
import { cn } from './cn'

type MetricTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'
type MetricValueSize = 'stat' | 'compact' | 'long'

const metricCardToneClasses: Record<MetricTone, string> = {
  neutral: 'before:bg-line-strong/65 after:bg-slate-400/[0.08]',
  info: 'before:bg-primary/80 after:bg-primary/[0.10]',
  success: 'before:bg-success/80 after:bg-success/[0.10]',
  warning: 'before:bg-warning/85 after:bg-warning/[0.12]',
  danger: 'before:bg-danger/80 after:bg-danger/[0.10]',
}

const metricIconToneClasses: Record<MetricTone, string> = {
  neutral: 'border-line/80 bg-white/78 text-muted-foreground',
  info: 'border-primary/16 bg-primary/[0.08] text-primary',
  success:
    'border-[color-mix(in_srgb,var(--success)_16%,white)] bg-[color-mix(in_srgb,var(--success)_10%,white)] text-[color:var(--success)]',
  warning:
    'border-[color-mix(in_srgb,var(--warning)_18%,white)] bg-[color-mix(in_srgb,var(--warning)_10%,white)] text-[color:var(--warning)]',
  danger: 'border-destructive/16 bg-destructive/[0.08] text-destructive',
}

const attentionItemToneClasses: Record<MetricTone, string> = {
  neutral: 'border-line/80 bg-white/82 hover:bg-white',
  info: 'border-primary/16 bg-primary/[0.045] hover:bg-primary/[0.07]',
  success:
    'border-[color-mix(in_srgb,var(--success)_18%,white)] bg-[color-mix(in_srgb,var(--success)_7%,white)] hover:bg-[color-mix(in_srgb,var(--success)_10%,white)]',
  warning:
    'border-[color-mix(in_srgb,var(--warning)_20%,white)] bg-[color-mix(in_srgb,var(--warning)_8%,white)] hover:bg-[color-mix(in_srgb,var(--warning)_11%,white)]',
  danger: 'border-destructive/18 bg-destructive/[0.05] hover:bg-destructive/[0.08]',
}

const metricValueSizeClasses: Record<MetricValueSize, string> = {
  stat: 'text-[1.58rem] leading-[0.96] tracking-[-0.028em] md:text-[1.72rem]',
  compact: 'text-[1.22rem] leading-[1.04] tracking-[-0.018em] md:text-[1.32rem]',
  long: 'text-[1.02rem] leading-[1.14] tracking-[-0.012em] md:text-[1.12rem]',
}

export function CommandCenterMetricGrid({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn('grid gap-2.5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6', className)}
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
  valueSize = 'stat',
  className,
}: {
  label: ReactNode
  value: ReactNode
  delta?: ReactNode
  caption?: ReactNode
  icon?: ReactNode
  tone?: MetricTone
  valueSize?: MetricValueSize
  className?: string
}) {
  return (
    <Card
      className={cn(
        'relative isolate overflow-hidden rounded-[1.08rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.99)_0%,rgba(247,250,255,0.985)_100%)] shadow-[0_16px_34px_rgba(15,23,42,0.06)] before:absolute before:bottom-4 before:left-0 before:top-4 before:w-[3px] before:rounded-r-full after:absolute after:right-[-1.25rem] after:top-[-1.5rem] after:h-20 after:w-20 after:rounded-full after:blur-2xl',
        metricCardToneClasses[tone],
        className,
      )}
    >
      <CardContent className="relative flex items-start justify-between gap-3 p-3.5 md:p-4">
        <div className="min-w-0 space-y-1.5">
          <p className="ui-type-page-eyebrow text-text-subtle">{label}</p>
          <div className="space-y-0.5">
            <strong
              className={cn(
                'block font-semibold text-foreground',
                metricValueSizeClasses[valueSize],
              )}
            >
              {value}
            </strong>
            {delta ? <p className="text-[0.78rem] leading-[1.4] text-muted-foreground">{delta}</p> : null}
            {caption ? <p className="text-[0.72rem] leading-[1.3] text-text-subtle">{caption}</p> : null}
          </div>
        </div>
        {icon ? (
          <span
            className={cn(
              'grid h-10 w-10 shrink-0 place-items-center rounded-[1rem] border shadow-[inset_0_1px_0_rgba(255,255,255,0.74)]',
              metricIconToneClasses[tone],
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
  return <div className={cn('grid gap-3 xl:grid-cols-[minmax(0,1fr)_19rem]', className)} {...props} />
}

export function CommandCenterMain({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('min-w-0 space-y-3', className)} {...props} />
}

export function CommandCenterRail({
  className,
  ...props
}: ComponentPropsWithoutRef<'aside'>) {
  return <aside className={cn('space-y-3', className)} {...props} />
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
    <Card
      className={cn(
        'relative overflow-hidden rounded-[1.1rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.99)_0%,rgba(246,249,253,0.99)_100%)] shadow-[0_16px_34px_rgba(15,23,42,0.055)] before:absolute before:inset-x-6 before:top-0 before:h-px before:bg-[linear-gradient(90deg,transparent,rgba(124,174,255,0.34),rgba(184,92,16,0.22),transparent)]',
        className,
      )}
    >
      <CardHeader className="relative flex flex-row items-start justify-between gap-2 border-b border-line/80 bg-[linear-gradient(180deg,rgba(252,253,255,0.98)_0%,rgba(244,248,252,0.98)_100%)] px-4 py-3 shadow-[inset_0_-1px_0_rgba(255,255,255,0.58)]">
        <div className="min-w-0 space-y-1">
          <CardTitle>{title}</CardTitle>
          {description ? <CardDescription>{description}</CardDescription> : null}
        </div>
        {actions ? <div className="ml-auto flex shrink-0 items-center gap-2 self-start">{actions}</div> : null}
      </CardHeader>
      <CardContent className="relative p-0">{children}</CardContent>
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
      className={cn(
        'border-[color-mix(in_srgb,var(--warning)_16%,white)] bg-[linear-gradient(180deg,rgba(255,253,248,0.98)_0%,rgba(255,248,239,0.98)_100%)]',
        className,
      )}
    >
      <div className="grid gap-2.5 p-2.5 xl:grid-cols-2 2xl:grid-cols-4">
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
    <div
      className={cn(
        'flex items-start gap-3 rounded-[1rem] border px-3.5 py-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.82),0_10px_18px_rgba(15,23,42,0.03)] transition-all hover:-translate-y-px hover:shadow-[inset_0_1px_0_rgba(255,255,255,0.86),0_14px_22px_rgba(15,23,42,0.055)]',
        attentionItemToneClasses[tone],
        className,
      )}
    >
      {icon ? (
        <span
          className={cn(
            'mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-[0.95rem] border shadow-[inset_0_1px_0_rgba(255,255,255,0.76)]',
            metricIconToneClasses[tone],
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
        <div className="ml-auto flex shrink-0 items-center gap-1 pl-1.5">
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
  return <div className={cn('space-y-2 p-2.5', className)} {...props} />
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
    <div
      className={cn(
        'flex items-start gap-3 rounded-[1rem] border border-line/75 bg-white/78 px-3.5 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.78)] transition-all hover:border-line hover:bg-white',
        className,
      )}
    >
      {icon ? (
        <span
          className={cn(
            'mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-xl border shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]',
            metricIconToneClasses[tone],
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
        <div className="ml-auto flex shrink-0 items-center gap-1 pl-1.5">
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
  return <div className={cn('grid gap-3 xl:grid-cols-3', className)} {...props} />
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
        'relative overflow-hidden rounded-[1.08rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.99)_0%,rgba(247,250,255,0.985)_100%)] shadow-[0_14px_28px_rgba(15,23,42,0.05)] before:absolute before:inset-x-0 before:top-0 before:h-[2px] before:bg-[linear-gradient(90deg,rgba(9,114,211,0.36),rgba(184,92,16,0.24),transparent)]',
        className,
      )}
    >
      <CardHeader className="space-y-1 border-b border-line/80 bg-[linear-gradient(180deg,rgba(252,253,255,0.98)_0%,rgba(245,248,252,0.98)_100%)] px-3.5 py-3">
        <CardTitle>{title}</CardTitle>
        {description ? <CardDescription>{description}</CardDescription> : null}
      </CardHeader>
      <CardContent className="space-y-1.5 p-3.5">{children}</CardContent>
    </Card>
  )
}
