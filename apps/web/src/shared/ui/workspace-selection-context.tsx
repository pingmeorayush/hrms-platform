import type { ReactNode } from 'react'

type WorkspaceSelectionFact = {
  label: string
  value: ReactNode
}

export function WorkspaceSelectionContext({
  eyebrow = 'Selected record',
  title,
  copy,
  facts,
  actions,
}: {
  eyebrow?: string
  title: string
  copy: string
  facts: WorkspaceSelectionFact[]
  actions?: ReactNode
}) {
  return (
    <div className="rounded-xl border border-line bg-panel-soft/70 px-4 py-4 shadow-[var(--shadow-sm)]">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div className="min-w-0 space-y-1">
          <p className="text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">{eyebrow}</p>
          <h3 className="text-base font-semibold text-foreground">{title}</h3>
          <p className="text-sm leading-6 text-muted-foreground">{copy}</p>
        </div>
        {actions ? <div className="flex flex-wrap items-center gap-2">{actions}</div> : null}
      </div>
      {facts.length ? (
        <div className="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-2 xl:grid-cols-4">
          {facts.map((fact) => (
            <div className="space-y-1" key={fact.label}>
              <span className="block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">
                {fact.label}
              </span>
              <strong className="block text-sm font-semibold text-foreground">{fact.value}</strong>
            </div>
          ))}
        </div>
      ) : null}
    </div>
  )
}
