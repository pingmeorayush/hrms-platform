# Platform Foundation

## Purpose

The Platform Foundation defines the shared security, tenancy, workflow, audit, notification, and API governance capabilities that all business modules depend on.

## Business Value

- Enforces tenant isolation and least-privilege access
- Standardizes approval routing and cross-module orchestration
- Makes critical actions traceable and supportable
- Gives downstream modules a stable platform contract

## In Scope

- Authentication, password reset, MFA, and session controls
- Tenant resolution, tenant settings, and tenant-aware request handling
- Roles, permissions, policies, and data-scope enforcement
- Workflow engine, approvals, delegation, SLA, and escalations
- Audit logging and compliance-ready event capture
- Notification delivery and template-driven communications
- API standards and OpenAPI governance

## Out Of Scope

- External BPM suites and RPA platforms
- External marketing automation
- Full enterprise GRC replacement

## Primary Actors

- Platform Super Admin
- Tenant Administrator
- HR Administrator
- Auditor
- Support and Security Operations

## Core Workflows

- Login, MFA verification, session issue, and tenant context resolution
- Permission and policy evaluation before resource access
- Approval workflow creation, versioning, execution, and escalation
- Event-triggered notification delivery across channels
- Immutable audit capture for security, configuration, and business actions

## Key Rules

- Default authorization posture is deny by default
- Every protected request must be authenticated, authorized, and tenant-validated
- Workflow versions are immutable once published
- Critical platform and business actions must create audit records
- Notification retries are required for failed delivery
- API contracts must remain versioned and governed

## Engineering Conventions

- Use constructor DI by default for controllers, services, and listeners.
- Do not leave protected `FormRequest` classes on `authorize(): true`; use route-permission authorization for straightforward cases and explicit actor or ownership checks for workflow or self-service actions.
- Use domain events and listeners when one module change triggers another module's side effect, such as workflow transitions triggering notifications.
- Use Laravel facades at framework boundaries like transactions, hashing, password broker flows, and rate limiting, not to hide domain dependencies.
- Apply SOLID pragmatically by keeping platform services focused and avoiding abstractions that do not reduce coupling or improve testability.

## Current Delivery Baseline

- Protected request authorization is standardized across the internal modules instead of relying on permissive `FormRequest` defaults.
- Performance and recruitment request flows keep action-aware authorization where route access alone is too broad.
- Workflow and self-service flows still rely on service-level scope resolution where preserving `404` semantics is part of the current contract behavior.
- Backend quality gates now include Pint, PHPUnit, Larastan, and PHPStan level `6`.

## Core Entities

- `users`
- `roles`
- `permissions`
- `policies`
- `tenant_settings`
- `workflow_definitions`
- `workflow_instances`
- `tasks`
- `notifications`
- `audit_logs`

## Sprint 1 Seed Role Baseline

- `platform.super_admin`: full platform foundation access across tenant, auth, workflow, audit, and notification controls
- `platform.support`: operational support access with tenant lookup, user support, and notification visibility
- `platform.auditor`: read-oriented audit and notification visibility
- `tenant.admin`: tenant-scoped admin access for role, user, workflow, and notification management
- `hr.admin`: approval and workflow execution access for HR-driven employee and leave flows
- `manager`: workflow review access for approval participation
- `employee`: in-app notification visibility baseline

The versioned implementation source of truth for role-to-permission mapping is the Sprint 1 seeder in `apps/api/database/seeders/PermissionRoleSeeder.php`.

## Primary APIs

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/verify-mfa`
- `GET /api/v1/admin/roles`
- `POST /api/v1/admin/roles`
- `GET /api/v1/admin/permissions`
- `GET /api/v1/notifications`
- `PATCH /api/v1/notifications/{id}/read`
- `GET /api/v1/audit-logs`

## Dependencies

- Foundational architecture, security, and deployment decisions
- Shared database, cache, queue, and storage infrastructure

## Related Sprints

- [Sprint 00: Program Alignment](../sprints/sprint-00-program-alignment.md)
- [Sprint 01: Auth, RBAC, and Tenant Foundation](../sprints/sprint-01-auth-rbac-tenant-foundation.md)
- [Sprint 10: AI, Operations Hardening, and Release Readiness](../sprints/sprint-10-ai-ops-release-readiness.md)

## Source Specs

- `docs/files/PhoenixHRMS Security Architecture.txt`
- `docs/files/PhoenixHRMS Multi-Tenancy Design.txt`
- `docs/files/PhoenixHRMS RBAC & Authorization Platform Specification.txt`
- `docs/files/PhoenixHRMS Workflow & Business Process Engine Specification.txt`
- `docs/files/PhoenixHRMS Audit & Compliance Platform Specification.txt`
- `docs/files/PhoenixHRMS Notification & Communication Module Specification.txt`
- `docs/files/PhoenixHRMS API Standards.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
