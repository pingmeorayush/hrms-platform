import type { ReactNode } from 'react'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import {
  WorkspaceEmptyState,
  WorkspaceField,
} from '../../../shared/ui/workspace'
export function MetricCard({
  label,
  value,
  caption,
}: {
  label: string
  value: string
  caption: string
}) {
  return (
    <article className="rounded-xl border border-line bg-card px-4 py-4 shadow-[var(--shadow-sm)]">
      <span className="block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</span>
      <strong className="mt-1 block text-lg font-semibold text-foreground">{value}</strong>
      <small className="mt-2 block text-sm leading-6 text-muted-foreground">{caption}</small>
    </article>
  )
}

export function SelectField({
  label,
  value,
  options,
  onChange,
  disabled = false,
  error,
}: {
  label: string
  value: string
  options: Array<[string, string]>
  onChange: (value: string) => void
  disabled?: boolean
  error?: string
}) {
  return (
    <AppSelectField
      label={label}
      value={value}
      options={options}
      onChange={onChange}
      disabled={disabled}
      error={error}
    />
  )
}

export function Field({
  label,
  children,
  error,
}: {
  label: string
  children: ReactNode
  error?: string
}) {
  return <WorkspaceField label={label} error={error}>{children}</WorkspaceField>
}

export function EmptyState({ title, copy }: { title: string; copy: string }) {
  return <WorkspaceEmptyState title={title} copy={copy} />
}

export function FormNotice({
  error,
  message,
}: {
  error: string | null
  message: string | null
}) {
  return (
    <>
      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}
    </>
  )
}

export function PermissionNotice({ copy }: { copy: string }) {
  return (
    <WorkspaceEmptyState title="Permission limited" copy={copy} />
  )
}
