import { cva } from 'class-variance-authority'

export const badgeVariants = cva(
  'ui-badge inline-flex items-center rounded-full border px-2.5 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.12em] transition-colors',
  {
    variants: {
      variant: {
        subtle: 'ui-badge--subtle border-line bg-transparent text-text-subtle',
        neutral: 'ui-badge--neutral border-line bg-panel-soft text-muted-foreground',
        info: 'ui-badge--info border-[color-mix(in_srgb,var(--primary)_24%,white)] bg-[color-mix(in_srgb,var(--primary)_10%,white)] text-primary',
        success:
          'ui-badge--success border-[color-mix(in_srgb,var(--success)_24%,white)] bg-[color-mix(in_srgb,var(--success)_10%,white)] text-success',
        warning:
          'ui-badge--warning border-[color-mix(in_srgb,var(--warning)_30%,white)] bg-[color-mix(in_srgb,var(--warning)_10%,white)] text-[color:var(--warning)]',
        danger:
          'ui-badge--danger border-[color-mix(in_srgb,var(--danger)_24%,white)] bg-[color-mix(in_srgb,var(--danger)_10%,white)] text-destructive',
      },
    },
    defaultVariants: {
      variant: 'neutral',
    },
  },
)
