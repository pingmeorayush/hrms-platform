import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { usePayrollSetupWorkspace } from '../hooks/usePayrollSetupWorkspace'
import type {
  PayrollCalendarRecord,
  PayrollPeriodRecord,
  SalaryComponentRecord,
  SalaryStructureComponentRecord,
  SalaryStructureRecord,
} from '../types'
import { formatCurrency, formatDate, formatRelativeTimestamp } from '../utils'

type SetupTab = 'calendars' | 'periods' | 'components' | 'structures' | 'compensation'

interface CalendarFormState {
  name: string
  frequency: PayrollCalendarRecord['frequency']
  timezone: string
  payroll_day: string
  payroll_weekday: string
  is_default: 'yes' | 'no'
  status: PayrollCalendarRecord['status']
}

interface PeriodFormState {
  payroll_calendar_id: string
  name: string
  start_date: string
  end_date: string
  payroll_date: string
}

interface SalaryComponentFormState {
  code: string
  name: string
  category: SalaryComponentRecord['category']
  calculation_type: SalaryComponentRecord['calculation_type']
  flat_amount: string
  percentage_value: string
  percentage_basis_component_codes: string
  expression_formula: string
  is_taxable: 'yes' | 'no'
  is_proratable: 'yes' | 'no'
  display_order: string
  status: SalaryComponentRecord['status']
}

interface SalaryStructureComponentDraft {
  client_id: string
  salary_component_id: string
  display_order: string
  configured_amount: string
  configured_percentage: string
  configured_basis_component_codes: string
  configured_expression_formula: string
}

interface SalaryStructureFormState {
  code: string
  name: string
  currency: string
  country_code: string
  pay_frequency: SalaryStructureRecord['pay_frequency']
  grade: string
  band: string
  level: string
  annual_ctc_amount: string
  basic_salary_amount: string
  gross_salary_amount: string
  net_salary_amount: string
  effective_from: string
  revision_date: string
  status: 'draft' | 'active' | 'inactive'
  notes: string
  components: SalaryStructureComponentDraft[]
}

interface CompensationFormState {
  employee_id: string
  salary_structure_id: string
  revision_reason:
    | 'initial_assignment'
    | 'annual_revision'
    | 'promotion'
    | 'transfer'
    | 'market_adjustment'
    | 'correction'
    | 'manual_change'
  effective_from: string
  revision_date: string
  notes: string
}

const setupTabs: Array<{ id: SetupTab; label: string }> = [
  { id: 'calendars', label: 'Calendars' },
  { id: 'periods', label: 'Periods' },
  { id: 'components', label: 'Components' },
  { id: 'structures', label: 'Structures' },
  { id: 'compensation', label: 'Compensation' },
]

const payrollFrequencyOptions = [
  ['monthly', 'Monthly'],
  ['weekly', 'Weekly'],
  ['biweekly', 'Biweekly'],
  ['semi_monthly', 'Semi-monthly'],
  ['custom', 'Custom'],
] as const

const payrollWeekdayOptions = [
  ['', 'Select payroll weekday'],
  ['0', 'Sunday'],
  ['1', 'Monday'],
  ['2', 'Tuesday'],
  ['3', 'Wednesday'],
  ['4', 'Thursday'],
  ['5', 'Friday'],
  ['6', 'Saturday'],
] as const

const salaryComponentCategoryOptions = [
  ['earning', 'Earning'],
  ['deduction', 'Deduction'],
  ['employer_contribution', 'Employer contribution'],
] as const

const salaryCalculationTypeOptions = [
  ['fixed', 'Fixed'],
  ['percentage', 'Percentage'],
  ['expression', 'Expression'],
] as const

const revisionReasonOptions = [
  ['initial_assignment', 'Initial assignment'],
  ['annual_revision', 'Annual revision'],
  ['promotion', 'Promotion'],
  ['transfer', 'Transfer'],
  ['market_adjustment', 'Market adjustment'],
  ['correction', 'Correction'],
  ['manual_change', 'Manual change'],
] as const

export function PayrollSetupPage() {
  const workspace = usePayrollSetupWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<SetupTab>('calendars')
  const [selectedCalendarId, setSelectedCalendarId] = useState<number | null>(null)
  const [selectedComponentId, setSelectedComponentId] = useState<number | null>(null)
  const [selectedStructureId, setSelectedStructureId] = useState<number | null>(null)
  const [calendarForm, setCalendarForm] = useState<CalendarFormState>(createDefaultCalendarForm())
  const [periodForm, setPeriodForm] = useState<PeriodFormState>(createDefaultPeriodForm())
  const [componentForm, setComponentForm] = useState<SalaryComponentFormState>(createDefaultComponentForm())
  const [structureForm, setStructureForm] = useState<SalaryStructureFormState>(createDefaultStructureForm())
  const [compensationForm, setCompensationForm] = useState<CompensationFormState>(createDefaultCompensationForm())

  const selectedCalendar = data?.calendars.find((calendar) => calendar.id === selectedCalendarId) ?? null
  const selectedComponent = data?.salaryComponents.find((component) => component.id === selectedComponentId) ?? null
  const selectedStructure = data?.salaryStructures.find((structure) => structure.id === selectedStructureId) ?? null

  const activeCalendarCount = data?.calendars.filter((calendar) => calendar.status === 'active').length ?? 0
  const periodsInFlightCount = data?.periods.filter((period) => period.status !== 'closed').length ?? 0
  const activeComponentCount = data?.salaryComponents.filter((component) => component.status === 'active').length ?? 0
  const activeStructureCount = data?.salaryStructures.filter((structure) => structure.status === 'active').length ?? 0
  const assignedEmployeeCount = data?.compensations.length ?? 0
  const defaultCalendarId = data?.calendars.find((calendar) => calendar.is_default)?.id ?? data?.calendars[0]?.id ?? null
  const unassignedEmployeeCount = useMemo(() => {
    if (!data) {
      return 0
    }

    const assignedEmployeeIds = new Set(data.compensations.map((record) => record.employee_id))
    return data.employees.filter((employee) => !assignedEmployeeIds.has(employee.id)).length
  }, [data])
  const resolvedPeriodCalendarId = periodForm.payroll_calendar_id || (defaultCalendarId ? String(defaultCalendarId) : '')

  if (workspace.error && !data) {
    return (
      <WorkspacePage>
        <WorkspaceSurface>
          <WorkspaceHeader compact>
            <div className="min-w-0 space-y-1">
              <CardTitle>Payroll setup studio</CardTitle>
              <CardDescription>
                The payroll configuration workspace could not be loaded for this session.
              </CardDescription>
            </div>
          </WorkspaceHeader>
          <WorkspaceContent>
            <WorkspaceEmptyState
              title="Payroll setup is unavailable"
              copy={workspace.error.message}
            />
          </WorkspaceContent>
        </WorkspaceSurface>
      </WorkspacePage>
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Payroll Setup"
          title="Payroll Setup Studio"
          description="Configure payroll calendars, create future periods, manage salary definitions, and assign compensation before runs move into the console."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo setup surface' : 'Live setup surface'}</Badge>}
          context={[
            `${activeCalendarCount} active calendar(s)`,
            `${activeStructureCount} active structure(s)`,
            `${assignedEmployeeCount} compensation assignment(s)`,
          ]}
          actions={
            <>
              {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
              {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
              {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
            </>
          }
        />

        <WorkspaceContent className="space-y-3.5">
          <div className="organization-metric-grid">
            <MetricCard
              label="Active calendars"
              value={String(activeCalendarCount)}
              caption={`${periodsInFlightCount} payroll period(s) are currently in flight`}
            />
            <MetricCard
              label="Active components"
              value={String(activeComponentCount)}
              caption={`${activeStructureCount} salary structure(s) are currently active`}
            />
            <MetricCard
              label="Compensation assigned"
              value={String(assignedEmployeeCount)}
              caption={`${unassignedEmployeeCount} employee(s) still need a visible payroll assignment`}
            />
            <MetricCard
              label="Default calendar"
              value={data?.calendars.find((calendar) => calendar.is_default)?.name ?? 'Pending'}
              caption="New payroll periods start from the default calendar unless a different cycle is selected"
            />
          </div>

          <WorkspaceTabs aria-label="Payroll setup sections">
            {setupTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                active={activeTab === tab.id}
                aria-selected={activeTab === tab.id}
                role="tab"
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          {workspace.isLoading && !data ? (
            <WorkspaceEmptyState
              title="Loading payroll setup"
              copy="Pulling payroll calendars, salary definitions, structures, and compensation assignments for this session."
            />
          ) : null}

          {data ? (
            <>
              {activeTab === 'calendars' ? (
                <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
                  <div className="space-y-3.5">
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Calendar</TableHead>
                            <TableHead>Frequency</TableHead>
                            <TableHead>Default</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Updated</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {data.calendars.map((calendar) => (
                            <TableRow
                              key={calendar.id}
                              className={calendar.id === selectedCalendarId ? 'bg-primary/[0.06]' : undefined}
                              onClick={() => {
                                setSelectedCalendarId(calendar.id)
                                setCalendarForm(mapCalendarToFormState(calendar))
                              }}
                            >
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{calendar.name}</p>
                                  <p className="text-xs text-muted-foreground">{calendar.timezone}</p>
                                </div>
                              </TableCell>
                              <TableCell className="capitalize">{calendar.frequency.replace(/_/g, ' ')}</TableCell>
                              <TableCell>
                                <Badge variant={calendar.is_default ? 'success' : 'neutral'}>
                                  {calendar.is_default ? 'Default' : 'Secondary'}
                                </Badge>
                              </TableCell>
                              <TableCell>
                                <Badge variant={calendar.status === 'active' ? 'success' : 'neutral'}>
                                  {calendar.status}
                                </Badge>
                              </TableCell>
                              <TableCell>{formatRelativeTimestamp(calendar.updated_at)}</TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>

                    <WorkspaceSurface>
                      <WorkspaceContent>
                        <WorkspaceSummaryRow
                          label="Payroll day rule"
                          value={
                            selectedCalendar
                              ? describePayrollCalendarRule(selectedCalendar)
                              : 'Select a calendar to inspect its cutoff rule'
                          }
                        />
                        <WorkspaceSummaryRow
                          label="Future periods"
                          value={String(data.periods.filter((period) => period.payroll_calendar_id === selectedCalendarId).length)}
                        />
                        <WorkspaceSummaryRow
                          label="Last updated"
                          value={selectedCalendar ? formatDate(selectedCalendar.updated_at) : 'Pending'}
                        />
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>{selectedCalendar ? 'Edit payroll calendar' : 'Create payroll calendar'}</CardTitle>
                        <CardDescription>
                          Define payroll cadence, cutoff rules, and which calendar is the default source for new payroll periods.
                        </CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <form className="space-y-3.5" onSubmit={(event) => void handleCalendarSubmit(event)}>
                        <WorkspaceField label="Calendar name">
                          <Input
                            value={calendarForm.name}
                            onChange={(event) => setCalendarForm((current) => ({ ...current, name: event.target.value }))}
                            placeholder="Main monthly payroll"
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <SelectField
                          label="Frequency"
                          value={calendarForm.frequency}
                          options={payrollFrequencyOptions.map(([value, label]) => ({ value, label }))}
                          onChange={(value) =>
                            setCalendarForm((current) => ({
                              ...current,
                              frequency: value as CalendarFormState['frequency'],
                              payroll_day: requiresPayrollDay(value as PayrollCalendarRecord['frequency']) ? current.payroll_day : '',
                              payroll_weekday: requiresPayrollWeekday(value as PayrollCalendarRecord['frequency']) ? current.payroll_weekday : '',
                            }))
                          }
                          disabled={!workspace.canProcessPayroll || workspace.isSaving}
                        />
                        <WorkspaceField label="Timezone">
                          <Input
                            value={calendarForm.timezone}
                            onChange={(event) => setCalendarForm((current) => ({ ...current, timezone: event.target.value }))}
                            placeholder="Asia/Kolkata"
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        {requiresPayrollDay(calendarForm.frequency) ? (
                          <WorkspaceField label="Payroll day">
                            <Input
                              type="number"
                              min="1"
                              max="31"
                              value={calendarForm.payroll_day}
                              onChange={(event) => setCalendarForm((current) => ({ ...current, payroll_day: event.target.value }))}
                              placeholder="30"
                              disabled={!workspace.canProcessPayroll || workspace.isSaving}
                            />
                          </WorkspaceField>
                        ) : null}
                        {requiresPayrollWeekday(calendarForm.frequency) ? (
                          <SelectField
                            label="Payroll weekday"
                            value={calendarForm.payroll_weekday}
                            options={payrollWeekdayOptions.map(([value, label]) => ({ value, label }))}
                            onChange={(value) => setCalendarForm((current) => ({ ...current, payroll_weekday: value }))}
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        ) : null}
                        <SelectField
                          label="Default calendar"
                          value={calendarForm.is_default}
                          options={[
                            { value: 'yes', label: 'Yes, make default' },
                            { value: 'no', label: 'No, keep secondary' },
                          ]}
                          onChange={(value) => setCalendarForm((current) => ({ ...current, is_default: value as CalendarFormState['is_default'] }))}
                          disabled={!workspace.canProcessPayroll || workspace.isSaving}
                        />
                        <SelectField
                          label="Status"
                          value={calendarForm.status}
                          options={[
                            { value: 'active', label: 'Active' },
                            { value: 'inactive', label: 'Inactive' },
                          ]}
                          onChange={(value) => setCalendarForm((current) => ({ ...current, status: value as CalendarFormState['status'] }))}
                          disabled={!workspace.canProcessPayroll || workspace.isSaving}
                        />
                        <div className="flex flex-wrap items-center gap-2">
                          <Button
                            type="submit"
                            disabled={!workspace.canProcessPayroll || !canSubmitCalendar(calendarForm) || workspace.isSaving}
                          >
                            {selectedCalendar ? 'Save calendar' : 'Create payroll calendar'}
                          </Button>
                          <Button
                            variant="secondary"
                            onClick={() => {
                              setSelectedCalendarId(null)
                              setCalendarForm(createDefaultCalendarForm())
                              workspace.clearActionMessage()
                            }}
                            disabled={workspace.isSaving}
                          >
                            {selectedCalendar ? 'Start new calendar' : 'Reset form'}
                          </Button>
                        </div>
                      </form>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </WorkspaceSplit>
              ) : null}

              {activeTab === 'periods' ? (
                <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.05fr)_minmax(20rem,0.95fr)]">
                  <div className="space-y-3.5">
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Period</TableHead>
                            <TableHead>Calendar</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Payroll date</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {data.periods.map((period) => (
                            <TableRow key={period.id}>
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{period.name}</p>
                                  <p className="text-xs text-muted-foreground">
                                    {formatDate(period.start_date)} to {formatDate(period.end_date)}
                                  </p>
                                </div>
                              </TableCell>
                              <TableCell>{period.payroll_calendar?.name ?? `Calendar ${period.payroll_calendar_id}`}</TableCell>
                              <TableCell>
                                <Badge variant={mapPeriodBadgeVariant(period.status)}>{period.status}</Badge>
                              </TableCell>
                              <TableCell>{formatDate(period.payroll_date)}</TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>

                    <WorkspaceSurface>
                      <WorkspaceContent>
                        <WorkspaceSummaryRow label="Periods in flight" value={String(periodsInFlightCount)} />
                        <WorkspaceSummaryRow
                          label="Next payroll date"
                          value={formatDate(data.periods.find((period) => period.status !== 'closed')?.payroll_date ?? null)}
                        />
                        <WorkspaceSummaryRow
                          label="Run-control note"
                          value="Open, prepare, calculate, and close actions stay in the payroll run console."
                        />
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>Create payroll period</CardTitle>
                        <CardDescription>
                          Create the next payroll window here, then move into the run console for open, prepare, and close actions.
                        </CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <form className="space-y-3.5" onSubmit={(event) => void handlePeriodSubmit(event)}>
                        <SelectField
                          label="Payroll calendar"
                          value={resolvedPeriodCalendarId}
                          options={data.calendars.map((calendar) => ({ value: String(calendar.id), label: calendar.name }))}
                          onChange={(value) => setPeriodForm((current) => ({ ...current, payroll_calendar_id: value }))}
                          disabled={!workspace.canProcessPayroll || workspace.isSaving}
                        />
                        <WorkspaceField label="Period name">
                          <Input
                            value={periodForm.name}
                            onChange={(event) => setPeriodForm((current) => ({ ...current, name: event.target.value }))}
                            placeholder="September 2026 Payroll"
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Start date">
                          <Input
                            type="date"
                            value={periodForm.start_date}
                            onChange={(event) => setPeriodForm((current) => ({ ...current, start_date: event.target.value }))}
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <WorkspaceField label="End date">
                          <Input
                            type="date"
                            value={periodForm.end_date}
                            onChange={(event) => setPeriodForm((current) => ({ ...current, end_date: event.target.value }))}
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Payroll date">
                          <Input
                            type="date"
                            value={periodForm.payroll_date}
                            onChange={(event) => setPeriodForm((current) => ({ ...current, payroll_date: event.target.value }))}
                            disabled={!workspace.canProcessPayroll || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <div className="flex flex-wrap items-center gap-2">
                          <Button
                            type="submit"
                            disabled={!workspace.canProcessPayroll || !canSubmitPeriod(periodForm, resolvedPeriodCalendarId) || workspace.isSaving}
                          >
                            Create period
                          </Button>
                          <Button
                            variant="secondary"
                            onClick={() => {
                              setPeriodForm(createDefaultPeriodForm(data.calendars.find((calendar) => calendar.is_default)?.id ?? data.calendars[0]?.id ?? null))
                              workspace.clearActionMessage()
                            }}
                            disabled={workspace.isSaving}
                          >
                            Reset form
                          </Button>
                        </div>
                      </form>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </WorkspaceSplit>
              ) : null}

              {activeTab === 'components' ? (
                <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
                  <div className="space-y-3.5">
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Component</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Status</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {data.salaryComponents.map((component) => (
                            <TableRow
                              key={component.id}
                              className={component.id === selectedComponentId ? 'bg-primary/[0.06]' : undefined}
                              onClick={() => {
                                setSelectedComponentId(component.id)
                                setComponentForm(mapSalaryComponentToFormState(component))
                              }}
                            >
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{component.name}</p>
                                  <p className="text-xs text-muted-foreground">{component.code}</p>
                                </div>
                              </TableCell>
                              <TableCell className="capitalize">{component.calculation_type}</TableCell>
                              <TableCell className="capitalize">{component.category.replace(/_/g, ' ')}</TableCell>
                              <TableCell>
                                <Badge variant={component.status === 'active' ? 'success' : 'neutral'}>
                                  {component.status}
                                </Badge>
                              </TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>

                    <WorkspaceSurface>
                      <WorkspaceContent>
                        <WorkspaceSummaryRow
                          label="Selected formula"
                          value={selectedComponent ? describeSalaryComponentFormula(selectedComponent) : 'Select a component to inspect the formula rules'}
                        />
                        <WorkspaceSummaryRow
                          label="Proration"
                          value={selectedComponent ? (selectedComponent.is_proratable ? 'Enabled' : 'Disabled') : 'Pending'}
                        />
                        <WorkspaceSummaryRow
                          label="Taxable"
                          value={selectedComponent ? (selectedComponent.is_taxable ? 'Yes' : 'No') : 'Pending'}
                        />
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>{selectedComponent ? 'Edit salary component' : 'Create salary component'}</CardTitle>
                        <CardDescription>
                          Define earnings, deductions, or employer contributions with explicit formula inputs that payroll can trust later.
                        </CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <form className="space-y-3.5" onSubmit={(event) => void handleSalaryComponentSubmit(event)}>
                        <WorkspaceField label="Component code">
                          <Input
                            value={componentForm.code}
                            onChange={(event) => setComponentForm((current) => ({ ...current, code: event.target.value.toUpperCase() }))}
                            placeholder="BASIC"
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Component name">
                          <Input
                            value={componentForm.name}
                            onChange={(event) => setComponentForm((current) => ({ ...current, name: event.target.value }))}
                            placeholder="Basic salary"
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <SelectField
                          label="Category"
                          value={componentForm.category}
                          options={salaryComponentCategoryOptions.map(([value, label]) => ({ value, label }))}
                          onChange={(value) => setComponentForm((current) => ({ ...current, category: value as SalaryComponentFormState['category'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <SelectField
                          label="Calculation type"
                          value={componentForm.calculation_type}
                          options={salaryCalculationTypeOptions.map(([value, label]) => ({ value, label }))}
                          onChange={(value) =>
                            setComponentForm((current) => ({
                              ...current,
                              calculation_type: value as SalaryComponentFormState['calculation_type'],
                              flat_amount: value === 'fixed' ? current.flat_amount : '',
                              percentage_value: value === 'percentage' ? current.percentage_value : '',
                              percentage_basis_component_codes:
                                value === 'percentage' ? current.percentage_basis_component_codes : '',
                              expression_formula: value === 'expression' ? current.expression_formula : '',
                            }))
                          }
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        {componentForm.calculation_type === 'fixed' ? (
                          <WorkspaceField label="Flat amount">
                            <Input
                              type="number"
                              step="0.01"
                              value={componentForm.flat_amount}
                              onChange={(event) => setComponentForm((current) => ({ ...current, flat_amount: event.target.value }))}
                              placeholder="50000"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        ) : null}
                        {componentForm.calculation_type === 'percentage' ? (
                          <>
                            <WorkspaceField label="Percentage value">
                              <Input
                                type="number"
                                step="0.0001"
                                value={componentForm.percentage_value}
                                onChange={(event) => setComponentForm((current) => ({ ...current, percentage_value: event.target.value }))}
                                placeholder="40"
                                disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                              />
                            </WorkspaceField>
                            <WorkspaceField label="Basis component codes">
                              <Textarea
                                value={componentForm.percentage_basis_component_codes}
                                onChange={(event) =>
                                  setComponentForm((current) => ({
                                    ...current,
                                    percentage_basis_component_codes: event.target.value,
                                  }))
                                }
                                placeholder="BASIC, HRA"
                                disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                              />
                            </WorkspaceField>
                          </>
                        ) : null}
                        {componentForm.calculation_type === 'expression' ? (
                          <WorkspaceField label="Expression formula">
                            <Textarea
                              value={componentForm.expression_formula}
                              onChange={(event) => setComponentForm((current) => ({ ...current, expression_formula: event.target.value }))}
                              placeholder="MIN(BASIC * 0.12, 1800)"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        ) : null}
                        <WorkspaceField label="Display order">
                          <Input
                            type="number"
                            min="0"
                            value={componentForm.display_order}
                            onChange={(event) => setComponentForm((current) => ({ ...current, display_order: event.target.value }))}
                            placeholder="1"
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <SelectField
                          label="Taxable"
                          value={componentForm.is_taxable}
                          options={[
                            { value: 'yes', label: 'Yes' },
                            { value: 'no', label: 'No' },
                          ]}
                          onChange={(value) => setComponentForm((current) => ({ ...current, is_taxable: value as SalaryComponentFormState['is_taxable'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <SelectField
                          label="Proratable"
                          value={componentForm.is_proratable}
                          options={[
                            { value: 'yes', label: 'Yes' },
                            { value: 'no', label: 'No' },
                          ]}
                          onChange={(value) => setComponentForm((current) => ({ ...current, is_proratable: value as SalaryComponentFormState['is_proratable'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <SelectField
                          label="Status"
                          value={componentForm.status}
                          options={[
                            { value: 'active', label: 'Active' },
                            { value: 'inactive', label: 'Inactive' },
                          ]}
                          onChange={(value) => setComponentForm((current) => ({ ...current, status: value as SalaryComponentFormState['status'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <div className="flex flex-wrap items-center gap-2">
                          <Button
                            type="submit"
                            disabled={!workspace.canManageSalaryConfiguration || !canSubmitSalaryComponent(componentForm) || workspace.isSaving}
                          >
                            {selectedComponent ? 'Save component' : 'Create component'}
                          </Button>
                          <Button
                            variant="secondary"
                            onClick={() => {
                              setSelectedComponentId(null)
                              setComponentForm(createDefaultComponentForm())
                              workspace.clearActionMessage()
                            }}
                            disabled={workspace.isSaving}
                          >
                            {selectedComponent ? 'Start new component' : 'Reset form'}
                          </Button>
                        </div>
                      </form>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </WorkspaceSplit>
              ) : null}

              {activeTab === 'structures' ? (
                <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.1fr)_minmax(22rem,0.9fr)]">
                  <div className="space-y-3.5">
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Structure</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Effective</TableHead>
                            <TableHead>Annual CTC</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {data.salaryStructures.map((structure) => (
                            <TableRow
                              key={structure.id}
                              className={structure.id === selectedStructureId ? 'bg-primary/[0.06]' : undefined}
                              onClick={() => {
                                setSelectedStructureId(structure.id)
                                setStructureForm(mapSalaryStructureToFormState(structure))
                              }}
                            >
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">
                                    {structure.code} v{structure.version}
                                  </p>
                                  <p className="text-xs text-muted-foreground">{structure.name ?? 'Unnamed structure'}</p>
                                </div>
                              </TableCell>
                              <TableCell>
                                <Badge variant={mapStructureBadgeVariant(structure.status)}>{structure.status}</Badge>
                              </TableCell>
                              <TableCell>{formatDate(structure.effective_from)}</TableCell>
                              <TableCell>{formatCurrency(structure.annual_ctc_amount, structure.currency)}</TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>

                    <WorkspaceSurface>
                      <WorkspaceContent>
                        <WorkspaceSummaryRow
                          label="Component rows"
                          value={String(selectedStructure?.components.length ?? 0)}
                        />
                        <WorkspaceSummaryRow
                          label="Gross monthly"
                          value={
                            selectedStructure
                              ? formatCurrency(selectedStructure.gross_salary_amount, selectedStructure.currency)
                              : 'Pending'
                          }
                        />
                        <WorkspaceSummaryRow
                          label="Version note"
                          value={
                            selectedStructure
                              ? `Saving from ${selectedStructure.code} v${selectedStructure.version} creates a new version.`
                              : 'Create a new structure or select one to branch a new version.'
                          }
                        />
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>{selectedStructure ? 'Create next structure version' : 'Create salary structure'}</CardTitle>
                        <CardDescription>
                          Combine salary components into an auditable structure with explicit component-level overrides.
                        </CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <form className="space-y-3.5" onSubmit={(event) => void handleSalaryStructureSubmit(event)}>
                        <WorkspaceField label="Structure code">
                          <Input
                            value={structureForm.code}
                            onChange={(event) => setStructureForm((current) => ({ ...current, code: event.target.value.toUpperCase() }))}
                            placeholder="ENG-G6"
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Structure name">
                          <Input
                            value={structureForm.name}
                            onChange={(event) => setStructureForm((current) => ({ ...current, name: event.target.value }))}
                            placeholder="Engineering Grade 6"
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <div className="grid gap-3 sm:grid-cols-2">
                          <WorkspaceField label="Currency">
                            <Input
                              value={structureForm.currency}
                              onChange={(event) => setStructureForm((current) => ({ ...current, currency: event.target.value.toUpperCase() }))}
                              placeholder="INR"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Country code">
                            <Input
                              value={structureForm.country_code}
                              onChange={(event) => setStructureForm((current) => ({ ...current, country_code: event.target.value.toUpperCase() }))}
                              placeholder="IN"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        </div>
                        <SelectField
                          label="Pay frequency"
                          value={structureForm.pay_frequency}
                          options={payrollFrequencyOptions.map(([value, label]) => ({ value, label }))}
                          onChange={(value) => setStructureForm((current) => ({ ...current, pay_frequency: value as SalaryStructureFormState['pay_frequency'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <div className="grid gap-3 sm:grid-cols-3">
                          <WorkspaceField label="Grade">
                            <Input
                              value={structureForm.grade}
                              onChange={(event) => setStructureForm((current) => ({ ...current, grade: event.target.value }))}
                              placeholder="G6"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Band">
                            <Input
                              value={structureForm.band}
                              onChange={(event) => setStructureForm((current) => ({ ...current, band: event.target.value }))}
                              placeholder="B3"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Level">
                            <Input
                              value={structureForm.level}
                              onChange={(event) => setStructureForm((current) => ({ ...current, level: event.target.value }))}
                              placeholder="L2"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                          <WorkspaceField label="Annual CTC">
                            <Input
                              type="number"
                              step="0.01"
                              value={structureForm.annual_ctc_amount}
                              onChange={(event) => setStructureForm((current) => ({ ...current, annual_ctc_amount: event.target.value }))}
                              placeholder="1980000"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Basic salary">
                            <Input
                              type="number"
                              step="0.01"
                              value={structureForm.basic_salary_amount}
                              onChange={(event) => setStructureForm((current) => ({ ...current, basic_salary_amount: event.target.value }))}
                              placeholder="660000"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Gross salary">
                            <Input
                              type="number"
                              step="0.01"
                              value={structureForm.gross_salary_amount}
                              onChange={(event) => setStructureForm((current) => ({ ...current, gross_salary_amount: event.target.value }))}
                              placeholder="165000"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Net salary">
                            <Input
                              type="number"
                              step="0.01"
                              value={structureForm.net_salary_amount}
                              onChange={(event) => setStructureForm((current) => ({ ...current, net_salary_amount: event.target.value }))}
                              placeholder="129000"
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                          <WorkspaceField label="Effective from">
                            <Input
                              type="date"
                              value={structureForm.effective_from}
                              onChange={(event) => setStructureForm((current) => ({ ...current, effective_from: event.target.value }))}
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Revision date">
                            <Input
                              type="date"
                              value={structureForm.revision_date}
                              onChange={(event) => setStructureForm((current) => ({ ...current, revision_date: event.target.value }))}
                              disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                            />
                          </WorkspaceField>
                        </div>
                        <SelectField
                          label="Status"
                          value={structureForm.status}
                          options={[
                            { value: 'draft', label: 'Draft' },
                            { value: 'active', label: 'Active' },
                            { value: 'inactive', label: 'Inactive' },
                          ]}
                          onChange={(value) => setStructureForm((current) => ({ ...current, status: value as SalaryStructureFormState['status'] }))}
                          disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                        />
                        <WorkspaceField label="Notes">
                          <Textarea
                            value={structureForm.notes}
                            onChange={(event) => setStructureForm((current) => ({ ...current, notes: event.target.value }))}
                            placeholder="Explain who this structure is for and why the revision matters."
                            disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                          />
                        </WorkspaceField>

                        <WorkspaceSurface className="border-dashed border-line-strong/70 shadow-none">
                          <WorkspaceHeader compact>
                            <div className="space-y-1">
                              <CardTitle>Structure components</CardTitle>
                              <CardDescription>
                                Add at least one component. You can override amounts, percentages, basis codes, or formulas per structure row.
                              </CardDescription>
                            </div>
                            <WorkspaceHeaderActions>
                              <Button
                                size="xs"
                                variant="secondary"
                                onClick={() =>
                                  setStructureForm((current) => ({
                                    ...current,
                                    components: [...current.components, createStructureComponentDraft(current.components.length + 1)],
                                  }))
                                }
                                disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                              >
                                Add component
                              </Button>
                            </WorkspaceHeaderActions>
                          </WorkspaceHeader>
                          <WorkspaceContent className="space-y-3.5">
                            {structureForm.components.length ? (
                              structureForm.components.map((componentDraft, index) => (
                                <div key={componentDraft.client_id} className="rounded-xl border border-line/80 bg-white/75 p-3">
                                  <div className="mb-3 flex items-center justify-between gap-3">
                                    <strong className="text-sm text-foreground">Component row {index + 1}</strong>
                                    <Button
                                      size="xs"
                                      variant="ghost"
                                      onClick={() =>
                                        setStructureForm((current) => ({
                                          ...current,
                                          components: current.components.filter((entry) => entry.client_id !== componentDraft.client_id),
                                        }))
                                      }
                                      disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                    >
                                      Remove
                                    </Button>
                                  </div>
                                  <div className="grid gap-3 sm:grid-cols-2">
                                    <SelectField
                                      label="Salary component"
                                      value={componentDraft.salary_component_id}
                                      options={[
                                        { value: '', label: 'Select component' },
                                        ...data.salaryComponents.map((component) => ({
                                          value: String(component.id),
                                          label: `${component.code} · ${component.name}`,
                                        })),
                                      ]}
                                      onChange={(value) =>
                                        updateStructureComponentDraft(componentDraft.client_id, {
                                          salary_component_id: value,
                                        })
                                      }
                                      disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                    />
                                    <WorkspaceField label="Display order">
                                      <Input
                                        type="number"
                                        min="0"
                                        value={componentDraft.display_order}
                                        onChange={(event) =>
                                          updateStructureComponentDraft(componentDraft.client_id, {
                                            display_order: event.target.value,
                                          })
                                        }
                                        disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                      />
                                    </WorkspaceField>
                                    <WorkspaceField label="Configured amount">
                                      <Input
                                        type="number"
                                        step="0.01"
                                        value={componentDraft.configured_amount}
                                        onChange={(event) =>
                                          updateStructureComponentDraft(componentDraft.client_id, {
                                            configured_amount: event.target.value,
                                          })
                                        }
                                        placeholder="Optional override"
                                        disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                      />
                                    </WorkspaceField>
                                    <WorkspaceField label="Configured percentage">
                                      <Input
                                        type="number"
                                        step="0.0001"
                                        value={componentDraft.configured_percentage}
                                        onChange={(event) =>
                                          updateStructureComponentDraft(componentDraft.client_id, {
                                            configured_percentage: event.target.value,
                                          })
                                        }
                                        placeholder="Optional override"
                                        disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                      />
                                    </WorkspaceField>
                                  </div>
                                  <div className="mt-3 grid gap-3">
                                    <WorkspaceField label="Basis component codes">
                                      <Textarea
                                        value={componentDraft.configured_basis_component_codes}
                                        onChange={(event) =>
                                          updateStructureComponentDraft(componentDraft.client_id, {
                                            configured_basis_component_codes: event.target.value,
                                          })
                                        }
                                        placeholder="BASIC, HRA"
                                        disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                      />
                                    </WorkspaceField>
                                    <WorkspaceField label="Expression formula">
                                      <Textarea
                                        value={componentDraft.configured_expression_formula}
                                        onChange={(event) =>
                                          updateStructureComponentDraft(componentDraft.client_id, {
                                            configured_expression_formula: event.target.value,
                                          })
                                        }
                                        placeholder="Optional expression override"
                                        disabled={!workspace.canManageSalaryConfiguration || workspace.isSaving}
                                      />
                                    </WorkspaceField>
                                  </div>
                                </div>
                              ))
                            ) : (
                              <WorkspaceEmptyState
                                title="No component rows yet"
                                copy="Add at least one salary component so the structure can be versioned and assigned."
                              />
                            )}
                          </WorkspaceContent>
                        </WorkspaceSurface>

                        <div className="flex flex-wrap items-center gap-2">
                          <Button
                            type="submit"
                            disabled={!workspace.canManageSalaryConfiguration || !canSubmitSalaryStructure(structureForm) || workspace.isSaving}
                          >
                            {selectedStructure ? 'Create next version' : 'Create structure'}
                          </Button>
                          <Button
                            variant="secondary"
                            onClick={() => {
                              setSelectedStructureId(null)
                              setStructureForm(createDefaultStructureForm())
                              workspace.clearActionMessage()
                            }}
                            disabled={workspace.isSaving}
                          >
                            {selectedStructure ? 'Start new structure' : 'Reset form'}
                          </Button>
                        </div>
                      </form>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </WorkspaceSplit>
              ) : null}

              {activeTab === 'compensation' ? (
                <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.05fr)_minmax(20rem,0.95fr)]">
                  <div className="space-y-3.5">
                    <WorkspaceTableShell>
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Employee</TableHead>
                            <TableHead>Structure</TableHead>
                            <TableHead>Effective</TableHead>
                            <TableHead>Annual CTC</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {data.compensations.map((record) => (
                            <TableRow key={record.id}>
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{record.employee?.full_name ?? 'Employee pending'}</p>
                                  <p className="text-xs text-muted-foreground">{record.employee?.employee_code ?? `Employee ${record.employee_id}`}</p>
                                </div>
                              </TableCell>
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">
                                    {record.salary_structure.code} v{record.salary_structure.version}
                                  </p>
                                  <p className="text-xs text-muted-foreground">{record.revision_reason.replace(/_/g, ' ')}</p>
                                </div>
                              </TableCell>
                              <TableCell>{formatDate(record.effective_from)}</TableCell>
                              <TableCell>{formatCurrency(record.annual_ctc_amount, record.salary_structure.currency)}</TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    </WorkspaceTableShell>

                    <WorkspaceSurface>
                      <WorkspaceContent>
                        <WorkspaceSummaryRow label="Assigned employees" value={String(assignedEmployeeCount)} />
                        <WorkspaceSummaryRow label="Unassigned employees" value={String(unassignedEmployeeCount)} />
                        <WorkspaceSummaryRow
                          label="Latest revision"
                          value={
                            data.compensations[0]
                              ? `${data.compensations[0].salary_structure.code} · ${formatDate(data.compensations[0].revision_date)}`
                              : 'Pending'
                          }
                        />
                      </WorkspaceContent>
                    </WorkspaceSurface>
                  </div>

                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>Assign employee compensation</CardTitle>
                        <CardDescription>
                          Attach the right salary structure to an employee and preserve the revision reason with effective dates.
                        </CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      <form className="space-y-3.5" onSubmit={(event) => void handleCompensationSubmit(event)}>
                        <SelectField
                          label="Employee"
                          value={compensationForm.employee_id}
                          options={[
                            { value: '', label: 'Select employee' },
                            ...data.employees.map((employee) => ({
                              value: String(employee.id),
                              label: `${employee.full_name} · ${employee.employee_code}`,
                            })),
                          ]}
                          onChange={(value) => setCompensationForm((current) => ({ ...current, employee_id: value }))}
                          disabled={!workspace.canManageCompensation || workspace.isSaving}
                        />
                        <SelectField
                          label="Salary structure"
                          value={compensationForm.salary_structure_id}
                          options={[
                            { value: '', label: 'Select structure' },
                            ...data.salaryStructures
                              .filter((structure) => structure.status !== 'superseded')
                              .map((structure) => ({
                                value: String(structure.id),
                                label: `${structure.code} v${structure.version} · ${structure.name ?? 'Unnamed structure'}`,
                              })),
                          ]}
                          onChange={(value) => setCompensationForm((current) => ({ ...current, salary_structure_id: value }))}
                          disabled={!workspace.canManageCompensation || workspace.isSaving}
                        />
                        <SelectField
                          label="Revision reason"
                          value={compensationForm.revision_reason}
                          options={revisionReasonOptions.map(([value, label]) => ({ value, label }))}
                          onChange={(value) =>
                            setCompensationForm((current) => ({
                              ...current,
                              revision_reason: value as CompensationFormState['revision_reason'],
                            }))
                          }
                          disabled={!workspace.canManageCompensation || workspace.isSaving}
                        />
                        <div className="grid gap-3 sm:grid-cols-2">
                          <WorkspaceField label="Effective from">
                            <Input
                              type="date"
                              value={compensationForm.effective_from}
                              onChange={(event) => setCompensationForm((current) => ({ ...current, effective_from: event.target.value }))}
                              disabled={!workspace.canManageCompensation || workspace.isSaving}
                            />
                          </WorkspaceField>
                          <WorkspaceField label="Revision date">
                            <Input
                              type="date"
                              value={compensationForm.revision_date}
                              onChange={(event) => setCompensationForm((current) => ({ ...current, revision_date: event.target.value }))}
                              disabled={!workspace.canManageCompensation || workspace.isSaving}
                            />
                          </WorkspaceField>
                        </div>
                        <WorkspaceField label="Notes">
                          <Textarea
                            value={compensationForm.notes}
                            onChange={(event) => setCompensationForm((current) => ({ ...current, notes: event.target.value }))}
                            placeholder="Explain the compensation change, approval context, or rollout note."
                            disabled={!workspace.canManageCompensation || workspace.isSaving}
                          />
                        </WorkspaceField>
                        <div className="flex flex-wrap items-center gap-2">
                          <Button
                            type="submit"
                            disabled={!workspace.canManageCompensation || !canSubmitCompensation(compensationForm) || workspace.isSaving}
                          >
                            Assign compensation
                          </Button>
                          <Button
                            variant="secondary"
                            onClick={() => {
                              setCompensationForm(createDefaultCompensationForm())
                              workspace.clearActionMessage()
                            }}
                            disabled={workspace.isSaving}
                          >
                            Reset form
                          </Button>
                        </div>
                        {!data.employees.length ? (
                          <WorkspaceEmptyState
                            title="Employee directory is unavailable"
                            copy="This session can still review current compensation assignments, but employee lookup is unavailable until the linked directory scope is accessible."
                          />
                        ) : null}
                      </form>
                    </WorkspaceContent>
                  </WorkspaceSurface>
                </WorkspaceSplit>
              ) : null}
            </>
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )

  async function handleCalendarSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!canSubmitCalendar(calendarForm)) {
      return
    }

    await workspace.saveCalendar(selectedCalendarId, {
      name: calendarForm.name.trim(),
      frequency: calendarForm.frequency,
      timezone: calendarForm.timezone.trim(),
      payroll_day: requiresPayrollDay(calendarForm.frequency) ? Number(calendarForm.payroll_day) : null,
      payroll_weekday: requiresPayrollWeekday(calendarForm.frequency) ? Number(calendarForm.payroll_weekday) : null,
      is_default: calendarForm.is_default === 'yes',
      status: calendarForm.status,
    })

    if (!selectedCalendarId) {
      setCalendarForm(createDefaultCalendarForm())
    }
  }

  async function handlePeriodSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!canSubmitPeriod(periodForm, resolvedPeriodCalendarId)) {
      return
    }

    await workspace.savePeriod({
      payroll_calendar_id: Number(resolvedPeriodCalendarId),
      name: periodForm.name.trim(),
      start_date: periodForm.start_date,
      end_date: periodForm.end_date,
      payroll_date: periodForm.payroll_date,
    })

    setPeriodForm(createDefaultPeriodForm(Number(resolvedPeriodCalendarId)))
  }

  async function handleSalaryComponentSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!canSubmitSalaryComponent(componentForm)) {
      return
    }

    await workspace.saveSalaryComponent(selectedComponentId, {
      code: componentForm.code.trim().toUpperCase(),
      name: componentForm.name.trim(),
      category: componentForm.category,
      calculation_type: componentForm.calculation_type,
      flat_amount: componentForm.calculation_type === 'fixed' ? normalizeNullableNumber(componentForm.flat_amount) : null,
      percentage_value:
        componentForm.calculation_type === 'percentage' ? normalizeNullableNumber(componentForm.percentage_value) : null,
      percentage_basis_component_codes:
        componentForm.calculation_type === 'percentage'
          ? splitCodeList(componentForm.percentage_basis_component_codes)
          : [],
      expression_formula:
        componentForm.calculation_type === 'expression'
          ? normalizeNullableText(componentForm.expression_formula)
          : null,
      is_taxable: componentForm.is_taxable === 'yes',
      is_proratable: componentForm.is_proratable === 'yes',
      display_order: Number(componentForm.display_order || 0),
      status: componentForm.status,
    })

    if (!selectedComponentId) {
      setComponentForm(createDefaultComponentForm())
    }
  }

  async function handleSalaryStructureSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!canSubmitSalaryStructure(structureForm)) {
      return
    }

    await workspace.saveSalaryStructure(selectedStructureId, {
      code: structureForm.code.trim().toUpperCase(),
      name: structureForm.name.trim(),
      currency: structureForm.currency.trim().toUpperCase(),
      country_code: structureForm.country_code.trim().toUpperCase(),
      pay_frequency: structureForm.pay_frequency,
      grade: normalizeNullableText(structureForm.grade),
      band: normalizeNullableText(structureForm.band),
      level: normalizeNullableText(structureForm.level),
      annual_ctc_amount: normalizeRequiredNumber(structureForm.annual_ctc_amount),
      basic_salary_amount: normalizeRequiredNumber(structureForm.basic_salary_amount),
      gross_salary_amount: normalizeRequiredNumber(structureForm.gross_salary_amount),
      net_salary_amount: normalizeRequiredNumber(structureForm.net_salary_amount),
      effective_from: structureForm.effective_from,
      revision_date: structureForm.revision_date,
      status: structureForm.status,
      notes: normalizeNullableText(structureForm.notes),
      components: structureForm.components
        .filter((component) => component.salary_component_id)
        .map((component, index) => ({
          salary_component_id: Number(component.salary_component_id),
          display_order: Number(component.display_order || index + 1),
          configured_amount: normalizeNullableNumber(component.configured_amount),
          configured_percentage: normalizeNullableNumber(component.configured_percentage),
          configured_basis_component_codes: splitCodeList(component.configured_basis_component_codes),
          configured_expression_formula: normalizeNullableText(component.configured_expression_formula),
        })),
    })

    setSelectedStructureId(null)
    setStructureForm(createDefaultStructureForm())
  }

  async function handleCompensationSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!canSubmitCompensation(compensationForm)) {
      return
    }

    await workspace.assignCompensation({
      employee_id: Number(compensationForm.employee_id),
      salary_structure_id: Number(compensationForm.salary_structure_id),
      revision_reason: compensationForm.revision_reason,
      effective_from: compensationForm.effective_from,
      revision_date: compensationForm.revision_date,
      notes: normalizeNullableText(compensationForm.notes),
    })

    setCompensationForm(createDefaultCompensationForm())
  }

  function updateStructureComponentDraft(clientId: string, patch: Partial<SalaryStructureComponentDraft>) {
    setStructureForm((current) => ({
      ...current,
      components: current.components.map((component) =>
        component.client_id === clientId
          ? { ...component, ...patch }
          : component,
      ),
    }))
  }
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <div className="rounded-[1rem] border border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(246,249,253,0.985)_100%)] p-4 shadow-[0_12px_26px_rgba(15,23,42,0.05)]">
      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">{label}</p>
      <p className="mt-2 text-xl font-semibold tracking-tight text-foreground">{value}</p>
      <p className="mt-1 text-sm text-muted-foreground">{caption}</p>
    </div>
  )
}

function createDefaultCalendarForm(): CalendarFormState {
  return {
    name: '',
    frequency: 'monthly',
    timezone: 'Asia/Kolkata',
    payroll_day: '30',
    payroll_weekday: '',
    is_default: 'no',
    status: 'active',
  }
}

function createDefaultPeriodForm(defaultCalendarId: number | null = null): PeriodFormState {
  return {
    payroll_calendar_id: defaultCalendarId ? String(defaultCalendarId) : '',
    name: '',
    start_date: '',
    end_date: '',
    payroll_date: '',
  }
}

function createDefaultComponentForm(): SalaryComponentFormState {
  return {
    code: '',
    name: '',
    category: 'earning',
    calculation_type: 'fixed',
    flat_amount: '',
    percentage_value: '',
    percentage_basis_component_codes: '',
    expression_formula: '',
    is_taxable: 'yes',
    is_proratable: 'yes',
    display_order: '1',
    status: 'active',
  }
}

function createDefaultStructureForm(): SalaryStructureFormState {
  return {
    code: '',
    name: '',
    currency: 'INR',
    country_code: 'IN',
    pay_frequency: 'monthly',
    grade: '',
    band: '',
    level: '',
    annual_ctc_amount: '',
    basic_salary_amount: '',
    gross_salary_amount: '',
    net_salary_amount: '',
    effective_from: '',
    revision_date: '',
    status: 'draft',
    notes: '',
    components: [createStructureComponentDraft(1)],
  }
}

function createDefaultCompensationForm(): CompensationFormState {
  return {
    employee_id: '',
    salary_structure_id: '',
    revision_reason: 'initial_assignment',
    effective_from: '',
    revision_date: '',
    notes: '',
  }
}

function createStructureComponentDraft(displayOrder: number): SalaryStructureComponentDraft {
  return {
    client_id: `component-draft-${displayOrder}-${Math.random().toString(36).slice(2, 9)}`,
    salary_component_id: '',
    display_order: String(displayOrder),
    configured_amount: '',
    configured_percentage: '',
    configured_basis_component_codes: '',
    configured_expression_formula: '',
  }
}

function mapCalendarToFormState(calendar: PayrollCalendarRecord): CalendarFormState {
  return {
    name: calendar.name,
    frequency: calendar.frequency,
    timezone: calendar.timezone,
    payroll_day: calendar.payroll_day ? String(calendar.payroll_day) : '',
    payroll_weekday: calendar.payroll_weekday !== null ? String(calendar.payroll_weekday) : '',
    is_default: calendar.is_default ? 'yes' : 'no',
    status: calendar.status,
  }
}

function mapSalaryComponentToFormState(component: SalaryComponentRecord): SalaryComponentFormState {
  return {
    code: component.code,
    name: component.name,
    category: component.category,
    calculation_type: component.calculation_type,
    flat_amount: component.default_formula_inputs.flat_amount ?? '',
    percentage_value: component.default_formula_inputs.percentage_value ?? '',
    percentage_basis_component_codes: component.default_formula_inputs.percentage_basis_component_codes.join(', '),
    expression_formula: component.default_formula_inputs.expression_formula ?? '',
    is_taxable: component.is_taxable ? 'yes' : 'no',
    is_proratable: component.is_proratable ? 'yes' : 'no',
    display_order: String(component.display_order),
    status: component.status,
  }
}

function mapSalaryStructureToFormState(structure: SalaryStructureRecord): SalaryStructureFormState {
  return {
    code: structure.code,
    name: structure.name ?? '',
    currency: structure.currency,
    country_code: structure.country_code,
    pay_frequency: structure.pay_frequency,
    grade: structure.grade ?? '',
    band: structure.band ?? '',
    level: structure.level ?? '',
    annual_ctc_amount: structure.annual_ctc_amount,
    basic_salary_amount: structure.basic_salary_amount,
    gross_salary_amount: structure.gross_salary_amount,
    net_salary_amount: structure.net_salary_amount,
    effective_from: structure.effective_from,
    revision_date: structure.revision_date,
    status: structure.status === 'superseded' ? 'inactive' : structure.status,
    notes: structure.notes ?? '',
    components: structure.components.length
      ? structure.components.map(mapSalaryStructureComponentDraft)
      : [createStructureComponentDraft(1)],
  }
}

function mapSalaryStructureComponentDraft(component: SalaryStructureComponentRecord): SalaryStructureComponentDraft {
  return {
    client_id: `component-${component.id}`,
    salary_component_id: String(component.salary_component_id),
    display_order: String(component.display_order),
    configured_amount: component.resolved_formula_inputs.flat_amount ?? '',
    configured_percentage: component.resolved_formula_inputs.percentage_value ?? '',
    configured_basis_component_codes: component.resolved_formula_inputs.percentage_basis_component_codes.join(', '),
    configured_expression_formula: component.resolved_formula_inputs.expression_formula ?? '',
  }
}

function requiresPayrollDay(frequency: PayrollCalendarRecord['frequency']) {
  return ['monthly', 'semi_monthly', 'custom'].includes(frequency)
}

function requiresPayrollWeekday(frequency: PayrollCalendarRecord['frequency']) {
  return ['weekly', 'biweekly'].includes(frequency)
}

function canSubmitCalendar(form: CalendarFormState) {
  if (!form.name.trim() || !form.timezone.trim()) {
    return false
  }

  if (requiresPayrollDay(form.frequency) && !form.payroll_day.trim()) {
    return false
  }

  if (requiresPayrollWeekday(form.frequency) && form.payroll_weekday === '') {
    return false
  }

  return true
}

function canSubmitPeriod(form: PeriodFormState, resolvedCalendarId: string) {
  return (
    resolvedCalendarId !== '' &&
    form.name.trim().length > 0 &&
    form.start_date !== '' &&
    form.end_date !== '' &&
    form.payroll_date !== ''
  )
}

function canSubmitSalaryComponent(form: SalaryComponentFormState) {
  if (!form.code.trim() || !form.name.trim()) {
    return false
  }

  if (form.calculation_type === 'fixed') {
    return form.flat_amount.trim().length > 0
  }

  if (form.calculation_type === 'percentage') {
    return form.percentage_value.trim().length > 0 && splitCodeList(form.percentage_basis_component_codes).length > 0
  }

  if (form.calculation_type === 'expression') {
    return form.expression_formula.trim().length > 0
  }

  return true
}

function canSubmitSalaryStructure(form: SalaryStructureFormState) {
  return (
    form.code.trim().length > 0 &&
    form.name.trim().length > 0 &&
    form.currency.trim().length > 0 &&
    form.country_code.trim().length === 2 &&
    form.annual_ctc_amount.trim().length > 0 &&
    form.basic_salary_amount.trim().length > 0 &&
    form.gross_salary_amount.trim().length > 0 &&
    form.net_salary_amount.trim().length > 0 &&
    form.effective_from !== '' &&
    form.revision_date !== '' &&
    form.components.some((component) => component.salary_component_id !== '')
  )
}

function canSubmitCompensation(form: CompensationFormState) {
  return (
    form.employee_id !== '' &&
    form.salary_structure_id !== '' &&
    form.effective_from !== '' &&
    form.revision_date !== ''
  )
}

function normalizeNullableText(value: string) {
  const text = value.trim()
  return text ? text : null
}

function normalizeNullableNumber(value: string) {
  const text = value.trim()
  return text ? text : null
}

function normalizeRequiredNumber(value: string) {
  return value.trim()
}

function splitCodeList(value: string) {
  return value
    .split(',')
    .map((entry) => entry.trim().toUpperCase())
    .filter(Boolean)
}

function describePayrollCalendarRule(calendar: PayrollCalendarRecord) {
  if (requiresPayrollDay(calendar.frequency)) {
    return `Day ${calendar.payroll_day ?? 'pending'} each ${calendar.frequency.replace(/_/g, ' ')} cycle`
  }

  if (requiresPayrollWeekday(calendar.frequency)) {
    const weekdayLabel = payrollWeekdayOptions.find(([value]) => value === String(calendar.payroll_weekday))?.[1] ?? 'Pending'
    return `${weekdayLabel} ${calendar.frequency.replace(/_/g, ' ')} cycle`
  }

  return 'Rule pending'
}

function describeSalaryComponentFormula(component: SalaryComponentRecord) {
  if (component.calculation_type === 'fixed') {
    return `Fixed at ${formatCurrency(component.default_formula_inputs.flat_amount ?? 0)}`
  }

  if (component.calculation_type === 'percentage') {
    return `${component.default_formula_inputs.percentage_value ?? '0'}% of ${component.default_formula_inputs.percentage_basis_component_codes.join(', ') || 'basis components'}`
  }

  return component.default_formula_inputs.expression_formula ?? 'Expression formula pending'
}

function mapPeriodBadgeVariant(status: PayrollPeriodRecord['status']) {
  switch (status) {
    case 'open':
      return 'info'
    case 'prepared':
      return 'warning'
    case 'closed':
      return 'success'
    case 'draft':
    default:
      return 'neutral'
  }
}

function mapStructureBadgeVariant(status: SalaryStructureRecord['status']) {
  switch (status) {
    case 'active':
      return 'success'
    case 'superseded':
      return 'warning'
    case 'inactive':
      return 'neutral'
    case 'draft':
    default:
      return 'info'
  }
}
