import { Search } from 'lucide-react'
import { useEffect, useRef } from 'react'
import type { ComponentPropsWithoutRef, ReactNode } from 'react'
import { cn } from './cn'
import { Input } from './input'

export function ConsoleToolbar({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'space-y-2.5 rounded-xl border border-line/80 bg-card px-3.5 py-3.5 shadow-[var(--shadow-sm)]',
        className,
      )}
      {...props}
    />
  )
}

export function ConsoleToolbarRow({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return (
    <div
      className={cn(
        'flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between',
        className,
      )}
      {...props}
    />
  )
}

export function ConsoleSearchField({
  className,
  inputClassName,
  ...props
}: Omit<ComponentPropsWithoutRef<typeof Input>, 'type'> & {
  inputClassName?: string
}) {
  return (
    <div className={cn('relative w-full max-w-2xl', className)}>
      <Search
        className="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
        aria-hidden="true"
      />
      <Input
        type="search"
        className={cn('h-10 rounded-lg bg-panel pl-8.5', inputClassName)}
        {...props}
      />
    </div>
  )
}

export function ConsoleMetricRow({
  className,
  ...props
}: ComponentPropsWithoutRef<'div'>) {
  return <div className={cn('flex flex-wrap items-center gap-1.5', className)} {...props} />
}

export function ConsoleMetricChip({
  label,
  value,
  tone = 'neutral',
  className,
}: {
  label: string
  value: ReactNode
  tone?: 'neutral' | 'info' | 'success' | 'warning'
  className?: string
}) {
  const toneClassName = {
    neutral: 'border-line bg-panel-soft text-muted-foreground',
    info: 'border-primary/20 bg-primary/[0.08] text-primary',
    success: 'border-emerald-500/20 bg-emerald-500/[0.08] text-emerald-700 dark:text-emerald-300',
    warning: 'border-amber-500/20 bg-amber-500/[0.09] text-amber-700 dark:text-amber-300',
  }[tone]

  return (
    <span
      className={cn(
        'ui-console-chip inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1.25 font-medium',
        toneClassName,
        className,
      )}
    >
      <span className="ui-console-chip__label">{label}</span>
      <strong className="ui-console-chip__value text-foreground">{value}</strong>
    </span>
  )
}

export function ConsoleBulkBar({
  className,
  summary,
  actions,
}: {
  className?: string
  summary: ReactNode
  actions?: ReactNode
}) {
  return (
    <div
      className={cn(
        'sticky bottom-3 z-10 mt-3 flex flex-col gap-2.5 rounded-xl border border-slate-950/90 bg-slate-950 px-3.5 py-2.5 text-slate-100 shadow-[0_18px_40px_rgba(15,23,42,0.28)] xl:flex-row xl:items-center xl:justify-between',
        className,
      )}
    >
      <div className="flex items-center gap-2.5">{summary}</div>
      {actions ? <div className="flex flex-wrap items-center gap-1.5">{actions}</div> : null}
    </div>
  )
}

export function TableSelectionCheckbox({
  checked,
  indeterminate = false,
  onChange,
  ariaLabel,
}: {
  checked: boolean
  indeterminate?: boolean
  onChange: (checked: boolean) => void
  ariaLabel: string
}) {
  const inputRef = useRef<HTMLInputElement | null>(null)

  useEffect(() => {
    if (inputRef.current) {
      inputRef.current.indeterminate = indeterminate
    }
  }, [indeterminate])

  return (
    <input
      ref={inputRef}
      type="checkbox"
      checked={checked}
      onChange={(event) => onChange(event.target.checked)}
      aria-label={ariaLabel}
      className="h-4 w-4 rounded-[4px] border border-line-strong bg-card accent-[color:var(--primary)] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-ring/40"
    />
  )
}
