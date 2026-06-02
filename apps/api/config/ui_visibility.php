<?php

return [
    'navigation' => [
        [
            'id' => 'foundation-overview',
            'label' => 'Foundation Overview',
            'href' => '/foundation',
            'description' => 'Shared Sprint 01 foundations, platform posture, and tenant context.',
            'required_permissions' => [],
        ],
        [
            'id' => 'workflow-console',
            'label' => 'Workflow Console',
            'href' => '/workflows',
            'description' => 'Definitions, publishing, and workflow monitoring.',
            'required_permissions' => ['workflow.view'],
        ],
        [
            'id' => 'task-inbox',
            'label' => 'Task Inbox',
            'href' => '/tasks',
            'description' => 'Approval tasks assigned to the current user.',
            'required_permissions' => ['workflow.view'],
        ],
        [
            'id' => 'notification-center',
            'label' => 'Notification Center',
            'href' => '/notifications',
            'description' => 'In-app alerts, reminders, and workflow communications.',
            'required_permissions' => ['notification.view'],
        ],
        [
            'id' => 'audit-trail',
            'label' => 'Audit Trail',
            'href' => '/audit',
            'description' => 'Tenant-scoped immutable platform and security events.',
            'required_permissions' => ['audit.view'],
        ],
        [
            'id' => 'access-control',
            'label' => 'Access Control',
            'href' => '/access',
            'description' => 'Roles, permissions, and protected admin operations.',
            'required_permissions' => ['auth.manage_roles', 'auth.manage_permissions'],
            'match' => 'any',
        ],
    ],
    'action_groups' => [
        [
            'id' => 'workflow-admin',
            'title' => 'Workflow Administration',
            'description' => 'Define, publish, and initiate approval flows.',
            'actions' => [
                [
                    'id' => 'create-workflow',
                    'label' => 'Create Workflow',
                    'description' => 'Draft a new tenant-specific approval flow.',
                    'required_permissions' => ['workflow.create'],
                ],
                [
                    'id' => 'publish-workflow',
                    'label' => 'Publish Version',
                    'description' => 'Promote the latest workflow version to active status.',
                    'required_permissions' => ['workflow.publish'],
                ],
                [
                    'id' => 'start-approval',
                    'label' => 'Start Approval',
                    'description' => 'Trigger a leave or employee workflow instance.',
                    'required_permissions' => ['workflow.execute'],
                ],
            ],
        ],
        [
            'id' => 'communication-ops',
            'title' => 'Communication Operations',
            'description' => 'Manage in-app delivery and operational notifications.',
            'actions' => [
                [
                    'id' => 'send-notification',
                    'label' => 'Send Notification',
                    'description' => 'Create a targeted in-app alert for a tenant user.',
                    'required_permissions' => ['notification.manage'],
                ],
                [
                    'id' => 'retry-notification',
                    'label' => 'Retry Failed Delivery',
                    'description' => 'Retry notifications that failed due to template or delivery issues.',
                    'required_permissions' => ['notification.manage'],
                ],
            ],
        ],
        [
            'id' => 'governance',
            'title' => 'Governance and Security',
            'description' => 'Visibility into access control and audit posture.',
            'actions' => [
                [
                    'id' => 'create-role',
                    'label' => 'Create Role',
                    'description' => 'Add a new tenant or platform role mapping.',
                    'required_permissions' => ['auth.manage_roles'],
                ],
                [
                    'id' => 'review-audit-log',
                    'label' => 'Review Audit Logs',
                    'description' => 'Inspect security and admin actions captured by the platform.',
                    'required_permissions' => ['audit.view'],
                ],
            ],
        ],
    ],
];
