import type { FormEvent } from 'react'
import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { SelectField } from '../../../shared/ui/select-field'
import {
  formatRegionalCurrency,
  formatRegionalDate,
  formatRegionalDateTime,
  formatRegionalNumber,
} from '../../../shared/regionalization/formatters'
import { useRegionalization } from '../../../shared/regionalization/context'
import { useRegionalPreferences } from '../../../shared/regionalization/useRegionalPreferences'
import type {
  RegionalPreferenceOverrides,
  RegionalTimeFormat,
} from '../../../shared/regionalization/types'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
} from '../../../shared/ui/workspace'
import { useSelfServiceRouteWorkspace } from './useSelfServiceRouteWorkspace'

const REGIONAL_SOURCE_LABELS = {
  tenant: 'Company default',
  user: 'Personal override',
} as const

export function SelfServiceProfilePage() {
  const workspace = useSelfServiceRouteWorkspace()
  const { configuration } = useRegionalization()
  const { savePreferences, isSaving } = useRegionalPreferences()
  const [isRegionalPreferencesOpen, setIsRegionalPreferencesOpen] = useState(false)
  const [regionalPreferenceMessage, setRegionalPreferenceMessage] = useState<string | null>(null)
  const [regionalPreferenceError, setRegionalPreferenceError] = useState<string | null>(null)
  const [regionalFieldErrors, setRegionalFieldErrors] = useState<Record<string, string[]>>({})

  if (workspace.isLoading) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Loading self-service profile"
          copy="We are resolving the linked employee profile and the self-service access contract."
        />
      </WorkspacePage>
    )
  }

  if (workspace.error) {
    return (
      <WorkspacePage>
        <WorkspaceEmptyState
          title="Unable to load the self-service profile"
          copy={workspace.error.message}
        />
      </WorkspacePage>
    )
  }

  const employee = workspace.employee
  const hasLinkedProfile = Boolean(workspace.data && workspace.employee)
  const profile = workspace.data?.profile ?? null
  const contacts = profile?.contacts ?? []
  const addresses = profile?.addresses ?? []
  const emergencyContacts = profile?.emergency_contacts ?? []
  const sensitivePanels = profile?.sensitive_panels ?? {
    bank_accounts: {
      visible: false,
      message: null,
    },
  }
  const effectiveSettings = configuration.effective_settings
  const tenantDefaults = configuration.tenant_defaults
  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="min-w-0 space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight text-foreground">My profile</h1>
            <p className="max-w-3xl text-sm text-muted-foreground">
              Review the linked employee record, employment context, and approved self-service profile details.
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo self-service profile' : 'Live self-service profile'}
            </Badge>
            <Button size="sm" variant="secondary" onClick={() => setIsRegionalPreferencesOpen(true)}>
              Regional preferences
            </Button>
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          {regionalPreferenceError ? <p className="workspace-error">{regionalPreferenceError}</p> : null}
          {regionalPreferenceMessage ? <p className="workspace-success">{regionalPreferenceMessage}</p> : null}

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Regional preferences</h2>
                <p className="text-sm text-muted-foreground">
                  Tune how dates, time, and money render for your session without changing company-wide defaults.
                </p>
              </div>
            </WorkspaceHeader>
            <WorkspaceContent className="grid gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
              <div className="space-y-1.5">
                <WorkspaceSummaryRow
                  label="Locale"
                  value={<PreferenceValue value={effectiveSettings.locale} source={REGIONAL_SOURCE_LABELS[effectiveSettings.source.locale]} />}
                />
                <WorkspaceSummaryRow
                  label="Language"
                  value={<PreferenceValue value={effectiveSettings.language} source={REGIONAL_SOURCE_LABELS[effectiveSettings.source.language]} />}
                />
                <WorkspaceSummaryRow
                  label="Timezone"
                  value={<PreferenceValue value={effectiveSettings.timezone} source={REGIONAL_SOURCE_LABELS[effectiveSettings.source.timezone]} />}
                />
                <WorkspaceSummaryRow
                  label="Currency"
                  value={<PreferenceValue value={effectiveSettings.currency} source={REGIONAL_SOURCE_LABELS[effectiveSettings.source.currency]} />}
                />
                <WorkspaceSummaryRow
                  label="Clock format"
                  value={
                    <PreferenceValue
                      value={effectiveSettings.time_format === '12h' ? '12-hour clock' : '24-hour clock'}
                      source={REGIONAL_SOURCE_LABELS[effectiveSettings.source.time_format]}
                    />
                  }
                />
                <WorkspaceSummaryRow
                  label="Tenant baseline"
                  value={`${tenantDefaults.country_code} · ${tenantDefaults.locale} · ${tenantDefaults.timezone}`}
                />
              </div>
              <div className="rounded-xl border border-line/80 bg-panel-soft/60 px-4 py-3">
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                  Formatting preview
                </p>
                <div className="mt-3 space-y-2 text-sm text-foreground">
                  <div className="flex items-center justify-between gap-3">
                    <span className="text-muted-foreground">Date and time</span>
                    <strong>{formatRegionalDateTime(new Date())}</strong>
                  </div>
                  <div className="flex items-center justify-between gap-3">
                    <span className="text-muted-foreground">Currency</span>
                    <strong>{formatRegionalCurrency(123456.78)}</strong>
                  </div>
                  <div className="flex items-center justify-between gap-3">
                    <span className="text-muted-foreground">Number</span>
                    <strong>{formatRegionalNumber(1234567.89)}</strong>
                  </div>
                </div>
                <WorkspaceActionsRow className="mt-4">
                  <Button size="sm" variant="secondary" onClick={() => setIsRegionalPreferencesOpen(true)}>
                    Edit regional preferences
                  </Button>
                </WorkspaceActionsRow>
              </div>
            </WorkspaceContent>
          </WorkspaceSurface>

          {hasLinkedProfile && employee && workspace.data ? (
            <>
              <div className="organization-metric-grid">
                <MetricCard label="Profile" value={employee.full_name} caption={employee.employee_code} />
                <MetricCard label="Employment status" value={employee.employment_status.replace(/_/g, ' ')} caption={employee.employment_type.replace(/_/g, ' ')} />
                <MetricCard label="Pending acknowledgements" value={String(workspace.data.documents.summary.pending_acknowledgement_count)} caption="Policy items that still need action" />
                <MetricCard label="Assigned assets" value={String(workspace.data.assets.summary.active_count)} caption="Visible devices and issued equipment" />
              </div>

              <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <WorkspaceSurface>
                  <WorkspaceContent>
                    <WorkspaceSummaryRow label="Work email" value={employee.email} />
                    <WorkspaceSummaryRow label="Phone" value={employee.phone ?? 'Not recorded'} />
                    <WorkspaceSummaryRow label="Date of joining" value={formatDate(employee.date_of_joining)} />
                    <WorkspaceSummaryRow label="Department" value={employee.department.name} />
                    <WorkspaceSummaryRow label="Designation" value={employee.designation.name} />
                    <WorkspaceSummaryRow label="Manager" value={employee.manager?.full_name ?? 'Not assigned'} />
                    <WorkspaceSummaryRow label="Location" value={employee.location?.name ?? 'Not assigned'} />
                    <WorkspaceSummaryRow label="Cost center" value={employee.cost_center?.name ?? 'Not assigned'} />
                  </WorkspaceContent>
                </WorkspaceSurface>

                <WorkspaceSurface>
                  <WorkspaceContent>
                    <WorkspaceSummaryRow label="Primary contact" value={contacts.find((contact) => contact.is_primary)?.value ?? 'Not recorded'} />
                    <WorkspaceSummaryRow label="Address types" value={String(addresses.length)} />
                    <WorkspaceSummaryRow label="Emergency contacts" value={String(emergencyContacts.length)} />
                    <WorkspaceSummaryRow label="Last profile sync" value={formatDateTime(employee.updated_at)} />
                    <WorkspaceSummaryRow
                      label="Banking panel"
                      value={sensitivePanels.bank_accounts.visible ? 'Visible' : 'Hidden'}
                    />
                    <WorkspaceSummaryRow
                      label="Hidden sensitive docs"
                      value={String(workspace.data.documents.summary.hidden_sensitive_count)}
                    />
                  </WorkspaceContent>
                </WorkspaceSurface>
              </div>

              <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="space-y-1">
                      <h2 className="text-lg font-semibold text-foreground">Contacts</h2>
                      <p className="text-sm text-muted-foreground">Primary work and mobile channels approved for self-service visibility.</p>
                    </div>
                  </WorkspaceHeader>
                  <WorkspaceContent>
                    {contacts.length ? (
                      contacts.map((contact) => (
                        <WorkspaceSummaryRow
                          key={contact.id}
                          label={`${contact.label ?? contact.type}${contact.is_primary ? ' · primary' : ''}`}
                          value={contact.value}
                        />
                      ))
                    ) : (
                      <WorkspaceEmptyState
                        title="No contact channels are visible"
                        copy="Contacts appear here once the linked employee profile has approved self-service contact records."
                      />
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>

                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="space-y-1">
                      <h2 className="text-lg font-semibold text-foreground">Addresses</h2>
                      <p className="text-sm text-muted-foreground">Only approved address records are available in this self-service view.</p>
                    </div>
                  </WorkspaceHeader>
                  <WorkspaceContent>
                    {addresses.length ? (
                      addresses.map((address) => (
                        <WorkspaceSummaryRow
                          key={address.id}
                          label={address.type.charAt(0).toUpperCase() + address.type.slice(1)}
                          value={[address.address_line_1, address.address_line_2, address.city, address.state, address.country, address.postal_code].filter(Boolean).join(', ')}
                        />
                      ))
                    ) : (
                      <WorkspaceEmptyState
                        title="No approved addresses are visible"
                        copy="Address records show up here once the linked employee profile has approved self-service address visibility."
                      />
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>
              </div>

              <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="space-y-1">
                      <h2 className="text-lg font-semibold text-foreground">Emergency contacts</h2>
                      <p className="text-sm text-muted-foreground">Emergency contact data is read only in the current Sprint 6 scope.</p>
                    </div>
                  </WorkspaceHeader>
                  <WorkspaceContent>
                    {emergencyContacts.length ? (
                      emergencyContacts.map((contact) => (
                        <WorkspaceSummaryRow
                          key={contact.id}
                          label={`${contact.name} · ${contact.relationship}`}
                          value={`${contact.phone_number}${contact.email ? ` · ${contact.email}` : ''}`}
                        />
                      ))
                    ) : (
                      <WorkspaceEmptyState
                        title="No emergency contacts are visible"
                        copy="Emergency contacts appear here once the linked employee profile has approved them for self-service review."
                      />
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>

                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="space-y-1">
                      <h2 className="text-lg font-semibold text-foreground">Sensitive panels</h2>
                      <p className="text-sm text-muted-foreground">Restricted panels stay hidden or masked until the current session has the required permission.</p>
                    </div>
                  </WorkspaceHeader>
                  <WorkspaceContent>
                    <WorkspaceSummaryRow
                      label="Banking details"
                      value={sensitivePanels.bank_accounts.visible ? 'Visible in this session' : 'Sensitive banking is hidden'}
                    />
                    {!sensitivePanels.bank_accounts.visible && sensitivePanels.bank_accounts.message ? (
                      <p className="text-sm text-muted-foreground">{sensitivePanels.bank_accounts.message}</p>
                    ) : null}
                  </WorkspaceContent>
                </WorkspaceSurface>
              </div>
            </>
          ) : (
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceEmptyState
                  title="No linked employee profile"
                  copy="This session does not resolve to an employee profile yet, but you can still manage personal regional preferences for your web experience."
                />
              </WorkspaceContent>
            </WorkspaceSurface>
          )}
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isRegionalPreferencesOpen}
        title="Regional preferences"
        description="Override company defaults for your own website session when you need a different locale, timezone, currency, or clock format."
        onClose={() => setIsRegionalPreferencesOpen(false)}
      >
        <RegionalPreferencesEditor
          configuration={configuration}
          isSaving={isSaving}
          fieldErrors={regionalFieldErrors}
          onSave={async (overrides) => {
            setRegionalPreferenceError(null)
            setRegionalPreferenceMessage(null)
            setRegionalFieldErrors({})

            try {
              await savePreferences(overrides)
              setRegionalPreferenceMessage('Regional preferences updated successfully.')
              setIsRegionalPreferencesOpen(false)
            } catch (error) {
              const nextError = error as Error & { fieldErrors?: Record<string, string[]> }
              setRegionalPreferenceError(nextError.message)
              setRegionalFieldErrors(nextError.fieldErrors ?? {})
            }
          }}
        />
      </Modal>
    </WorkspacePage>
  )
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <div className="metric-card">
      <span className="metric-card__label">{label}</span>
      <strong className="metric-card__value">{value}</strong>
      <p className="metric-card__caption">{caption}</p>
    </div>
  )
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

function formatDateTime(value: string | null) {
  return formatRegionalDateTime(value, 'Not available')
}

function PreferenceValue({ value, source }: { value: string; source: string }) {
  return (
    <span className="inline-flex flex-wrap items-center justify-end gap-2">
      <span>{value}</span>
      <Badge variant={source === 'Personal override' ? 'warning' : 'neutral'}>{source}</Badge>
    </span>
  )
}

interface RegionalPreferencesEditorProps {
  configuration: ReturnType<typeof useRegionalization>['configuration']
  isSaving: boolean
  fieldErrors: Record<string, string[]>
  onSave: (overrides: RegionalPreferenceOverrides) => Promise<unknown>
}

interface RegionalPreferencesFormState {
  locale: string
  language: string
  timezone: string
  currency: string
  time_format: '' | RegionalTimeFormat
}

function RegionalPreferencesEditor({
  configuration,
  isSaving,
  fieldErrors,
  onSave,
}: RegionalPreferencesEditorProps) {
  const [values, setValues] = useState<RegionalPreferencesFormState>(() =>
    buildRegionalPreferenceFormState(configuration),
  )
  const previewSettings = useMemo(
    () => resolvePreviewSettings(values, configuration.tenant_defaults),
    [configuration.tenant_defaults, values],
  )

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()

    await onSave({
      locale: values.locale || null,
      language: values.language || null,
      timezone: values.timezone.trim() || null,
      currency: values.currency || null,
      time_format: values.time_format || null,
    })
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      <div className="workspace-form-grid">
        <SelectField
          label="Locale"
          value={values.locale}
          options={[
            { value: '', label: `Use company default (${configuration.tenant_defaults.locale})` },
            ...configuration.supported.locales.map((locale) => ({ value: locale.code, label: locale.label })),
          ]}
          disabled={isSaving}
          error={fieldErrors.locale?.[0]}
          onChange={(locale) => setValues((current) => ({ ...current, locale }))}
        />
        <SelectField
          label="Language"
          value={values.language}
          options={[
            { value: '', label: `Use company default (${configuration.tenant_defaults.language})` },
            ...configuration.supported.languages.map((language) => ({
              value: language.code,
              label: language.label,
            })),
          ]}
          disabled={isSaving}
          error={fieldErrors.language?.[0]}
          onChange={(language) => setValues((current) => ({ ...current, language }))}
        />
        <WorkspaceField label="Timezone" error={fieldErrors.timezone?.[0]}>
          <Input
            value={values.timezone}
            placeholder={configuration.tenant_defaults.timezone}
            disabled={isSaving}
            onChange={(event) => setValues((current) => ({ ...current, timezone: event.target.value }))}
          />
        </WorkspaceField>
        <SelectField
          label="Currency"
          value={values.currency}
          options={[
            { value: '', label: `Use company default (${configuration.tenant_defaults.currency})` },
            ...configuration.supported.currencies.map((currency) => ({
              value: currency.code,
              label: currency.label,
            })),
          ]}
          disabled={isSaving}
          error={fieldErrors.currency?.[0]}
          onChange={(currency) => setValues((current) => ({ ...current, currency }))}
        />
        <SelectField
          label="Clock format"
          value={values.time_format}
          options={[
            {
              value: '',
              label: `Use company default (${configuration.tenant_defaults.time_format === '12h' ? '12-hour' : '24-hour'})`,
            },
            ...configuration.supported.time_formats.map((timeFormat) => ({
              value: timeFormat.code,
              label: timeFormat.label,
            })),
          ]}
          disabled={isSaving}
          error={fieldErrors.time_format?.[0]}
          onChange={(time_format) =>
            setValues((current) => ({ ...current, time_format: time_format as RegionalPreferencesFormState['time_format'] }))
          }
        />
      </div>

      <div className="rounded-xl border border-line/80 bg-panel-soft/60 px-4 py-3">
        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Preview</p>
        <div className="mt-3 grid gap-2 text-sm text-foreground">
          <PreviewRow label="Date and time" value={formatPreviewDateTime(previewSettings)} />
          <PreviewRow label="Currency" value={formatPreviewCurrency(previewSettings)} />
          <PreviewRow label="Number" value={formatPreviewNumber(previewSettings)} />
        </div>
      </div>

      <WorkspaceActionsRow>
        <Button
          type="button"
          variant="secondary"
          disabled={isSaving}
          onClick={() =>
            setValues({
              locale: '',
              language: '',
              timezone: '',
              currency: '',
              time_format: '',
            })
          }
        >
          Reset form
        </Button>
        <Button type="submit" variant="primary" disabled={isSaving}>
          {isSaving ? 'Saving...' : 'Save preferences'}
        </Button>
      </WorkspaceActionsRow>
    </form>
  )
}

function buildRegionalPreferenceFormState(
  configuration: ReturnType<typeof useRegionalization>['configuration'],
): RegionalPreferencesFormState {
  const { effective_settings: effectiveSettings } = configuration

  return {
    locale: effectiveSettings.source.locale === 'user' ? effectiveSettings.locale : '',
    language: effectiveSettings.source.language === 'user' ? effectiveSettings.language : '',
    timezone: effectiveSettings.source.timezone === 'user' ? effectiveSettings.timezone : '',
    currency: effectiveSettings.source.currency === 'user' ? effectiveSettings.currency : '',
    time_format: effectiveSettings.source.time_format === 'user' ? effectiveSettings.time_format : '',
  }
}

function resolvePreviewSettings(
  values: RegionalPreferencesFormState,
  tenantDefaults: ReturnType<typeof useRegionalization>['configuration']['tenant_defaults'],
) {
  const requestedTimezone = values.timezone.trim()

  return {
    locale: values.locale || tenantDefaults.locale,
    language: values.language || tenantDefaults.language,
    timezone:
      requestedTimezone && isValidPreviewTimeZone(requestedTimezone)
        ? requestedTimezone
        : tenantDefaults.timezone,
    currency: values.currency || tenantDefaults.currency,
    time_format: values.time_format || tenantDefaults.time_format,
  }
}

function PreviewRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-center justify-between gap-3">
      <span className="text-muted-foreground">{label}</span>
      <strong>{value}</strong>
    </div>
  )
}

function formatPreviewDateTime(settings: {
  locale: string
  timezone: string
  time_format: RegionalTimeFormat
}) {
  return new Intl.DateTimeFormat(settings.locale, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: settings.time_format === '12h',
    timeZone: settings.timezone,
  }).format(new Date('2026-06-30T18:45:00Z'))
}

function formatPreviewCurrency(settings: { locale: string; currency: string }) {
  return new Intl.NumberFormat(settings.locale, {
    style: 'currency',
    currency: settings.currency,
    maximumFractionDigits: 2,
  }).format(123456.78)
}

function formatPreviewNumber(settings: { locale: string }) {
  return new Intl.NumberFormat(settings.locale, {
    maximumFractionDigits: 2,
  }).format(1234567.89)
}

function isValidPreviewTimeZone(timeZone: string) {
  try {
    new Intl.DateTimeFormat('en-US', {
      hour: 'numeric',
      timeZone,
    }).format(new Date('2026-06-30T18:45:00Z'))

    return true
  } catch {
    return false
  }
}
