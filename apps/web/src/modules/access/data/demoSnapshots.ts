import type {
  AccessSnapshot,
  AccessUser,
  DemoPersona,
  VisibilityAction,
  VisibilityActionGroup,
  VisibilityContract,
  VisibilityItem,
} from '../types'

interface VisibilityBlueprint {
  id: string
  label: string
  href: string | null
  description: string
  required_permissions: string[]
  match?: 'all' | 'any'
}

const navigationBlueprint: VisibilityBlueprint[] = [
  {
    id: 'foundation-overview',
    label: 'Foundation Overview',
    href: '/foundation',
    description: 'Shared Sprint 01 foundations, platform posture, and tenant context.',
    required_permissions: [],
  },
  {
    id: 'workflow-console',
    label: 'Workflow Console',
    href: '/workflows',
    description: 'Definitions, publishing, and workflow monitoring.',
    required_permissions: ['workflow.view'],
  },
  {
    id: 'task-inbox',
    label: 'Task Inbox',
    href: '/tasks',
    description: 'Approval tasks assigned to the current user.',
    required_permissions: ['workflow.view'],
  },
  {
    id: 'notification-center',
    label: 'Notification Center',
    href: '/notifications',
    description: 'In-app alerts, reminders, and workflow communications.',
    required_permissions: ['notification.view'],
  },
  {
    id: 'audit-trail',
    label: 'Audit Trail',
    href: '/audit',
    description: 'Tenant-scoped immutable platform and security events.',
    required_permissions: ['audit.view'],
  },
  {
    id: 'access-control',
    label: 'Access Control',
    href: '/access',
    description: 'Roles, permissions, and protected admin operations.',
    required_permissions: ['auth.manage_roles', 'auth.manage_permissions'],
    match: 'any',
  },
]

const actionGroupBlueprint = [
  {
    id: 'workflow-admin',
    title: 'Workflow Administration',
    description: 'Define, publish, and initiate approval flows.',
    actions: [
      {
        id: 'create-workflow',
        label: 'Create Workflow',
        href: '/workflows',
        description: 'Draft a new tenant-specific approval flow.',
        required_permissions: ['workflow.create'],
      },
      {
        id: 'publish-workflow',
        label: 'Publish Version',
        href: '/workflows',
        description: 'Promote the latest workflow version to active status.',
        required_permissions: ['workflow.publish'],
      },
      {
        id: 'start-approval',
        label: 'Start Approval',
        href: '/tasks',
        description: 'Trigger a leave or employee workflow instance.',
        required_permissions: ['workflow.execute'],
      },
    ],
  },
  {
    id: 'communication-ops',
    title: 'Communication Operations',
    description: 'Manage in-app delivery and operational notifications.',
    actions: [
      {
        id: 'send-notification',
        label: 'Send Notification',
        href: '/notifications',
        description: 'Create a targeted in-app alert for a tenant user.',
        required_permissions: ['notification.manage'],
      },
      {
        id: 'retry-notification',
        label: 'Retry Failed Delivery',
        href: '/notifications',
        description: 'Retry notifications that failed due to template or delivery issues.',
        required_permissions: ['notification.manage'],
      },
    ],
  },
  {
    id: 'governance',
    title: 'Governance and Security',
    description: 'Visibility into access control and audit posture.',
    actions: [
      {
        id: 'create-role',
        label: 'Create Role',
        href: '/access',
        description: 'Add a new tenant or platform role mapping.',
        required_permissions: ['auth.manage_roles'],
      },
      {
        id: 'review-audit-log',
        label: 'Review Audit Logs',
        href: '/audit',
        description: 'Inspect security and admin actions captured by the platform.',
        required_permissions: ['audit.view'],
      },
    ],
  },
] satisfies Array<{
  id: string
  title: string
  description: string
  actions: VisibilityBlueprint[]
}>

const personaUsers: Record<DemoPersona, AccessUser> = {
  platformAdmin: {
    id: 1,
    name: 'Platform Admin',
    initials: 'PA',
    email: 'admin@phoenixhrms.test',
    roles: ['platform.super_admin'],
    permissions: [
      'audit.view',
      'auth.manage_permissions',
      'auth.manage_roles',
      'auth.manage_users',
      'tenant.manage',
      'tenant.view',
      'workflow.view',
      'workflow.create',
      'workflow.edit',
      'workflow.publish',
      'workflow.execute',
      'workflow.monitor',
      'workflow.admin',
      'notification.manage',
      'notification.view',
      'employee.approve',
      'leave.approve',
    ],
    tenant: {
      company_id: 1,
      company_name: 'Phoenix Demo Company',
      subscription_plan: 'enterprise',
      timezone: 'Asia/Kolkata',
      currency: 'INR',
    },
  },
  tenantAdmin: {
    id: 2,
    name: 'Tenant Administrator',
    initials: 'TA',
    email: 'tenant.admin@phoenixhrms.test',
    roles: ['tenant.admin'],
    permissions: [
      'auth.manage_permissions',
      'auth.manage_roles',
      'auth.manage_users',
      'tenant.manage',
      'tenant.view',
      'workflow.view',
      'workflow.create',
      'workflow.edit',
      'workflow.publish',
      'workflow.execute',
      'workflow.monitor',
      'notification.manage',
      'notification.view',
    ],
    tenant: {
      company_id: 1,
      company_name: 'Phoenix Demo Company',
      subscription_plan: 'enterprise',
      timezone: 'Asia/Kolkata',
      currency: 'INR',
    },
  },
  manager: {
    id: 3,
    name: 'Manager Reviewer',
    initials: 'MR',
    email: 'manager@phoenixhrms.test',
    roles: ['manager'],
    permissions: ['workflow.view', 'workflow.execute', 'leave.approve', 'notification.view'],
    tenant: {
      company_id: 1,
      company_name: 'Phoenix Demo Company',
      subscription_plan: 'enterprise',
      timezone: 'Asia/Kolkata',
      currency: 'INR',
    },
  },
  employee: {
    id: 4,
    name: 'Employee Viewer',
    initials: 'EV',
    email: 'employee@phoenixhrms.test',
    roles: ['employee'],
    permissions: ['notification.view'],
    tenant: {
      company_id: 1,
      company_name: 'Phoenix Demo Company',
      subscription_plan: 'enterprise',
      timezone: 'Asia/Kolkata',
      currency: 'INR',
    },
  },
}

function isVisible(item: VisibilityBlueprint, permissions: string[]) {
  if (item.required_permissions.length === 0) {
    return true
  }

  if ((item.match ?? 'all') === 'any') {
    return item.required_permissions.some((permission) => permissions.includes(permission))
  }

  return item.required_permissions.every((permission) => permissions.includes(permission))
}

function materializeItem(item: VisibilityBlueprint, permissions: string[]): VisibilityItem {
  return {
    ...item,
    match: item.match ?? 'all',
    visible: isVisible(item, permissions),
  }
}

function buildActionGroup(group: (typeof actionGroupBlueprint)[number], permissions: string[]): VisibilityActionGroup {
  const actions: VisibilityAction[] = group.actions.map((action) => materializeItem(action, permissions))

  return {
    id: group.id,
    title: group.title,
    description: group.description,
    actions,
    visible_count: actions.filter((action) => action.visible).length,
    hidden_count: actions.filter((action) => !action.visible).length,
  }
}

function buildVisibilityContract(user: AccessUser): VisibilityContract {
  const navigation = navigationBlueprint.map((item) => materializeItem(item, user.permissions))
  const actionGroups = actionGroupBlueprint.map((group) => buildActionGroup(group, user.permissions))

  return {
    navigation,
    action_groups: actionGroups,
    meta: {
      visible_navigation_count: navigation.filter((item) => item.visible).length,
      hidden_navigation_count: navigation.filter((item) => !item.visible).length,
      backend_enforcement_note:
        'This contract is advisory for rendering only. Backend permission checks remain the source of truth.',
    },
  }
}

export function getDemoSnapshot(persona: DemoPersona): AccessSnapshot {
  const user = personaUsers[persona]

  return {
    user,
    visibility: buildVisibilityContract(user),
  }
}

export const demoPersonaLabels: Record<DemoPersona, string> = {
  platformAdmin: 'Platform Admin',
  tenantAdmin: 'Tenant Admin',
  manager: 'Manager',
  employee: 'Employee',
}
