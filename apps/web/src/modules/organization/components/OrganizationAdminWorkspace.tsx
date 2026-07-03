import type { Dispatch, FormEvent, ReactNode, SetStateAction } from 'react'
import { useDeferredValue, useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import {
  formatRegionalCurrency,
  formatRegionalDate,
  formatRegionalDateTime,
} from '../../../shared/regionalization/formatters'
import { useRegionalization } from '../../../shared/regionalization/context'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../../../shared/ui/select'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceTabButton,
  WorkspaceTabs,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { ApiRequestError } from '../../../shared/api/http'
import { useOrganizationWorkspace } from '../hooks/useOrganizationWorkspace'
import type {
  CompanyProfile,
  CompanyProfileFormValues,
  LocationFormValues,
  LocationRecord,
  OrganizationCollection,
  OrganizationMasterFormValues,
  OrganizationMasterRecord,
} from '../types'

type OrganizationWorkspaceController = ReturnType<typeof useOrganizationWorkspace>

type OrganizationTab = { id: OrganizationCollection; label: string }

type OrganizationRegistrySectionProps = {
  workspace: OrganizationWorkspaceController
  title: string
  description: string
  tabs: OrganizationTab[]
  defaultTab: OrganizationCollection
}

const organizationTabs = [
  { id: 'departments', label: 'Departments' },
  { id: 'designations', label: 'Designations' },
  { id: 'locations', label: 'Locations' },
  { id: 'costCenters', label: 'Cost Centers' },
] as const satisfies OrganizationTab[]

const structureTabs = organizationTabs.filter(
  (tab) => tab.id === 'departments' || tab.id === 'designations',
)
const locationTabs = organizationTabs.filter((tab) => tab.id === 'locations')
const costCenterTabs = organizationTabs.filter((tab) => tab.id === 'costCenters')

const emptyMasterForm: OrganizationMasterFormValues = {
  code: '',
  name: '',
  description: '',
  status: 'active',
}

const emptyLocationForm: LocationFormValues = {
  code: '',
  name: '',
  timezone: '',
  currency: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  country: '',
  postal_code: '',
  status: 'active',
}

export function OrganizationAdminWorkspace() {
  const workspace = useOrganizationWorkspace()

  return <OrganizationStructureWorkspaceView workspace={workspace} />
}

export function OrganizationCompanyProfileWorkspaceView({
  workspace,
}: {
  workspace: OrganizationWorkspaceController
}) {
  const { data, isLoading, error, canManage, isSaving, saveCompanyProfile } = workspace
  const { configuration } = useRegionalization()
  const [isCompanyModalOpen, setIsCompanyModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()

  const launchCountry = data
    ? configuration.supported.countries.find((country) => country.code === data.companyProfile.country_code)
    : null
  const expansionCountries = data
    ? configuration.supported.countries.filter((country) =>
        data.companyProfile.expansion_country_codes.includes(country.code),
      )
    : []

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading company profile...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      {data ? (
        <>
          <WorkspaceSurface>
            <WorkspaceHeader>
              <div>
                <CardTitle>Company profile</CardTitle>
                <CardDescription>
                  Keep the tenant identity and inherited defaults clean, current, and easy to audit.
                </CardDescription>
              </div>
              <WorkspaceHeaderActions>
                <Button size="xs" variant="primary" onClick={() => setIsCompanyModalOpen(true)}>
                  Edit company profile
                </Button>
              </WorkspaceHeaderActions>
            </WorkspaceHeader>
            <WorkspaceContent>
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead scope="col">Field</TableHead>
                      <TableHead scope="col">Value</TableHead>
                      <TableHead scope="col">Operational use</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <ProfileRow
                      label="Company name"
                      value={data.companyProfile.name}
                      hint="Primary tenant identity across the admin console"
                    />
                    <ProfileRow
                      label="Tenant slug"
                      value={data.companyProfile.slug}
                      hint="System-safe identifier used in downstream references"
                    />
                    <ProfileRow
                      label="Subscription plan"
                      value={data.companyProfile.subscription_plan ?? 'Plan not set'}
                      hint="Controls expected commercial context"
                    />
                    <ProfileRow
                      label="Launch country"
                      value={launchCountry ? `${launchCountry.label} (${launchCountry.code})` : data.companyProfile.country_code}
                      hint="Anchor market that drives the default regional preset"
                    />
                    <ProfileRow
                      label="Locale"
                      value={data.companyProfile.locale}
                      hint="Primary regional format profile for dates, numbers, and messages"
                    />
                    <ProfileRow
                      label="Language"
                      value={data.companyProfile.language}
                      hint="Default application language for locale-aware copy and fallbacks"
                    />
                    <ProfileRow
                      label="Timezone"
                      value={data.companyProfile.timezone}
                      hint="Default timezone for attendance and scheduling behavior"
                    />
                    <ProfileRow
                      label="Currency"
                      value={data.companyProfile.currency}
                      hint="Default currency for finance-linked HR operations"
                    />
                    <ProfileRow
                      label="Time format"
                      value={data.companyProfile.time_format === '12h' ? '12-hour clock' : '24-hour clock'}
                      hint="Controls hour formatting across schedules, timecards, and reports"
                    />
                    <ProfileRow
                      label="Expansion placeholders"
                      value={
                        expansionCountries.length
                          ? expansionCountries.map((country) => country.label).join(', ')
                          : 'No expansion placeholders selected'
                      }
                      hint="Reserved markets that stay visible while future regional rollout is staged"
                    />
                    <ProfileRow
                      label="Regional preview"
                      value={`${formatRegionalDateTime(new Date())} · ${formatRegionalCurrency(123456.78, data.companyProfile.currency)}`}
                      hint="Sanity-check how shared website formatting resolves for the tenant defaults"
                    />
                    <ProfileRow
                      label="Last updated"
                      value={formatRegionalDateTime(data.companyProfile.updated_at, 'Not updated yet')}
                      hint="Most recent profile change visible to operators"
                    />
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            </WorkspaceContent>
          </WorkspaceSurface>

          <Modal
            open={isCompanyModalOpen}
            title="Edit company profile"
            description="Update the tenant profile that downstream workspaces inherit."
            size="lg"
            onClose={() => setIsCompanyModalOpen(false)}
          >
            <CompanyProfileEditor
              key={`${data.companyProfile.id}:${data.companyProfile.name}`}
              canManage={canManage}
              isSaving={isSaving}
              profile={data.companyProfile}
              onSave={(values) =>
                runConfirmedAction({
                  title: 'Save company profile?',
                  description: 'This updates shared tenant settings across the admin console.',
                  confirmLabel: 'Save profile',
                  tone: 'warning',
                  successTitle: 'Company profile updated',
                  successDescription: 'Tenant profile changes have been applied.',
                  errorTitle: 'Unable to save company profile',
                  action: async () => {
                    await saveCompanyProfile(values)
                    setIsCompanyModalOpen(false)
                  },
                })
              }
            />
          </Modal>
        </>
      ) : null}
    </WorkspacePage>
  )
}

export function OrganizationStructureWorkspaceView({
  workspace,
}: {
  workspace: OrganizationWorkspaceController
}) {
  return (
    <OrganizationRegistrySection
      workspace={workspace}
      title="Organization structure"
      description="Maintain departments and designations without mixing them with unrelated tenant setup."
      tabs={structureTabs}
      defaultTab="departments"
    />
  )
}

export function OrganizationLocationsWorkspaceView({
  workspace,
}: {
  workspace: OrganizationWorkspaceController
}) {
  return (
    <OrganizationRegistrySection
      workspace={workspace}
      title="Locations registry"
      description="Keep site, timezone, and address data on one dedicated page so scheduling and employee assignment stay clean."
      tabs={locationTabs}
      defaultTab="locations"
    />
  )
}

export function OrganizationCostCentersWorkspaceView({
  workspace,
}: {
  workspace: OrganizationWorkspaceController
}) {
  return (
    <OrganizationRegistrySection
      workspace={workspace}
      title="Cost center registry"
      description="Manage cost center records separately so finance-linked HR setup stays focused and uncluttered."
      tabs={costCenterTabs}
      defaultTab="costCenters"
    />
  )
}

function OrganizationRegistrySection({
  workspace,
  title,
  description,
  tabs,
  defaultTab,
}: OrganizationRegistrySectionProps) {
  const { data, isLoading, error, canManage, isSaving, saveLocation, saveMasterRecord } = workspace
  const [activeTab, setActiveTab] = useState<OrganizationCollection>(defaultTab)
  const [selectedRecordId, setSelectedRecordId] = useState<number | null>(null)
  const [search, setSearch] = useState('')
  const [isRecordModalOpen, setIsRecordModalOpen] = useState(false)
  const deferredSearch = useDeferredValue(search)
  const { runConfirmedAction } = useOperationFeedback()

  const activeRecords = useMemo(() => {
    if (!data) {
      return []
    }

    return data[activeTab]
  }, [activeTab, data])

  const selectedRecord = useMemo(() => {
    return activeRecords.find((item) => item.id === selectedRecordId) ?? null
  }, [activeRecords, selectedRecordId])

  const visibleRecords = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query.length) {
      return activeRecords
    }

    return activeRecords.filter((record) => {
      const searchableSummary =
        activeTab === 'locations'
          ? `${(record as LocationRecord).city ?? ''} ${(record as LocationRecord).timezone} ${(record as LocationRecord).currency}`
          : (record as OrganizationMasterRecord).description ?? ''

      return [record.name, record.code, searchableSummary].join(' ').toLowerCase().includes(query)
    })
  }, [activeRecords, activeTab, deferredSearch])

  const currentTabLabel = getTabLabel(activeTab)
  const currentRecordNoun = getSingularLabel(currentTabLabel)

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading organization records...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      {data ? (
        <>
          <WorkspaceSurface>
            <WorkspaceHeader>
              <div>
                <CardTitle>{title}</CardTitle>
                <CardDescription>{description}</CardDescription>
              </div>
              <WorkspaceHeaderActions>
                <Button
                  size="xs"
                  variant="primary"
                  onClick={() => {
                    setSelectedRecordId(null)
                    setIsRecordModalOpen(true)
                  }}
                >
                  Create {currentRecordNoun.toLowerCase()}
                </Button>
              </WorkspaceHeaderActions>
            </WorkspaceHeader>
            <WorkspaceContent>
              <WorkspaceToolbar>
                <WorkspaceToolbarRow>
                  {tabs.length > 1 ? (
                    <WorkspaceTabs role="tablist" aria-label={`${title} collections`}>
                      {tabs.map((tab) => (
                        <WorkspaceTabButton
                          key={tab.id}
                          type="button"
                          role="tab"
                          active={activeTab === tab.id}
                          aria-selected={activeTab === tab.id}
                          onClick={() => {
                            setActiveTab(tab.id)
                            setSelectedRecordId(null)
                          }}
                        >
                          {tab.label}
                        </WorkspaceTabButton>
                      ))}
                    </WorkspaceTabs>
                  ) : null}
                </WorkspaceToolbarRow>
                <WorkspaceToolbarRow>
                  <WorkspaceField label="Search records" compact className="xl:max-w-md">
                    <Input
                      value={search}
                      onChange={(event) => setSearch(event.target.value)}
                      placeholder={`Search ${currentTabLabel.toLowerCase()} by name, code, or summary`}
                    />
                  </WorkspaceField>
                </WorkspaceToolbarRow>
              </WorkspaceToolbar>

              {visibleRecords.length ? (
                <WorkspaceTableShell>
                  <Table>
                    <colgroup>
                      <col style={{ width: '22%' }} />
                      <col style={{ width: '34%' }} />
                      <col style={{ width: '12%' }} />
                      <col style={{ width: '14%' }} />
                      <col style={{ width: '18%' }} />
                    </colgroup>
                    <TableHeader>
                      <TableRow>
                        <TableHead scope="col">Record</TableHead>
                        <TableHead scope="col">Summary</TableHead>
                        <TableHead scope="col">Status</TableHead>
                        <TableHead scope="col">Updated</TableHead>
                        <TableHead scope="col">Action</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {visibleRecords.map((record) => (
                        <TableRow key={record.id}>
                          <TableHead scope="row" className="align-top">
                            <div className="ui-table-stack">
                              <strong className="ui-table-primary block">{record.name}</strong>
                              <small className="ui-table-secondary block">{record.code}</small>
                            </div>
                          </TableHead>
                          <TableCell className="align-top">
                            <p className="ui-table-body-muted">{getRecordSummary(activeTab, record)}</p>
                          </TableCell>
                          <TableCell className="align-top">
                            <Badge variant={record.status === 'active' ? 'success' : 'warning'}>
                              {record.status}
                            </Badge>
                          </TableCell>
                          <TableCell className="align-top">
                            <small className="ui-table-secondary block">
                              {formatRegionalDate(record.updated_at, 'Not updated yet')}
                            </small>
                          </TableCell>
                          <TableCell className="ui-table-action-cell align-top">
                            <Button
                              variant="primary"
                              size="sm"
                              disabled={!canManage}
                              onClick={() => {
                                setSelectedRecordId(record.id)
                                setIsRecordModalOpen(true)
                              }}
                            >
                              Edit
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>
              ) : (
                <WorkspaceEmptyState
                  title="No records match the current filter"
                  copy="Clear the search or switch collections to widen the visible records."
                />
              )}
            </WorkspaceContent>
          </WorkspaceSurface>

          <Modal
            open={isRecordModalOpen}
            title={selectedRecord ? `Edit ${selectedRecord.name}` : `Create ${currentRecordNoun}`}
            description={`Manage ${currentTabLabel.toLowerCase()} without leaving the collection view.`}
            size={activeTab === 'locations' ? 'lg' : 'md'}
            onClose={() => setIsRecordModalOpen(false)}
          >
            <RecordEditor
              key={`${activeTab}:${selectedRecordId ?? 'new'}:${data.companyProfile.timezone}:${data.companyProfile.currency}`}
              activeTab={activeTab}
              canManage={canManage}
              isSaving={isSaving}
              companyProfile={data.companyProfile}
              record={selectedRecord}
              onSaveMaster={(values) =>
                runConfirmedAction({
                  title: selectedRecord ? `Save ${selectedRecord.name}?` : `Create ${currentRecordNoun}?`,
                  description: selectedRecord
                    ? 'Review the changes to this master record before saving.'
                    : `Create this ${currentRecordNoun.toLowerCase()} record for the current tenant.`,
                  confirmLabel: selectedRecord ? 'Save changes' : 'Create record',
                  tone: selectedRecord ? 'warning' : 'default',
                  successTitle: selectedRecord ? 'Record updated' : 'Record created',
                  successDescription: `${currentRecordNoun} changes are now available in the workspace.`,
                  errorTitle: 'Unable to save record',
                  action: async () => {
                    await saveMasterRecord(
                      activeTab as Exclude<OrganizationCollection, 'locations'>,
                      values,
                      selectedRecord?.id,
                    )
                    setIsRecordModalOpen(false)
                    if (!selectedRecord) {
                      setSelectedRecordId(null)
                    }
                  },
                })
              }
              onSaveLocation={(values) =>
                runConfirmedAction({
                  title: selectedRecord ? `Save ${selectedRecord.name}?` : 'Create location?',
                  description: selectedRecord
                    ? 'Review the scoped location changes before saving.'
                    : 'Create this location so downstream modules can reuse it.',
                  confirmLabel: selectedRecord ? 'Save changes' : 'Create location',
                  tone: selectedRecord ? 'warning' : 'default',
                  successTitle: selectedRecord ? 'Location updated' : 'Location created',
                  successDescription: 'The location record is now available across the workspace.',
                  errorTitle: 'Unable to save location',
                  action: async () => {
                    await saveLocation(values, selectedRecord?.id)
                    setIsRecordModalOpen(false)
                    if (!selectedRecord) {
                      setSelectedRecordId(null)
                    }
                  },
                })
              }
            />
          </Modal>
        </>
      ) : null}
    </WorkspacePage>
  )
}

function ProfileRow({
  label,
  value,
  hint,
}: {
  label: string
  value: ReactNode
  hint: string
}) {
  return (
    <TableRow>
      <TableHead scope="row" className="align-top">
        <span className="ui-table-primary block">{label}</span>
      </TableHead>
      <TableCell className="ui-table-body-copy align-top">{value}</TableCell>
      <TableCell className="ui-table-body-muted align-top">{hint}</TableCell>
    </TableRow>
  )
}

function getTabLabel(activeTab: OrganizationCollection) {
  return organizationTabs.find((tab) => tab.id === activeTab)?.label ?? 'Records'
}

function getSingularLabel(label: string) {
  return label.endsWith('s') ? label.slice(0, -1) : label
}

function getRecordSummary(
  activeTab: OrganizationCollection,
  record: LocationRecord | OrganizationMasterRecord,
) {
  if (activeTab === 'locations') {
    return `${(record as LocationRecord).city ?? 'City pending'} · ${(record as LocationRecord).timezone} · ${(record as LocationRecord).currency}`
  }

  return (record as OrganizationMasterRecord).description ?? 'No description added yet.'
}

function CompanyProfileEditor({
  profile,
  canManage,
  isSaving,
  onSave,
}: {
  profile: CompanyProfile
  canManage: boolean
  isSaving: boolean
  onSave: (values: CompanyProfileFormValues) => Promise<unknown>
}) {
  const { configuration } = useRegionalization()
  const [values, setValues] = useState<CompanyProfileFormValues>({
    name: profile.name,
    subscription_plan: profile.subscription_plan ?? '',
    timezone: profile.timezone,
    currency: profile.currency,
    country_code: profile.country_code,
    locale: profile.locale,
    language: profile.language,
    time_format: profile.time_format,
    expansion_country_codes: profile.expansion_country_codes,
  })
  const [expansionCountryCodesInput, setExpansionCountryCodesInput] = useState(
    profile.expansion_country_codes.join(', '),
  )
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)
  const supportedCountryCodes = useMemo(
    () => new Set(configuration.supported.countries.map((country) => country.code)),
    [configuration.supported.countries],
  )
  const countryOptions = useMemo(
    () =>
      configuration.supported.countries.map((country) => ({
        value: country.code,
        label: `${country.label} (${country.code})`,
      })),
    [configuration.supported.countries],
  )
  const localeOptions = useMemo(
    () => configuration.supported.locales.map((locale) => ({ value: locale.code, label: locale.label })),
    [configuration.supported.locales],
  )
  const languageOptions = useMemo(
    () => configuration.supported.languages.map((language) => ({ value: language.code, label: language.label })),
    [configuration.supported.languages],
  )
  const currencyOptions = useMemo(
    () =>
      configuration.supported.currencies.map((currency) => ({ value: currency.code, label: currency.label })),
    [configuration.supported.currencies],
  )
  const timeFormatOptions = useMemo(
    () =>
      configuration.supported.time_formats.map((format) => ({ value: format.code, label: format.label })),
    [configuration.supported.time_formats],
  )

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setMessage(null)
    setFieldErrors({})

    const normalizedCountryCode = values.country_code.trim().toUpperCase()
    const normalizedExpansionCountryCodes = expansionCountryCodesInput
      .split(',')
      .map((item) => item.trim().toUpperCase())
      .filter((item, index, array) => Boolean(item) && array.indexOf(item) === index)

    if (
      !values.name.trim() ||
      !normalizedCountryCode ||
      !values.locale.trim() ||
      !values.language.trim() ||
      !values.timezone.trim() ||
      !values.currency.trim()
    ) {
      setError('Name, launch country, locale, language, timezone, and currency are required.')
      return
    }

    if (!supportedCountryCodes.has(normalizedCountryCode)) {
      setError(`Launch country ${normalizedCountryCode} is not part of the current regional preset list.`)
      return
    }

    const invalidExpansionCountryCode = normalizedExpansionCountryCodes.find(
      (countryCode) => !supportedCountryCodes.has(countryCode),
    )

    if (invalidExpansionCountryCode) {
      setError(`Expansion country ${invalidExpansionCountryCode} is not part of the current preset list.`)
      return
    }

    if (normalizedExpansionCountryCodes.includes(normalizedCountryCode)) {
      setError('Expansion placeholders should not repeat the selected launch country.')
      return
    }

    try {
      await onSave({
        ...values,
        timezone: values.timezone.trim(),
        currency: values.currency.trim().toUpperCase(),
        country_code: normalizedCountryCode,
        locale: values.locale.trim(),
        language: values.language.trim().toLowerCase(),
        expansion_country_codes: normalizedExpansionCountryCodes,
      })
      setMessage('Company profile saved successfully.')
    } catch (caughtError) {
      const nextError = caughtError as Error
      setError(nextError.message)

      if (nextError instanceof ApiRequestError) {
        setFieldErrors(nextError.fieldErrors)
      }
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      <div className="workspace-form-grid workspace-form-grid--company">
        <Field
          label="Company name"
          error={fieldErrors.name?.[0]}
          className="workspace-form-field--span-full xl:col-span-2"
        >
          <Input
            value={values.name}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
            disabled={!canManage || isSaving}
          />
        </Field>
        <Field label="Subscription plan" error={fieldErrors.subscription_plan?.[0]}>
          <Input
            value={values.subscription_plan}
            onChange={(event) =>
              setValues((current) => ({ ...current, subscription_plan: event.target.value }))
            }
            disabled={!canManage || isSaving}
          />
        </Field>
        <SelectField
          label="Launch country"
          value={values.country_code}
          options={countryOptions}
          error={fieldErrors.country_code?.[0]}
          disabled={!canManage || isSaving}
          onChange={(nextCountryCode) => {
            const preset = configuration.supported.countries.find((country) => country.code === nextCountryCode)

            if (!preset) {
              setValues((current) => ({ ...current, country_code: nextCountryCode }))
              return
            }

            setValues((current) => ({
              ...current,
              country_code: preset.code,
              locale: preset.locale,
              language: preset.language,
              timezone: preset.timezone,
              currency: preset.currency,
              time_format: preset.time_format,
              expansion_country_codes: current.expansion_country_codes.filter(
                (countryCode) => countryCode !== preset.code,
              ),
            }))
            setExpansionCountryCodesInput((current) =>
              current
                .split(',')
                .map((item) => item.trim().toUpperCase())
                .filter(
                  (countryCode, index, array) =>
                    Boolean(countryCode) && countryCode !== preset.code && array.indexOf(countryCode) === index,
                )
                .join(', '),
            )
          }}
        />
        <SelectField
          label="Locale"
          value={values.locale}
          options={localeOptions}
          error={fieldErrors.locale?.[0]}
          disabled={!canManage || isSaving}
          onChange={(locale) => setValues((current) => ({ ...current, locale }))}
        />
        <SelectField
          label="Language"
          value={values.language}
          options={languageOptions}
          error={fieldErrors.language?.[0]}
          disabled={!canManage || isSaving}
          onChange={(language) => setValues((current) => ({ ...current, language }))}
        />
        <Field label="Timezone" error={fieldErrors.timezone?.[0]}>
          <Input
            value={values.timezone}
            onChange={(event) => setValues((current) => ({ ...current, timezone: event.target.value }))}
            disabled={!canManage || isSaving}
          />
        </Field>
        <SelectField
          label="Currency"
          value={values.currency}
          options={currencyOptions}
          error={fieldErrors.currency?.[0]}
          disabled={!canManage || isSaving}
          onChange={(currency) => setValues((current) => ({ ...current, currency }))}
        />
        <SelectField
          label="Time format"
          value={values.time_format}
          options={timeFormatOptions}
          error={fieldErrors.time_format?.[0]}
          disabled={!canManage || isSaving}
          onChange={(timeFormat) =>
            setValues((current) => ({ ...current, time_format: timeFormat as CompanyProfileFormValues['time_format'] }))
          }
        />
        <Field
          label="Expansion country placeholders"
          error={fieldErrors.expansion_country_codes?.[0] ?? fieldErrors['expansion_country_codes.0']?.[0]}
          className="workspace-form-field--span-full xl:col-span-2"
        >
          <Input
            value={expansionCountryCodesInput}
            onChange={(event) => setExpansionCountryCodesInput(event.target.value)}
            placeholder="US, DE"
            disabled={!canManage || isSaving}
          />
        </Field>
      </div>

      <p className="workspace-muted">
        Launch-country presets refresh locale, language, timezone, currency, and time formatting. Expansion
        placeholders keep future rollout markets visible in planning without changing current tenant behavior.
      </p>

      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}

      <WorkspaceActionsRow>
        <Button type="submit" variant="primary" disabled={!canManage || isSaving}>
          {isSaving ? 'Saving...' : 'Save company profile'}
        </Button>
      </WorkspaceActionsRow>
    </form>
  )
}

function RecordEditor({
  activeTab,
  canManage,
  isSaving,
  companyProfile,
  record,
  onSaveMaster,
  onSaveLocation,
}: {
  activeTab: OrganizationCollection
  canManage: boolean
  isSaving: boolean
  companyProfile: CompanyProfile
  record: OrganizationMasterRecord | LocationRecord | null
  onSaveMaster: (values: OrganizationMasterFormValues) => Promise<unknown>
  onSaveLocation: (values: LocationFormValues) => Promise<unknown>
}) {
  const [masterValues, setMasterValues] = useState<OrganizationMasterFormValues>(
    record && activeTab !== 'locations'
      ? {
          code: record.code,
          name: record.name,
          description: (record as OrganizationMasterRecord).description ?? '',
          status: record.status,
        }
      : emptyMasterForm,
  )
  const [locationValues, setLocationValues] = useState<LocationFormValues>(
    record && activeTab === 'locations'
      ? {
          code: record.code,
          name: record.name,
          timezone: (record as LocationRecord).timezone,
          currency: (record as LocationRecord).currency,
          address_line_1: (record as LocationRecord).address_line_1 ?? '',
          address_line_2: (record as LocationRecord).address_line_2 ?? '',
          city: (record as LocationRecord).city ?? '',
          state: (record as LocationRecord).state ?? '',
          country: (record as LocationRecord).country ?? '',
          postal_code: (record as LocationRecord).postal_code ?? '',
          status: record.status,
        }
      : {
          ...emptyLocationForm,
          timezone: companyProfile.timezone,
          currency: companyProfile.currency,
        },
  )
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setMessage(null)
    setFieldErrors({})

    if (activeTab === 'locations') {
      if (
        !locationValues.code.trim() ||
        !locationValues.name.trim() ||
        !locationValues.timezone.trim() ||
        !locationValues.currency.trim()
      ) {
        setError('Code, name, timezone, and currency are required for locations.')
        return
      }

      try {
        await onSaveLocation(locationValues)
        setMessage(record ? 'Location updated successfully.' : 'Location created successfully.')

        if (!record) {
          setLocationValues({
            ...emptyLocationForm,
            timezone: companyProfile.timezone,
            currency: companyProfile.currency,
          })
        }
      } catch (caughtError) {
        const nextError = caughtError as Error
        setError(nextError.message)

        if (nextError instanceof ApiRequestError) {
          setFieldErrors(nextError.fieldErrors)
        }
      }

      return
    }

    if (!masterValues.code.trim() || !masterValues.name.trim()) {
      setError('Code and name are required.')
      return
    }

    try {
      await onSaveMaster(masterValues)
      setMessage(record ? 'Record updated successfully.' : 'Record created successfully.')

      if (!record) {
        setMasterValues(emptyMasterForm)
      }
    } catch (caughtError) {
      const nextError = caughtError as Error
      setError(nextError.message)

      if (nextError instanceof ApiRequestError) {
        setFieldErrors(nextError.fieldErrors)
      }
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {activeTab === 'locations' ? (
        <LocationFormFields
          values={locationValues}
          onChange={setLocationValues}
          disabled={!canManage || isSaving}
          fieldErrors={fieldErrors}
        />
      ) : (
        <MasterFormFields
          values={masterValues}
          onChange={setMasterValues}
          disabled={!canManage || isSaving}
          fieldErrors={fieldErrors}
        />
      )}

      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}

      <WorkspaceActionsRow>
        <Button type="submit" variant="primary" disabled={!canManage || isSaving}>
          {isSaving ? 'Saving...' : record ? 'Save changes' : 'Create record'}
        </Button>
      </WorkspaceActionsRow>
    </form>
  )
}

function Field({
  label,
  error,
  className,
  children,
}: {
  label: string
  error?: string
  className?: string
  children: ReactNode
}) {
  return (
    <WorkspaceField label={label} error={error} className={className}>
      {children}
    </WorkspaceField>
  )
}

function MasterFormFields({
  values,
  onChange,
  disabled,
  fieldErrors,
}: {
  values: OrganizationMasterFormValues
  onChange: Dispatch<SetStateAction<OrganizationMasterFormValues>>
  disabled: boolean
  fieldErrors: Record<string, string[]>
}) {
  return (
    <div className="workspace-form-grid">
      <Field label="Code" error={fieldErrors.code?.[0]}>
        <Input
          value={values.code}
          onChange={(event) => onChange((current) => ({ ...current, code: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Name" error={fieldErrors.name?.[0]}>
        <Input
          value={values.name}
          onChange={(event) => onChange((current) => ({ ...current, name: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
        <Select
          value={values.status}
          disabled={disabled}
          onValueChange={(value) =>
            onChange((current) => ({ ...current, status: value as OrganizationMasterFormValues['status'] }))
          }
        >
          <SelectTrigger
            aria-label="Status"
            className={fieldErrors.status?.[0] ? 'border-destructive focus:ring-destructive/20' : undefined}
          >
            <SelectValue placeholder="Select status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
          </SelectContent>
        </Select>
      </WorkspaceField>
      <Field label="Description" error={fieldErrors.description?.[0]}>
        <Textarea
          rows={4}
          value={values.description}
          onChange={(event) => onChange((current) => ({ ...current, description: event.target.value }))}
          disabled={disabled}
        />
      </Field>
    </div>
  )
}

function LocationFormFields({
  values,
  onChange,
  disabled,
  fieldErrors,
}: {
  values: LocationFormValues
  onChange: Dispatch<SetStateAction<LocationFormValues>>
  disabled: boolean
  fieldErrors: Record<string, string[]>
}) {
  return (
    <div className="workspace-form-grid workspace-form-grid--location">
      <Field label="Code" error={fieldErrors.code?.[0]}>
        <Input
          value={values.code}
          onChange={(event) => onChange((current) => ({ ...current, code: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Name" error={fieldErrors.name?.[0]}>
        <Input
          value={values.name}
          onChange={(event) => onChange((current) => ({ ...current, name: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Timezone" error={fieldErrors.timezone?.[0]}>
        <Input
          value={values.timezone}
          onChange={(event) => onChange((current) => ({ ...current, timezone: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Currency" error={fieldErrors.currency?.[0]}>
        <Input
          value={values.currency}
          onChange={(event) => onChange((current) => ({ ...current, currency: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Address line 1" error={fieldErrors.address_line_1?.[0]}>
        <Input
          value={values.address_line_1}
          onChange={(event) =>
            onChange((current) => ({ ...current, address_line_1: event.target.value }))
          }
          disabled={disabled}
        />
      </Field>
      <Field label="Address line 2" error={fieldErrors.address_line_2?.[0]}>
        <Input
          value={values.address_line_2}
          onChange={(event) =>
            onChange((current) => ({ ...current, address_line_2: event.target.value }))
          }
          disabled={disabled}
        />
      </Field>
      <Field label="City" error={fieldErrors.city?.[0]}>
        <Input
          value={values.city}
          onChange={(event) => onChange((current) => ({ ...current, city: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="State" error={fieldErrors.state?.[0]}>
        <Input
          value={values.state}
          onChange={(event) => onChange((current) => ({ ...current, state: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Country" error={fieldErrors.country?.[0]}>
        <Input
          value={values.country}
          onChange={(event) => onChange((current) => ({ ...current, country: event.target.value }))}
          disabled={disabled}
        />
      </Field>
      <Field label="Postal code" error={fieldErrors.postal_code?.[0]}>
        <Input
          value={values.postal_code}
          onChange={(event) =>
            onChange((current) => ({ ...current, postal_code: event.target.value }))
          }
          disabled={disabled}
        />
      </Field>
      <WorkspaceField label="Status" error={fieldErrors.status?.[0]}>
        <Select
          value={values.status}
          disabled={disabled}
          onValueChange={(value) =>
            onChange((current) => ({ ...current, status: value as LocationFormValues['status'] }))
          }
        >
          <SelectTrigger
            aria-label="Status"
            className={fieldErrors.status?.[0] ? 'border-destructive focus:ring-destructive/20' : undefined}
          >
            <SelectValue placeholder="Select status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
          </SelectContent>
        </Select>
      </WorkspaceField>
    </div>
  )
}
