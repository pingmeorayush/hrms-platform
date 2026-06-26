import { useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  BadgeCheck,
  Building2,
  MapPin,
  ShieldCheck,
  WalletCards,
} from 'lucide-react'
import { useShellFavorites } from '../../../app/shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../../../app/shell/recent'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardTitle } from '../../../shared/ui/card'
import {
  CommandCenterActivityItem,
  CommandCenterActivityList,
  CommandCenterAttentionItem,
  CommandCenterAttentionStrip,
  CommandCenterInsightCard,
  CommandCenterInsightGrid,
  CommandCenterLayout,
  CommandCenterMain,
  CommandCenterMetricCard,
  CommandCenterMetricGrid,
  CommandCenterPanel,
  CommandCenterRail,
} from '../../../shared/ui/command-center'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import type {
  LocationRecord,
  OrganizationMasterRecord,
} from '../types'
import { useOrganizationRouteWorkspace } from './useOrganizationRouteWorkspace'

type OrganizationOverviewTab = 'companyProfile' | 'structure' | 'locations' | 'costCenters'
type MetricCardTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'
const emptyOrganizationRecords: OrganizationMasterRecord[] = []
const emptyLocationRecords: LocationRecord[] = []

type StructureRow = {
  id: string
  type: 'Department' | 'Designation'
  record: OrganizationMasterRecord
}

export function OrganizationOverviewPage() {
  const workspace = useOrganizationRouteWorkspace()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const { recentItems } = useShellRecent()
  const { data, canManage, source, isLoading, error } = workspace
  const [activeTab, setActiveTab] = useState<OrganizationOverviewTab>('companyProfile')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search)
  const departments = data?.departments ?? emptyOrganizationRecords
  const designations = data?.designations ?? emptyOrganizationRecords
  const locations = data?.locations ?? emptyLocationRecords
  const costCenters = data?.costCenters ?? emptyOrganizationRecords
  const activeDepartments = useMemo(
    () => departments.filter((record) => record.status === 'active'),
    [departments],
  )
  const inactiveDepartments = useMemo(
    () => departments.filter((record) => record.status !== 'active'),
    [departments],
  )
  const activeDesignations = useMemo(
    () => designations.filter((record) => record.status === 'active'),
    [designations],
  )
  const inactiveDesignations = useMemo(
    () => designations.filter((record) => record.status !== 'active'),
    [designations],
  )
  const activeLocations = useMemo(
    () => locations.filter((record) => record.status === 'active'),
    [locations],
  )
  const inactiveLocations = useMemo(
    () => locations.filter((record) => record.status !== 'active'),
    [locations],
  )
  const activeCostCenters = useMemo(
    () => costCenters.filter((record) => record.status === 'active'),
    [costCenters],
  )
  const inactiveCostCenters = useMemo(
    () => costCenters.filter((record) => record.status !== 'active'),
    [costCenters],
  )
  const inactiveCount =
    inactiveDepartments.length + inactiveDesignations.length + inactiveLocations.length + inactiveCostCenters.length

  const structureRows = useMemo<StructureRow[]>(
    () =>
      [
        ...departments.map((record) => ({ id: `department-${record.id}`, type: 'Department' as const, record })),
        ...designations.map((record) => ({ id: `designation-${record.id}`, type: 'Designation' as const, record })),
      ].sort((left, right) => left.record.name.localeCompare(right.record.name)),
    [departments, designations],
  )

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: MetricCardTone
    valueSize?: 'stat' | 'compact' | 'long'
  }> = data
    ? [
        {
          label: 'Active departments',
          value: String(activeDepartments.length),
          delta: `${inactiveDepartments.length} inactive department record(s)`,
          icon: <Building2 className="h-4 w-4" />,
          tone: inactiveDepartments.length ? 'warning' : 'success',
        },
        {
          label: 'Active designations',
          value: String(activeDesignations.length),
          delta: `${inactiveDesignations.length} inactive designation record(s)`,
          icon: <BadgeCheck className="h-4 w-4" />,
          tone: inactiveDesignations.length ? 'warning' : 'success',
        },
        {
          label: 'Active locations',
          value: String(activeLocations.length),
          delta: `${new Set(activeLocations.map((record) => record.timezone)).size} timezone pattern(s) in use`,
          icon: <MapPin className="h-4 w-4" />,
          tone: activeLocations.length ? 'info' : 'warning',
        },
        {
          label: 'Active cost centers',
          value: String(activeCostCenters.length),
          delta: `${inactiveCostCenters.length} inactive cost center record(s)`,
          icon: <WalletCards className="h-4 w-4" />,
          tone: inactiveCostCenters.length ? 'warning' : 'success',
        },
        {
          label: 'Tenant defaults',
          value: `${data.companyProfile.currency} · ${data.companyProfile.timezone}`,
          delta: `${data.companyProfile.subscription_plan ?? 'plan pending'} plan`,
          icon: <ShieldCheck className="h-4 w-4" />,
          tone: 'info',
          valueSize: 'compact',
        },
        {
          label: 'Master-data warnings',
          value: String(inactiveCount),
          delta: inactiveCount ? 'Inactive records need verification' : 'No inactive master records detected',
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: inactiveCount ? 'warning' : 'success',
        },
      ]
    : []

  const attentionItems = useMemo(() => {
    if (!data) {
      return []
    }

    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      tone: 'warning' | 'danger' | 'success' | 'info'
      icon: ReactNode
    }> = []

    const firstInactiveDepartment = inactiveDepartments[0]
    if (firstInactiveDepartment) {
      items.push({
        id: 'inactive-department',
        path: '/admin/organization/structure',
        title: `${inactiveDepartments.length} inactive department record(s) need review`,
        detail: `${firstInactiveDepartment.name} is currently inactive in structure setup.`,
        meta: 'Review structure before employee or attendance inheritance drifts.',
        tone: 'warning',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    const firstInactiveLocation = inactiveLocations[0]
    if (firstInactiveLocation) {
      items.push({
        id: 'inactive-location',
        path: '/admin/organization/locations',
        title: `${inactiveLocations.length} location record(s) are inactive`,
        detail: `${firstInactiveLocation.name} is not available for new operational assignments.`,
        meta: 'Check timezone and address coverage before disabling locations.',
        tone: 'warning',
        icon: <MapPin className="h-4 w-4" />,
      })
    }

    const firstInactiveCostCenter = inactiveCostCenters[0]
    if (firstInactiveCostCenter) {
      items.push({
        id: 'inactive-cost-center',
        path: '/admin/organization/cost-centers',
        title: `${inactiveCostCenters.length} cost center record(s) are inactive`,
        detail: `${firstInactiveCostCenter.name} may still be referenced downstream.`,
        meta: 'Audit finance-linked mappings before removing cost-center coverage.',
        tone: 'info',
        icon: <WalletCards className="h-4 w-4" />,
      })
    }

    items.push({
      id: 'tenant-defaults',
      path: '/admin/organization/company-profile',
      title: `${data.companyProfile.name} defaults are active`,
      detail: `${data.companyProfile.timezone} · ${data.companyProfile.currency} · ${data.companyProfile.subscription_plan ?? 'plan pending'}`,
      meta: source === 'live' ? 'Live organization workspace' : 'Demo organization workspace',
      tone: 'success',
      icon: <ShieldCheck className="h-4 w-4" />,
    })

    return items.slice(0, 4)
  }, [data, inactiveCostCenters, inactiveDepartments, inactiveLocations, source])

  const fallbackActivityItems = useMemo(() => {
    if (!data) {
      return []
    }

    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      timestamp: string | null
      tone: 'neutral' | 'info' | 'success' | 'warning'
    }> = [
      {
        id: 'company-profile',
        title: `${data.companyProfile.name} profile updated`,
        detail: `${data.companyProfile.subscription_plan ?? 'Plan pending'} · ${data.companyProfile.timezone}`,
        meta: relativeTime(data.companyProfile.updated_at ?? data.companyProfile.created_at),
        timestamp: data.companyProfile.updated_at ?? data.companyProfile.created_at,
        tone: 'info',
      },
      ...data.departments.map((record) => ({
        id: `department-${record.id}`,
        title: `${record.name} department updated`,
        detail: record.description ?? 'Department record maintained in the structure registry.',
        meta: relativeTime(record.updated_at ?? record.created_at),
        timestamp: record.updated_at ?? record.created_at,
        tone: record.status === 'active' ? ('success' as const) : ('warning' as const),
      })),
      ...data.locations.map((record) => ({
        id: `location-${record.id}`,
        title: `${record.name} location updated`,
        detail: `${record.timezone} · ${record.currency}`,
        meta: relativeTime(record.updated_at ?? record.created_at),
        timestamp: record.updated_at ?? record.created_at,
        tone: record.status === 'active' ? ('neutral' as const) : ('warning' as const),
      })),
      ...data.costCenters.map((record) => ({
        id: `cost-center-${record.id}`,
        title: `${record.name} cost center updated`,
        detail: record.description ?? 'Cost center registry record updated.',
        meta: relativeTime(record.updated_at ?? record.created_at),
        timestamp: record.updated_at ?? record.created_at,
        tone: record.status === 'active' ? ('neutral' as const) : ('warning' as const),
      })),
    ]

    return items
      .filter((item) => item.timestamp)
      .sort((left, right) => (right.timestamp ?? '').localeCompare(left.timestamp ?? ''))
      .slice(0, 6)
  }, [data])

  const activityItems = useMemo(() => {
    const recentActivity = getModuleRecentActivity('organization', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [fallbackActivityItems, recentItems])

  const filteredCompanyRows = useMemo(() => {
    if (!data) {
      return []
    }

    const rows = [
      {
        field: 'Company name',
        value: data.companyProfile.name,
        detail: 'Primary tenant identity across the admin console.',
      },
      {
        field: 'Tenant slug',
        value: data.companyProfile.slug,
        detail: 'System-safe identifier inherited by downstream records.',
      },
      {
        field: 'Subscription plan',
        value: data.companyProfile.subscription_plan ?? 'Plan not set',
        detail: 'Commercial plan context for the current tenant.',
      },
      {
        field: 'Timezone',
        value: data.companyProfile.timezone,
        detail: 'Default scheduling and attendance timezone.',
      },
      {
        field: 'Currency',
        value: data.companyProfile.currency,
        detail: 'Default financial display and payout currency.',
      },
    ]

    const query = deferredSearch.trim().toLowerCase()
    if (!query) {
      return rows
    }

    return rows.filter((row) => [row.field, row.value, row.detail].join(' ').toLowerCase().includes(query))
  }, [data, deferredSearch])

  const filteredStructureRows = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()
    if (!query) {
      return structureRows
    }

    return structureRows.filter(({ type, record }) =>
      [type, record.name, record.code, record.description ?? '', record.status].join(' ').toLowerCase().includes(query),
    )
  }, [deferredSearch, structureRows])

  const filteredLocations = useMemo(() => {
    const rows = data?.locations ?? []
    const query = deferredSearch.trim().toLowerCase()
    if (!query) {
      return rows
    }

    return rows.filter((record) =>
      [
        record.name,
        record.code,
        record.timezone,
        record.currency,
        record.city ?? '',
        record.state ?? '',
        record.country ?? '',
        record.status,
      ]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [data?.locations, deferredSearch])

  const filteredCostCenters = useMemo(() => {
    const rows = data?.costCenters ?? []
    const query = deferredSearch.trim().toLowerCase()
    if (!query) {
      return rows
    }

    return rows.filter((record) =>
      [record.name, record.code, record.description ?? '', record.status].join(' ').toLowerCase().includes(query),
    )
  }, [data?.costCenters, deferredSearch])

  const collectionCount =
    activeTab === 'companyProfile'
      ? filteredCompanyRows.length
      : activeTab === 'structure'
        ? filteredStructureRows.length
        : activeTab === 'locations'
          ? filteredLocations.length
          : filteredCostCenters.length

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading organization operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}
      {!data && !isLoading && !error ? <p className="workspace-muted">No organization workspace is available yet.</p> : null}

      {data ? (
        <WorkspaceSurface>
          <WorkspaceHeroHeader
            moduleLabel="Organization"
            title="Organization Operations Center"
            description="Monitor structure health, location coverage, and master-data posture before downstream workflows drift."
            badge={<Badge variant={source === 'live' ? 'info' : 'warning'}>{source === 'live' ? 'Live contract' : 'Demo contract'}</Badge>}
            context={[canManage ? 'Master-data controls live' : 'Coverage workspace', 'Structure and location posture']}
            actions={
              <>
                <Button asChild size="xs" variant="secondary">
                  <Link to="/admin/organization/structure">Open structure</Link>
                </Button>
                <Button asChild size="xs" variant="primary">
                  <Link to={canManage ? '/admin/organization/company-profile' : '/admin/organization/locations'}>
                    {canManage ? 'Open company profile' : 'Open locations'}
                  </Link>
                </Button>
              </>
            }
          />
          <WorkspaceContent className="space-y-4">
            <CommandCenterMetricGrid>
              {metricCards.map((card) => (
                <CommandCenterMetricCard
                  key={card.label}
                  label={card.label}
                  value={card.value}
                  delta={card.delta}
                  icon={card.icon}
                  tone={card.tone}
                  valueSize={card.valueSize}
                />
              ))}
            </CommandCenterMetricGrid>

            <CommandCenterLayout>
              <CommandCenterMain>
                <CommandCenterAttentionStrip
                  title="Needs attention"
                  action={
                    <Button asChild size="xs" variant="ghost">
                      <Link to="/admin/organization/structure">View structure</Link>
                    </Button>
                  }
                >
                  {attentionItems.map((item) => (
                    <CommandCenterAttentionItem
                      key={item.id}
                      title={item.title}
                      detail={item.detail}
                      meta={item.meta}
                      tone={item.tone}
                      icon={item.icon}
                      to={item.path}
                      pinned={item.path ? isFavorite(item.path) : false}
                      onTogglePinned={
                        item.path
                          ? () =>
                              toggleFavorite({
                                path: item.path!,
                                label: item.title,
                                icon: 'organization',
                                description: item.detail,
                                meta: item.meta,
                              })
                          : undefined
                      }
                      pinLabel={item.path ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}` : undefined}
                    />
                  ))}
                </CommandCenterAttentionStrip>

                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div>
                      <CardTitle>Organization workspace</CardTitle>
                    </div>
                    <Badge variant="subtle">{collectionCount} record(s) in view</Badge>
                  </WorkspaceHeader>
                  <WorkspaceContent className="space-y-4">
                    <ConsoleToolbar>
                      <ConsoleToolbarRow>
                        <WorkspaceTabs role="tablist" aria-label="Organization operations collections">
                          {organizationOverviewTabs.map((tab) => (
                            <WorkspaceTabButton
                              key={tab.id}
                              type="button"
                              role="tab"
                              active={activeTab === tab.id}
                              aria-selected={activeTab === tab.id}
                              onClick={() => setActiveTab(tab.id)}
                            >
                              {tab.label}
                            </WorkspaceTabButton>
                          ))}
                        </WorkspaceTabs>
                        <div className="flex flex-wrap items-center gap-2">
                          <Badge variant="subtle">
                            {activeTab === 'companyProfile'
                              ? 'Tenant defaults'
                              : activeTab === 'structure'
                                ? 'Master structure'
                                : activeTab === 'locations'
                                  ? 'Location coverage'
                                  : 'Finance mapping'}
                          </Badge>
                          <Button size="xs" variant="secondary" onClick={() => setSearch('')} disabled={!search.length}>
                            Clear search
                          </Button>
                        </div>
                      </ConsoleToolbarRow>
                      <ConsoleToolbarRow>
                        <ConsoleSearchField
                          value={search}
                          onChange={(event) => setSearch(event.target.value)}
                          placeholder={
                            activeTab === 'companyProfile'
                              ? 'Search by company field, plan, timezone, or currency'
                              : activeTab === 'structure'
                                ? 'Search structure by type, code, name, or status'
                                : activeTab === 'locations'
                                  ? 'Search locations by site, city, timezone, or status'
                                  : 'Search cost centers by code, name, or status'
                          }
                          aria-label="Search organization operations"
                          className="max-w-2xl"
                        />
                      </ConsoleToolbarRow>
                    </ConsoleToolbar>

                    {!collectionCount ? (
                      <WorkspaceEmptyState
                        title="No organization records match the current view"
                        copy="Adjust the search or switch collections to widen the organization workspace."
                      />
                    ) : (
                      <WorkspaceTableShell>
                        {activeTab === 'companyProfile' ? renderCompanyProfileTable(filteredCompanyRows) : null}
                        {activeTab === 'structure' ? renderStructureTable(filteredStructureRows) : null}
                        {activeTab === 'locations' ? renderLocationsTable(filteredLocations) : null}
                        {activeTab === 'costCenters' ? renderCostCentersTable(filteredCostCenters) : null}
                      </WorkspaceTableShell>
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>

                <CommandCenterInsightGrid>
                  <CommandCenterInsightCard
                    title="Structure health"
                    description="Keep departments and designations clean so employees, leave, and attendance inherit the right records."
                  >
                    <WorkspaceSummaryRow label="Active departments" value={activeDepartments.length} />
                    <WorkspaceSummaryRow label="Inactive departments" value={inactiveDepartments.length} />
                    <WorkspaceSummaryRow label="Active designations" value={activeDesignations.length} />
                    <WorkspaceSummaryRow label="Inactive designations" value={inactiveDesignations.length} />
                  </CommandCenterInsightCard>
                  <CommandCenterInsightCard
                    title="Location coverage"
                    description="Location defaults power scheduling, employee assignment, and any multi-site operational behavior."
                  >
                    <WorkspaceSummaryRow label="Active locations" value={activeLocations.length} />
                    <WorkspaceSummaryRow label="Inactive locations" value={inactiveLocations.length} />
                    <WorkspaceSummaryRow label="Timezones in use" value={new Set((data.locations ?? []).map((record) => record.timezone)).size} />
                    <WorkspaceSummaryRow label="Currencies in use" value={new Set((data.locations ?? []).map((record) => record.currency)).size} />
                  </CommandCenterInsightCard>
                  <CommandCenterInsightCard
                    title="Tenant defaults and finance"
                    description="Company defaults and cost-center coverage should stay aligned with operational inheritance."
                  >
                    <WorkspaceSummaryRow label="Plan" value={data.companyProfile.subscription_plan ?? 'Plan not set'} />
                    <WorkspaceSummaryRow label="Timezone" value={data.companyProfile.timezone} />
                    <WorkspaceSummaryRow label="Currency" value={data.companyProfile.currency} />
                    <WorkspaceSummaryRow label="Active cost centers" value={activeCostCenters.length} />
                  </CommandCenterInsightCard>
                </CommandCenterInsightGrid>
              </CommandCenterMain>

              <CommandCenterRail>
                <CommandCenterPanel
                  title="Recent activity"
                  actions={
                    <Button asChild size="xs" variant="ghost">
                      <Link to="/admin/organization/company-profile">Open profile</Link>
                    </Button>
                  }
                >
                  <CommandCenterActivityList>
                    {activityItems.map((item) => (
                      <CommandCenterActivityItem
                        key={item.id}
                        title={item.title}
                        detail={item.detail}
                        meta={item.meta}
                        tone={item.tone}
                        to={item.path}
                        pinned={item.path ? isFavorite(item.path) : false}
                        onTogglePinned={
                          item.path
                            ? () =>
                                toggleFavorite({
                                  path: item.path!,
                                  label: item.title,
                                  icon: 'organization',
                                  description: item.detail,
                                  meta: item.meta,
                                })
                            : undefined
                        }
                        pinLabel={
                          item.path
                            ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}`
                            : undefined
                        }
                        icon={<ArrowUpRight className="h-4 w-4" />}
                      />
                    ))}
                  </CommandCenterActivityList>
                </CommandCenterPanel>
              </CommandCenterRail>
            </CommandCenterLayout>
          </WorkspaceContent>
        </WorkspaceSurface>
      ) : null}
    </WorkspacePage>
  )
}

const organizationOverviewTabs: Array<{ id: OrganizationOverviewTab; label: string }> = [
  { id: 'companyProfile', label: 'Company profile' },
  { id: 'structure', label: 'Structure' },
  { id: 'locations', label: 'Locations' },
  { id: 'costCenters', label: 'Cost centers' },
]

function renderCompanyProfileTable(
  rows: Array<{ field: string; value: string; detail: string }>,
) {
  return (
    <Table>
      <TableHeader className="bg-panel-soft/55">
        <TableRow>
          <TableHead>Field</TableHead>
          <TableHead>Value</TableHead>
          <TableHead>Operational use</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {rows.map((row) => (
          <TableRow key={row.field}>
            <TableHead scope="row" className="align-top">
              <span className="ui-table-primary">{row.field}</span>
            </TableHead>
            <TableCell className="align-top">
              <span className="ui-table-primary">{row.value}</span>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">{row.detail}</span>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderStructureTable(rows: StructureRow[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Record</TableHead>
          <TableHead>Type</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Updated</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {rows.map(({ id, type, record }) => (
          <TableRow key={id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{record.name}</span>
                <span className="ui-table-secondary">{record.code}</span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <span className="ui-table-body-copy">{type}</span>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={record.status === 'active' ? 'success' : 'warning'}>{record.status}</Badge>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">{formatDate(record.updated_at ?? record.created_at)}</span>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/admin/organization/structure">Open structure</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderLocationsTable(rows: LocationRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Location</TableHead>
          <TableHead>Defaults</TableHead>
          <TableHead>Address</TableHead>
          <TableHead>Status</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {rows.map((record) => (
          <TableRow key={record.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{record.name}</span>
                <span className="ui-table-secondary">{record.code}</span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{record.timezone}</span>
                <span className="ui-table-secondary">{record.currency}</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">
                {[record.city, record.state, record.country].filter(Boolean).join(', ') || 'Address pending'}
              </span>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={record.status === 'active' ? 'success' : 'warning'}>{record.status}</Badge>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/admin/organization/locations">Open locations</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderCostCentersTable(rows: OrganizationMasterRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Cost center</TableHead>
          <TableHead>Summary</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Updated</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {rows.map((record) => (
          <TableRow key={record.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{record.name}</span>
                <span className="ui-table-secondary">{record.code}</span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">{record.description ?? 'Finance-linked HR mapping'}</span>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={record.status === 'active' ? 'success' : 'warning'}>{record.status}</Badge>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">{formatDate(record.updated_at ?? record.created_at)}</span>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/admin/organization/cost-centers">Open cost centers</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not updated yet'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function relativeTime(value: string | null) {
  if (!value) {
    return 'No activity time'
  }

  const now = Date.now()
  const then = new Date(value).getTime()
  const diffHours = Math.max(1, Math.round((now - then) / (1000 * 60 * 60)))

  return diffHours < 24 ? `${diffHours}h ago` : `${Math.round(diffHours / 24)}d ago`
}
