import { cva } from 'class-variance-authority'

export const buttonVariants = cva(
  'ui-button inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-semibold transition-colors outline-none focus-visible:ring-4 focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-60 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        primary:
          'ui-button--primary border border-transparent bg-primary text-primary-foreground shadow-[var(--shadow-sm)] hover:bg-[color-mix(in_srgb,var(--primary)_92%,black)]',
        secondary:
          'ui-button--secondary border border-line bg-panel text-foreground hover:border-line-strong hover:bg-panel-tint',
        ghost:
          'ui-button--ghost border border-transparent bg-transparent text-muted-foreground hover:bg-panel-soft hover:text-foreground',
        segmented:
          'ui-button--segmented border border-line bg-panel text-foreground hover:border-line-strong hover:bg-panel-tint',
        danger:
          'ui-button--danger border border-transparent bg-destructive text-white hover:bg-[color-mix(in_srgb,var(--danger)_90%,black)]',
      },
      size: {
        xs: 'ui-button--xs h-7 px-2.5 text-[0.74rem]',
        sm: 'ui-button--sm h-8 px-3 text-[0.78rem]',
        md: 'ui-button--md h-10 px-4 text-[0.86rem]',
      },
    },
    defaultVariants: {
      variant: 'secondary',
      size: 'md',
    },
  },
)
