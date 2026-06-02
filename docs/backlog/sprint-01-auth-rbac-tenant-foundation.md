# Sprint 01 Backlog: Auth, RBAC, and Tenant Foundation

## Scope Reference

- [Sprint 01 Plan](../sprints/sprint-01-auth-rbac-tenant-foundation.md)
- [Platform Foundation Module](../modules/platform-foundation.md)
- [Decision Log](../decisions/README.md)

## Epics

### EPIC S01-E1: Authentication and Session Security

Delivers secure access, password recovery, MFA, session controls, and foundational auth auditing.

### EPIC S01-E2: Tenant Resolution and Isolation

Delivers tenant-aware request handling, scoped data access, and isolation enforcement.

### EPIC S01-E3: Roles, Permissions, and Policy Enforcement

Delivers permission model, seed roles, policy checks, and UI/API alignment.

### EPIC S01-E4: Workflow, Notification, and Audit Baseline

Delivers workflow execution, core notifications, and immutable audit coverage for foundational actions.

### EPIC S01-E5: OpenAPI and Engineering Baseline

Delivers contract scaffolding, test and CI expectations, and platform-ready implementation standards.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S01-001 | Story | P0 | Implement login, logout, forgot-password, and reset-password APIs | DEC-003 |
| S01-002 | Story | P0 | Implement MFA verification and account lockout controls | S01-001 |
| S01-003 | Story | P0 | Add session timeout, token lifecycle, and auth audit events | S01-001 |
| S01-004 | Story | P0 | Implement tenant resolution middleware and tenant context loading | DEC-001, DEC-003 |
| S01-005 | Story | P0 | Enforce tenant scoping at data access layer | S01-004 |
| S01-006 | Story | P0 | Define seed roles, permissions, and permission naming baseline | S01-004 |
| S01-007 | Story | P0 | Enforce API-side permission and policy checks | S01-006 |
| S01-008 | Story | P1 | Add role-aware UI visibility contract for protected actions | S01-007 |
| S01-009 | Story | P0 | Implement workflow definition and workflow instance MVP | S01-004 |
| S01-010 | Story | P0 | Implement leave and employee approval workflow templates | S01-009 |
| S01-011 | Story | P1 | Implement notification templates and in-app notification center baseline | S01-009 |
| S01-012 | Story | P0 | Implement immutable audit log baseline for auth and admin actions | S01-001, S01-004 |
| S01-013 | Story | P1 | Publish initial OpenAPI contract set for auth, admin, audit, and notifications | DEC-003, S01-001 |
| S01-014 | Task | P1 | Configure test harness and CI gates for Sprint 01 services | DEC-004 |

## Ticket Details

### S01-001: Implement login, logout, forgot-password, and reset-password APIs

Type: Story  
Priority: P0

Description:

Build the core authentication endpoints and service flows required for secure platform access.

Dependencies:

- DEC-003

Acceptance Criteria:

- Users can log in with valid credentials
- Invalid credentials return standard error responses
- Forgot-password issues single-use reset tokens
- Reset tokens expire after configurable duration
- Login and reset attempts are auditable

### S01-002: Implement MFA verification and account lockout controls

Type: Story  
Priority: P0

Description:

Add MFA challenge handling and failed-attempt protection for user accounts.

Dependencies:

- S01-001

Acceptance Criteria:

- MFA is configurable per policy
- Email OTP and authenticator-app verification are supported in v1 scope
- Accounts lock after configured failed attempts
- Unlock behavior is defined and auditable

### S01-003: Add session timeout, token lifecycle, and auth audit events

Type: Story  
Priority: P0

Description:

Implement secure session expiration, refresh or renewal behavior, and auth event capture.

Dependencies:

- S01-001

Acceptance Criteria:

- Session timeout is enforced
- Tokens can be revoked on logout
- Sensitive auth events appear in audit logs

### S01-004: Implement tenant resolution middleware and tenant context loading

Type: Story  
Priority: P0

Description:

Add a standard request pipeline that resolves tenant identity and exposes tenant context to authorized application code.

Dependencies:

- DEC-001
- DEC-003

Acceptance Criteria:

- Authenticated requests resolve a tenant consistently
- Invalid or inactive tenant context blocks access
- Tenant context includes company ID, timezone, currency, and plan data

### S01-005: Enforce tenant scoping at data access layer

Type: Story  
Priority: P0

Description:

Apply tenant isolation through query scoping and shared access patterns for business tables.

Dependencies:

- S01-004

Acceptance Criteria:

- Cross-tenant reads are blocked by default
- Cross-tenant writes are blocked by default
- Regression coverage exists for accidental leakage scenarios

### S01-006: Define seed roles, permissions, and permission naming baseline

Type: Story  
Priority: P0

Description:

Create the initial role and permission model for platform and tenant users.

Dependencies:

- S01-004

Acceptance Criteria:

- Seed roles exist for core platform and tenant personas
- Permissions follow the approved `resource.action` pattern
- Role-to-permission mapping is documented and versionable

### S01-007: Enforce API-side permission and policy checks

Type: Story  
Priority: P0

Description:

Ensure protected APIs validate permissions and relevant policy conditions before execution.

Dependencies:

- S01-006

Acceptance Criteria:

- Unauthorized requests return `403`
- Permission checks are server-side, not frontend-only
- Audit events capture denied access where required

### S01-008: Add role-aware UI visibility contract for protected actions

Type: Story  
Priority: P1

Description:

Provide the frontend with a permission-aware model for showing or hiding protected navigation and actions.

Dependencies:

- S01-007

Acceptance Criteria:

- Protected UI elements can be hidden from unauthorized users
- UI contract does not replace backend enforcement
- Frontend tests cover at least core hidden and visible states

### S01-009: Implement workflow definition and workflow instance MVP

Type: Story  
Priority: P0

Description:

Create the first workflow engine cut that supports approval-driven module behavior.

Dependencies:

- S01-004

Acceptance Criteria:

- Workflow definitions can be created and versioned
- Workflow instances can start, move, approve, reject, and complete
- Workflow actions are auditable

### S01-010: Implement leave and employee approval workflow templates

Type: Story  
Priority: P0

Description:

Seed the platform with initial templates for employee and leave approval use cases.

Dependencies:

- S01-009

Acceptance Criteria:

- Employee and leave workflows can be instantiated from templates
- Sequential approval path works end to end
- Escalation or delegation placeholders are identified even if limited in v1

### S01-011: Implement notification templates and in-app notification center baseline

Type: Story  
Priority: P1

Description:

Provide reusable templates and in-app delivery for approval, reminder, and system notifications.

Dependencies:

- S01-009

Acceptance Criteria:

- Notifications can be generated from workflow events
- Users can read and mark notifications in-app
- Failed notification attempts support retry handling

### S01-012: Implement immutable audit log baseline for auth and admin actions

Type: Story  
Priority: P0

Description:

Create append-only audit event capture for core platform actions.

Dependencies:

- S01-001
- S01-004

Acceptance Criteria:

- Login success, login failure, password reset, role changes, and permission changes are logged
- Audit records include tenant, user, event type, timestamp, and metadata
- Audit records cannot be edited through normal application flows

### S01-013: Publish initial OpenAPI contract set for auth, admin, audit, and notifications

Type: Story  
Priority: P1

Description:

Create the initial API contract inventory for the shared platform services in Sprint 01.

Dependencies:

- DEC-003
- S01-001

Acceptance Criteria:

- Contract definitions exist for auth, admin, audit, and notifications
- Shared response and error schema standards are applied
- Contract validation can run in CI

### S01-014: Configure test harness and CI gates for Sprint 01 services

Type: Task  
Priority: P1

Description:

Set up the agreed test, validation, and quality gates for the Sprint 01 deliverables.

Dependencies:

- DEC-004

Acceptance Criteria:

- Frontend, backend, API, and contract validation commands are defined
- CI includes lint, test, and contract validation stages
- Failing quality gates block merge or deployment promotion
