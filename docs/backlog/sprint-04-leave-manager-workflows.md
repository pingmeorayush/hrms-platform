# Sprint 04 Backlog: Leave Management and Manager Workflows

## Scope Reference

- [Sprint 04 Plan](../sprints/sprint-04-leave-manager-workflows.md)
- [Leave Management Module](../modules/leave-management.md)
- [Employee Management Module](../modules/employee-management.md)
- [Attendance Module](../modules/attendance.md)
- [Frontend Delivery Order for Sprints 02 to 04](./frontend-delivery-order-sprints-02-to-04.md)

## Epics

### EPIC S04-E1: Leave Policy and Balance Foundation

Delivers the leave policy, eligibility, accrual, and balance rules required before operational leave usage can begin.

### EPIC S04-E2: Leave Request and Approval Operations

Delivers policy-aware leave requests, approval workflows, cancellations, and attendance-aware downstream effects.

### EPIC S04-E3: Leave UI for Employees, Managers, and HR

Delivers the web experience for leave balances, calendars, team visibility, and approval queues.

### EPIC S04-E4: Contract and Quality Baseline

Delivers published contracts, test coverage, and implementation alignment for leave operations.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S04-001 | Story | P0 | Implement leave types, policy configuration, and eligibility rules | Sprint 03 complete |
| S04-002 | Story | P0 | Implement accrual, carry-forward, and encashment baseline | S04-001 |
| S04-003 | Story | P0 | Implement leave balance ledger and policy-aware balance APIs | S04-001, S04-002, S02-003 |
| S04-004 | Story | P0 | Implement leave request, overlap validation, cancellation, and attendance sync | S04-003, S03-001, S03-004 |
| S04-005 | Story | P0 | Implement leave approval workflow and manager decision path | S04-004, S01-009, S02-002 |
| S04-006 | Story | P1 | Implement employee leave balance, request, and history screens | S04-003, S04-004 |
| S04-007 | Story | P1 | Implement manager approval queue, team availability, and leave calendar screens | S04-005, S02-002 |
| S04-008 | Story | P1 | Implement HR leave policy and leave-calendar admin screens | S04-001, S04-002, S04-003 |
| S04-009 | Story | P1 | Publish leave and manager-workflow OpenAPI contracts | S04-004, S04-005, S01-013 |

## Ticket Details

### S04-001: Implement leave types, policy configuration, and eligibility rules

Type: Story  
Priority: P0

Description:

Create the tenant-scoped leave policy model that defines leave types, rule parameters, and employee eligibility.

Dependencies:

- Sprint 03 complete

Acceptance Criteria:

- HR admins can configure leave types with rule fields required for the v1 policy set
- Eligibility rules can be expressed by employee attributes or organization scope where required
- Policy changes are tenant-scoped, auditable, and version-aware

### S04-002: Implement accrual, carry-forward, and encashment baseline

Type: Story  
Priority: P0

Description:

Implement the rule engine baseline for earned leave accruals and policy-driven carry-forward or encashment behavior.

Dependencies:

- S04-001

Acceptance Criteria:

- Accrual frequency and opening-balance behavior follow configured policy rules
- Carry-forward and encashment constraints are enforced for the v1 scope
- Balance calculations are repeatable for the same employee, period, and policy version

### S04-003: Implement leave balance ledger and policy-aware balance APIs

Type: Story  
Priority: P0

Description:

Provide a durable leave-balance source of truth that supports requests, approvals, and historical review.

Dependencies:

- S04-001
- S04-002
- S02-003

Acceptance Criteria:

- Each leave-impacting action writes a traceable balance entry or derived balance state
- Employees and authorized managers can retrieve current balances and relevant history
- Balance APIs respect tenant, role, and leave-type visibility rules

### S04-004: Implement leave request, overlap validation, cancellation, and attendance sync

Type: Story  
Priority: P0

Description:

Implement the employee-facing leave request path and the core business rules around date validation and downstream attendance effects.

Dependencies:

- S04-003
- S03-001
- S03-004

Acceptance Criteria:

- Invalid, overlapping, or balance-exceeding requests are rejected with standard errors
- Employees can cancel leave within policy-controlled states
- Approved leave updates downstream attendance-related states deterministically where applicable

### S04-005: Implement leave approval workflow and manager decision path

Type: Story  
Priority: P0

Description:

Integrate leave requests with the Sprint 01 workflow baseline so manager approvals are auditable and notification-aware.

Dependencies:

- S04-004
- S01-009
- S02-002

Acceptance Criteria:

- Leave requests can enter configured approval workflows
- Managers can approve or reject within scoped authority
- Approval history, comments, and notification events are retained and auditable

### S04-006: Implement employee leave balance, request, and history screens

Type: Story  
Priority: P1

Description:

Create the employee web experience for viewing balances, submitting leave, and reviewing leave history.

Dependencies:

- S04-003
- S04-004

Acceptance Criteria:

- Employees can view current balances and requestable leave types through the UI
- Leave request screens expose validation feedback, attachment or note placeholders where required, and clear status messaging
- Leave history states and empty or pending states are covered in frontend tests

### S04-007: Implement manager approval queue, team availability, and leave calendar screens

Type: Story  
Priority: P1

Description:

Create the manager-facing workspace for leave approvals and team leave visibility.

Dependencies:

- S04-005
- S02-002

Acceptance Criteria:

- Managers can review pending leave requests, approve or reject them, and inspect relevant balance context
- Team leave calendars or availability views reflect approved and pending leave states at the permitted scope
- Manager UI only exposes employees within hierarchy scope

### S04-008: Implement HR leave policy and leave-calendar admin screens

Type: Story  
Priority: P1

Description:

Create the HR admin web experience for leave setup, policy maintenance, and organization-level leave visibility.

Dependencies:

- S04-001
- S04-002
- S04-003

Acceptance Criteria:

- HR admins can configure leave types and balance rules through web forms with inline validation
- Calendar views surface approved leave at organization scope with tenant and permission boundaries
- Policy update actions and calendar filtering states are covered in frontend tests

### S04-009: Publish leave and manager-workflow OpenAPI contracts

Type: Story  
Priority: P1

Description:

Publish the contract set for leave policy, balance, request, approval, and calendar APIs so frontend and integration work can proceed against versioned schemas.

Dependencies:

- S04-004
- S04-005
- S01-013

Acceptance Criteria:

- Core leave endpoints are documented with shared schema conventions
- Contract changes are version-controlled, linted, and reviewable
- Frontend and QA teams have a stable contract source of truth for Sprint 04 flows
