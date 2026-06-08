import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import { startTransition, useEffect, useMemo, useState } from 'react'
import {
  Bell,
  Building2,
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  CircleHelp,
  Clock3,
  Command,
  LogOut,
  PanelLeftClose,
  PanelLeftOpen,
  Search,
  Settings2,
  Star,
  UserRound,
  X,
} from 'lucide-react'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { demoPersonaLabels } from '../../modules/access/data/demoSnapshots'
import { buildDemoEmployeeWorkspace } from '../../modules/employees/data/demoEmployeeProfiles'
import { matchEmployeeDetailSection } from '../../modules/employees/navigation'
import { hasPermissions } from '../../shared/auth/permissions'
import { Button } from '../../shared/ui/button'
import { cn } from '../../shared/ui/cn'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '../../shared/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '../../shared/ui/dialog'
import { Input } from '../../shared/ui/input'
import { setDemoPersona, setMode } from '../store/accessSlice'
import { useAppDispatch } from '../store/hooks'
import { useShellFavorites, type ShellFavoriteDraft } from './favorites'
import { appNavigation, type AppNavItem } from './navigation'
import { useShellRecent } from './recent'

const SHELL_COLLAPSE_STORAGE_KEY = 'phoenixhrms.shell.collapsed'

type NavIconName = AppNavItem['icon']

type CommandCenterItem = {
  id: string
  label: string
  description: string
  path: string
  icon: NavIconName
  source: 'Favorites' | 'Recent' | 'Navigation'
  meta?: string
}

function formatRoleLabel(role: string | undefined) {
  if (!role) {
    return 'No role loaded'
  }

  return role
    .replace(/[._]/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

function formatPlanLabel(plan: string | null | undefined) {
  if (!plan) {
    return 'Enterprise admin'
  }

  return `${plan.replace(/[._-]/g, ' ').replace(/\b\w/g, (character) => character.toUpperCase())} plan`
}

function initialsFromName(name: string | undefined) {
  if (!name) {
    return 'PH'
  }

  const parts = name.trim().split(/\s+/).slice(0, 2)
  return parts.map((part) => part[0]?.toUpperCase() ?? '').join('') || 'PH'
}

function formatRelativeVisit(visitedAt: number) {
  const diffMs = Date.now() - visitedAt
  const diffMinutes = Math.floor(diffMs / 60000)

  if (diffMinutes <= 0) {
    return 'Just now'
  }

  if (diffMinutes < 60) {
    return `${diffMinutes}m ago`
  }

  const diffHours = Math.floor(diffMinutes / 60)
  if (diffHours < 24) {
    return `${diffHours}h ago`
  }

  const diffDays = Math.floor(diffHours / 24)
  return `${diffDays}d ago`
}

function ShellIcon({ name }: { name: NavIconName }) {
  const iconProps = {
    viewBox: '0 0 20 20',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 1.6,
    strokeLinecap: 'round' as const,
    strokeLinejoin: 'round' as const,
    className: 'h-4 w-4',
    'aria-hidden': true,
  }

  switch (name) {
    case 'organization':
      return (
        <svg {...iconProps}>
          <path d="M4 16V6l6-3 6 3v10" />
          <path d="M7 9h2M7 12h2M11 9h2M11 12h2" />
        </svg>
      )
    case 'employees':
      return (
        <svg {...iconProps}>
          <path d="M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
          <path d="M4.5 16a5.5 5.5 0 0 1 11 0" />
        </svg>
      )
    case 'attendance':
      return (
        <svg {...iconProps}>
          <path d="M5 4h10v12H5z" />
          <path d="M7 2v4M13 2v4M7 9h6M10 9v4" />
        </svg>
      )
    case 'leave':
      return (
        <svg {...iconProps}>
          <path d="M6 4h8l2 2v10H4V4z" />
          <path d="M7 8h6M7 11h6M7 14h4" />
        </svg>
      )
    case 'access':
      return (
        <svg {...iconProps}>
          <path d="M10 3 5 5v4c0 3.4 2 5.9 5 7 3-1.1 5-3.6 5-7V5z" />
          <path d="M8.2 9.8 9.5 11l2.5-2.7" />
        </svg>
      )
    case 'foundation':
    default:
      return (
        <svg {...iconProps}>
          <path d="M4 10 10 4l6 6v6H4z" />
          <path d="M8 16v-4h4v4" />
        </svg>
      )
  }
}

function isPathForNav(pathname: string, path: string) {
  return pathname === path || pathname.startsWith(`${path}/`)
}

export function AppShell() {
  const dispatch = useAppDispatch()
  const location = useLocation()
  const navigate = useNavigate()
  const { snapshot } = useAccessSnapshot()
  const [isRailCollapsed, setIsRailCollapsed] = useState(false)
  const [isCommandCenterOpen, setIsCommandCenterOpen] = useState(false)
  const [commandQuery, setCommandQuery] = useState('')
  const { recentItems, touchRecent, removeRecent, clearRecent } = useShellRecent()

  const grantedPermissions = useMemo(() => snapshot?.user.permissions ?? [], [snapshot?.user.permissions])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    const storedCollapse = window.localStorage.getItem(SHELL_COLLAPSE_STORAGE_KEY)
    setIsRailCollapsed(storedCollapse === 'true')
  }, [])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    window.localStorage.setItem(SHELL_COLLAPSE_STORAGE_KEY, String(isRailCollapsed))
  }, [isRailCollapsed])

  const visibleNavigation = useMemo(() => {
    return appNavigation.filter((item) =>
      hasPermissions(grantedPermissions, item.requiredPermissions, item.match ?? 'all'),
    )
  }, [grantedPermissions])

  const currentPage =
    visibleNavigation.find((item) => isPathForNav(location.pathname, item.to)) ??
    visibleNavigation[0] ??
    appNavigation[0]

  const currentPageChildren =
    currentPage.children?.filter((item) =>
      hasPermissions(grantedPermissions, item.requiredPermissions, item.match ?? 'all'),
    ) ?? []

  const employeeDetailSection =
    currentPage.id === 'employees' ? matchEmployeeDetailSection(location.pathname) : null

  const currentSection =
    currentPageChildren.find((item) => isPathForNav(location.pathname, item.to)) ?? employeeDetailSection

  const currentRoleLabel = formatRoleLabel(snapshot?.user.roles[0])
  const tenantLabel = snapshot?.user.tenant.company_name ?? 'Workspace pending'
  const planLabel = formatPlanLabel(snapshot?.user.tenant.subscription_plan)
  const userName = snapshot?.user.name ?? 'PhoenixHRMS User'
  const userInitials = snapshot?.user.initials ?? initialsFromName(userName)
  const currentSectionLabel = currentSection?.label ?? null
  const currentRecentLabel = useMemo(() => {
    const employeeDetailMatch = location.pathname.match(/^\/employees\/(\d+)\/([^/]+)/)
    if (employeeDetailMatch) {
      const employeeId = Number(employeeDetailMatch[1])
      const workspace = buildDemoEmployeeWorkspace(snapshot ?? null, Number.isNaN(employeeId) ? null : employeeId)
      const detailSection = matchEmployeeDetailSection(location.pathname)

      if (workspace?.employee) {
        return `${workspace.employee.full_name} · ${detailSection?.label ?? 'Profile'}`
      }
    }

    if (location.pathname === '/access') {
      if (location.hash === '#actions') {
        return 'Access · Actions'
      }

      if (location.hash === '#diagnostics') {
        return 'Access · Diagnostics'
      }

      return 'Access · Routes'
    }

    if (location.pathname === '/attendance/operational-review') {
      return location.hash === '#exceptions'
        ? 'Attendance · Exception records'
        : 'Attendance · Decision queue'
    }

    return currentSectionLabel ? `${currentPage.label} · ${currentSectionLabel}` : currentPage.label
  }, [currentPage.label, currentSectionLabel, location.hash, location.pathname, snapshot])

  useEffect(() => {
    touchRecent({
      path: `${location.pathname}${location.hash}`,
      label: currentRecentLabel,
      icon: currentPage.icon,
    })
  }, [currentPage.icon, currentRecentLabel, location.hash, location.pathname, touchRecent])

  const defaultFavoriteItems = useMemo<ShellFavoriteDraft[]>(() => {
    const visiblePaths = new Set(
      visibleNavigation.flatMap((item) => [
        item.to,
        ...(item.children
          ?.filter((child) => hasPermissions(grantedPermissions, child.requiredPermissions, child.match ?? 'all'))
          .map((child) => child.to) ?? []),
      ]),
    )

    const candidates: Array<ShellFavoriteDraft> = [
      {
        label: 'Employees',
        path: '/employees/directory',
        icon: 'employees',
        description: 'Pinned workforce directory',
        meta: 'Directory',
      },
      {
        label: 'Attendance Overview',
        path: '/attendance/overview',
        icon: 'attendance',
        description: 'Pinned attendance operations center',
        meta: 'Overview',
      },
      {
        label: 'Leave Approvals',
        path: '/leave/approvals',
        icon: 'leave',
        description: 'Pinned approval queue',
        meta: 'Approvals',
      },
      {
        label: 'Organization Structure',
        path: '/admin/organization/structure',
        icon: 'organization',
        description: 'Pinned structure registry',
        meta: 'Structure',
      },
    ]

    return candidates.filter((item) => visiblePaths.has(item.path))
  }, [grantedPermissions, visibleNavigation])

  const { favorites, isFavorite, toggleFavorite } = useShellFavorites(defaultFavoriteItems)

  const favoriteItems = useMemo(() => {
    const visibleModuleIcons = new Set(visibleNavigation.map((item) => item.icon))
    return favorites.filter((item) => visibleModuleIcons.has(item.icon))
  }, [favorites, visibleNavigation])

  const visibleRecentItems = useMemo(() => {
    const visibleModuleIcons = new Set(visibleNavigation.map((item) => item.icon))
    return recentItems.filter((item) => visibleModuleIcons.has(item.icon))
  }, [recentItems, visibleNavigation])

  const commandCenterSections = useMemo(() => {
    const favorites: CommandCenterItem[] = favoriteItems.map((item) => ({
      id: `favorite-${item.path}`,
      label: item.label,
      description: item.description,
      path: item.path,
      icon: item.icon,
      source: 'Favorites',
      meta: item.meta,
    }))

    const recent: CommandCenterItem[] = visibleRecentItems.map((item) => ({
      id: `recent-${item.path}`,
      label: item.label,
      description: 'Recently visited',
      path: item.path,
      icon: item.icon,
      source: 'Recent',
      meta: formatRelativeVisit(item.visitedAt),
    }))

    const reservedPaths = new Set([...favorites, ...recent].map((item) => item.path))
    const navigation: CommandCenterItem[] = visibleNavigation.flatMap((item) => {
      const childItems =
        item.children
          ?.filter((child) =>
            hasPermissions(grantedPermissions, child.requiredPermissions, child.match ?? 'all'),
          )
          .map((child) => ({
            id: `${item.id}-${child.id}`,
            label: child.label,
            description: child.description,
            path: child.to,
            icon: item.icon,
            source: 'Navigation' as const,
            meta: item.label,
          })) ?? []

      const moduleItem: CommandCenterItem = {
        id: item.id,
        label: item.label,
        description: item.description,
        path: item.to,
        icon: item.icon,
        source: 'Navigation',
        meta: 'Module overview',
      }

      return [moduleItem, ...childItems]
    }).filter((item) => !reservedPaths.has(item.path))

    return [
      { title: 'Favorites', items: favorites },
      { title: 'Recent', items: recent },
      { title: 'Navigation', items: navigation },
    ].filter((section) => section.items.length)
  }, [favoriteItems, grantedPermissions, visibleNavigation, visibleRecentItems])

  const filteredCommandCenterSections = useMemo(() => {
    const query = commandQuery.trim().toLowerCase()

    if (!query) {
      return commandCenterSections
    }

    return commandCenterSections
      .map((section) => ({
        ...section,
        items: section.items.filter((item) =>
          `${item.label} ${item.description} ${item.meta ?? ''} ${item.source}`.toLowerCase().includes(query),
        ),
      }))
      .filter((section) => section.items.length)
  }, [commandCenterSections, commandQuery])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    const handleKeyDown = (event: KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault()
        setIsCommandCenterOpen(true)
      }
    }

    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [])

  const openCommandCenter = () => {
    setCommandQuery('')
    setIsCommandCenterOpen(true)
  }

  const handleCommandNavigation = (path: string) => {
    setIsCommandCenterOpen(false)
    setCommandQuery('')
    navigate(path)
  }

  return (
    <div
      className={cn(
        'min-h-svh bg-page lg:grid',
        isRailCollapsed ? 'lg:grid-cols-[4.5rem_minmax(0,1fr)]' : 'lg:grid-cols-[17.5rem_minmax(0,1fr)]',
      )}
    >
      <aside className="border-b border-[#0c1220] bg-[linear-gradient(180deg,#0f1624_0%,#0b1220_100%)] lg:sticky lg:top-0 lg:h-svh lg:border-b-0 lg:border-r lg:border-r-white/[0.06]">
        <div
          className={cn(
            'relative flex h-full min-h-svh flex-col overflow-hidden py-3',
            isRailCollapsed ? 'px-2.5' : 'px-3',
          )}
        >
          <div className="pointer-events-none absolute inset-x-0 top-0 h-40 bg-[radial-gradient(circle_at_top_left,rgba(245,144,59,0.18),transparent_55%),radial-gradient(circle_at_top_right,rgba(60,116,255,0.14),transparent_48%)]" />
          <div className="pointer-events-none absolute inset-y-0 right-0 w-px bg-[linear-gradient(180deg,rgba(255,255,255,0.05),transparent_18%,transparent_82%,rgba(255,255,255,0.03))]" />

          <div className="relative flex h-full min-h-0 flex-col">
            <div className={cn('flex items-center gap-3 px-2 py-1', isRailCollapsed && 'justify-center px-0')}>
              <div className="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-[#f8c27f]/20 bg-[linear-gradient(135deg,#ffb458_0%,#ea8a34_58%,#c96718_100%)] text-sm font-bold text-white shadow-[0_14px_28px_rgba(233,137,52,0.34)]">
                P
              </div>
              {!isRailCollapsed ? (
                <div className="min-w-0">
                  <p className="ui-type-page-eyebrow text-[#9daaba]">PhoenixHRMS</p>
                  <strong className="ui-type-card-title block text-white">Operations console</strong>
                </div>
              ) : null}
            </div>

            <div className="mt-2 min-h-0 flex-1 overflow-y-auto pr-1">
              <div className={cn('space-y-3', isRailCollapsed && 'space-y-2')}>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <button
                      type="button"
                      className={cn(
                        'w-full rounded-2xl border border-white/[0.08] bg-[linear-gradient(180deg,rgba(255,255,255,0.05)_0%,rgba(255,255,255,0.025)_100%)] text-left shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] transition-colors hover:bg-white/[0.07]',
                        isRailCollapsed ? 'mx-auto flex h-11 w-11 items-center justify-center rounded-2xl px-0' : 'flex items-center gap-3 px-3 py-2.5',
                      )}
                      aria-label="Workspace switcher"
                    >
                      <span className="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-[#ffb663]/20 bg-[linear-gradient(180deg,#ff9c3f_0%,#f0872f_100%)] text-white shadow-[0_12px_22px_rgba(240,135,47,0.28)]">
                        <Building2 className="h-5 w-5" />
                      </span>
                      {!isRailCollapsed ? (
                        <>
                          <span className="min-w-0 flex-1">
                            <strong className="ui-type-body-strong block truncate text-white">{tenantLabel}</strong>
                            <span className="ui-type-caption block truncate text-[#8fa0b3]">{planLabel}</span>
                          </span>
                          <ChevronDown className="h-4 w-4 text-[#91a0b5]" />
                        </>
                      ) : null}
                    </button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="start" className="w-72">
                    <DropdownMenuLabel>Workspace switcher</DropdownMenuLabel>
                    <DropdownMenuItem className="flex-col items-start gap-0.5">
                      <span className="ui-type-body-strong">{tenantLabel}</span>
                      <span className="ui-type-caption text-muted-foreground">{planLabel}</span>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuLabel>Session mode</DropdownMenuLabel>
                    <DropdownMenuItem
                      onSelect={() =>
                        startTransition(() => {
                          dispatch(setMode('demo'))
                        })
                      }
                    >
                      Demo workspace
                    </DropdownMenuItem>
                    <DropdownMenuItem
                      onSelect={() =>
                        startTransition(() => {
                          dispatch(setMode('live'))
                        })
                      }
                    >
                      Live API session
                    </DropdownMenuItem>
                    {snapshot ? (
                      <>
                        <DropdownMenuSeparator />
                        <DropdownMenuLabel>Demo personas</DropdownMenuLabel>
                        {(Object.keys(demoPersonaLabels) as Array<keyof typeof demoPersonaLabels>).map((persona) => (
                          <DropdownMenuItem
                            key={persona}
                            onSelect={() =>
                              startTransition(() => {
                                dispatch(setMode('demo'))
                                dispatch(setDemoPersona(persona))
                              })
                            }
                          >
                            {demoPersonaLabels[persona]}
                          </DropdownMenuItem>
                        ))}
                      </>
                    ) : null}
                  </DropdownMenuContent>
                </DropdownMenu>

                {!isRailCollapsed ? (
                  <div className="relative">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8091a7]" />
                    <button
                      type="button"
                      onClick={openCommandCenter}
                      className="flex h-10 w-full items-center rounded-2xl border border-white/[0.08] bg-[#121b2b] pl-10 pr-14 text-left text-[0.95rem] text-[#8ea0b3] shadow-[inset_0_1px_0_rgba(255,255,255,0.03)] transition-colors hover:bg-[#172233] hover:text-[#dfe7f1]"
                    >
                      Search employee, shift, department...
                    </button>
                    <span className="pointer-events-none absolute right-3 top-1/2 inline-flex -translate-y-1/2 items-center gap-1 rounded-md border border-white/[0.08] bg-white/[0.04] px-2 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-[#9eacc0]">
                      <Command className="h-3 w-3" />
                      K
                    </span>
                  </div>
                ) : (
                  <button
                    type="button"
                    aria-label="Expand sidebar search"
                    className="mx-auto grid h-11 w-11 place-items-center rounded-2xl border border-white/[0.08] bg-white/[0.035] text-[#dce4ee] shadow-[inset_0_1px_0_rgba(255,255,255,0.03)] transition-colors hover:bg-white/[0.07]"
                    onClick={openCommandCenter}
                    title="Search"
                  >
                    <Search className="h-5 w-5" />
                  </button>
                )}

                {!isRailCollapsed ? (
                  <div className="flex items-center justify-between rounded-xl border border-white/[0.05] bg-white/[0.025] px-3 py-2">
                    <span className="ui-type-page-eyebrow text-[#8ea0b3]">Role</span>
                    <span className="ui-type-caption font-semibold text-[#e4ebf4]">{currentRoleLabel}</span>
                  </div>
                ) : null}

                <div className="space-y-2">
                  {!isRailCollapsed ? (
                    <div className="flex items-center gap-3 px-1">
                      <span className="ui-type-page-eyebrow text-[#8ea0b3]">Main navigation</span>
                      <span className="h-px flex-1 bg-[linear-gradient(90deg,rgba(255,255,255,0.12),transparent)]" />
                    </div>
                  ) : (
                    <div className="mx-auto h-px w-8 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.16),transparent)]" />
                  )}

                  <nav className={cn('space-y-0.5', isRailCollapsed && 'space-y-1')} aria-label="Primary">
                    {visibleNavigation.map((item) => {
                      const visibleChildren =
                        item.children?.filter((child) =>
                          hasPermissions(grantedPermissions, child.requiredPermissions, child.match ?? 'all'),
                        ) ?? []
                      const isCurrentModule = currentPage.id === item.id
                      const activeChildren = visibleChildren

                      return (
                        <div key={item.id} className="space-y-1">
                          <NavLink
                            to={item.to}
                            title={isRailCollapsed ? item.label : undefined}
                            className={({ isActive }) =>
                              cn(
                                'group flex items-center rounded-xl px-2.5 py-2 text-[#dfe6ef] transition-all duration-200',
                                isRailCollapsed ? 'mx-auto h-12 w-12 justify-center rounded-2xl px-0 py-0' : 'gap-3',
                                isActive || isCurrentModule
                                  ? 'border border-[#4978f6]/70 bg-[linear-gradient(180deg,#2f66f2_0%,#2358dc_100%)] text-white shadow-[0_16px_28px_rgba(35,88,220,0.28)]'
                                  : 'border border-transparent text-[#e0e8f1] hover:bg-white/[0.045] hover:text-white',
                              )
                            }
                          >
                            <span
                              className={cn(
                                'grid h-8 w-8 shrink-0 place-items-center rounded-lg border transition-colors',
                                isCurrentModule
                                  ? 'border-white/15 bg-white/[0.1] text-white'
                                  : 'border-white/[0.05] bg-white/[0.02] text-[#d9e2ec] group-hover:border-white/[0.08] group-hover:bg-white/[0.04]',
                                isRailCollapsed && 'h-10 w-10 rounded-2xl',
                              )}
                            >
                              <ShellIcon name={item.icon} />
                            </span>
                            {!isRailCollapsed ? (
                              <>
                                <span className="min-w-0 flex-1">
                                  <strong className="ui-type-body-strong block truncate text-[#edf2f8] group-hover:text-white">{item.label}</strong>
                                </span>
                                {activeChildren.length ? (
                                  <ChevronRight
                                    className={cn(
                                      'h-4 w-4 shrink-0 text-[#8fa0b3] transition-transform',
                                      isCurrentModule && 'rotate-90',
                                    )}
                                  />
                                ) : null}
                              </>
                            ) : null}
                          </NavLink>

                          {!isRailCollapsed && isCurrentModule && activeChildren.length ? (
                            <nav
                              className="ml-4 border-l border-white/[0.07] pl-4"
                              aria-label={`${item.label} sections`}
                            >
                              <div className="space-y-0">
                                {activeChildren.map((child) => (
                                  <NavLink key={child.id} to={child.to} title={child.description} className="group block">
                                    {({ isActive }) => (
                                      <span
                                        className={cn(
                                          'flex items-center gap-3 rounded-lg px-2 py-1.5 text-sm transition-colors',
                                          isActive
                                            ? 'text-[#7ea7ff]'
                                            : 'text-[#a9b6c6] hover:bg-white/[0.04] hover:text-white',
                                        )}
                                      >
                                        <span
                                          className={cn(
                                            'h-1.5 w-1.5 rounded-full',
                                            isActive ? 'bg-[#4f7dff]' : 'bg-[#5f6f82]',
                                          )}
                                        />
                                        <span className={cn('truncate transition-colors', isActive ? 'text-[#7ea7ff]' : 'text-[#b8c5d5] group-hover:text-white')}>
                                          {child.label}
                                        </span>
                                      </span>
                                    )}
                                  </NavLink>
                                ))}
                              </div>
                            </nav>
                          ) : null}
                        </div>
                      )
                    })}
                  </nav>
                </div>

                {!isRailCollapsed ? (
                  <>
                    <div className="space-y-2">
                      <div className="flex items-center gap-3 px-1">
                        <span className="ui-type-page-eyebrow text-[#8ea0b3]">Favorites</span>
                        <span className="h-px flex-1 bg-[linear-gradient(90deg,rgba(255,255,255,0.12),transparent)]" />
                      </div>
                      <div className="space-y-0.5">
                        {favoriteItems.map((item) => (
                          <div
                            key={item.path}
                            className="flex items-center gap-2 rounded-lg px-1 py-0.5 transition-colors hover:bg-white/[0.05]"
                          >
                            <NavLink
                              to={item.path}
                              className="flex min-w-0 flex-1 items-center gap-3 rounded-lg px-1 py-1 text-[#d4dee8] transition-colors hover:text-white"
                            >
                              <span className="grid h-7.5 w-7.5 place-items-center rounded-lg border border-white/[0.05] bg-white/[0.025] text-[#f4b73f]">
                                <ShellIcon name={item.icon} />
                              </span>
                              <span className="truncate text-sm text-[#e4ebf4]">{item.label}</span>
                            </NavLink>
                            <button
                              type="button"
                              aria-label={`Unpin ${item.label}`}
                              className="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-[#f4b73f] transition-colors hover:bg-white/[0.06] hover:text-[#ffd26a]"
                              onClick={(event) => {
                                event.preventDefault()
                                event.stopPropagation()
                                toggleFavorite({
                                  path: item.path,
                                  label: item.label,
                                  icon: item.icon,
                                  description: item.description,
                                  meta: item.meta,
                                })
                              }}
                            >
                              <Star className="h-3.5 w-3.5 fill-current" />
                            </button>
                          </div>
                        ))}
                        {!favoriteItems.length ? (
                          <p className="px-2 py-2 text-sm text-[#8091a7]">No favorites pinned yet.</p>
                        ) : null}
                      </div>
                    </div>

                    <div className="space-y-2">
                      <div className="flex items-center gap-3 px-1">
                        <span className="ui-type-page-eyebrow text-[#8ea0b3]">Recent</span>
                        <span className="h-px flex-1 bg-[linear-gradient(90deg,rgba(255,255,255,0.12),transparent)]" />
                        {visibleRecentItems.length ? (
                          <button
                            type="button"
                            className="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-[#8ea0b3] transition-colors hover:text-white"
                            onClick={clearRecent}
                          >
                            Clear
                          </button>
                        ) : null}
                      </div>
                      <div className="space-y-0.5">
                        {visibleRecentItems.map((item) => (
                          <div
                            key={item.path}
                            className="flex items-center gap-2 rounded-lg px-1 py-0.5 transition-colors hover:bg-white/[0.05]"
                          >
                            <NavLink
                              to={item.path}
                              className="flex min-w-0 flex-1 items-center gap-3 rounded-lg px-1 py-1 text-[#d4dee8] transition-colors hover:text-white"
                            >
                              <span className="grid h-7.5 w-7.5 place-items-center rounded-lg border border-white/[0.05] bg-white/[0.025] text-[#9cb0c7]">
                                <ShellIcon name={item.icon} />
                              </span>
                              <span className="min-w-0 flex-1">
                                <span className="block truncate text-sm text-[#e4ebf4]">{item.label}</span>
                              </span>
                              <span className="shrink-0 text-[0.72rem] text-[#7f90a6]">
                                {formatRelativeVisit(item.visitedAt)}
                              </span>
                            </NavLink>
                            <button
                              type="button"
                              aria-label={`Remove ${item.label} from recent`}
                              className="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-[#93a4b8] transition-colors hover:bg-white/[0.06] hover:text-white"
                              onClick={(event) => {
                                event.preventDefault()
                                event.stopPropagation()
                                removeRecent(item.path)
                              }}
                            >
                              <X className="h-3.5 w-3.5" />
                            </button>
                          </div>
                        ))}
                        {!visibleRecentItems.length ? (
                          <p className="px-2 py-2 text-sm text-[#8091a7]">No recent pages yet.</p>
                        ) : null}
                      </div>
                    </div>
                  </>
                ) : null}
              </div>
            </div>

              <div className="mt-3 space-y-2">
              {isRailCollapsed ? (
                <div className="space-y-2">
                  <div className="mx-auto h-px w-8 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.16),transparent)]" />
                  <button
                    type="button"
                    title="Favorites"
                    className="mx-auto grid h-11 w-11 place-items-center rounded-2xl border border-white/[0.06] bg-white/[0.03] text-[#f2c251] transition-colors hover:bg-white/[0.06]"
                    onClick={() => setIsRailCollapsed(false)}
                  >
                    <Star className="h-5 w-5" />
                  </button>
                  <button
                    type="button"
                    title="Recent"
                    className="mx-auto grid h-11 w-11 place-items-center rounded-2xl border border-white/[0.06] bg-white/[0.03] text-[#cbd5e1] transition-colors hover:bg-white/[0.06]"
                    onClick={() => setIsRailCollapsed(false)}
                  >
                    <Clock3 className="h-5 w-5" />
                  </button>
                </div>
              ) : null}

              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <button
                    type="button"
                    className={cn(
                      'w-full rounded-2xl border border-white/[0.08] bg-[linear-gradient(180deg,rgba(255,255,255,0.05)_0%,rgba(255,255,255,0.025)_100%)] text-left shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] transition-colors hover:bg-white/[0.07]',
                      isRailCollapsed ? 'mx-auto flex h-12 w-12 items-center justify-center rounded-full px-0' : 'flex items-center gap-3 px-3 py-2.5',
                    )}
                    aria-label="Profile menu"
                  >
                    <span className="relative grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[linear-gradient(135deg,#f8fafc_0%,#d4dee9_100%)] text-[0.78rem] font-bold text-[#152130]">
                      {userInitials}
                      <span className="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#0f1624] bg-emerald-400" />
                    </span>
                    {!isRailCollapsed ? (
                      <>
                        <span className="min-w-0 flex-1">
                          <strong className="ui-type-body-strong block truncate text-white">{userName}</strong>
                          <span className="ui-type-caption block truncate text-[#8fa0b3]">{currentRoleLabel}</span>
                          <span className="mt-1 inline-flex items-center gap-1.5 text-[0.72rem] font-medium text-[#9ab0c9]">
                            <span className="h-2 w-2 rounded-full bg-emerald-400" />
                            Online
                          </span>
                        </span>
                        <ChevronDown className="h-4 w-4 text-[#91a0b5]" />
                      </>
                    ) : null}
                  </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="w-64">
                  <DropdownMenuLabel>{userName}</DropdownMenuLabel>
                  <DropdownMenuItem>
                    <UserRound className="h-4 w-4" />
                    My profile
                  </DropdownMenuItem>
                  <DropdownMenuItem>
                    <Bell className="h-4 w-4" />
                    Notifications
                  </DropdownMenuItem>
                  <DropdownMenuItem>
                    <Settings2 className="h-4 w-4" />
                    Settings
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem>
                    <CircleHelp className="h-4 w-4" />
                    Support center
                  </DropdownMenuItem>
                  <DropdownMenuItem className="text-destructive focus:text-destructive">
                    <LogOut className="h-4 w-4" />
                    Logout
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>

              <Button
                type="button"
                variant="ghost"
                size="sm"
                className={cn(
                  'h-10 w-full justify-start rounded-2xl border border-white/[0.06] bg-white/[0.03] text-[#d7e2ef] hover:bg-white/[0.06] hover:text-white',
                  isRailCollapsed && 'mx-auto h-11 w-11 justify-center rounded-2xl px-0',
                )}
                onClick={() => setIsRailCollapsed((current) => !current)}
              >
                {isRailCollapsed ? <PanelLeftOpen className="h-4 w-4" /> : <PanelLeftClose className="h-4 w-4" />}
                {!isRailCollapsed ? 'Collapse' : null}
              </Button>
            </div>
          </div>
        </div>
      </aside>

      <main className="min-w-0 bg-[radial-gradient(circle_at_top_right,rgba(92,167,255,0.08),transparent_26%),radial-gradient(circle_at_top_left,rgba(234,138,52,0.06),transparent_22%),var(--page-bg)]">
        <header className="relative overflow-hidden border-b border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.9)_0%,rgba(248,250,253,0.96)_100%)] px-4 py-4 shadow-[inset_0_-1px_0_rgba(255,255,255,0.7)] lg:px-6">
          <div className="pointer-events-none absolute inset-x-6 top-0 h-px bg-[linear-gradient(90deg,transparent,rgba(124,174,255,0.28),rgba(234,138,52,0.18),transparent)]" />
          <div className="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <div className="min-w-0 space-y-1">
              <p className="ui-type-page-eyebrow text-text-subtle">
                {currentPage.status === 'live' ? 'Live module' : 'Planned module'}
                {currentSection ? ` · ${currentSection.label}` : ''}
              </p>
              <h1 className="ui-type-page-title text-foreground">{currentPage.label}</h1>
              <p className="ui-type-body max-w-3xl text-muted-foreground">
                {currentSection?.description ?? currentPage.description}
              </p>
            </div>
            <div className="flex flex-wrap items-center gap-2 text-muted-foreground" aria-label="Session context">
              <button
                type="button"
                onClick={openCommandCenter}
                className="inline-flex items-center gap-2 rounded-full border border-line/80 bg-white/74 px-3 py-1.5 text-[0.83rem] font-semibold text-foreground shadow-[inset_0_1px_0_rgba(255,255,255,0.75)] transition-colors hover:bg-white"
              >
                <Command className="h-3.5 w-3.5 text-primary" />
                Command center
                <span className="inline-flex items-center gap-1 rounded-md border border-line/80 bg-page px-1.5 py-0.5 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-text-subtle">
                  <Command className="h-3 w-3" />
                  K
                </span>
              </button>
              <span className="ui-type-label rounded-full border border-line/80 bg-white/74 px-3 py-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)]">
                {tenantLabel}
              </span>
              <span className="ui-type-label rounded-full border border-line/80 bg-white/74 px-3 py-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.75)]">
                {currentRoleLabel}
              </span>
            </div>
          </div>
        </header>

        <section className="mx-auto w-full max-w-[1400px] px-4 py-4 lg:px-6 lg:py-5">
          <Outlet />
        </section>
      </main>

      <Dialog
        open={isCommandCenterOpen}
        onOpenChange={(open) => {
          setIsCommandCenterOpen(open)
          if (!open) {
            setCommandQuery('')
          }
        }}
      >
        <DialogContent size="md" className="border-line/80 bg-[linear-gradient(180deg,#ffffff_0%,#f7faff_100%)] p-0">
          <DialogHeader className="border-b border-line/80 px-5 py-4">
            <DialogTitle>Command center</DialogTitle>
            <DialogDescription>
              Search across workspaces, module views, and recent destinations.
            </DialogDescription>
          </DialogHeader>
          <div className="border-b border-line/80 px-5 py-4">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-subtle" />
              <Input
                autoFocus
                value={commandQuery}
                onChange={(event) => setCommandQuery(event.target.value)}
                placeholder="Search employee, shift, department, location..."
                className="h-11 rounded-2xl pl-10 pr-14"
              />
              <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md border border-line/80 bg-page px-2 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">
                Esc
              </span>
            </div>
          </div>
          <div className="max-h-[26rem] overflow-y-auto px-3 py-3">
            {filteredCommandCenterSections.length ? (
              <div className="space-y-4">
                {filteredCommandCenterSections.map((section) => (
                  <div key={section.title} className="space-y-1.5">
                    <div className="flex items-center gap-3 px-2">
                      <span className="ui-type-page-eyebrow text-text-subtle">{section.title}</span>
                      <span className="h-px flex-1 bg-[linear-gradient(90deg,var(--workspace-line),transparent)]" />
                      {section.title === 'Recent' && section.items.length ? (
                        <button
                          type="button"
                          className="ui-type-page-eyebrow text-text-subtle transition-colors hover:text-foreground"
                          onClick={clearRecent}
                        >
                          Clear
                        </button>
                      ) : null}
                    </div>
                    <div className="space-y-1">
                      {section.items.map((item) => (
                        <div key={item.id} className="flex items-start gap-2 rounded-2xl px-1 py-1 transition-colors hover:bg-panel-soft">
                          <button
                            type="button"
                            onClick={() => handleCommandNavigation(item.path)}
                            className="flex min-w-0 flex-1 items-start gap-3 rounded-2xl px-2 py-1.5 text-left"
                          >
                            <span className="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-line/80 bg-panel text-foreground">
                              <ShellIcon name={item.icon} />
                            </span>
                            <span className="min-w-0 flex-1">
                              <span className="flex items-center gap-2">
                                <span className="ui-type-body-strong truncate text-foreground">{item.label}</span>
                                {item.source === 'Recent' ? (
                                  <Clock3 className="h-3.5 w-3.5 text-text-subtle" />
                                ) : (
                                  <CheckCircle2 className="h-3.5 w-3.5 text-primary" />
                                )}
                              </span>
                              <span className="ui-type-caption mt-0.5 block truncate text-muted-foreground">
                                {item.description}
                                {item.meta ? ` · ${item.meta}` : ''}
                              </span>
                            </span>
                          </button>
                          <button
                            type="button"
                            aria-label={isFavorite(item.path) ? `Unpin ${item.label}` : `Pin ${item.label}`}
                            className={cn(
                              'mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border transition-colors',
                              isFavorite(item.path)
                                ? 'border-warning/25 bg-warning/[0.08] text-warning hover:bg-warning/[0.14]'
                                : 'border-line/80 bg-panel text-text-subtle hover:bg-panel-soft hover:text-foreground',
                            )}
                            onClick={() =>
                              toggleFavorite({
                                path: item.path,
                                label: item.label,
                                icon: item.icon,
                                description: item.description,
                                meta: item.meta,
                              })
                            }
                          >
                            <Star className={cn('h-4 w-4', isFavorite(item.path) && 'fill-current')} />
                          </button>
                          {item.source === 'Recent' ? (
                            <button
                              type="button"
                              aria-label={`Remove ${item.label} from recent`}
                              className="mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line/80 bg-panel text-text-subtle transition-colors hover:bg-panel-soft hover:text-foreground"
                              onClick={() => removeRecent(item.path)}
                            >
                              <X className="h-4 w-4" />
                            </button>
                          ) : null}
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="rounded-2xl border border-dashed border-line/80 bg-panel-soft px-4 py-8 text-center">
                <p className="ui-type-body-strong text-foreground">No matching destinations</p>
                <p className="ui-type-caption mt-1 text-muted-foreground">
                  Try a module name, employee area, or recent workspace.
                </p>
              </div>
            )}
          </div>
        </DialogContent>
      </Dialog>
    </div>
  )
}
