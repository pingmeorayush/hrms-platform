<?php

return [
    'policy' => [
        'review_cadence' => 'Every production promotion requires a same-day go or no-go review with accountable release ownership recorded before launch approval is granted.',
        'decision_owner_roles' => [
            'platform.super_admin',
            'platform.support',
            'tenant.admin',
            'it.admin',
        ],
        'target_environments' => [
            'staging',
            'production',
        ],
        'artifact_refs' => [
            'docs/runbooks/release-incident-response.md',
            'docs/runbooks/release-rollback.md',
            'docs/runbooks/release-common-launch-issues.md',
            'docs/runbooks/backup-restore-disaster-recovery-validation.md',
        ],
    ],
    'areas' => [
        [
            'key' => 'testing',
            'name' => 'Testing and build verification',
            'source' => 'release_gates',
            'owner_role' => 'platform.support',
            'summary' => 'API and web regression gates must stay green before launch review can proceed.',
            'gate_keys' => [
                'api_quality',
                'web_quality',
            ],
            'evidence_requirements' => [
                'Latest API CI workflow result',
                'Latest Web CI workflow result',
            ],
        ],
        [
            'key' => 'security',
            'name' => 'Security and dependency posture',
            'source' => 'release_gates',
            'owner_role' => 'platform.support',
            'summary' => 'Dependency and security checks must remain current before protected promotion is approved.',
            'gate_keys' => [
                'dependency_security',
            ],
            'evidence_requirements' => [
                'Composer audit result',
                'npm audit result for API and web toolchains',
            ],
        ],
        [
            'key' => 'contracts',
            'name' => 'Contract and interface validation',
            'source' => 'release_gates',
            'owner_role' => 'tenant.admin',
            'summary' => 'Approved API and release-control contracts must stay linted and reviewable for downstream consumers.',
            'gate_keys' => [
                'contract_validation',
            ],
            'evidence_requirements' => [
                'OpenAPI inventory review',
                'OpenAPI lint result',
            ],
        ],
        [
            'key' => 'backups',
            'name' => 'Backups and recovery evidence',
            'source' => 'resilience_scenarios',
            'owner_role' => 'platform.super_admin',
            'summary' => 'Backup, restore, and failover evidence must be current enough to support launch recovery commitments.',
            'scenario_keys' => [
                'daily_application_backup',
                'monthly_database_restore',
                'payroll_artifact_restore',
                'regional_failover_drill',
            ],
            'evidence_requirements' => [
                'Recent recovery validation runs',
                'Backup retention confirmation',
                'Failover drill evidence',
            ],
        ],
        [
            'key' => 'monitoring',
            'name' => 'Monitoring and operator telemetry',
            'source' => 'observability_services',
            'owner_role' => 'platform.support',
            'summary' => 'Release-critical monitoring must stay healthy so launch issues can be detected and routed quickly.',
            'service_keys' => [
                'core_api',
                'integration_delivery',
                'workflow_approvals',
                'payroll_controls',
                'notification_delivery',
                'reporting_delivery',
                'release_governance',
            ],
            'evidence_requirements' => [
                'Healthy observability service posture',
                'No unresolved critical alerts',
            ],
        ],
        [
            'key' => 'critical_workflows',
            'name' => 'Critical workflow verification',
            'source' => 'workflow_checks',
            'owner_role' => 'tenant.admin',
            'summary' => 'Launch-critical user journeys require explicit smoke-test evidence rather than assumption.',
            'evidence_requirements' => [
                'Smoke-test output for critical workflows',
                'Operator acknowledgement of workflow verification',
            ],
        ],
    ],
    'workflow_verifications' => [
        [
            'key' => 'employee_directory_search',
            'label' => 'Employee directory search and profile drill-in',
            'status' => 'passing',
            'owner_role' => 'employee.manage',
            'summary' => 'HR can search employees and open routed profile views used during launch support and onboarding triage.',
            'last_reviewed_at' => '2026-07-01T10:05:00+05:30',
            'artifact_refs' => [
                'apps/web/src/modules/employees/pages/EmployeeSectionPages.tsx',
                'apps/web/src/modules/employees/components/EmployeeDetailShell.tsx',
            ],
        ],
        [
            'key' => 'attendance_review_flow',
            'label' => 'Attendance operational review and correction handling',
            'status' => 'passing',
            'owner_role' => 'attendance.approve',
            'summary' => 'Managers and HR can still resolve attendance exceptions and approvals through the governed operational-review surface.',
            'last_reviewed_at' => '2026-07-01T10:12:00+05:30',
            'artifact_refs' => [
                'apps/web/src/modules/attendance/pages/AttendanceOperationalReviewPage.tsx',
            ],
        ],
        [
            'key' => 'payroll_payslip_access',
            'label' => 'Payroll review and payslip access verification',
            'status' => 'passing',
            'owner_role' => 'payroll.process',
            'summary' => 'Payroll reviewers can inspect run posture and employees can still reach governed payslip download surfaces.',
            'last_reviewed_at' => '2026-07-01T10:18:00+05:30',
            'artifact_refs' => [
                'apps/web/src/modules/payroll/pages/PayrollReviewPage.tsx',
                'apps/web/src/modules/payroll/pages/PayrollSelfServicePage.tsx',
            ],
        ],
    ],
    'runbooks' => [
        [
            'key' => 'incident_response',
            'name' => 'Launch incident response runbook',
            'path' => 'docs/runbooks/release-incident-response.md',
            'owner_role' => 'platform.super_admin',
            'summary' => 'Defines how incident command, communications, triage, and customer-impact decisions are handled during launch.',
            'when_to_use' => 'Use when launch telemetry or user reports indicate a Sev1 or Sev2 production incident.',
        ],
        [
            'key' => 'rollback',
            'name' => 'Release rollback runbook',
            'path' => 'docs/runbooks/release-rollback.md',
            'owner_role' => 'platform.support',
            'summary' => 'Documents the controlled rollback sequence, evidence capture, and validation needed after reversing a launch.',
            'when_to_use' => 'Use when the approved go-live decision changes because production behavior is unsafe or non-compliant.',
        ],
        [
            'key' => 'common_launch_issues',
            'name' => 'Common launch issues playbook',
            'path' => 'docs/runbooks/release-common-launch-issues.md',
            'owner_role' => 'tenant.admin',
            'summary' => 'Lists repeatable responses for the most common post-launch issues across auth, integrations, payroll, and reporting.',
            'when_to_use' => 'Use when operators need a fast recovery pattern for known launch issues before escalation expands.',
        ],
    ],
];
