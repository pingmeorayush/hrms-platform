import { startTransition, useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  Building2,
  KeyRound,
  Layers3,
  Lock,
  ShieldCheck,
  SlidersHorizontal,
  UserRoundCog,
  Waypoints,
} from 'lucide-react'
import { useShellFavorites } from '../shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../shell/recent'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { demoPersonaLabels } from '../../modules/access/data/demoSnapshots'
import type { DemoPersona } from '../../modules/access/types'
import { setApiBaseUrl, setDemoPersona, setMode, setToken } from '../store/accessSlice'
import { useAppDispatch, useAppSelector } from '../store/hooks'
import { hasPermissions } from '../../shared/auth/permissions'
import { Badge } from '../../shared/ui/badge'
import { Button } from '../../shared/ui/button'
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
} from '../../shared/ui/command-center'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../shared/ui/console-table'
import { Input } from '../../shared/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../shared/ui/table'
import { Textarea } from '../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceField,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../shared/ui/workspace'
import { appNavigation } from '../shell/navigation'

type WorkspaceCatalogTab = 'visible' | 'all' | 'restricted'
const emptyPermissions: string[] = []

type CatalogRow = {
  id: string
  label: string
  description: string
  href: string
  visible: boolean
  visibleChildCount: number
  hiddenChildCount: number
  totalChildCount: number
}

const catalogTabs: Array<{ id: WorkspaceCatalogTab; label: string }> = [
  { id: 'visible', label: 'Visible now' },
  { id: 'all', label: 'All workspaces' },
  { id: 'restricted', label: 'Restricted here' },
]

export function FoundationOverviewPage() {
  const dispatch = useAppDispatch()
  const access = useAppSelector((state) => state.access)
  const { recentItems } = useShellRecent()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const { snapshot, source, isLoading, error } = useAccessSnapshot()
  const [activeTab, setActiveTab] = useState<WorkspaceCatalogTab>('visible')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search)

  const grantedPermissions = useMemo(
    () => snapshot?.user.permissions ?? emptyPermissions,
    [snapshot?.user.permissions],
  )
  const catalogRows = useMemo<CatalogRow[]>(
    () =>
      appNavigation.map((item) => {
        const visible = hasPermissions(grantedPermissions, item.requiredPermissions, item.match ?? 'all')
        const visibleChildren =
          item.children?.filter((child) =>
            hasPermissions(grantedPermissions, child.requiredPermissions, child.match ?? 'all'),
          ) ?? []
        const totalChildCount = item.children?.length ?? 0

        return {
          id: item.id,
          label: item.label,
          description: item.description,
          href: item.to,
          visible,
          visibleChildCount: visibleChildren.length,
          hiddenChildCount: Math.max(totalChildCount - visibleChildren.length, 0),
          totalChildCount,
        }
      }),
    [grantedPermissions],
  )

  const filteredRows = useMemo(() => {
    const normalizedQuery = deferredSearch.trim().toLowerCase()
    const searchedRows = normalizedQuery
      ? catalogRows.filter((row) =>
          [row.label, row.description].some((value) => value.toLowerCase().includes(normalizedQuery)),
        )
      : catalogRows

    if (activeTab === 'visible') {
      return searchedRows.filter((row) => row.visible)
    }

    if (activeTab === 'restricted') {
      return searchedRows.filter((row) => !row.visible)
    }

    return searchedRows
  }, [activeTab, catalogRows, deferredSearch])

  const visibleModuleCount = catalogRows.filter((row) => row.visible).length
  const restrictedModuleCount = catalogRows.filter((row) => !row.visible).length
  const visibleSectionCount = catalogRows.reduce(
    (count, row) => count + (row.visible ? row.visibleChildCount : 0),
    0,
  )
  const permissionGrantCount = grantedPermissions.length
  const tenant = snapshot?.user.tenant
  const resolvedRole = formatRoleLabel(snapshot?.user.roles[0])
  const currentPersonaLabel = demoPersonaLabels[access.demoPersona]
  const missingToken = access.mode === 'live' && access.token.trim().length === 0

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: 'neutral' | 'info' | 'success' | 'warning'
    valueSize?: 'stat' | 'compact' | 'long'
  }> = [
    {
      label: 'Visible modules',
      value: String(visibleModuleCount),
      delta: `${restrictedModuleCount} restricted in this session`,
      icon: <Layers3 className="h-4 w-4" />,
      tone: restrictedModuleCount ? 'warning' : 'success',
    },
    {
      label: 'Visible routed sections',
      value: String(visibleSectionCount),
      delta: `${catalogRows.reduce((count, row) => count + row.totalChildCount, 0)} routed sections modeled`,
      icon: <Waypoints className="h-4 w-4" />,
      tone: 'info',
    },
    {
      label: 'Permission grants',
      value: String(permissionGrantCount),
      delta: resolvedRole,
      icon: <KeyRound className="h-4 w-4" />,
      tone: permissionGrantCount ? 'success' : 'warning',
    },
    {
      label: 'Session source',
      value: access.mode === 'demo' ? 'Demo' : 'Live',
      delta: access.mode === 'demo' ? currentPersonaLabel : missingToken ? 'Token pending' : 'Bearer token loaded',
      icon: <SlidersHorizontal className="h-4 w-4" />,
      tone: missingToken ? 'warning' : 'info',
      valueSize: 'compact',
    },
    {
      label: 'Tenant context',
      value: tenant?.company_name ?? 'No tenant loaded',
      delta: tenant ? `${tenant.subscription_plan ?? 'plan pending'} · ${tenant.timezone ?? 'timezone pending'}` : 'Resolve a live token or keep using a demo persona',
      icon: <Building2 className="h-4 w-4" />,
      tone: tenant ? 'info' : 'warning',
      valueSize: 'long',
    },
    {
      label: 'Access posture',
      value: restrictedModuleCount ? 'Scoped' : 'Wide open',
      delta: restrictedModuleCount
        ? 'Some modules need a broader role or extra permissions'
        : 'All top-level modules are available in this session',
      icon: <ShieldCheck className="h-4 w-4" />,
      tone: restrictedModuleCount ? 'warning' : 'success',
      valueSize: 'compact',
    },
  ]

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      tone: 'warning' | 'danger' | 'success' | 'info'
      icon: ReactNode
    }> = []

    if (restrictedModuleCount) {
      const firstRestricted = catalogRows.find((row) => !row.visible)
      items.push({
        id: 'restricted-module',
        path: '/foundation',
        title: `${restrictedModuleCount} workspace module(s) are currently restricted`,
        detail: firstRestricted
          ? `${firstRestricted.label} is not visible with the current session role.`
          : 'Some modules are hidden in the current session.',
        meta: 'Use the restricted tab below to inspect the access gaps.',
        tone: 'warning',
        icon: <Lock className="h-4 w-4" />,
      })
    }

    if (missingToken) {
      items.push({
        id: 'live-token',
        path: '/foundation',
        title: 'Live mode is enabled without a bearer token',
        detail: 'Paste a platform token to resolve tenant and permission visibility from the API.',
        meta: 'Until then, the workspace posture reflects an unresolved live session.',
        tone: 'danger',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    if (access.mode === 'demo') {
      items.push({
        id: 'demo-persona',
        path: '/foundation',
        title: `${currentPersonaLabel} demo session is active`,
        detail: `Use demo personas to validate module visibility and permission coverage without changing environment data.`,
        meta: source === 'demo' ? 'Demo snapshot loaded from seeded access profiles.' : 'Switch to live mode to resolve API-backed contract data.',
        tone: 'info',
        icon: <UserRoundCog className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        path: '/foundation',
        title: 'Foundation posture looks healthy',
        detail: 'The session is resolved and the module catalog is ready for operational navigation.',
        meta: 'Use the access table below to inspect workspace reach and tenant defaults.',
        tone: 'success',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [access.mode, catalogRows, currentPersonaLabel, missingToken, restrictedModuleCount, source])

  const fallbackActivityItems = useMemo(
    () =>
      [
        {
          id: 'source',
          path: undefined,
          title: access.mode === 'demo' ? 'Demo source active' : 'Live source active',
          detail:
            access.mode === 'demo'
              ? `${currentPersonaLabel} visibility contract is seeded locally`
              : missingToken
                ? 'Token pending for live workspace access'
                : 'Live token is available for API-backed access resolution',
          meta: source === 'demo' ? 'Demo contract' : 'Live contract',
          tone: access.mode === 'demo' ? 'info' : missingToken ? 'warning' : 'success',
          icon: <SlidersHorizontal className="h-4 w-4" />,
        },
        {
          id: 'role',
          path: undefined,
          title: resolvedRole,
          detail: snapshot?.user.email ?? 'Resolve an identity to load role and tenant context',
          meta: `${permissionGrantCount} permission grant(s) active`,
          tone: permissionGrantCount ? 'neutral' : 'warning',
          icon: <KeyRound className="h-4 w-4" />,
        },
        {
          id: 'tenant',
          path: undefined,
          title: tenant?.company_name ?? 'Tenant pending',
          detail: tenant ? `${tenant.subscription_plan ?? 'plan pending'} · ${tenant.currency ?? 'currency pending'}` : 'Tenant defaults load after the session resolves',
          meta: tenant?.timezone ?? 'Timezone pending',
          tone: tenant ? 'neutral' : 'warning',
          icon: <Building2 className="h-4 w-4" />,
        },
        {
          id: 'catalog',
          path: undefined,
          title: `${visibleModuleCount} modules visible`,
          detail: `${restrictedModuleCount} restricted · ${visibleSectionCount} routed sections exposed`,
          meta: 'Foundation tracks top-level workspace reach for this session',
          tone: restrictedModuleCount ? 'warning' : 'success',
          icon: <Layers3 className="h-4 w-4" />,
        },
      ] as const,
    [
      access.mode,
      currentPersonaLabel,
      missingToken,
      permissionGrantCount,
      resolvedRole,
      restrictedModuleCount,
      snapshot?.user.email,
      source,
      tenant,
      visibleModuleCount,
      visibleSectionCount,
    ],
  )

  const activityItems = useMemo(() => {
    const recentActivity = getModuleRecentActivity('foundation', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [fallbackActivityItems, recentItems])

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading foundation operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Foundation"
          title="Foundation Operations Center"
          description="Control the current session, inspect workspace reach, and verify tenant posture before moving into deeper modules."
          badge={<Badge variant={source === 'demo' ? 'warning' : 'info'}>{source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[resolvedRole, tenant?.company_name ?? 'Tenant pending']}
        />

        <WorkspaceContent>
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

          <CommandCenterAttentionStrip title="Needs attention">
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
                          icon: 'foundation',
                          description: item.detail,
                          meta: item.meta,
                        })
                    : undefined
                }
                pinLabel={item.path ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}` : undefined}
              />
            ))}
          </CommandCenterAttentionStrip>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterPanel
                title="Workspace access"
                description="See which top-level modules and routed subsections are available to the active session."
              >
                <div className="space-y-3 p-3.5">
                  <ConsoleToolbar>
                    <ConsoleToolbarRow className="gap-3">
                      <ConsoleSearchField
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Search module, section, or workspace copy"
                      />
                    </ConsoleToolbarRow>
                    <ConsoleToolbarRow className="items-center gap-2">
                      <WorkspaceTabs role="tablist" aria-label="Workspace access views">
                        {catalogTabs.map((tab) => (
                          <WorkspaceTabButton
                            key={tab.id}
                            role="tab"
                            aria-selected={activeTab === tab.id}
                            active={activeTab === tab.id}
                            onClick={() => setActiveTab(tab.id)}
                          >
                            {tab.label}
                          </WorkspaceTabButton>
                        ))}
                      </WorkspaceTabs>
                      <div className="flex flex-wrap items-center gap-2 xl:justify-end">
                        <Badge variant="subtle">{filteredRows.length} result(s)</Badge>
                        <Badge variant="subtle">{visibleModuleCount} visible</Badge>
                        <Badge variant="subtle">{restrictedModuleCount} restricted</Badge>
                      </div>
                    </ConsoleToolbarRow>
                  </ConsoleToolbar>

                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Workspace</TableHead>
                          <TableHead>Coverage</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead className="text-right">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredRows.length ? (
                          filteredRows.map((row) => (
                            <TableRow key={row.id}>
                              <TableCell>
                                <div className="ui-table-stack">
                                  <span className="ui-table-primary">{row.label}</span>
                                  <span className="ui-table-secondary">{row.description}</span>
                                </div>
                              </TableCell>
                              <TableCell>
                                <div className="ui-table-stack">
                                  <span className="ui-table-body-copy">
                                    {row.totalChildCount
                                      ? `${row.visibleChildCount} of ${row.totalChildCount} routed section(s) available`
                                      : row.visible
                                        ? 'Direct module access is available'
                                        : 'Top-level access is currently restricted'}
                                  </span>
                                  <span className="ui-table-secondary">
                                    {row.hiddenChildCount
                                      ? `${row.hiddenChildCount} section(s) still need a broader role`
                                      : row.visible
                                        ? 'Ready for direct navigation'
                                        : 'Needs a broader role or additional permissions in this session'}
                                  </span>
                                </div>
                              </TableCell>
                              <TableCell>
                                <Badge variant={row.visible ? 'success' : 'warning'}>
                                  {row.visible ? 'Available' : 'Restricted'}
                                </Badge>
                              </TableCell>
                              <TableCell className="text-right">
                                {row.visible ? (
                                  <Button asChild variant="secondary" size="xs">
                                    <Link to={row.href}>
                                      Open
                                      <ArrowUpRight className="h-4 w-4" />
                                    </Link>
                                  </Button>
                                ) : (
                                  <Button variant="ghost" size="xs" disabled>
                                    Restricted
                                  </Button>
                                )}
                              </TableCell>
                            </TableRow>
                          ))
                        ) : (
                          <TableRow>
                            <TableCell colSpan={4}>
                              <div className="rounded-xl border border-dashed border-line/80 bg-panel-soft/60 px-4 py-6 text-center">
                                <p className="ui-type-body-strong text-foreground">No workspaces match this filter.</p>
                                <p className="ui-type-body mt-1 text-muted-foreground">
                                  Clear the search or switch tabs to inspect a different access slice.
                                </p>
                              </div>
                            </TableCell>
                          </TableRow>
                        )}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                </div>
              </CommandCenterPanel>

              <CommandCenterPanel
                title="Workspace session"
                description="Switch between demo and live posture, then confirm the resolved identity and tenant defaults."
              >
                <div className="grid gap-0 xl:grid-cols-[minmax(0,1fr)_21rem]">
                  <div className="space-y-4 border-b border-line/80 p-4 xl:border-b-0 xl:border-r">
                    <div className="space-y-2">
                      <p className="ui-type-body-strong text-foreground">Session source</p>
                      <div className="flex flex-wrap gap-2">
                        {(['demo', 'live'] as const).map((mode) => (
                          <Button
                            key={mode}
                            variant={access.mode === mode ? 'primary' : 'segmented'}
                            size="xs"
                            onClick={() =>
                              startTransition(() => {
                                dispatch(setMode(mode))
                              })
                            }
                          >
                            {mode === 'demo' ? 'Demo' : 'Live'}
                          </Button>
                        ))}
                      </div>
                    </div>

                    {access.mode === 'demo' ? (
                      <div className="space-y-2">
                        <p className="ui-type-body-strong text-foreground">Demo persona</p>
                        <div className="flex flex-wrap gap-2">
                          {(Object.keys(demoPersonaLabels) as DemoPersona[]).map((persona) => (
                            <Button
                              key={persona}
                              variant={access.demoPersona === persona ? 'primary' : 'secondary'}
                              size="xs"
                              onClick={() =>
                                startTransition(() => {
                                  dispatch(setDemoPersona(persona))
                                })
                              }
                            >
                              {demoPersonaLabels[persona]}
                            </Button>
                          ))}
                        </div>
                      </div>
                    ) : (
                      <div className="grid gap-3 sm:grid-cols-2">
                        <WorkspaceField label="API base URL" compact className="sm:col-span-2">
                          <Input
                            type="url"
                            value={access.apiBaseUrl}
                            onChange={(event) => dispatch(setApiBaseUrl(event.target.value))}
                            placeholder="http://127.0.0.1:8000/api/v1"
                          />
                        </WorkspaceField>
                        <WorkspaceField label="Bearer token" compact className="sm:col-span-2">
                          <Textarea
                            value={access.token}
                            onChange={(event) => dispatch(setToken(event.target.value))}
                            placeholder="Paste a bearer token from the platform API"
                            className="min-h-[7rem]"
                          />
                        </WorkspaceField>
                        <div className="sm:col-span-2">
                          <Button
                            variant="primary"
                            size="xs"
                            onClick={() =>
                              startTransition(() => {
                                dispatch(setToken(access.token.trim()))
                              })
                            }
                          >
                            Refresh contract
                          </Button>
                        </div>
                      </div>
                    )}
                  </div>

                  <div className="space-y-2.5 p-4">
                    <p className="ui-type-body-strong text-foreground">Resolved identity</p>
                    <WorkspaceSummaryRow label="User" value={snapshot?.user.name ?? 'Not resolved'} />
                    <WorkspaceSummaryRow label="Role" value={resolvedRole} />
                    <WorkspaceSummaryRow label="Tenant" value={tenant?.company_name ?? 'Pending'} />
                    <WorkspaceSummaryRow
                      label="Plan"
                      value={tenant?.subscription_plan ?? 'Pending'}
                    />
                    <WorkspaceSummaryRow label="Timezone" value={tenant?.timezone ?? 'Pending'} />
                    <WorkspaceSummaryRow label="Currency" value={tenant?.currency ?? 'Pending'} />
                    <WorkspaceSummaryRow label="Permissions" value={String(permissionGrantCount)} />
                  </div>
                </div>
              </CommandCenterPanel>

              <CommandCenterInsightGrid>
                <CommandCenterInsightCard
                  title="Module exposure"
                  description="Top-level workspace availability for the active session."
                >
                  <WorkspaceSummaryRow label="Visible modules" value={String(visibleModuleCount)} />
                  <WorkspaceSummaryRow label="Restricted modules" value={String(restrictedModuleCount)} />
                  <WorkspaceSummaryRow label="Visible routed sections" value={String(visibleSectionCount)} />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Permission reach"
                  description="How broadly this session can operate across the admin console."
                >
                  <WorkspaceSummaryRow label="Granted permissions" value={String(permissionGrantCount)} />
                  <WorkspaceSummaryRow
                    label="Administrative posture"
                    value={restrictedModuleCount ? 'Scoped role' : 'Broad role'}
                  />
                  <WorkspaceSummaryRow
                    label="Live readiness"
                    value={missingToken ? 'Token pending' : access.mode === 'live' ? 'Resolved' : 'Demo ready'}
                  />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Tenant defaults"
                  description="Current company context inherited across other modules."
                >
                  <WorkspaceSummaryRow label="Company" value={tenant?.company_name ?? 'Pending'} />
                  <WorkspaceSummaryRow label="Plan" value={tenant?.subscription_plan ?? 'Pending'} />
                  <WorkspaceSummaryRow label="Timezone" value={tenant?.timezone ?? 'Pending'} />
                  <WorkspaceSummaryRow label="Currency" value={tenant?.currency ?? 'Pending'} />
                </CommandCenterInsightCard>
              </CommandCenterInsightGrid>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterPanel
                title="Recent activity"
                description="Recent foundation workspaces appear here. Session posture remains as fallback context for fresh sessions."
              >
                <CommandCenterActivityList>
                  {activityItems.map((item) => (
                    <CommandCenterActivityItem
                      key={item.id}
                      title={item.title}
                      detail={item.detail}
                      meta={item.meta}
                      to={item.path}
                      pinned={item.path ? isFavorite(item.path) : false}
                      onTogglePinned={
                        item.path
                          ? () =>
                              toggleFavorite({
                                path: item.path!,
                                label: item.title,
                                icon: 'foundation',
                                description: item.detail,
                                meta: item.meta,
                              })
                          : undefined
                      }
                      pinLabel={
                        item.path ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}` : undefined
                      }
                      icon={'icon' in item ? item.icon : <ArrowUpRight className="h-4 w-4" />}
                      tone={item.tone}
                    />
                  ))}
                </CommandCenterActivityList>
              </CommandCenterPanel>
            </CommandCenterRail>
          </CommandCenterLayout>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function formatRoleLabel(role: string | undefined) {
  if (!role) {
    return 'Role pending'
  }

  return role
    .replace(/[._]/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}
