import { cva } from 'class-variance-authority'
import type { ComponentPropsWithoutRef, HTMLAttributes, PropsWithChildren, ReactNode } from 'react'
import { Star } from 'lucide-react'
import { Button } from './button'
import { Card, CardContent, CardHeader } from './card'
import { cn } from './cn'

const workspaceTabVariants = cva(
  'inline-flex items-center justify-center rounded-xl border px-3 py-1.5 text-sm font-semibold transition-all outline-none focus-visible:ring-4 focus-visible:ring-ring/35',
  {
    variants: {
      active: {
        true: 'border-[#1a2432] bg-[linear-gradient(180deg,#253142_0%,#141c27_100%)] text-white shadow-[0_12px_24px_rgba(15,23,42,0.18)] hover:bg-[linear-gradient(180deg,#2a384b_0%,#182130_100%)]',
        false: 'border-line/80 bg-white/80 text-foreground shadow-[inset_0_1px_0_rgba(255,255,255,0.72)] hover:border-line-strong hover:bg-panel-tint',
      },
    },
    defaultVariants: {
      active: false,
    },
  },
)

export function WorkspacePage({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-col gap-4 lg:gap-[1.15rem]', className)} {...props} />
}

export function WorkspaceSurface({
  className,
  ...props
}: ComponentPropsWithoutRef<typeof Card>) {
  return (
    <Card
      className={cn(
        'relative isolate overflow-hidden rounded-[1.25rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(248,251,255,0.985)_100%)] shadow-[0_18px_38px_rgba(15,23,42,0.07)] before:absolute before:inset-x-5 before:top-0 before:h-px before:bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.88),transparent)]',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceHeader({
  className,
  compact = false,
  ...props
}: ComponentPropsWithoutRef<typeof CardHeader> & { compact?: boolean }) {
  return (
    <CardHeader
      className={cn(
        'relative gap-3 border-b border-line/80 bg-[linear-gradient(180deg,rgba(252,253,255,0.98)_0%,rgba(245,248,252,0.98)_100%)] px-4 py-4 shadow-[inset_0_-1px_0_rgba(255,255,255,0.55)] before:absolute before:inset-x-0 before:top-0 before:h-px before:bg-[linear-gradient(90deg,transparent,rgba(124,174,255,0.32),rgba(234,138,52,0.24),transparent)] [&>*:first-child]:min-w-0 [&>*:last-child]:shrink-0',
        compact ? 'md:flex-row md:items-start md:justify-between md:gap-4' : 'lg:flex-row lg:items-start lg:justify-between lg:gap-4',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceContent({
  className,
  ...props
}: ComponentPropsWithoutRef<typeof CardContent>) {
  return <CardContent className={cn('space-y-4 p-4 pt-4', className)} {...props} />
}

export function WorkspacePillRow({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-1.5', className)} {...props} />
}

export function WorkspaceToolbar({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'flex flex-col gap-3 rounded-[1.1rem] border border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.84)_0%,rgba(245,248,252,0.96)_100%)] p-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.86),0_10px_22px_rgba(15,23,42,0.04)]',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceToolbarRow({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn('flex flex-col gap-2.5 xl:flex-row xl:items-start xl:justify-between', className)}
      {...props}
    />
  )
}

export function WorkspaceTabs({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-2', className)} {...props} />
}

export function workspaceTabClassName(active: boolean, className?: string) {
  return cn(workspaceTabVariants({ active }), className)
}

export function WorkspaceTabButton({
  active = false,
  className,
  ...props
}: ComponentPropsWithoutRef<'button'> & { active?: boolean }) {
  return <button className={workspaceTabClassName(active, className)} {...props} />
}

export function WorkspaceToolbarStatus({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-2', className)} {...props} />
}

export function WorkspaceToolbarActions({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-2', className)} {...props} />
}

export function WorkspaceToolbarMeta({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'flex flex-1 flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceToolbarSummary({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'min-w-0 space-y-0.5 rounded-xl border border-line/70 bg-white/62 px-3 py-2.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.78)]',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceFilters({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-1 flex-wrap items-end gap-2.5', className)} {...props} />
}

export function WorkspaceField({
  label,
  error,
  compact = false,
  className,
  children,
}: PropsWithChildren<{
  label: ReactNode
  error?: string
  compact?: boolean
  className?: string
}>) {
  return (
    <label
      className={cn(
        'flex min-w-[11rem] flex-col gap-1.5',
        compact ? 'flex-1 sm:min-w-[9.5rem]' : 'w-full',
        className,
      )}
      >
      <span className="ui-workspace-field__label text-text-subtle">
        {label}
      </span>
      {children}
      {error ? <small className="ui-workspace-field__error font-medium text-destructive">{error}</small> : null}
    </label>
  )
}

export function WorkspaceEmptyState({
  title,
  copy,
  actions,
  className,
}: {
  title: string
  copy: string
  actions?: ReactNode
  className?: string
}) {
  return (
    <div
      className={cn(
        'rounded-xl border border-dashed border-line bg-panel-soft/70 px-4 py-6 text-center',
        className,
      )}
    >
      <h3 className="ui-workspace-empty__title text-foreground">{title}</h3>
      <p className="ui-workspace-empty__copy mx-auto mt-2 max-w-2xl text-muted-foreground">{copy}</p>
      {actions ? <div className="mt-4 flex justify-center gap-2">{actions}</div> : null}
    </div>
  )
}

export function WorkspaceTableShell({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'overflow-hidden rounded-[1.05rem] border border-line/80 bg-white shadow-[0_14px_30px_rgba(15,23,42,0.06)]',
        className,
      )}
      {...props}
    />
  )
}

export function WorkspaceActionsRow({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-1.5', className)} {...props} />
}

export function WorkspaceHeaderActions({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn('flex flex-wrap items-center gap-2 self-start md:ml-auto md:justify-end', className)}
      {...props}
    />
  )
}

export function WorkspacePinButton({
  pinned,
  onToggle,
  className,
  pinLabel = 'Pin workspace',
  unpinLabel = 'Pinned',
}: {
  pinned: boolean
  onToggle: () => void
  className?: string
  pinLabel?: string
  unpinLabel?: string
}) {
  return (
    <Button
      size="sm"
      variant="secondary"
      aria-pressed={pinned}
      onClick={onToggle}
      className={cn(
        'min-w-[8.4rem] justify-center shadow-[inset_0_1px_0_rgba(255,255,255,0.74)]',
        pinned &&
          'border-amber-200/80 bg-[linear-gradient(180deg,rgba(255,251,235,0.98)_0%,rgba(254,243,199,0.9)_100%)] text-amber-800 hover:border-amber-300 hover:bg-[linear-gradient(180deg,rgba(255,248,220,0.98)_0%,rgba(253,230,138,0.9)_100%)]',
        className,
      )}
    >
      <Star className={cn('h-4 w-4', pinned && 'fill-current')} />
      {pinned ? unpinLabel : pinLabel}
    </Button>
  )
}

export function WorkspaceFactsStrip({
  className,
  contentClassName,
  children,
}: PropsWithChildren<{ className?: string; contentClassName?: string }>) {
  return (
    <Card
      className={cn(
        'relative isolate overflow-hidden rounded-[1.2rem] border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(247,250,254,0.985)_100%)] shadow-[0_16px_34px_rgba(15,23,42,0.06)] before:absolute before:inset-x-4 before:top-0 before:h-px before:bg-[linear-gradient(90deg,transparent,rgba(124,174,255,0.24),rgba(234,138,52,0.18),transparent)]',
        className,
      )}
    >
      <CardContent
        className={cn(
          'grid divide-y divide-line/80 p-0 sm:grid-cols-2 sm:divide-y-0 sm:[&>*:nth-child(odd)]:border-r sm:[&>*]:border-line/80 xl:grid-cols-5 xl:[&>*]:border-r xl:[&>*:last-child]:border-r-0',
          contentClassName,
        )}
      >
        {children}
      </CardContent>
    </Card>
  )
}

export function WorkspaceFact({
  label,
  value,
  hint,
  className,
}: {
  label: ReactNode
  value: ReactNode
  hint?: ReactNode
  className?: string
}) {
  return (
    <div className={cn('space-y-0.5 px-4 py-3.5', className)}>
      <span className="ui-workspace-fact__label block text-text-subtle">
        {label}
      </span>
      <strong className="ui-workspace-fact__value block text-foreground">{value}</strong>
      {hint ? <small className="ui-workspace-fact__hint block text-muted-foreground">{hint}</small> : null}
    </div>
  )
}

export function WorkspaceSummaryRow({
  label,
  value,
  className,
}: {
  label: ReactNode
  value: ReactNode
  className?: string
}) {
  return (
    <div className={cn('flex items-start justify-between gap-3 border-b border-line-soft py-1.5 last:border-b-0', className)}>
      <span className="ui-workspace-summary__label text-muted-foreground">{label}</span>
      <strong className="ui-workspace-summary__value text-right text-foreground">{value}</strong>
    </div>
  )
}

export function WorkspaceSplit({
  className,
  ...props
}: HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('grid gap-3.5 xl:grid-cols-[minmax(0,1fr)_19rem]', className)} {...props} />
}
