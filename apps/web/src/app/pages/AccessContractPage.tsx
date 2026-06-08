import { useDeferredValue, useEffect, useMemo, useState, type ReactNode } from 'react'
import { AlertTriangle, ShieldCheck, Star } from 'lucide-react'
import { useLocation, useNavigate } from 'react-router-dom'
import { useShellFavorites } from '../shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../shell/recent'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { Badge } from '../../shared/ui/badge'
import { Button } from '../../shared/ui/button'
import { CardDescription, CardTitle } from '../../shared/ui/card'
import { cn } from '../../shared/ui/cn'
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
import { Modal } from '../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../shared/ui/table'
import {
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspacePinButton,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTableShell,
  WorkspaceTabButton,
  WorkspaceTabs,
  WorkspaceContent,
} from '../../shared/ui/workspace'

export function AccessContractPage() {
  const location = useLocation()
  const navigate = useNavigate()
  const { recentItems } = useShellRecent()
  const { snapshot, isLoading, error, source } = useAccessSnapshot()
  const [activeTab, setActiveTab] = useState<'navigation' | 'actions' | 'diagnostics'>(() => {
    if (typeof window === 'undefined') {
      return 'navigation'
    }

    return window.location.hash === '#actions'
      ? 'actions'
      : window.location.hash === '#diagnostics'
        ? 'diagnostics'
        : 'navigation'
  })
  const [search, setSearch] = useState('')
  const [inspector, setInspector] = useState<
    | { type: 'navigation'; id: string }
    | { type: 'actions'; id: string }
    | { type: 'diagnostics'; id: string }
    | null
  >(null)
  const deferredSearch = useDeferredValue(search)
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const activeWorkspaceFavorite =
    activeTab === 'actions'
      ? {
          path: '/access#actions',
          label: 'Access actions',
          icon: 'access' as const,
          description: 'Pinned access action contract workspace',
        }
      : activeTab === 'diagnostics'
        ? {
            path: '/access#diagnostics',
            label: 'Access diagnostics',
            icon: 'access' as const,
            description: 'Pinned access diagnostics workspace',
          }
        : {
            path: '/access#routes',
            label: 'Access routes',
            icon: 'access' as const,
            description: 'Pinned access route contract workspace',
          }

  useEffect(() => {
    const hash = location.hash
    const nextTab =
      hash === '#actions' ? 'actions' : hash === '#diagnostics' ? 'diagnostics' : 'navigation'

    setActiveTab((current) => (current === nextTab ? current : nextTab))
  }, [location.hash])

  const setAccessTab = (tab: 'navigation' | 'actions' | 'diagnostics') => {
    setActiveTab(tab)
    navigate(
      {
        pathname: location.pathname,
        hash: tab === 'navigation' ? '#routes' : tab === 'actions' ? '#actions' : '#diagnostics',
      },
      { replace: true },
    )
  }

  const actionRows = useMemo(() => {
    if (!snapshot) {
      return []
    }

    return snapshot.visibility.action_groups.flatMap((group) =>
      group.actions
        .filter((action) => action.visible)
        .map((action) => ({
          id: action.id,
          group: group.title,
          label: action.label,
          description: action.description ?? 'No description provided.',
          permissions: action.required_permissions,
          href: action.href ?? 'Route pending',
          match: action.match,
        })),
    )
  }, [snapshot])

  const diagnosticsRows = useMemo(() => {
    if (!snapshot) {
      return []
    }

    return [
      {
        id: 'backend-note',
        label: 'Backend enforcement',
        summary: snapshot.visibility.meta.backend_enforcement_note,
        value: 'Server enforced',
        sourceLabel: source === 'live' ? 'Live API' : 'Demo contract',
        note: 'Visibility decisions stay backend-driven.',
      },
      {
        id: 'hidden-routes',
        label: 'Hidden routes',
        summary: 'Routes excluded from the current session contract.',
        value: String(snapshot.visibility.meta.hidden_navigation_count),
        sourceLabel: 'Navigation contract',
        note: 'Use role changes to widen route exposure.',
      },
      {
        id: 'hidden-actions',
        label: 'Suppressed actions',
        summary: 'Actions not exposed to the current identity.',
        value: String(
          snapshot.visibility.action_groups.reduce((total, group) => total + group.hidden_count, 0),
        ),
        sourceLabel: 'Action contract',
        note: 'Hidden actions remain blocked even if users know the route.',
      },
    ]
  }, [snapshot, source])

  const navigationRows = useMemo(() => {
    if (!snapshot) {
      return []
    }

    return snapshot.visibility.navigation.filter((item) => item.visible)
  }, [snapshot])

  const filteredNavigationRows = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query.length) {
      return navigationRows
    }

    return navigationRows.filter((item) =>
      [item.label, item.id, item.description ?? '', item.href ?? '', item.required_permissions.join(' ')]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [deferredSearch, navigationRows])

  const filteredActionRows = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query.length) {
      return actionRows
    }

    return actionRows.filter((action) =>
      [action.label, action.group, action.description, action.href, action.permissions.join(' ')]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [actionRows, deferredSearch])

  const filteredDiagnosticsRows = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query.length) {
      return diagnosticsRows
    }

    return diagnosticsRows.filter((row) =>
      [row.label, row.summary, row.value, row.sourceLabel, row.note].join(' ').toLowerCase().includes(query),
    )
  }, [deferredSearch, diagnosticsRows])

  const activeRowCount =
    activeTab === 'navigation'
      ? filteredNavigationRows.length
      : activeTab === 'actions'
        ? filteredActionRows.length
        : filteredDiagnosticsRows.length
  const searchPlaceholder =
    activeTab === 'navigation'
      ? 'Search by route, label, or permission'
      : activeTab === 'actions'
        ? 'Search by action, group, or permission'
        : 'Search by signal or note'

  const selectedNavigationRow =
    inspector?.type === 'navigation'
      ? navigationRows.find((row) => row.id === inspector.id) ?? null
      : null
  const selectedActionRow =
    inspector?.type === 'actions'
      ? actionRows.find((row) => row.id === inspector.id) ?? null
      : null
  const selectedDiagnosticsRow =
    inspector?.type === 'diagnostics'
      ? diagnosticsRows.find((row) => row.id === inspector.id) ?? null
      : null

  const visibleRouteCount = snapshot?.visibility.meta.visible_navigation_count ?? 0
  const hiddenRouteCount = snapshot?.visibility.meta.hidden_navigation_count ?? 0
  const visibleActionCount = snapshot?.visibility.action_groups.reduce((total, group) => total + group.visible_count, 0) ?? 0
  const hiddenActionCount = snapshot?.visibility.action_groups.reduce((total, group) => total + group.hidden_count, 0) ?? 0

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: 'neutral' | 'info' | 'success' | 'warning' | 'danger'
  }> = snapshot
    ? [
        {
          label: 'Visible routes',
          value: String(visibleRouteCount),
          delta: `${snapshot.visibility.navigation.length} route contract item(s) in total`,
          icon: <ShieldCheck className="h-4 w-4" />,
          tone: 'info',
        },
        {
          label: 'Hidden routes',
          value: String(hiddenRouteCount),
          delta: hiddenRouteCount ? 'Excluded from the current session contract' : 'Full route exposure for this contract',
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: hiddenRouteCount ? 'warning' : 'success',
        },
        {
          label: 'Visible actions',
          value: String(visibleActionCount),
          delta: `${snapshot.visibility.action_groups.length} action group(s) configured`,
          icon: <Badge variant="subtle" className="h-4 w-4 rounded-full p-0" />,
          tone: visibleActionCount ? 'success' : 'neutral',
        },
        {
          label: 'Suppressed actions',
          value: String(hiddenActionCount),
          delta: hiddenActionCount ? 'Backend is suppressing privileged operations' : 'No action suppression in this contract',
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: hiddenActionCount ? 'warning' : 'success',
        },
        {
          label: 'Roles in session',
          value: String(snapshot.user.roles.length),
          delta: `${snapshot.user.permissions.length} permission grant(s) active`,
          icon: <ShieldCheck className="h-4 w-4" />,
          tone: 'neutral',
        },
        {
          label: 'Contract source',
          value: source === 'live' ? 'Live' : 'Demo',
          delta: source === 'live' ? 'Real backend access contract' : 'Seeded governance contract',
          icon: <ShieldCheck className="h-4 w-4" />,
          tone: source === 'live' ? 'success' : 'info',
        },
      ]
    : []

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

    if (!snapshot) {
      return items
    }

    if (hiddenRouteCount) {
      const hiddenRoute = snapshot.visibility.navigation.find((item) => !item.visible)
      items.push({
        id: 'hidden-routes',
        path: '/access#routes',
        title: `${hiddenRouteCount} route(s) are hidden in this contract`,
        detail: hiddenRoute ? `${hiddenRoute.label} is currently excluded from the session.` : 'Navigation visibility is being restricted.',
        meta: 'Use role changes or permission updates to widen route exposure.',
        tone: hiddenRouteCount > 1 ? 'danger' : 'warning',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    if (hiddenActionCount) {
      const restrictedGroup = snapshot.visibility.action_groups.find((group) => group.hidden_count > 0)
      items.push({
        id: 'hidden-actions',
        path: '/access#actions',
        title: `${hiddenActionCount} action(s) are suppressed by backend policy`,
        detail: restrictedGroup
          ? `${restrictedGroup.title} has ${restrictedGroup.hidden_count} hidden operation(s).`
          : 'Privileged actions are being filtered from the current identity.',
        meta: 'Suppressed actions remain blocked even if the route is known.',
        tone: 'warning',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    items.push({
      id: 'backend-enforcement',
      path: '/access#diagnostics',
      title: 'Backend enforcement is active',
      detail: snapshot.visibility.meta.backend_enforcement_note,
      meta: source === 'live' ? 'Live API contract' : 'Demo governance contract',
      tone: 'info',
      icon: <ShieldCheck className="h-4 w-4" />,
    })

    if (!hiddenRouteCount && !hiddenActionCount) {
      items.push({
        id: 'healthy',
        path: '/access#routes',
        title: 'Governance posture is stable',
        detail: 'Routes and actions are aligned with the current access contract.',
        meta: 'Use the collections below to inspect detailed route and action visibility.',
        tone: 'success',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [hiddenActionCount, hiddenRouteCount, snapshot, source])

  const fallbackActivityItems = useMemo(() => {
    if (!snapshot) {
      return []
    }

    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      tone: 'neutral' | 'info' | 'success' | 'warning'
    }> = [
      {
        id: 'session-role',
        title: snapshot.user.roles.join(', '),
        detail: `${snapshot.user.name} · ${snapshot.user.tenant.company_name}`,
        meta: `${snapshot.user.permissions.length} permission grant(s) in session`,
        tone: 'info',
      },
      {
        id: 'tenant-plan',
        title: snapshot.user.tenant.subscription_plan ?? 'Plan unavailable',
        detail: `${snapshot.user.tenant.timezone ?? 'Timezone unavailable'} · ${snapshot.user.tenant.currency ?? 'Currency unavailable'}`,
        meta: source === 'live' ? 'Live tenant context' : 'Demo tenant context',
        tone: 'neutral',
      },
      ...snapshot.visibility.action_groups.map((group) => ({
        id: `group-${group.id}`,
        title: `${group.title} · ${group.visible_count} visible`,
        detail: group.description,
        meta: `${group.hidden_count} hidden action(s)`,
        tone: (group.hidden_count ? 'warning' : 'success') as 'warning' | 'success',
      })),
    ]

    return items.slice(0, 6)
  }, [snapshot, source])

  const activityItems = useMemo(() => {
    const recentActivity = getModuleRecentActivity('access', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [fallbackActivityItems, recentItems])

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading the access contract...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}
      {!snapshot && !isLoading && !error ? (
        <p className="workspace-muted">No contract is available yet. Use the Foundation workspace to configure a session.</p>
      ) : null}

      {snapshot ? (
        <>
          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <p className="ui-type-page-eyebrow text-text-subtle">Live module · Operations center</p>
                <CardTitle>Access Operations Center</CardTitle>
                <CardDescription>
                  Review route exposure, permitted actions, and backend enforcement posture from one governance workspace.
                </CardDescription>
              </div>
              <WorkspaceHeaderActions>
                <WorkspacePinButton
                  pinned={isFavorite(activeWorkspaceFavorite.path)}
                  onToggle={() => toggleFavorite(activeWorkspaceFavorite)}
                />
                <Button size="sm" variant="secondary" onClick={() => setAccessTab('diagnostics')}>
                  Review diagnostics
                </Button>
                <Button size="sm" variant="primary" onClick={() => setAccessTab('actions')}>
                  Open actions
                </Button>
              </WorkspaceHeaderActions>
            </WorkspaceHeader>
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
                  />
                ))}
              </CommandCenterMetricGrid>

              <CommandCenterLayout>
                <CommandCenterMain>
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
                                  icon: 'access',
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
                      <div className="space-y-1">
                        <CardTitle>Governance workspace</CardTitle>
                        <CardDescription>
                          Inspect route exposure, permitted actions, and enforcement diagnostics from one command surface.
                        </CardDescription>
                      </div>
                      <Badge variant="subtle">{activeRowCount} record(s) in view</Badge>
                    </WorkspaceHeader>
                    <WorkspaceContent className="space-y-4">
                      <ConsoleToolbar>
                        <ConsoleToolbarRow>
                          <WorkspaceTabs role="tablist" aria-label="Access collection views">
                            <WorkspaceTabButton
                              type="button"
                              role="tab"
                              active={activeTab === 'navigation'}
                              aria-selected={activeTab === 'navigation'}
                              onClick={() => setAccessTab('navigation')}
                            >
                              Routes
                            </WorkspaceTabButton>
                            <WorkspaceTabButton
                              type="button"
                              role="tab"
                              active={activeTab === 'actions'}
                              aria-selected={activeTab === 'actions'}
                              onClick={() => setAccessTab('actions')}
                            >
                              Actions
                            </WorkspaceTabButton>
                            <WorkspaceTabButton
                              type="button"
                              role="tab"
                              active={activeTab === 'diagnostics'}
                              aria-selected={activeTab === 'diagnostics'}
                              onClick={() => setAccessTab('diagnostics')}
                            >
                              Diagnostics
                            </WorkspaceTabButton>
                          </WorkspaceTabs>
                          <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="subtle">
                              {activeTab === 'navigation'
                                ? 'Route contract'
                                : activeTab === 'actions'
                                  ? 'Action contract'
                                  : 'Enforcement signals'}
                            </Badge>
                            <Button size="sm" variant="secondary" onClick={() => setSearch('')} disabled={!search.length}>
                              Clear search
                            </Button>
                          </div>
                        </ConsoleToolbarRow>
                        <ConsoleToolbarRow>
                          <ConsoleSearchField
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={searchPlaceholder}
                            aria-label="Search access operations"
                            className="max-w-2xl"
                          />
                        </ConsoleToolbarRow>
                      </ConsoleToolbar>

                      {activeTab === 'navigation' ? (
                        filteredNavigationRows.length ? (
                          <WorkspaceTableShell>
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>Route</TableHead>
                                  <TableHead>Summary</TableHead>
                                  <TableHead>Match</TableHead>
                                  <TableHead>Path</TableHead>
                                  <TableHead className="w-[132px] text-right">Action</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {filteredNavigationRows.map((item) => (
                                  <TableRow key={item.id}>
                                    <TableCell className="align-top">
                                      <div className="grid gap-1">
                                        <span className="ui-type-body-strong text-foreground">{item.label}</span>
                                        <span className="ui-type-caption text-muted-foreground">{item.id}</span>
                                      </div>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {item.description ?? 'No description provided.'}
                                    </TableCell>
                                    <TableCell className="align-top">
                                      <Badge variant="subtle">{item.match}</Badge>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {item.href ?? 'No route bound'}
                                    </TableCell>
                                    <TableCell className="align-top text-right">
                                      <div className="flex items-center justify-end gap-2">
                                        <Button
                                          variant="ghost"
                                          size="sm"
                                          aria-label={isFavorite('/access#routes') ? 'Unpin routes workspace' : 'Pin routes workspace'}
                                          onClick={() =>
                                            toggleFavorite({
                                              path: '/access#routes',
                                              label: 'Access routes',
                                              icon: 'access',
                                              description: 'Pinned access route contract workspace',
                                              meta: item.label,
                                            })
                                          }
                                        >
                                          <Star className={cn('h-4 w-4', isFavorite('/access#routes') && 'fill-current')} />
                                        </Button>
                                        <Button
                                          variant="secondary"
                                          size="sm"
                                          aria-label={`Inspect ${item.label}`}
                                          onClick={() => setInspector({ type: 'navigation', id: item.id })}
                                        >
                                          Inspect
                                        </Button>
                                      </div>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </WorkspaceTableShell>
                        ) : (
                          <WorkspaceEmptyState
                            title="No routes match the current filter"
                            copy="Change the search to inspect a broader navigation contract."
                          />
                        )
                      ) : null}

                      {activeTab === 'actions' ? (
                        filteredActionRows.length ? (
                          <WorkspaceTableShell>
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>Action</TableHead>
                                  <TableHead>Summary</TableHead>
                                  <TableHead>Group</TableHead>
                                  <TableHead>Route</TableHead>
                                  <TableHead className="w-[132px] text-right">Action</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {filteredActionRows.map((action) => (
                                  <TableRow key={action.id}>
                                    <TableCell className="align-top">
                                      <div className="grid gap-1">
                                        <span className="ui-type-body-strong text-foreground">{action.label}</span>
                                        <span className="ui-type-caption text-muted-foreground">{action.match}</span>
                                      </div>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {action.description}
                                    </TableCell>
                                    <TableCell className="align-top">
                                      <Badge variant="subtle">{action.group}</Badge>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {action.href}
                                    </TableCell>
                                    <TableCell className="align-top text-right">
                                      <div className="flex items-center justify-end gap-2">
                                        <Button
                                          variant="ghost"
                                          size="sm"
                                          aria-label={isFavorite('/access#actions') ? 'Unpin actions workspace' : 'Pin actions workspace'}
                                          onClick={() =>
                                            toggleFavorite({
                                              path: '/access#actions',
                                              label: 'Access actions',
                                              icon: 'access',
                                              description: 'Pinned access action contract workspace',
                                              meta: action.label,
                                            })
                                          }
                                        >
                                          <Star className={cn('h-4 w-4', isFavorite('/access#actions') && 'fill-current')} />
                                        </Button>
                                        <Button
                                          variant="secondary"
                                          size="sm"
                                          aria-label={`Inspect ${action.label}`}
                                          onClick={() => setInspector({ type: 'actions', id: action.id })}
                                        >
                                          Inspect
                                        </Button>
                                      </div>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </WorkspaceTableShell>
                        ) : (
                          <WorkspaceEmptyState
                            title="No actions match the current filter"
                            copy="Change the search to inspect a broader action contract."
                          />
                        )
                      ) : null}

                      {activeTab === 'diagnostics' ? (
                        filteredDiagnosticsRows.length ? (
                          <WorkspaceTableShell>
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>Signal</TableHead>
                                  <TableHead>Summary</TableHead>
                                  <TableHead>Value</TableHead>
                                  <TableHead>Source</TableHead>
                                  <TableHead className="w-[132px] text-right">Action</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {filteredDiagnosticsRows.map((row) => (
                                  <TableRow key={row.id}>
                                    <TableCell className="align-top">
                                      <span className="ui-type-body-strong text-foreground">{row.label}</span>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {row.summary}
                                    </TableCell>
                                    <TableCell className="align-top">
                                      <Badge variant="subtle">{row.value}</Badge>
                                    </TableCell>
                                    <TableCell className="ui-type-body align-top text-muted-foreground">
                                      {row.sourceLabel}
                                    </TableCell>
                                    <TableCell className="align-top text-right">
                                      <div className="flex items-center justify-end gap-2">
                                        <Button
                                          variant="ghost"
                                          size="sm"
                                          aria-label={isFavorite('/access#diagnostics') ? 'Unpin diagnostics workspace' : 'Pin diagnostics workspace'}
                                          onClick={() =>
                                            toggleFavorite({
                                              path: '/access#diagnostics',
                                              label: 'Access diagnostics',
                                              icon: 'access',
                                              description: 'Pinned access diagnostics workspace',
                                              meta: row.label,
                                            })
                                          }
                                        >
                                          <Star className={cn('h-4 w-4', isFavorite('/access#diagnostics') && 'fill-current')} />
                                        </Button>
                                        <Button
                                          variant="secondary"
                                          size="sm"
                                          aria-label={`Inspect ${row.label}`}
                                          onClick={() => setInspector({ type: 'diagnostics', id: row.id })}
                                        >
                                          Inspect
                                        </Button>
                                      </div>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </WorkspaceTableShell>
                        ) : (
                          <WorkspaceEmptyState
                            title="No diagnostics match the current filter"
                            copy="Change the search to restore the current enforcement signals."
                          />
                        )
                      ) : null}
                    </WorkspaceContent>
                  </WorkspaceSurface>

                  <CommandCenterInsightGrid>
                    <CommandCenterInsightCard
                      title="Route contract summary"
                      description="Use the navigation contract to understand what is currently exposed to the active identity."
                    >
                      <WorkspaceSummaryRow label="Visible routes" value={visibleRouteCount} />
                      <WorkspaceSummaryRow label="Hidden routes" value={hiddenRouteCount} />
                      <WorkspaceSummaryRow label="Current search result" value={activeTab === 'navigation' ? filteredNavigationRows.length : '—'} />
                      <WorkspaceSummaryRow label="Backend source" value={source === 'live' ? 'Live' : 'Demo'} />
                    </CommandCenterInsightCard>
                    <CommandCenterInsightCard
                      title="Action contract summary"
                      description="Keep visible and suppressed operations aligned with the governance role model."
                    >
                      <WorkspaceSummaryRow label="Visible actions" value={visibleActionCount} />
                      <WorkspaceSummaryRow label="Suppressed actions" value={hiddenActionCount} />
                      <WorkspaceSummaryRow label="Action groups" value={snapshot.visibility.action_groups.length} />
                      <WorkspaceSummaryRow label="Current search result" value={activeTab === 'actions' ? filteredActionRows.length : '—'} />
                    </CommandCenterInsightCard>
                    <CommandCenterInsightCard
                      title="Session context"
                      description="Tenant, plan, and role context explain why the contract looks the way it does."
                    >
                      <WorkspaceSummaryRow label="Tenant" value={snapshot.user.tenant.company_name} />
                      <WorkspaceSummaryRow label="Plan" value={snapshot.user.tenant.subscription_plan ?? 'Unavailable'} />
                      <WorkspaceSummaryRow label="Roles" value={snapshot.user.roles.length} />
                      <WorkspaceSummaryRow label="Permissions" value={snapshot.user.permissions.length} />
                    </CommandCenterInsightCard>
                  </CommandCenterInsightGrid>
                </CommandCenterMain>

                <CommandCenterRail>
                  <CommandCenterPanel title="Recent activity">
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
                                    icon: 'access',
                                    description: item.detail,
                                    meta: item.meta,
                                  })
                              : undefined
                          }
                          pinLabel={
                            item.path ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}` : undefined
                          }
                          icon={<ShieldCheck className="h-4 w-4" />}
                        />
                      ))}
                    </CommandCenterActivityList>
                  </CommandCenterPanel>
                </CommandCenterRail>
              </CommandCenterLayout>
            </WorkspaceContent>
          </WorkspaceSurface>

          <Modal
            open={Boolean(inspector)}
            title={
              inspector?.type === 'navigation'
                ? 'Inspect route contract'
                : inspector?.type === 'actions'
                  ? 'Inspect action contract'
                  : 'Inspect access diagnostic'
            }
            description={
              inspector?.type === 'navigation'
                ? 'Review the backend navigation contract in a focused detail view.'
                : inspector?.type === 'actions'
                  ? 'Review the operational permission contract for this action.'
                  : 'Review the current enforcement signal and operator note.'
            }
            onClose={() => setInspector(null)}
          >
            {inspector?.type === 'navigation' ? <AccessNavigationInspector row={selectedNavigationRow} /> : null}
            {inspector?.type === 'actions' ? <AccessActionInspector row={selectedActionRow} /> : null}
            {inspector?.type === 'diagnostics' ? (
              <AccessDiagnosticsInspector row={selectedDiagnosticsRow} />
            ) : null}
          </Modal>
        </>
      ) : null}
    </WorkspacePage>
  )
}

function AccessNavigationInspector({
  row,
}: {
  row:
    | {
        id: string
        label: string
        description: string | null
        href: string | null | undefined
        match: string
        required_permissions: string[]
      }
    | null
}) {
  if (!row) {
    return <EmptyInspector copy="Select a route row to inspect the backend navigation contract in detail." />
  }

  return (
    <div className="space-y-4" aria-label="Selected navigation contract">
      <div className="space-y-1">
        <p className="ui-type-page-eyebrow text-text-subtle">Navigation contract</p>
        <h3 className="ui-type-card-title text-foreground">{row.label}</h3>
        <p className="ui-type-body text-muted-foreground">{row.description ?? 'No description provided.'}</p>
      </div>
      <div className="mt-4">
        <InspectorRow label="Route id" value={row.id} />
        <InspectorRow label="Match rule" value={row.match} />
        <InspectorRow label="Path" value={row.href ?? 'No route bound'} />
        <InspectorRow
          label="Permissions"
          value={row.required_permissions.join(', ') || 'Public route'}
        />
      </div>
    </div>
  )
}

function AccessActionInspector({
  row,
}: {
  row:
    | {
        id: string
        group: string
        label: string
        description: string
        permissions: string[]
        href: string
        match: string
      }
    | null
}) {
  if (!row) {
    return <EmptyInspector copy="Select an action row to inspect the operational permission contract." />
  }

  return (
    <div className="space-y-4" aria-label="Selected action contract">
      <div className="space-y-1">
        <p className="ui-type-page-eyebrow text-text-subtle">Action contract</p>
        <h3 className="ui-type-card-title text-foreground">{row.label}</h3>
        <p className="ui-type-body text-muted-foreground">{row.description}</p>
      </div>
      <div className="mt-4">
        <InspectorRow label="Action id" value={row.id} />
        <InspectorRow label="Action group" value={row.group} />
        <InspectorRow label="Match rule" value={row.match} />
        <InspectorRow label="Route" value={row.href} />
        <InspectorRow label="Permissions" value={row.permissions.join(', ')} />
      </div>
    </div>
  )
}

function AccessDiagnosticsInspector({
  row,
}: {
  row:
    | {
        id: string
        label: string
        summary: string
        value: string
        sourceLabel: string
        note: string
      }
    | null
}) {
  if (!row) {
    return <EmptyInspector copy="Select a diagnostics row to inspect the current enforcement posture." />
  }

  return (
    <div className="space-y-4" aria-label="Selected diagnostic contract">
      <div className="space-y-1">
        <p className="ui-type-page-eyebrow text-text-subtle">Diagnostics</p>
        <h3 className="ui-type-card-title text-foreground">{row.label}</h3>
        <p className="ui-type-body text-muted-foreground">{row.summary}</p>
      </div>
      <div className="mt-4">
        <InspectorRow label="Signal" value={row.label} />
        <InspectorRow label="Value" value={row.value} />
        <InspectorRow label="Source" value={row.sourceLabel} />
        <InspectorRow label="Operator note" value={row.note} />
      </div>
    </div>
  )
}

function InspectorRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-start justify-between gap-4 border-b border-line-soft py-2 last:border-b-0">
      <span className="ui-type-body text-muted-foreground">{label}</span>
      <strong className="ui-type-body-strong text-right text-foreground">{value}</strong>
    </div>
  )
}

function EmptyInspector({ copy }: { copy: string }) {
  return (
    <div className="flex min-h-52 items-center rounded-xl border border-dashed border-line bg-panel-soft/70 p-4">
      <div className="w-full text-center">
        <h3 className="ui-type-section-title text-foreground">No row selected</h3>
        <p className="ui-type-body mt-2 text-muted-foreground">{copy}</p>
      </div>
    </div>
  )
}
