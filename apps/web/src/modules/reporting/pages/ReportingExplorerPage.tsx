import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Archive, ArrowRight, Download, Save, Table2 } from 'lucide-react'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceFilters,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { useReportingRouteWorkspace } from './useReportingRouteWorkspace'
import type {
  ReportingApprovedField,
  ReportingDatasetRecord,
  ReportingSavedViewRecord,
  ReportingSavedViewShareScope,
} from '../types'

type SortDirection = 'asc' | 'desc'

function defaultVisibleColumns(dataset: ReportingDatasetRecord | null) {
  if (!dataset) {
    return []
  }

  return dataset.approved_fields.slice(0, 5).map((field) => field.key)
}

function renderValue(field: ReportingApprovedField, value: unknown) {
  if (value === null || value === undefined || value === '') {
    return '—'
  }

  if (field.type === 'boolean') {
    return value ? 'Yes' : 'No'
  }

  if (field.type === 'date' || field.type === 'datetime') {
    const date = new Date(String(value))
    return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString()
  }

  if (field.type === 'currency' && typeof value === 'number') {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      maximumFractionDigits: 0,
    }).format(value)
  }

  return String(value)
}

function shareScopeLabel(scope: ReportingSavedViewShareScope) {
  return scope === 'private' ? 'Private' : scope === 'roles' ? 'Role shared' : 'Company shared'
}

function datasetDomainLabel(dataset: ReportingDatasetRecord) {
  return dataset.domain.replace(/_/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase())
}

function formatFreshness(generatedAt: string | null, expectationMinutes: number | null) {
  if (!generatedAt) {
    return 'Awaiting governed query'
  }

  const generatedDate = new Date(generatedAt)
  if (Number.isNaN(generatedDate.getTime())) {
    return 'Freshness timestamp unavailable'
  }

  return expectationMinutes
    ? `${generatedDate.toLocaleString()} · ${expectationMinutes} minute expectation`
    : generatedDate.toLocaleString()
}

export function ReportingExplorerPage() {
  const workspace = useReportingRouteWorkspace()
  const { snapshot } = useAccessSnapshot()
  const datasets = workspace.data?.datasets ?? []
  const [selectedDatasetKey, setSelectedDatasetKey] = useState('')
  const [selectedViewId, setSelectedViewId] = useState<number | null>(null)
  const [filterValues, setFilterValues] = useState<Record<string, string>>({})
  const [filterOperators, setFilterOperators] = useState<Record<string, string>>({})
  const [sortBy, setSortBy] = useState('')
  const [sortDirection, setSortDirection] = useState<SortDirection>('asc')
  const [drilldownPath, setDrilldownPath] = useState('')
  const [page, setPage] = useState(1)
  const [visibleColumns, setVisibleColumns] = useState<string[]>([])
  const [newViewName, setNewViewName] = useState('')
  const [newViewDescription, setNewViewDescription] = useState('')
  const [newViewShareScope, setNewViewShareScope] = useState<ReportingSavedViewShareScope>('private')
  const [newViewRoleNames, setNewViewRoleNames] = useState('')

  const resolvedDatasetKey = selectedDatasetKey || datasets[0]?.key || ''
  const selectedDataset = datasets.find((dataset) => dataset.key === resolvedDatasetKey) ?? null
  const resolvedSortBy = sortBy || selectedDataset?.approved_fields[0]?.key || ''
  const canManageSavedViews =
    snapshot?.user.permissions.some((permission) =>
      ['reporting.manage', 'reporting.certify'].includes(permission),
    ) ?? false

  const savedViews = useMemo(
    () =>
      (workspace.data?.savedViews ?? []).filter((view) =>
        selectedDataset ? view.dataset?.key === selectedDataset.key : true,
      ),
    [selectedDataset, workspace.data?.savedViews],
  )

  const queryInput = useMemo(
    () => ({
      datasetKey: selectedDataset?.key ?? '',
      filters: Object.fromEntries(
        Object.entries(filterValues).filter(([, value]) => value.trim() !== ''),
      ),
      filterOperators,
      sortBy: resolvedSortBy || null,
      sortDirection,
      drilldownPath: drilldownPath || null,
      page,
      perPage: 25,
    }),
    [drilldownPath, filterOperators, filterValues, page, resolvedSortBy, selectedDataset?.key, sortDirection],
  )

  const reportQuery = useQuery({
    queryKey: ['reporting-explorer', workspace.source, queryInput],
    queryFn: () => workspace.actions.queryDataset(queryInput),
    enabled: workspace.canViewReporting && Boolean(selectedDataset?.key),
  })

  const result = reportQuery.data
  const activeColumns = useMemo(() => {
    if (!selectedDataset) {
      return []
    }

    const preferredKeys = visibleColumns.length ? visibleColumns : defaultVisibleColumns(selectedDataset)
    return selectedDataset.approved_fields.filter((field) => preferredKeys.includes(field.key))
  }, [selectedDataset, visibleColumns])

  function handleDatasetChange(nextDatasetKey: string) {
    const nextDataset = datasets.find((dataset) => dataset.key === nextDatasetKey) ?? null

    setSelectedDatasetKey(nextDatasetKey)
    setSelectedViewId(null)
    setFilterValues({})
    setFilterOperators({})
    setSortBy(nextDataset?.approved_fields[0]?.key ?? '')
    setSortDirection('asc')
    setDrilldownPath('')
    setPage(1)
    setVisibleColumns(defaultVisibleColumns(nextDataset))
  }

  function applySavedView(view: ReportingSavedViewRecord) {
    const nextDataset = datasets.find((dataset) => dataset.key === view.dataset?.key) ?? null

    setSelectedViewId(view.id)
    setSelectedDatasetKey(view.dataset?.key ?? '')
    setFilterValues(
      Object.fromEntries(
        Object.entries(view.query.filters).map(([key, value]) => [key, String(value ?? '')]),
      ),
    )
    setFilterOperators(view.query.filter_operators)
    setSortBy(view.query.sort_by ?? nextDataset?.approved_fields[0]?.key ?? '')
    setSortDirection((view.query.sort_direction ?? 'asc') as SortDirection)
    setDrilldownPath(view.query.drilldown_path ?? '')
    setPage(1)
    setVisibleColumns(
      Array.isArray(view.presentation_preferences.visible_columns)
        ? view.presentation_preferences.visible_columns.filter(
            (value): value is string => typeof value === 'string',
          )
        : defaultVisibleColumns(nextDataset),
    )
  }

  async function handleSaveView() {
    if (!selectedDataset || !newViewName.trim()) {
      return
    }

    await workspace.actions.createSavedView({
      dataset_key: selectedDataset.key,
      name: newViewName.trim(),
      description: newViewDescription.trim() || null,
      share_scope: newViewShareScope,
      shared_role_names:
        newViewShareScope === 'roles'
          ? newViewRoleNames
              .split(',')
              .map((value) => value.trim())
              .filter(Boolean)
          : [],
      filters: queryInput.filters,
      filter_operators: filterOperators,
      sort_by: resolvedSortBy || null,
      sort_direction: sortDirection,
      drilldown_path: drilldownPath || null,
      presentation_preferences: {
        visible_columns: activeColumns.map((field) => field.key),
      },
    })

    setNewViewName('')
    setNewViewDescription('')
    setNewViewShareScope('private')
    setNewViewRoleNames('')
  }

  async function handleRequestExport(format: 'csv' | 'json') {
    if (!selectedDataset) {
      return
    }

    await workspace.actions.requestExport({
      dataset_key: selectedDataset.key,
      format,
      execution_mode: format === 'csv' ? 'auto' : 'sync',
      filters: queryInput.filters,
      filter_operators: filterOperators,
      sort_by: resolvedSortBy || null,
      sort_direction: sortDirection,
      drilldown_path: drilldownPath || null,
    })
  }

  async function handleArchiveSavedView(view: ReportingSavedViewRecord) {
    if (selectedViewId === view.id) {
      setSelectedViewId(null)
    }

    await workspace.actions.archiveSavedView(view.id)
  }

  function toggleVisibleColumn(fieldKey: string) {
    setVisibleColumns((current) =>
      current.includes(fieldKey)
        ? current.filter((entry) => entry !== fieldKey)
        : [...current, fieldKey],
    )
  }

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title="Loading report explorer"
        copy="Resolving governed datasets, saved views, and export-ready reporting posture."
      />
    )
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title="Report explorer unavailable" copy={workspace.error.message} />
  }

  if (!workspace.canViewReporting) {
    return (
      <WorkspaceEmptyState
        title="Report explorer unavailable"
        copy="This session does not currently resolve to governed reporting visibility."
      />
    )
  }

  if (!selectedDataset) {
    return (
      <WorkspaceEmptyState
        title="No report datasets are in scope"
        copy="This reporting session is active, but no governed datasets are available for the resolved persona and domain permissions."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Reporting"
          title="Governed Report Explorer"
          description="Browse approved datasets, apply governed filters, consume saved views, and request controlled exports without drifting from the certified reporting contract."
          badge={
            <Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>
              {workspace.source === 'demo' ? 'Demo explorer' : 'Live explorer'}
            </Badge>
          }
          context={[
            `${datasets.length} dataset${datasets.length === 1 ? '' : 's'} visible`,
            result ? `${result.meta.total} row${result.meta.total === 1 ? '' : 's'} in current result` : 'Awaiting query',
          ]}
          actions={
            <WorkspaceActionsRow>
              <Button asChild size="sm" variant="secondary">
                <Link to="/reporting/exports">
                  Open export queue
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
              <Button asChild size="sm" variant="secondary">
                <Link to="/reporting/subscriptions">Subscription center</Link>
              </Button>
            </WorkspaceActionsRow>
          }
        />

        <WorkspaceContent className="space-y-4">
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <WorkspaceFilters>
                <SelectField
                  label="Dataset"
                  value={selectedDataset.key}
                  compact
                  options={datasets.map(
                    (dataset) =>
                      [dataset.key, `${dataset.name} · ${datasetDomainLabel(dataset)}`] as [string, string],
                  )}
                  onChange={handleDatasetChange}
                />
                <SelectField
                  label="Sort by"
                  value={resolvedSortBy}
                  compact
                  options={selectedDataset.approved_fields.map((field) => [field.key, field.label] as [string, string])}
                  onChange={(value) => {
                    setSortBy(value)
                    setPage(1)
                  }}
                />
                <SelectField
                  label="Direction"
                  value={sortDirection}
                  compact
                  options={[
                    ['asc', 'Ascending'],
                    ['desc', 'Descending'],
                  ]}
                  onChange={(value) => {
                    setSortDirection((value || 'asc') as SortDirection)
                    setPage(1)
                  }}
                />
                <SelectField
                  label="Drilldown path"
                  value={drilldownPath}
                  compact
                  options={[
                    ['', 'All approved drilldowns'],
                    ...selectedDataset.drilldown_paths.map((path) => [path.key, path.label] as [string, string]),
                  ]}
                  onChange={(value) => {
                    setDrilldownPath(value)
                    setPage(1)
                  }}
                />
              </WorkspaceFilters>
            </WorkspaceToolbarRow>

            <WorkspaceToolbarRow>
              <WorkspaceFilters>
                {selectedDataset.approved_filters.map((filter) => (
                  <div key={filter.key} className="flex min-w-[16rem] flex-1 gap-2">
                    <SelectField
                      label={`${filter.label} operator`}
                      value={filterOperators[filter.key] ?? filter.operators[0] ?? 'eq'}
                      compact
                      options={filter.operators.map(
                        (operator) => [operator, operator.replace(/_/g, ' ')] as [string, string],
                      )}
                      onChange={(value) =>
                        setFilterOperators((current) => ({
                          ...current,
                          [filter.key]: value || 'eq',
                        }))
                      }
                    />
                    <WorkspaceField label={filter.label} compact>
                      <Input
                        value={filterValues[filter.key] ?? ''}
                        placeholder={filter.required ? 'Required' : 'Optional'}
                        onChange={(event) => {
                          setFilterValues((current) => ({
                            ...current,
                            [filter.key]: event.target.value,
                          }))
                          setPage(1)
                        }}
                      />
                    </WorkspaceField>
                  </div>
                ))}
              </WorkspaceFilters>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>

          {workspace.pendingActionLabel ? (
            <div className="rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm text-foreground">
              {workspace.pendingActionLabel}…
            </div>
          ) : null}
          {workspace.lastActionMessage ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--success)_22%,white)] bg-[color-mix(in_srgb,var(--success)_10%,white)] px-3 py-2 text-sm text-success">
              {workspace.lastActionMessage}
            </div>
          ) : null}
          {workspace.actionError ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--danger)_22%,white)] bg-[color-mix(in_srgb,var(--danger)_10%,white)] px-3 py-2 text-sm text-destructive">
              {workspace.actionError}
            </div>
          ) : null}

          <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.3fr)_minmax(22rem,0.7fr)]">
            <div className="space-y-4">
              <div className="flex flex-wrap items-center gap-2">
                <Badge variant="info">{datasetDomainLabel(selectedDataset)}</Badge>
                <Badge
                  variant={
                    selectedDataset.governance.certification_status === 'certified'
                      ? 'success'
                      : 'warning'
                  }
                >
                  {selectedDataset.governance.certification_status}
                </Badge>
                {result?.visibility.masked_field_keys.length ? (
                  <Badge variant="warning">
                    {result.visibility.masked_field_keys.length} masked field
                    {result.visibility.masked_field_keys.length === 1 ? '' : 's'}
                  </Badge>
                ) : null}
                {result?.visibility.hidden_field_keys.length ? (
                  <Badge variant="danger">
                    {result.visibility.hidden_field_keys.length} hidden field
                    {result.visibility.hidden_field_keys.length === 1 ? '' : 's'}
                  </Badge>
                ) : null}
              </div>

              <WorkspaceTableShell>
                {reportQuery.isLoading ? (
                  <div className="p-6 text-sm text-muted-foreground">
                    Running the governed report query for the selected dataset and filters.
                  </div>
                ) : reportQuery.error ? (
                  <div className="p-6 text-sm text-destructive">
                    {reportQuery.error instanceof Error
                      ? reportQuery.error.message
                      : 'The report query could not be completed.'}
                  </div>
                ) : result && result.items.length ? (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        {activeColumns.map((field) => (
                          <TableHead key={field.key}>{field.label}</TableHead>
                        ))}
                        <TableHead>Drilldowns</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {result.items.map((row, index) => (
                        <TableRow key={`${selectedDataset.key}-${index}`}>
                          {activeColumns.map((field) => (
                            <TableCell key={field.key}>{renderValue(field, row[field.key])}</TableCell>
                          ))}
                          <TableCell>
                            {row.drilldowns.length ? (
                              <div className="flex flex-wrap gap-1.5">
                                {row.drilldowns.map((drilldown) => (
                                  <Badge key={drilldown.key} variant="subtle">
                                    {drilldown.label}
                                  </Badge>
                                ))}
                              </div>
                            ) : (
                              '—'
                            )}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                ) : (
                  <WorkspaceEmptyState
                    title="No rows match the current filters"
                    copy="Adjust the approved filters, switch to another saved view, or pick a different dataset to inspect a new governed result set."
                    className="m-4"
                  />
                )}
              </WorkspaceTableShell>

              <div className="flex flex-wrap items-center justify-between gap-3 rounded-[1rem] border border-line/80 bg-white/90 px-4 py-3 shadow-[0_12px_24px_rgba(15,23,42,0.04)]">
                <div className="text-sm text-muted-foreground">
                  {result
                    ? `Page ${result.meta.page} of ${result.meta.last_page} · ${result.meta.total} total row${result.meta.total === 1 ? '' : 's'}`
                    : 'Run a governed query to inspect result pagination.'}
                </div>
                <WorkspaceActionsRow>
                  <Button
                    size="xs"
                    variant="secondary"
                    onClick={() => setPage((current) => Math.max(1, current - 1))}
                    disabled={!result || page <= 1}
                  >
                    Previous
                  </Button>
                  <Button
                    size="xs"
                    variant="secondary"
                    onClick={() =>
                      setPage((current) =>
                        result ? Math.min(result.meta.last_page, current + 1) : current,
                      )
                    }
                    disabled={!result || page >= result.meta.last_page}
                  >
                    Next
                  </Button>
                </WorkspaceActionsRow>
              </div>
            </div>

            <div className="space-y-3.5">
              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-3">
                  <div>
                    <h2 className="text-base font-semibold text-foreground">Dataset posture</h2>
                    <p className="text-sm text-muted-foreground">
                      Current certification, freshness, and filter posture for the selected governed
                      dataset.
                    </p>
                  </div>
                  <Table2 className="h-5 w-5 text-muted-foreground" />
                </div>
                <div className="mt-4 space-y-1.5">
                  <WorkspaceSummaryRow label="Dataset" value={selectedDataset.name} />
                  <WorkspaceSummaryRow label="Domain" value={datasetDomainLabel(selectedDataset)} />
                  <WorkspaceSummaryRow
                    label="Certification"
                    value={selectedDataset.governance.certification_status}
                  />
                  <WorkspaceSummaryRow
                    label="Freshness"
                    value={formatFreshness(result?.freshness.generated_at ?? null, result?.freshness.expectation_minutes ?? selectedDataset.freshness_expectation_minutes)}
                  />
                  <WorkspaceSummaryRow label="Saved views" value={savedViews.length} />
                  <WorkspaceSummaryRow
                    label="Masked fields"
                    value={result?.visibility.masked_field_keys.join(', ') || 'None'}
                  />
                </div>
                <WorkspaceActionsRow className="mt-4">
                  <Button size="xs" variant="secondary" onClick={() => handleRequestExport('csv')}>
                    <Download className="h-3.5 w-3.5" />
                    Export CSV
                  </Button>
                  <Button size="xs" variant="secondary" onClick={() => handleRequestExport('json')}>
                    <Download className="h-3.5 w-3.5" />
                    Export JSON
                  </Button>
                </WorkspaceActionsRow>
              </div>

              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="space-y-1">
                  <h2 className="text-base font-semibold text-foreground">Saved views</h2>
                  <p className="text-sm text-muted-foreground">
                    Reuse approved filter state and presentation preferences without bypassing
                    current masking or permission rules.
                  </p>
                </div>
                <div className="mt-4 space-y-3">
                  {savedViews.length ? (
                    savedViews.map((view) => {
                      const canArchive =
                        canManageSavedViews || (snapshot?.user.id ? view.owner?.id === snapshot.user.id : false)

                      return (
                        <div
                          key={view.id}
                          className="rounded-2xl border border-line/70 bg-panel/80 px-4 py-3"
                        >
                          <div className="flex items-start justify-between gap-2">
                            <div className="space-y-1">
                              <div className="flex flex-wrap items-center gap-2">
                                <p className="font-medium text-foreground">{view.name}</p>
                                <Badge variant={selectedViewId === view.id ? 'info' : 'neutral'}>
                                  {shareScopeLabel(view.share.scope)}
                                </Badge>
                                <Badge variant={view.validation.status === 'valid' ? 'success' : 'warning'}>
                                  {view.validation.status}
                                </Badge>
                              </div>
                              <p className="text-xs text-muted-foreground">
                                {view.description ?? 'No description recorded.'}
                              </p>
                              <p className="text-xs text-muted-foreground">
                                Owner {view.owner?.name ?? 'Unknown'} · {Object.keys(view.query.filters).length}{' '}
                                filter{Object.keys(view.query.filters).length === 1 ? '' : 's'}
                                {view.share.shared_role_names.length
                                  ? ` · shared with ${view.share.shared_role_names.join(', ')}`
                                  : ''}
                              </p>
                            </div>
                            <WorkspaceActionsRow>
                              <Button size="xs" variant="secondary" onClick={() => applySavedView(view)}>
                                Apply
                              </Button>
                              {canArchive ? (
                                <Button
                                  size="xs"
                                  variant="ghost"
                                  onClick={() => handleArchiveSavedView(view)}
                                >
                                  <Archive className="h-3.5 w-3.5" />
                                  Archive
                                </Button>
                              ) : null}
                            </WorkspaceActionsRow>
                          </div>
                          {view.validation.reason ? (
                            <p className="mt-2 text-xs text-warning">{view.validation.reason}</p>
                          ) : null}
                        </div>
                      )
                    })
                  ) : (
                    <WorkspaceEmptyState
                      title="No saved views in scope"
                      copy="Save the current filter posture to create a reusable explorer entry for this dataset."
                    />
                  )}
                </div>
              </div>

              <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="space-y-1">
                  <h2 className="text-base font-semibold text-foreground">Save current view</h2>
                  <p className="text-sm text-muted-foreground">
                    Persist the current governed filters and visible columns as a reusable reporting
                    view.
                  </p>
                </div>
                <div className="mt-4 space-y-3">
                  <WorkspaceField label="View name">
                    <Input
                      value={newViewName}
                      onChange={(event) => setNewViewName(event.target.value)}
                      placeholder="Weekly workforce lens"
                    />
                  </WorkspaceField>
                  <WorkspaceField label="Description">
                    <Input
                      value={newViewDescription}
                      onChange={(event) => setNewViewDescription(event.target.value)}
                      placeholder="Who should use this and why"
                    />
                  </WorkspaceField>
                  <SelectField
                    label="Share scope"
                    value={newViewShareScope}
                    options={[
                      ['private', 'Private'],
                      ['roles', 'Roles'],
                      ['company', 'Company'],
                    ]}
                    onChange={(value) =>
                      setNewViewShareScope((value || 'private') as ReportingSavedViewShareScope)
                    }
                  />
                  {newViewShareScope === 'roles' ? (
                    <WorkspaceField label="Role names">
                      <Input
                        value={newViewRoleNames}
                        onChange={(event) => setNewViewRoleNames(event.target.value)}
                        placeholder="manager, recruiter"
                      />
                    </WorkspaceField>
                  ) : null}
                  <div className="space-y-2">
                    <p className="text-sm font-medium text-foreground">Visible columns</p>
                    <div className="flex flex-wrap gap-2">
                      {selectedDataset.approved_fields.map((field) => (
                        <button
                          key={field.key}
                          type="button"
                          className={`rounded-lg border px-2.5 py-1 text-xs font-medium transition ${
                            activeColumns.some((entry) => entry.key === field.key)
                              ? 'border-[#1a2432] bg-[linear-gradient(180deg,#253142_0%,#141c27_100%)] text-white'
                              : 'border-line/80 bg-white/80 text-foreground'
                          }`}
                          onClick={() => toggleVisibleColumn(field.key)}
                        >
                          {field.label}
                        </button>
                      ))}
                    </div>
                  </div>
                  <Button onClick={handleSaveView} disabled={!newViewName.trim()}>
                    <Save className="h-4 w-4" />
                    Save current view
                  </Button>
                </div>
              </div>
            </div>
          </WorkspaceSplit>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
