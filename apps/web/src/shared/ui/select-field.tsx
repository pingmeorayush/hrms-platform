import { cn } from '@/lib/utils'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './select'
import { WorkspaceField } from './workspace'

const EMPTY_OPTION_VALUE = '__empty__'

type SelectFieldOption = [string, string] | { value: string; label: string }

function normalizeOption(option: SelectFieldOption) {
  if (Array.isArray(option)) {
    return { value: option[0], label: option[1] }
  }

  return option
}

export function SelectField({
  label,
  value,
  options,
  onChange,
  disabled = false,
  error,
  compact = false,
  placeholder,
}: {
  label: string
  value: string
  options: SelectFieldOption[]
  onChange: (value: string) => void
  disabled?: boolean
  error?: string
  compact?: boolean
  placeholder?: string
}) {
  const normalizedOptions = options.map(normalizeOption)
  const hasBlankOption = normalizedOptions.some((option) => option.value === '')
  const hasMatchingValue = normalizedOptions.some((option) => option.value === value)
  const resolvedValue =
    value === '' ? (hasBlankOption ? EMPTY_OPTION_VALUE : undefined) : (hasMatchingValue ? value : undefined)

  return (
    <WorkspaceField label={label} error={error} compact={compact}>
      <Select
        value={resolvedValue}
        disabled={disabled}
        onValueChange={(nextValue) => onChange(nextValue === EMPTY_OPTION_VALUE ? '' : nextValue)}
      >
        <SelectTrigger aria-label={label} className={cn(error && 'border-destructive focus:ring-destructive/20')}>
          <SelectValue
            placeholder={
              placeholder ??
              normalizedOptions.find((option) => option.value === '')?.label ??
              `Select ${label.toLowerCase()}`
            }
          />
        </SelectTrigger>
        <SelectContent>
          {normalizedOptions.map((option) => (
            <SelectItem
              key={`${label}-${option.value || 'blank'}`}
              value={option.value === '' ? EMPTY_OPTION_VALUE : option.value}
            >
              {option.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </WorkspaceField>
  )
}
