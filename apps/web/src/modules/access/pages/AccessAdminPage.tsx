import { useContext, useDeferredValue, useEffect, useMemo, useState, type FormEvent, type ReactNode } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { AlertTriangle, KeyRound, Lock, ShieldCheck, UserRoundCog, Waypoints } from 'lucide-react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useAccessSnapshot } from '../hooks/useAccessSnapshot'
import {
  createAccessRole,
  createAccessUser,
  fetchAccessPermissions,
  fetchAccessRoles,
  fetchAccessUsers,
  updateAccessRole,
  updateAccessUser,
  type CreateAccessRolePayload,
  type CreateAccessUserPayload,
  type UpdateAccessRolePayload,
  type UpdateAccessUserPayload,
} from '../api/accessApi'
import type { AccessAdminPermission, AccessAdminRole, AccessAdminUser } from '../types'
import { useAppSelector } from '../../../app/store/hooks'
import { ApiRequestError } from '../../../shared/api/http'
import { hasPermissions } from '../../../shared/auth/permissions'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import {
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
import { cn } from '../../../shared/ui/cn'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { ToastContext } from '../../../shared/ui/toast-context'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'

type AccessTab = 'users' | 'roles' | 'navigation' | 'actions' | 'diagnostics'
type UserStatusFilter = 'all' | 'active' | 'inactive'

type UserDialogState =
  | {
      mode: 'create'
      user: null
    }
  | {
      mode: 'edit'
      user: AccessAdminUser
    }

type RoleDialogState =
  | {
      mode: 'create'
      role: null
    }
  | {
      mode: 'edit'
      role: AccessAdminRole
    }

type PermissionFieldErrors = Record<string, string[]>

function resolveAccessTab(hash: string): AccessTab {
  switch (hash) {
    case '#users':
      return 'users'
    case '#roles':
      return 'roles'
    case '#actions':
      return 'actions'
    case '#diagnostics':
      return 'diagnostics'
    case '#routes':
    default:
      return 'navigation'
  }
}

function formatDateTime(value: string | null) {
  if (!value) {
    return 'Not recorded'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(date)
}

function formatRoleName(role: string) {
  return role
    .split(/[._-]+/)
    .filter(Boolean)
    .map((segment) => segment[0]?.toUpperCase() + segment.slice(1))
    .join(' ')
}

function toggleSelection(items: string[], value: string) {
  return items.includes(value) ? items.filter((item) => item !== value) : [...items, value]
}

function applyFormError(
  error: unknown,
  setErrorMessage: (value: string | null) => void,
  setFieldErrors: (value: PermissionFieldErrors) => void,
) {
  if (error instanceof ApiRequestError) {
    setErrorMessage(error.message)
    setFieldErrors(error.fieldErrors)

    return
  }

  setErrorMessage('The request failed unexpectedly.')
  setFieldErrors({})
}

export function AccessAdminPage() {
  const location = useLocation()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const toast = useContext(ToastContext)
  const access = useAppSelector((state) => state.access)
  const { snapshot, source, error, isLoading } = useAccessSnapshot()
  const [search, setSearch] = useState('')
  const [userStatusFilter, setUserStatusFilter] = useState<UserStatusFilter>('all')
  const [userDialog, setUserDialog] = useState<UserDialogState | null>(null)
  const [roleDialog, setRoleDialog] = useState<RoleDialogState | null>(null)
  const deferredSearch = useDeferredValue(search)
  const activeTab = resolveAccessTab(location.hash)
  const liveAdminHref = `/login?next=${encodeURIComponent(`/access#${activeTab === 'users' ? 'users' : 'roles'}`)}`
  const grantedPermissions = snapshot?.user.permissions ?? []
  const hiddenRouteCount = snapshot?.visibility.meta.hidden_navigation_count ?? 0
  const hiddenActionCount =
    snapshot?.visibility.action_groups.reduce((total, group) => total + group.hidden_count, 0) ?? 0
  const canManageUsers = hasPermissions(grantedPermissions, ['auth.manage_users'])
  const canReadRoles = hasPermissions(
    grantedPermissions,
    ['auth.manage_roles', 'auth.manage_permissions', 'auth.manage_users'],
    'any',
  )
  const canManageRoleDefinitions = snapshot?.user.roles.includes('platform.super_admin') ?? false
  const isLiveSession = source === 'live' && access.token.trim().length > 0 && Boolean(snapshot)

  const navigationRows = useMemo(() => snapshot?.visibility.navigation.filter((item) => item.visible) ?? [], [snapshot])
  const actionRows = useMemo(() => {
    if (!snapshot) {
      return []
    }

    return snapshot.visibility.action_groups.flatMap((group) =>
      group.actions
        .filter((action) => action.visible)
        .map((action) => ({
          id: action.id,
          label: action.label,
          description: action.description ?? 'No description provided.',
          group: group.title,
          href: action.href ?? 'Route pending',
          match: action.match,
          permissions: action.required_permissions,
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
        sourceLabel: source === 'live' ? 'Live API' : 'Local contract',
      },
      {
        id: 'hidden-routes',
        label: 'Hidden routes',
        summary: 'Routes excluded from the current session contract.',
        value: String(snapshot.visibility.meta.hidden_navigation_count),
        sourceLabel: 'Navigation contract',
      },
      {
        id: 'hidden-actions',
        label: 'Suppressed actions',
        summary: 'Actions filtered out from the current identity.',
        value: String(hiddenActionCount),
        sourceLabel: 'Action contract',
      },
    ]
  }, [hiddenActionCount, snapshot, source])

  const rolesQuery = useQuery({
    queryKey: ['access-roles', access.apiBaseUrl, access.token],
    queryFn: () => fetchAccessRoles(access.apiBaseUrl, access.token),
    enabled: isLiveSession && canReadRoles,
  })

  const permissionsQuery = useQuery({
    queryKey: ['access-permissions', access.apiBaseUrl, access.token],
    queryFn: () => fetchAccessPermissions(access.apiBaseUrl, access.token),
    enabled: isLiveSession && canManageRoleDefinitions,
  })

  const usersQuery = useQuery({
    queryKey: ['access-users', access.apiBaseUrl, access.token, userStatusFilter, deferredSearch],
    queryFn: () =>
      fetchAccessUsers(access.apiBaseUrl, access.token, {
        search: deferredSearch,
        status: userStatusFilter,
      }),
    enabled: isLiveSession && canManageUsers,
  })

  const roles = rolesQuery.data ?? []
  const permissions = permissionsQuery.data ?? []
  const users = usersQuery.data ?? []
  const filteredRoles = useMemo(() => {
    const normalizedQuery = deferredSearch.trim().toLowerCase()

    if (!normalizedQuery) {
      return roles
    }

    return roles.filter((role) =>
      [role.name, role.permissions.join(' ')].join(' ').toLowerCase().includes(normalizedQuery),
    )
  }, [deferredSearch, roles])

  const filteredNavigationRows = useMemo(() => {
    const normalizedQuery = deferredSearch.trim().toLowerCase()

    if (!normalizedQuery) {
      return navigationRows
    }

    return navigationRows.filter((item) =>
      [item.label, item.id, item.description ?? '', item.href ?? '', item.required_permissions.join(' ')]
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery),
    )
  }, [deferredSearch, navigationRows])

  const filteredActionRows = useMemo(() => {
    const normalizedQuery = deferredSearch.trim().toLowerCase()

    if (!normalizedQuery) {
      return actionRows
    }

    return actionRows.filter((action) =>
      [action.label, action.group, action.description, action.href, action.permissions.join(' ')]
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery),
    )
  }, [actionRows, deferredSearch])

  const filteredDiagnosticsRows = useMemo(() => {
    const normalizedQuery = deferredSearch.trim().toLowerCase()

    if (!normalizedQuery) {
      return diagnosticsRows
    }

    return diagnosticsRows.filter((row) =>
      [row.label, row.summary, row.value, row.sourceLabel].join(' ').toLowerCase().includes(normalizedQuery),
    )
  }, [deferredSearch, diagnosticsRows])

  const visibleRouteCount = snapshot?.visibility.meta.visible_navigation_count ?? 0
  const visibleActionCount =
    snapshot?.visibility.action_groups.reduce((total, group) => total + group.visible_count, 0) ?? 0
  const mfaProtectedUsers = users.filter((user) => user.requires_mfa).length
  const activeUsers = users.filter((user) => user.is_active).length
  const usersWithoutMfa = users.filter((user) => user.is_active && !user.requires_mfa).length
  const roleDefinitionScope = canManageRoleDefinitions ? 'Shared role administration' : 'Read-only role catalog'

  const activeRowCount =
    activeTab === 'users'
      ? users.length
      : activeTab === 'roles'
        ? filteredRoles.length
        : activeTab === 'actions'
          ? filteredActionRows.length
          : activeTab === 'diagnostics'
            ? filteredDiagnosticsRows.length
            : filteredNavigationRows.length

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    tone: 'neutral' | 'info' | 'success' | 'warning'
    icon: ReactNode
    valueSize?: 'stat' | 'compact' | 'long'
  }> = [
    {
      label: 'Session source',
      value: source === 'live' ? 'Live' : 'Demo',
      delta: source === 'live' ? 'Protected admin surface is API-backed' : 'Routes and diagnostics are showing seeded contract data',
      tone: source === 'live' ? 'success' : 'warning',
      icon: <ShieldCheck className="h-4 w-4" />,
      valueSize: 'compact',
    },
    {
      label: 'Manageable users',
      value: isLiveSession && canManageUsers ? String(users.length) : 'Live only',
      delta:
        isLiveSession && canManageUsers
          ? `${activeUsers} active account(s) in scope`
          : 'Sign in with user-management access to administer identities',
      tone: isLiveSession && canManageUsers ? 'info' : 'warning',
      icon: <UserRoundCog className="h-4 w-4" />,
      valueSize: 'compact',
    },
    {
      label: 'MFA protected users',
      value: isLiveSession && canManageUsers ? String(mfaProtectedUsers) : 'Pending',
      delta:
        isLiveSession && canManageUsers
          ? `${usersWithoutMfa} active account(s) still lack MFA`
          : 'MFA posture loads after a live admin session resolves',
      tone: isLiveSession && canManageUsers && usersWithoutMfa === 0 ? 'success' : 'warning',
      icon: <Lock className="h-4 w-4" />,
      valueSize: 'compact',
    },
    {
      label: 'Visible roles',
      value: canReadRoles && (rolesQuery.isSuccess || rolesQuery.isFetching) ? String(roles.length) : 'Scoped',
      delta: roleDefinitionScope,
      tone: canManageRoleDefinitions ? 'success' : 'info',
      icon: <KeyRound className="h-4 w-4" />,
      valueSize: 'compact',
    },
    {
      label: 'Visible routes',
      value: String(visibleRouteCount),
      delta: `${hiddenRouteCount} hidden route(s) in this contract`,
      tone: hiddenRouteCount ? 'warning' : 'success',
      icon: <Waypoints className="h-4 w-4" />,
      valueSize: 'compact',
    },
    {
      label: 'Suppressed actions',
      value: String(hiddenActionCount),
      delta: `${visibleActionCount} visible governed action(s) remain available`,
      tone: hiddenActionCount ? 'warning' : 'success',
      icon: <AlertTriangle className="h-4 w-4" />,
      valueSize: 'compact',
    },
  ]

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      title: string
      detail: string
      meta: string
      tone: 'info' | 'warning' | 'success'
      icon: ReactNode
      action?: ReactNode
    }> = []

    if (!isLiveSession) {
      items.push({
        id: 'live-session',
        title: 'Live sign-in is required for concrete access administration',
        detail: 'The user and role tabs become fully operational only after a real authenticated workspace session resolves.',
        meta: 'Routes, actions, and diagnostics remain visible while the signed session is still pending.',
        tone: 'warning',
        icon: <Lock className="h-4 w-4" />,
        action: (
          <Button asChild size="xs" variant="primary">
            <Link to={liveAdminHref}>Sign in</Link>
          </Button>
        ),
      })
    }

    if (isLiveSession && canManageUsers && usersWithoutMfa > 0) {
      items.push({
        id: 'mfa-gap',
        title: `${usersWithoutMfa} active account(s) are missing MFA`,
        detail: 'These users can still sign in without a second factor unless you update their access record.',
        meta: 'Use the Users tab to enforce email OTP now, then move to stronger enrollment later.',
        tone: 'warning',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    if (isLiveSession && canReadRoles && !canManageRoleDefinitions) {
      items.push({
        id: 'shared-role-guardrail',
        title: 'Shared role definitions are read-only in this session',
        detail: 'Tenant administrators can assign approved roles, but only platform super admins can edit the shared permission map.',
        meta: 'This keeps cross-tenant role definitions governed centrally.',
        tone: 'info',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    if (hiddenRouteCount || hiddenActionCount) {
      items.push({
        id: 'contract-gaps',
        title: `${hiddenRouteCount} hidden route(s) and ${hiddenActionCount} suppressed action(s) remain`,
        detail: 'The current identity is intentionally scoped and the backend is actively narrowing what the UI exposes.',
        meta: 'Use Routes, Actions, and Diagnostics to inspect the remaining visibility boundaries.',
        tone: hiddenRouteCount || hiddenActionCount ? 'warning' : 'success',
        icon: <Waypoints className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        title: 'Access posture looks healthy',
        detail: 'Live authentication is connected and the RBAC administration surfaces are ready for use.',
        meta: 'Review users and roles below, then inspect diagnostics for backend enforcement notes.',
        tone: 'success',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [canManageRoleDefinitions, canManageUsers, canReadRoles, hiddenActionCount, hiddenRouteCount, isLiveSession, liveAdminHref, usersWithoutMfa])

  function setAccessTab(tab: AccessTab) {
    const nextHash =
      tab === 'users'
        ? '#users'
        : tab === 'roles'
          ? '#roles'
          : tab === 'actions'
            ? '#actions'
            : tab === 'diagnostics'
              ? '#diagnostics'
              : '#routes'

    navigate(
      {
        pathname: location.pathname,
        hash: nextHash,
      },
      { replace: true },
    )
  }

  async function refreshAccessQueries() {
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: ['access-users'] }),
      queryClient.invalidateQueries({ queryKey: ['access-roles'] }),
      queryClient.invalidateQueries({ queryKey: ['access-permissions'] }),
      queryClient.invalidateQueries({ queryKey: ['access-snapshot'] }),
    ])
  }

  async function handleCreateUser(payload: CreateAccessUserPayload | UpdateAccessUserPayload) {
    await createAccessUser(access.apiBaseUrl, access.token, payload as CreateAccessUserPayload)
    await refreshAccessQueries()
    toast.success('Admin user created', 'The account is now available in the tenant access roster.')
  }

  async function handleUpdateUser(payload: CreateAccessUserPayload | UpdateAccessUserPayload) {
    if (!userDialog || userDialog.mode !== 'edit') {
      return
    }

    await updateAccessUser(access.apiBaseUrl, access.token, userDialog.user.id, payload as UpdateAccessUserPayload)
    await refreshAccessQueries()
    toast.success('Admin user updated', 'The access record and assigned roles have been refreshed.')
  }

  async function handleCreateRole(payload: CreateAccessRolePayload | UpdateAccessRolePayload) {
    await createAccessRole(access.apiBaseUrl, access.token, payload as CreateAccessRolePayload)
    await refreshAccessQueries()
    toast.success('Role created', 'The shared role definition is now available for assignment.')
  }

  async function handleUpdateRole(payload: CreateAccessRolePayload | UpdateAccessRolePayload) {
    if (!roleDialog || roleDialog.mode !== 'edit') {
      return
    }

    await updateAccessRole(access.apiBaseUrl, access.token, roleDialog.role.id, payload as UpdateAccessRolePayload)
    await refreshAccessQueries()
    toast.success('Role updated', 'The shared permission map has been synchronized.')
  }

  const searchPlaceholder =
    activeTab === 'users'
      ? 'Search users by name or email'
      : activeTab === 'roles'
        ? 'Search roles or permission names'
        : activeTab === 'actions'
          ? 'Search governed actions, groups, or routes'
          : activeTab === 'diagnostics'
            ? 'Search enforcement signals'
            : 'Search visible routes or permission keys'

  const badgeLabel =
    activeTab === 'users'
      ? 'User access'
      : activeTab === 'roles'
        ? 'Role governance'
        : activeTab === 'actions'
          ? 'Action contract'
          : activeTab === 'diagnostics'
            ? 'Diagnostics'
            : 'Route contract'

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading access operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Access"
          title="Access Operations Center"
          description="Use real sign-in, tenant-scoped user administration, shared role governance, and backend visibility diagnostics from one concrete access workspace."
          badge={<Badge variant={source === 'live' ? 'info' : 'warning'}>{source === 'live' ? 'Live contract' : 'Local contract'}</Badge>}
          context={[
            snapshot?.user.roles.map(formatRoleName).join(', ') ?? 'No role resolved',
            snapshot?.user.tenant.company_name ?? 'Tenant pending',
          ]}
          actions={
            <>
              {!isLiveSession ? (
                <Button asChild size="xs" variant="primary">
                  <Link to={liveAdminHref}>Sign in</Link>
                </Button>
              ) : null}
              {isLiveSession && canManageUsers ? (
                <Button size="xs" variant="primary" onClick={() => setUserDialog({ mode: 'create', user: null })}>
                  Create user
                </Button>
              ) : null}
              {isLiveSession && canManageRoleDefinitions ? (
                <Button
                  size="xs"
                  variant="secondary"
                  onClick={() => {
                    setAccessTab('roles')
                    setRoleDialog({ mode: 'create', role: null })
                  }}
                >
                  Create role
                </Button>
              ) : null}
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
                tone={card.tone}
                icon={card.icon}
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
                actions={item.action}
              />
            ))}
          </CommandCenterAttentionStrip>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterPanel
                title="Governance workspace"
                description="Move between user access, role governance, route visibility, actions, and diagnostics without leaving the secure session."
              >
                <div className="space-y-3 p-3.5">
                  <ConsoleToolbar>
                    <ConsoleToolbarRow className="gap-3">
                      <WorkspaceTabs role="tablist" aria-label="Access workspace tabs">
                        <WorkspaceTabButton type="button" active={activeTab === 'users'} onClick={() => setAccessTab('users')}>
                          Users
                        </WorkspaceTabButton>
                        <WorkspaceTabButton type="button" active={activeTab === 'roles'} onClick={() => setAccessTab('roles')}>
                          Roles
                        </WorkspaceTabButton>
                        <WorkspaceTabButton type="button" active={activeTab === 'navigation'} onClick={() => setAccessTab('navigation')}>
                          Routes
                        </WorkspaceTabButton>
                        <WorkspaceTabButton type="button" active={activeTab === 'actions'} onClick={() => setAccessTab('actions')}>
                          Actions
                        </WorkspaceTabButton>
                        <WorkspaceTabButton type="button" active={activeTab === 'diagnostics'} onClick={() => setAccessTab('diagnostics')}>
                          Diagnostics
                        </WorkspaceTabButton>
                      </WorkspaceTabs>
                      <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="subtle">{badgeLabel}</Badge>
                        <Badge variant="subtle">{activeRowCount} record(s)</Badge>
                      </div>
                    </ConsoleToolbarRow>
                    <ConsoleToolbarRow className="gap-3">
                      <ConsoleSearchField
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={searchPlaceholder}
                        aria-label="Search access workspace"
                      />
                      {activeTab === 'users' ? (
                        <WorkspaceTabs aria-label="User status filters">
                          {(['all', 'active', 'inactive'] as const).map((status) => (
                            <WorkspaceTabButton
                              key={status}
                              type="button"
                              active={userStatusFilter === status}
                              onClick={() => setUserStatusFilter(status)}
                            >
                              {status === 'all' ? 'All' : status === 'active' ? 'Active' : 'Inactive'}
                            </WorkspaceTabButton>
                          ))}
                        </WorkspaceTabs>
                      ) : null}
                    </ConsoleToolbarRow>
                  </ConsoleToolbar>

                  {activeTab === 'users' ? (
                    !isLiveSession ? (
                      <WorkspaceEmptyState
                        title="Live sign-in is required for user administration"
                        copy="The governance contract remains visible, but creating and editing access-controlled users is only available in a real authenticated session."
                        actions={
                          <Button asChild variant="primary">
                            <Link to={liveAdminHref}>Sign in to manage users</Link>
                          </Button>
                        }
                      />
                    ) : !canManageUsers ? (
                      <WorkspaceEmptyState
                        title="This session cannot manage admin users"
                        copy="Open a session that includes `auth.manage_users` to create, update, or disable tenant-scoped identities."
                      />
                    ) : usersQuery.isLoading ? (
                      <WorkspaceEmptyState
                        title="Loading user access roster"
                        copy="We are resolving tenant-scoped admin users, assigned roles, and current MFA posture."
                      />
                    ) : usersQuery.error ? (
                      <WorkspaceEmptyState
                        title="The user roster could not be loaded"
                        copy={(usersQuery.error as Error).message}
                      />
                    ) : users.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>User</TableHead>
                              <TableHead>Roles</TableHead>
                              <TableHead>Status</TableHead>
                              <TableHead>MFA</TableHead>
                              <TableHead>Last login</TableHead>
                              <TableHead className="text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {users.map((user) => (
                              <TableRow key={user.id}>
                                <TableCell className="align-top">
                                  <div className="space-y-1">
                                    <p className="ui-type-body-strong text-foreground">{user.name}</p>
                                    <p className="ui-type-caption text-muted-foreground">{user.email}</p>
                                    <p className="ui-type-caption text-text-subtle">
                                      {user.employee
                                        ? `${user.employee.full_name} · ${user.employee.employee_code}`
                                        : 'No employee profile linked'}
                                    </p>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="flex flex-wrap gap-1.5">
                                    {user.roles.length ? (
                                      user.roles.map((role) => (
                                        <Badge key={`${user.id}-${role}`} variant="subtle">
                                          {formatRoleName(role)}
                                        </Badge>
                                      ))
                                    ) : (
                                      <Badge variant="subtle">No roles</Badge>
                                    )}
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={user.is_active ? 'success' : 'warning'}>
                                    {user.is_active ? 'Active' : 'Inactive'}
                                  </Badge>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={user.requires_mfa ? 'success' : 'warning'}>
                                    {user.requires_mfa ? user.mfa_method ?? 'Enabled' : 'Not enforced'}
                                  </Badge>
                                </TableCell>
                                <TableCell className="ui-type-body align-top text-muted-foreground">
                                  {formatDateTime(user.last_login_at)}
                                </TableCell>
                                <TableCell className="align-top text-right">
                                  <Button size="sm" variant="secondary" onClick={() => setUserDialog({ mode: 'edit', user })}>
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
                        title="No users match this filter"
                        copy="Change the search or status filter to inspect a broader access roster."
                      />
                    )
                  ) : null}

                  {activeTab === 'roles' ? (
                    !isLiveSession ? (
                      <WorkspaceEmptyState
                        title="Live sign-in is required for role governance"
                        copy="Role creation and permission-sync workflows are intentionally gated behind a real authenticated admin session."
                        actions={
                          <Button asChild variant="primary">
                            <Link to={liveAdminHref}>Sign in to manage roles</Link>
                          </Button>
                        }
                      />
                    ) : !canReadRoles ? (
                      <WorkspaceEmptyState
                        title="This session cannot read governed roles"
                        copy="Open a session with role, permission, or user-management access to inspect the assignable role catalog."
                      />
                    ) : rolesQuery.isLoading ? (
                      <WorkspaceEmptyState
                        title="Loading governed roles"
                        copy="We are resolving the visible shared role catalog and the permission maps available to this session."
                      />
                    ) : rolesQuery.error ? (
                      <WorkspaceEmptyState
                        title="The role catalog could not be loaded"
                        copy={(rolesQuery.error as Error).message}
                      />
                    ) : filteredRoles.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Role</TableHead>
                              <TableHead>Scope</TableHead>
                              <TableHead>Permission count</TableHead>
                              <TableHead>Key permissions</TableHead>
                              <TableHead className="text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredRoles.map((role) => (
                              <TableRow key={role.id}>
                                <TableCell className="align-top">
                                  <div className="space-y-1">
                                    <p className="ui-type-body-strong text-foreground">{role.name}</p>
                                    <p className="ui-type-caption text-muted-foreground">{role.guard_name}</p>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={role.name.startsWith('platform.') ? 'warning' : 'info'}>
                                    {role.name.startsWith('platform.') ? 'Platform shared' : 'Tenant assignable'}
                                  </Badge>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant="subtle">{role.permissions.length}</Badge>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="flex flex-wrap gap-1.5">
                                    {role.permissions.slice(0, 3).map((permission) => (
                                      <Badge key={`${role.id}-${permission}`} variant="subtle">
                                        {permission}
                                      </Badge>
                                    ))}
                                    {role.permissions.length > 3 ? (
                                      <Badge variant="subtle">+{role.permissions.length - 3} more</Badge>
                                    ) : null}
                                  </div>
                                </TableCell>
                                <TableCell className="align-top text-right">
                                  {canManageRoleDefinitions ? (
                                    <Button size="sm" variant="secondary" onClick={() => setRoleDialog({ mode: 'edit', role })}>
                                      Edit
                                    </Button>
                                  ) : (
                                    <Badge variant="subtle">Read only</Badge>
                                  )}
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No roles match this filter"
                        copy="Change the search to inspect a broader governed role catalog."
                      />
                    )
                  ) : null}

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
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredNavigationRows.map((item) => (
                              <TableRow key={item.id}>
                                <TableCell className="align-top">
                                  <div className="space-y-1">
                                    <p className="ui-type-body-strong text-foreground">{item.label}</p>
                                    <p className="ui-type-caption text-muted-foreground">{item.id}</p>
                                  </div>
                                </TableCell>
                                <TableCell className="ui-type-body align-top text-muted-foreground">
                                  {item.description ?? 'No description provided.'}
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant="subtle">{item.match}</Badge>
                                </TableCell>
                                <TableCell className="ui-type-body align-top text-muted-foreground">
                                  {item.href ?? 'Route pending'}
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
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredActionRows.map((action) => (
                              <TableRow key={action.id}>
                                <TableCell className="align-top">
                                  <div className="space-y-1">
                                    <p className="ui-type-body-strong text-foreground">{action.label}</p>
                                    <p className="ui-type-caption text-muted-foreground">{action.permissions.join(', ')}</p>
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
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No actions match the current filter"
                        copy="Change the search to inspect a broader governed action contract."
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
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredDiagnosticsRows.map((row) => (
                              <TableRow key={row.id}>
                                <TableCell className="align-top">
                                  <p className="ui-type-body-strong text-foreground">{row.label}</p>
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
                </div>
              </CommandCenterPanel>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterInsightGrid className="grid-cols-1 xl:grid-cols-1">
                <CommandCenterInsightCard
                  title="Session posture"
                  description="A quick view of the current identity and what it can govern."
                >
                  <WorkspaceSummaryRow label="Identity" value={snapshot?.user.name ?? 'Unresolved'} />
                  <WorkspaceSummaryRow
                    label="Role"
                    value={snapshot?.user.roles.map(formatRoleName).join(', ') ?? 'Unresolved'}
                  />
                  <WorkspaceSummaryRow label="Tenant" value={snapshot?.user.tenant.company_name ?? 'Pending'} />
                  <WorkspaceSummaryRow label="Source" value={source === 'live' ? 'Live API' : 'Local contract'} />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Authorization guardrails"
                  description="How this session is intentionally constrained."
                >
                  <WorkspaceSummaryRow label="User management" value={canManageUsers ? 'Enabled' : 'Unavailable'} />
                  <WorkspaceSummaryRow label="Role catalog" value={canReadRoles ? 'Visible' : 'Unavailable'} />
                  <WorkspaceSummaryRow
                    label="Role definitions"
                    value={canManageRoleDefinitions ? 'Shared edit access' : 'Read only'}
                  />
                  <WorkspaceSummaryRow label="Suppressed actions" value={String(hiddenActionCount)} />
                </CommandCenterInsightCard>
              </CommandCenterInsightGrid>
            </CommandCenterRail>
          </CommandCenterLayout>
        </WorkspaceContent>
      </WorkspaceSurface>

      <AccessUserModal
        open={userDialog !== null}
        mode={userDialog?.mode ?? 'create'}
        user={userDialog?.user ?? null}
        roles={roles}
        onClose={() => setUserDialog(null)}
        onSubmit={userDialog?.mode === 'edit' ? handleUpdateUser : handleCreateUser}
      />

      <AccessRoleModal
        open={roleDialog !== null}
        mode={roleDialog?.mode ?? 'create'}
        role={roleDialog?.role ?? null}
        permissions={permissions}
        onClose={() => setRoleDialog(null)}
        onSubmit={roleDialog?.mode === 'edit' ? handleUpdateRole : handleCreateRole}
      />
    </WorkspacePage>
  )
}

function AccessUserModal({
  open,
  mode,
  user,
  roles,
  onClose,
  onSubmit,
}: {
  open: boolean
  mode: 'create' | 'edit'
  user: AccessAdminUser | null
  roles: AccessAdminRole[]
  onClose: () => void
  onSubmit: (payload: CreateAccessUserPayload | UpdateAccessUserPayload) => Promise<void>
}) {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [selectedRoles, setSelectedRoles] = useState<string[]>([])
  const [isActive, setIsActive] = useState(true)
  const [requiresMfa, setRequiresMfa] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<PermissionFieldErrors>({})

  useEffect(() => {
    if (!open) {
      return
    }

    setName(user?.name ?? '')
    setEmail(user?.email ?? '')
    setPassword('')
    setPasswordConfirmation('')
    setSelectedRoles(user?.roles ?? [])
    setIsActive(user?.is_active ?? true)
    setRequiresMfa(user?.requires_mfa ?? false)
    setErrorMessage(null)
    setFieldErrors({})
  }, [open, user])

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setFieldErrors({})

    try {
      if (mode === 'create') {
        await onSubmit({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation,
          roles: selectedRoles,
          is_active: isActive,
          requires_mfa: requiresMfa,
        })
      } else {
        const payload: UpdateAccessUserPayload = {
          name,
          email,
          roles: selectedRoles,
          is_active: isActive,
          requires_mfa: requiresMfa,
        }

        if (password.trim().length > 0) {
          payload.password = password
          payload.password_confirmation = passwordConfirmation
        }

        await onSubmit(payload)
      }

      onClose()
    } catch (error) {
      applyFormError(error, setErrorMessage, setFieldErrors)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={mode === 'create' ? 'Create admin user' : 'Edit admin user'}
      description="Create or update a tenant-scoped identity, keep MFA posture explicit, and assign only approved roles."
      size="lg"
    >
      <form className="space-y-4" onSubmit={handleSubmit}>
        {errorMessage ? <p className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errorMessage}</p> : null}

        <div className="grid gap-4 md:grid-cols-2">
          <WorkspaceField label="Full name" error={fieldErrors.name?.[0]}>
            <Input value={name} onChange={(event) => setName(event.target.value)} />
          </WorkspaceField>
          <WorkspaceField label="Email" error={fieldErrors.email?.[0]}>
            <Input value={email} onChange={(event) => setEmail(event.target.value)} type="email" />
          </WorkspaceField>
          <WorkspaceField
            label={mode === 'create' ? 'Temporary password' : 'Reset password'}
            error={fieldErrors.password?.[0]}
          >
            <Input value={password} onChange={(event) => setPassword(event.target.value)} type="password" />
          </WorkspaceField>
          <WorkspaceField label="Confirm password">
            <Input
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              type="password"
            />
          </WorkspaceField>
        </div>

        <div className="grid gap-3 md:grid-cols-2">
          <label className="flex items-start gap-3 rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
            <input
              checked={isActive}
              onChange={(event) => setIsActive(event.target.checked)}
              type="checkbox"
              className="mt-1 h-4 w-4 rounded border border-line-strong"
            />
            <span className="space-y-1">
              <span className="ui-type-body-strong block text-foreground">Active account</span>
              <span className="ui-type-caption block text-muted-foreground">
                Inactive users remain on record but cannot keep using the workspace.
              </span>
            </span>
          </label>
          <label className="flex items-start gap-3 rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
            <input
              checked={requiresMfa}
              onChange={(event) => setRequiresMfa(event.target.checked)}
              type="checkbox"
              className="mt-1 h-4 w-4 rounded border border-line-strong"
            />
            <span className="space-y-1">
              <span className="ui-type-body-strong block text-foreground">Require MFA</span>
              <span className="ui-type-caption block text-muted-foreground">
                This currently enables the email OTP sign-in challenge for the user.
              </span>
            </span>
          </label>
        </div>

        <WorkspaceField label="Assigned roles" error={fieldErrors.roles?.[0]}>
          <div className="grid max-h-64 gap-2 overflow-y-auto rounded-xl border border-line/80 bg-panel-soft/50 p-3 sm:grid-cols-2">
            {roles.length ? (
              roles.map((role) => (
                <label
                  key={role.id}
                  className={cn(
                    'flex items-start gap-3 rounded-lg border px-3 py-2.5 transition-colors',
                    selectedRoles.includes(role.name)
                      ? 'border-primary/25 bg-primary/[0.07]'
                      : 'border-line/70 bg-white/70 hover:bg-white',
                  )}
                >
                  <input
                    type="checkbox"
                    checked={selectedRoles.includes(role.name)}
                    onChange={() => setSelectedRoles((current) => toggleSelection(current, role.name))}
                    className="mt-1 h-4 w-4 rounded border border-line-strong"
                  />
                  <span className="space-y-1">
                    <span className="ui-type-body-strong block text-foreground">{role.name}</span>
                    <span className="ui-type-caption block text-muted-foreground">
                      {role.permissions.length} permission grant(s)
                    </span>
                  </span>
                </label>
              ))
            ) : (
              <p className="ui-type-body text-muted-foreground sm:col-span-2">
                No assignable roles are available in this session yet.
              </p>
            )}
          </div>
        </WorkspaceField>

        <div className="flex flex-wrap gap-3">
          <Button type="submit" variant="primary" disabled={isSubmitting}>
            {isSubmitting ? (mode === 'create' ? 'Creating...' : 'Saving...') : mode === 'create' ? 'Create user' : 'Save changes'}
          </Button>
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
        </div>
      </form>
    </Modal>
  )
}

function AccessRoleModal({
  open,
  mode,
  role,
  permissions,
  onClose,
  onSubmit,
}: {
  open: boolean
  mode: 'create' | 'edit'
  role: AccessAdminRole | null
  permissions: AccessAdminPermission[]
  onClose: () => void
  onSubmit: (payload: CreateAccessRolePayload | UpdateAccessRolePayload) => Promise<void>
}) {
  const [name, setName] = useState('')
  const [selectedPermissions, setSelectedPermissions] = useState<string[]>([])
  const [permissionSearch, setPermissionSearch] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [errorMessage, setErrorMessage] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<PermissionFieldErrors>({})
  const deferredPermissionSearch = useDeferredValue(permissionSearch)

  useEffect(() => {
    if (!open) {
      return
    }

    setName(role?.name ?? '')
    setSelectedPermissions(role?.permissions ?? [])
    setPermissionSearch('')
    setIsSubmitting(false)
    setErrorMessage(null)
    setFieldErrors({})
  }, [open, role])

  const filteredPermissions = useMemo(() => {
    const normalizedQuery = deferredPermissionSearch.trim().toLowerCase()

    if (!normalizedQuery) {
      return permissions
    }

    return permissions.filter((permission) => permission.name.toLowerCase().includes(normalizedQuery))
  }, [deferredPermissionSearch, permissions])

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrorMessage(null)
    setFieldErrors({})

    try {
      if (mode === 'create') {
        await onSubmit({
          name,
          permissions: selectedPermissions,
        })
      } else {
        await onSubmit({
          permissions: selectedPermissions,
        })
      }

      onClose()
    } catch (error) {
      applyFormError(error, setErrorMessage, setFieldErrors)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={mode === 'create' ? 'Create shared role' : 'Edit shared role'}
      description="Shared role definitions remain platform-governed. Use this form carefully because updates affect assignable permission maps across the product."
      size="lg"
    >
      <form className="space-y-4" onSubmit={handleSubmit}>
        {errorMessage ? <p className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{errorMessage}</p> : null}

        <WorkspaceField label="Role name" error={fieldErrors.name?.[0]}>
          <Input
            value={name}
            onChange={(event) => setName(event.target.value)}
            readOnly={mode === 'edit'}
            placeholder="tenant.custom_role"
          />
        </WorkspaceField>

        <WorkspaceField label="Search permissions" error={fieldErrors.permissions?.[0]}>
          <Input
            value={permissionSearch}
            onChange={(event) => setPermissionSearch(event.target.value)}
            placeholder="Search permission names"
          />
        </WorkspaceField>

        <div className="rounded-xl border border-line/80 bg-panel-soft/50 p-3">
          <div className="mb-3 flex flex-wrap items-center gap-2">
            <Badge variant="subtle">{selectedPermissions.length} selected</Badge>
            <Badge variant="subtle">{filteredPermissions.length} visible</Badge>
          </div>
          <div className="grid max-h-72 gap-2 overflow-y-auto sm:grid-cols-2">
            {filteredPermissions.length ? (
              filteredPermissions.map((permission) => (
                <label
                  key={permission.id}
                  className={cn(
                    'flex items-start gap-3 rounded-lg border px-3 py-2.5 transition-colors',
                    selectedPermissions.includes(permission.name)
                      ? 'border-primary/25 bg-primary/[0.07]'
                      : 'border-line/70 bg-white/70 hover:bg-white',
                  )}
                >
                  <input
                    type="checkbox"
                    checked={selectedPermissions.includes(permission.name)}
                    onChange={() => setSelectedPermissions((current) => toggleSelection(current, permission.name))}
                    className="mt-1 h-4 w-4 rounded border border-line-strong"
                  />
                  <span className="ui-type-body text-foreground">{permission.name}</span>
                </label>
              ))
            ) : (
              <p className="ui-type-body text-muted-foreground sm:col-span-2">
                No permissions match the current filter.
              </p>
            )}
          </div>
        </div>

        <div className="flex flex-wrap gap-3">
          <Button type="submit" variant="primary" disabled={isSubmitting}>
            {isSubmitting ? (mode === 'create' ? 'Creating...' : 'Saving...') : mode === 'create' ? 'Create role' : 'Save changes'}
          </Button>
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
        </div>
      </form>
    </Modal>
  )
}
