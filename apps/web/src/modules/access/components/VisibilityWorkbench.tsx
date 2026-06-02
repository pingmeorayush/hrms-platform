import { startTransition, useDeferredValue, useEffect, useMemo, useState } from 'react'
import { NavLink, Route, Routes, useLocation, useNavigate } from 'react-router-dom'
import { useAccessSnapshot } from '../hooks/useAccessSnapshot'
import { demoPersonaLabels } from '../data/demoSnapshots'
import { useAppDispatch, useAppSelector } from '../../../app/store/hooks'
import {
  setApiBaseUrl,
  setDemoPersona,
  setMode,
  setToken,
} from '../../../app/store/accessSlice'
import type { AccessSnapshot, VisibilityActionGroup, VisibilityItem } from '../types'
import { Button } from '../../../shared/ui/button'
import { Badge } from '../../../shared/ui/badge'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Textarea } from '../../../shared/ui/textarea'

function countVisibleActions(snapshot: AccessSnapshot) {
  return snapshot.visibility.action_groups.reduce((total, group) => total + group.visible_count, 0)
}

function countHiddenActions(snapshot: AccessSnapshot) {
  return snapshot.visibility.action_groups.reduce((total, group) => total + group.hidden_count, 0)
}

function canAccess(requiredPermissions: string[], grantedPermissions: string[], match: 'all' | 'any' = 'all') {
  if (requiredPermissions.length === 0) {
    return true
  }

  return match === 'any'
    ? requiredPermissions.some((permission) => grantedPermissions.includes(permission))
    : requiredPermissions.every((permission) => grantedPermissions.includes(permission))
}

function filterActions(
  group: VisibilityActionGroup,
  grantedPermissions: string[],
  search: string,
) {
  const query = search.trim().toLowerCase()

  return group.actions.filter((action) => {
    const matchesSearch =
      query.length === 0 ||
      [action.label, action.description ?? '', action.required_permissions.join(' ')]
        .join(' ')
        .toLowerCase()
        .includes(query)

    return matchesSearch && canAccess(action.required_permissions, grantedPermissions, action.match)
  })
}

function Icon({ name }: { name: string }) {
  switch (name) {
    case 'menu':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M4 5h12M4 10h12M4 15h12" />
        </svg>
      )
    case 'search':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <circle cx="8.5" cy="8.5" r="4.5" />
          <path d="M12 12l4 4" />
        </svg>
      )
    case 'bell':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M10 3a4 4 0 0 0-4 4v2.6c0 .8-.3 1.6-.9 2.2L4 13h12l-1.1-1.2c-.6-.6-.9-1.4-.9-2.2V7a4 4 0 0 0-4-4Z" />
          <path d="M8 15a2 2 0 0 0 4 0" />
        </svg>
      )
    case 'settings':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <circle cx="10" cy="10" r="2.3" />
          <path d="M10 3.3v1.5M10 15.2v1.5M15.2 10h1.5M3.3 10h1.5M14.5 5.5l-1 1M6.5 13.5l-1 1M14.5 14.5l-1-1M6.5 6.5l-1-1" />
        </svg>
      )
    case 'grid':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M4 4h4v4H4zM12 4h4v4h-4zM4 12h4v4H4zM12 12h4v4h-4z" />
        </svg>
      )
    case 'home':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M4 9.5 10 4l6 5.5V16H4z" />
          <path d="M8 16v-4h4v4" />
        </svg>
      )
    case 'workflow':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M5 5h4v4H5zM11 11h4v4h-4z" />
          <path d="M9 7h2.5A2.5 2.5 0 0 1 14 9.5V11M11 13H8.5A2.5 2.5 0 0 1 6 10.5V9" />
        </svg>
      )
    case 'tasks':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M5 5h10M5 10h10M5 15h6" />
          <path d="m13.5 14.5 1.2 1.2 2.3-2.8" />
        </svg>
      )
    case 'notifications':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M10 4.2a3.5 3.5 0 0 0-3.5 3.5v2.2L5.4 11H14.6l-1.1-1.1V7.7A3.5 3.5 0 0 0 10 4.2Z" />
          <path d="M8.2 14.1a1.8 1.8 0 0 0 3.6 0" />
        </svg>
      )
    case 'audit':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M6 4h8l2 2v9H6z" />
          <path d="M8 8h4M8 11h5M8 14h3" />
        </svg>
      )
    case 'access':
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <path d="M10 3 5 5v4c0 3.4 2 5.9 5 7 3-1.1 5-3.6 5-7V5z" />
          <path d="M8.2 9.8 9.5 11l2.5-2.7" />
        </svg>
      )
    default:
      return (
        <svg viewBox="0 0 20 20" aria-hidden="true">
          <circle cx="10" cy="10" r="6" />
        </svg>
      )
  }
}

function iconForNavigation(itemId: string) {
  switch (itemId) {
    case 'foundation-overview':
      return 'home'
    case 'workflow-console':
      return 'workflow'
    case 'task-inbox':
      return 'tasks'
    case 'notification-center':
      return 'notifications'
    case 'audit-trail':
      return 'audit'
    case 'access-control':
      return 'access'
    default:
      return 'home'
  }
}

function formatRoleLabel(role: string | undefined) {
  if (!role) {
    return 'Workspace operator'
  }

  return role
    .replace(/[._]/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

function MetricCard({
  label,
  value,
  meta,
  tone,
}: {
  label: string
  value: string | number
  meta: string
  tone: 'blue' | 'green' | 'amber' | 'cyan'
}) {
  return (
    <Card className={`metric-card metric-card--${tone}`}>
      <CardContent className="metric-card__content">
        <div className="metric-card__row">
          <span className="metric-card__label">{label}</span>
          <strong className="metric-card__value">{value}</strong>
        </div>
        <p className="metric-card__meta">{meta}</p>
        <span className="metric-card__bar" aria-hidden="true" />
      </CardContent>
    </Card>
  )
}

function ActionGroupSection({
  group,
  snapshot,
  search,
}: {
  group: VisibilityActionGroup
  snapshot: AccessSnapshot
  search: string
}) {
  const visibleActions = filterActions(group, snapshot.user.permissions, search)

  if (visibleActions.length === 0) {
    return null
  }

  return (
    <section className="action-section">
      <header className="action-section__header">
        <div>
          <h3 className="action-section__title">{group.title}</h3>
          <p className="action-section__description">{group.description}</p>
        </div>
        <Badge variant="info">{visibleActions.length} visible</Badge>
      </header>

      <div className="action-table">
        <div className="action-table__head" aria-hidden="true">
          <span>Action</span>
          <span>Permissions</span>
          <span>Route</span>
        </div>
        {visibleActions.map((action) => (
          <article className="action-table__row" key={action.id}>
            <div className="action-table__main">
              <div className="action-table__title-row">
                <h4 className="action-table__title">{action.label}</h4>
              </div>
              <p className="action-table__description">{action.description}</p>
            </div>

            <div className="action-table__meta">
              <div className="action-table__permissions">
                {action.required_permissions.map((permission) => (
                  <code className="permission-token" key={permission}>
                    {permission}
                  </code>
                ))}
              </div>
            </div>

            <div className="action-table__route">
              <Badge>{action.match}</Badge>
              {action.href ? <span className="action-table__path">{action.href}</span> : null}
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}

function RouteContent({
  pathname,
  snapshot,
  search,
}: {
  pathname: string
  snapshot: AccessSnapshot
  search: string
}) {
  const workflowGroup = snapshot.visibility.action_groups.filter((group) => group.id === 'workflow-admin')
  const communicationGroup = snapshot.visibility.action_groups.filter(
    (group) => group.id === 'communication-ops',
  )
  const governanceGroup = snapshot.visibility.action_groups.filter((group) => group.id === 'governance')

  const routeMap: Record<
    string,
    {
      title: string
      description: string
      groups: VisibilityActionGroup[]
    }
  > = {
    '/workflows': {
      title: 'Workflow Console',
      description:
        'Versioned definitions, controlled publishing, and approval operations exposed for the active session.',
      groups: workflowGroup,
    },
    '/tasks': {
      title: 'Task Inbox',
      description:
        'Keep approver work clear and scoped so teams only see tasks and controls they can legitimately act on.',
      groups: workflowGroup,
    },
    '/notifications': {
      title: 'Notification Center',
      description:
        'Delivery and retry controls stay visible only to communication operators while passive users get a cleaner view.',
      groups: communicationGroup,
    },
    '/audit': {
      title: 'Audit Trail',
      description:
        'Governance users can inspect protected events without exposing these operational controls to every role.',
      groups: governanceGroup,
    },
    '/access': {
      title: 'Access Control',
      description:
        'Role and permission management remains available only to administrative users with the required backend grants.',
      groups: governanceGroup,
    },
  }

  const routeConfig = routeMap[pathname] ?? {
    title: 'Foundation Overview',
    description:
      'Inspect tenant-scoped navigation, permission contracts, and backend-enforced visibility rules from one operational workspace.',
    groups: snapshot.visibility.action_groups,
  }

  const hasFilteredView = search.trim().length > 0
  const visibleSectionCount = routeConfig.groups.filter(
    (group) => filterActions(group, snapshot.user.permissions, search).length > 0,
  ).length

  return (
    <Card className="workspace-panel">
      <CardHeader className="workspace-panel__header">
        <div className="workspace-panel__intro">
          <div>
            <span className="workspace-panel__eyebrow">Action catalog</span>
            <CardTitle className="workspace-panel__title">{routeConfig.title}</CardTitle>
            <CardDescription>{routeConfig.description}</CardDescription>
          </div>
          <div className="workspace-panel__badges">
            {hasFilteredView ? <Badge variant="warning">Filtered</Badge> : null}
            <Badge variant="info">{visibleSectionCount} sections</Badge>
          </div>
        </div>
      </CardHeader>

      <CardContent className="workspace-panel__content">
        {visibleSectionCount === 0 ? (
          <div className="empty-panel">
            <h3 className="empty-panel__title">No actions match the current filter</h3>
            <p className="empty-panel__copy">
              Change the filter text or switch personas to inspect a different contract view.
            </p>
          </div>
        ) : (
          routeConfig.groups.map((group) => (
            <ActionGroupSection key={group.id} group={group} snapshot={snapshot} search={search} />
          ))
        )}
      </CardContent>
    </Card>
  )
}

export function VisibilityWorkbench() {
  const dispatch = useAppDispatch()
  const access = useAppSelector((state) => state.access)
  const { snapshot, isLoading, error, source } = useAccessSnapshot()
  const location = useLocation()
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false)
  const deferredSearch = useDeferredValue(search)

  const visibleNavigation = useMemo(
    () => snapshot?.visibility.navigation.filter((item) => item.visible) ?? [],
    [snapshot],
  )

  useEffect(() => {
    if (!snapshot || visibleNavigation.length === 0) {
      return
    }

    const visiblePaths = visibleNavigation
      .map((item) => item.href)
      .filter((href): href is string => Boolean(href))

    const currentPath = location.pathname === '/' ? null : location.pathname

    if (currentPath === null || !visiblePaths.includes(currentPath)) {
      navigate(visiblePaths[0] ?? '/foundation', { replace: true })
    }
  }, [location.pathname, navigate, snapshot, visibleNavigation])

  const activeItem =
    visibleNavigation.find((item) => item.href === location.pathname) ?? visibleNavigation[0] ?? null

  const hiddenNavigation = snapshot?.visibility.navigation.filter((item) => !item.visible) ?? []

  function renderState() {
    if (isLoading) {
      return (
        <Card className="state-card">
          <CardContent className="state-card__content">
            <div className="state-card__indicator" />
            <div>
              <h3 className="state-card__title">Loading live visibility contract</h3>
              <p className="state-card__copy">
                Retrieving the authenticated session and backend visibility rules for this workspace.
              </p>
            </div>
          </CardContent>
        </Card>
      )
    }

    if (error) {
      return (
        <Card className="state-card state-card--error">
          <CardContent className="state-card__content">
            <div className="state-card__indicator" />
            <div>
              <h3 className="state-card__title">The live session could not be loaded</h3>
              <p className="state-card__copy">{error.message}</p>
            </div>
          </CardContent>
        </Card>
      )
    }

    if (!snapshot) {
      return (
        <Card className="state-card">
          <CardContent className="empty-panel">
            <h3 className="empty-panel__title">Connect a live token or keep using demo personas</h3>
            <p className="empty-panel__copy">
              Live mode expects a bearer token from the platform API. Demo mode remains useful while
              the foundation services are still taking shape.
            </p>
          </CardContent>
        </Card>
      )
    }

    return (
      <>
        <section className="metrics-strip" aria-label="Workspace summary">
          <MetricCard
            label="Visible navigation"
            value={snapshot.visibility.meta.visible_navigation_count}
            meta="Routes exposed to the current session."
            tone="blue"
          />
          <MetricCard
            label="Visible actions"
            value={countVisibleActions(snapshot)}
            meta="Operational actions currently available."
            tone="green"
          />
          <MetricCard
            label="Permission grants"
            value={snapshot.user.permissions.length}
            meta="Backend-granted capabilities for this identity."
            tone="amber"
          />
          <MetricCard
            label="Tenant context"
            value={snapshot.user.tenant.currency ?? 'N/A'}
            meta={`${snapshot.user.tenant.company_name} · ${snapshot.user.tenant.timezone}`}
            tone="cyan"
          />
        </section>

        <section className="workspace-grid">
          <div className="workspace-grid__main">
            <Routes>
              <Route
                path="*"
                element={
                  <RouteContent
                    pathname={location.pathname}
                    snapshot={snapshot}
                    search={deferredSearch}
                  />
                }
              />
            </Routes>
          </div>

          <aside className="workspace-grid__rail" aria-label="Governance summary">
            <Card className="rail-card">
              <CardHeader>
                <CardTitle>Governance posture</CardTitle>
                <CardDescription>{snapshot.visibility.meta.backend_enforcement_note}</CardDescription>
              </CardHeader>
              <CardContent className="rail-stats">
                <div className="rail-stats__row">
                  <span>Hidden routes</span>
                  <strong>{snapshot.visibility.meta.hidden_navigation_count}</strong>
                </div>
                <div className="rail-stats__row">
                  <span>Suppressed actions</span>
                  <strong>{countHiddenActions(snapshot)}</strong>
                </div>
                <div className="rail-stats__row">
                  <span>Contract source</span>
                  <strong>{source === 'demo' ? 'Demo' : 'Live API'}</strong>
                </div>
              </CardContent>
            </Card>

            <Card className="rail-card">
              <CardHeader>
                <CardTitle>Granted permissions</CardTitle>
                <CardDescription>
                  Useful for checking whether the shell is hiding the correct controls.
                </CardDescription>
              </CardHeader>
              <CardContent className="permission-list">
                {snapshot.user.permissions.map((permission) => (
                  <code className="permission-token" key={permission}>
                    {permission}
                  </code>
                ))}
              </CardContent>
            </Card>

            <Card className="rail-card">
              <CardHeader>
                <CardTitle>Hidden navigation</CardTitle>
                <CardDescription>Routes excluded from the current visibility contract.</CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="hidden-route-list">
                  {hiddenNavigation.length === 0 ? (
                    <li className="hidden-route-list__empty">No routes are hidden for this session.</li>
                  ) : (
                    hiddenNavigation.map((item: VisibilityItem) => (
                      <li className="hidden-route-list__item" key={item.id}>
                        <div>
                          <strong>{item.label}</strong>
                          <p>{item.description}</p>
                        </div>
                        <span>{item.required_permissions.join(', ')}</span>
                      </li>
                    ))
                  )}
                </ul>
              </CardContent>
            </Card>
          </aside>
        </section>
      </>
    )
  }

  return (
    <main className={`console-shell${sidebarCollapsed ? ' console-shell--collapsed' : ''}`}>
      <aside className="console-sidebar" aria-label="Navigation contract">
        <div className="console-sidebar__header">
          <div className="console-brand">
            <div className="console-brand__mark">PH</div>
            <div className="console-brand__copy">
              <span>PhoenixHRMS</span>
              <strong>Access Console</strong>
            </div>
          </div>

          <Button
            aria-label={sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
            className="icon-button"
            variant="ghost"
            size="sm"
            onClick={() => setSidebarCollapsed((value) => !value)}
          >
            <Icon name="menu" />
          </Button>
        </div>

        <div className="console-sidebar__profile">
          <div className="console-avatar">{snapshot?.user.initials ?? 'PA'}</div>
          <div className="console-profile__copy">
            <strong>{snapshot?.user.name ?? demoPersonaLabels[access.demoPersona]}</strong>
            <span>{formatRoleLabel(snapshot?.user.roles[0])}</span>
          </div>
        </div>

        <nav className="console-nav">
          <p className="console-nav__label">Workspace</p>
          {visibleNavigation.map((item) => (
            <NavLink
              key={item.id}
              to={item.href ?? '/foundation'}
              className={({ isActive }) =>
                `console-nav__link${isActive || activeItem?.id === item.id ? ' console-nav__link--active' : ''}`
              }
            >
              <span className="console-nav__icon">
                <Icon name={iconForNavigation(item.id)} />
              </span>
              <span className="console-nav__copy">
                <strong>{item.label}</strong>
              </span>
            </NavLink>
          ))}
        </nav>

        <div className="console-sidebar__controls">
          <div className="sidebar-control">
            <span className="sidebar-control__label">Mode</span>
            <div className="segmented-control" role="tablist" aria-label="Session source">
              {(['demo', 'live'] as const).map((mode) => (
                <Button
                  key={mode}
                  variant={access.mode === mode ? 'primary' : 'segmented'}
                  size="sm"
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
            <div className="sidebar-control">
              <span className="sidebar-control__label">Persona</span>
              <div className="persona-list">
                {(Object.keys(demoPersonaLabels) as Array<keyof typeof demoPersonaLabels>).map((persona) => (
                  <Button
                    key={persona}
                    className="persona-button"
                    variant={access.demoPersona === persona ? 'primary' : 'secondary'}
                    size="sm"
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
            <div className="sidebar-control sidebar-control--stack">
              <label className="sidebar-control__label" htmlFor="api-base-url">
                API base URL
              </label>
              <Input
                id="api-base-url"
                type="url"
                value={access.apiBaseUrl}
                onChange={(event) => dispatch(setApiBaseUrl(event.target.value))}
                placeholder="http://127.0.0.1:8000/api/v1"
              />
              <label className="sidebar-control__label" htmlFor="bearer-token">
                Bearer token
              </label>
              <Textarea
                id="bearer-token"
                value={access.token}
                onChange={(event) => dispatch(setToken(event.target.value))}
                placeholder="Paste a bearer token from the platform API"
              />
              <Button
                variant="primary"
                onClick={() =>
                  startTransition(() => {
                    dispatch(setToken(access.token.trim()))
                  })
                }
              >
                Refresh contract
              </Button>
            </div>
          )}
        </div>
      </aside>

      <section className="console-main">
        <header className="workspace-topbar">
          <div className="workspace-topbar__title">
            <span className="workspace-topbar__eyebrow">Access governance</span>
            <h1>{activeItem?.label ?? 'Foundation Overview'}</h1>
          </div>

          <div className="workspace-topbar__search">
            <span className="workspace-topbar__search-icon">
              <Icon name="search" />
            </span>
            <Input
              type="search"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Filter actions by label, description, or permission"
              aria-label="Filter visible actions"
            />
          </div>

          <div className="workspace-topbar__actions">
            <Button className="icon-button icon-button--dark" variant="ghost" size="sm" aria-label="Notifications">
              <Icon name="bell" />
            </Button>
            <Button className="icon-button icon-button--dark" variant="ghost" size="sm" aria-label="Workspace apps">
              <Icon name="grid" />
            </Button>
            <Button className="icon-button icon-button--dark" variant="ghost" size="sm" aria-label="Settings">
              <Icon name="settings" />
            </Button>
            <Badge variant={source === 'demo' ? 'warning' : 'success'}>
              {source === 'demo' ? 'Demo contract' : 'Live contract'}
            </Badge>
            <div className="workspace-topbar__user">
              <span className="workspace-topbar__user-avatar">
                {snapshot?.user.initials ?? 'PA'}
              </span>
              <span className="workspace-topbar__user-name">
                {snapshot?.user.name ?? demoPersonaLabels[access.demoPersona]}
              </span>
            </div>
          </div>
        </header>

        <div className="workspace-header">
          <p className="workspace-header__copy">
            Tenant-scoped navigation, operational permissions, and backend-enforced visibility rules.
          </p>

          <div className="workspace-header__facts">
            <div>
              <span>Tenant</span>
              <strong>{snapshot?.user.tenant.company_name ?? 'Phoenix Demo Company'}</strong>
            </div>
            <div>
              <span>Plan</span>
              <strong>{snapshot?.user.tenant.subscription_plan ?? 'enterprise'}</strong>
            </div>
            <div>
              <span>Timezone</span>
              <strong>{snapshot?.user.tenant.timezone ?? 'Asia/Kolkata'}</strong>
            </div>
          </div>
        </div>

        {renderState()}
      </section>
    </main>
  )
}
