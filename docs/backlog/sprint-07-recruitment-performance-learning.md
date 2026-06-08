# Sprint 07 Backlog: Recruitment, Performance, and Learning

## Scope Reference

- [Sprint 07 Plan](../sprints/sprint-07-recruitment-performance-learning.md)
- [Recruitment Module](../modules/recruitment.md)
- [Performance Management Module](../modules/performance-management.md)
- [Learning Management Module](../modules/learning-management.md)

## Epics

### EPIC S07-E1: Recruitment Pipeline Foundation

Delivers requisitions, candidate flow, interview coordination, and offer approval support.

### EPIC S07-E2: Performance Cycle Foundation

Delivers goal management, review-cycle setup, review submission, and visibility controls.

### EPIC S07-E3: Learning Catalog and Assignment Foundation

Delivers learning content assignment and completion tracking baseline.

### EPIC S07-E4: Talent UI Experience and Contract Publication

Delivers recruiter, manager, employee, and admin web experiences together with published contracts.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S07-001 | Story | P0 | Implement requisition setup and hiring workflow baseline | Sprint 06 complete, S01-009 |
| S07-002 | Story | P0 | Implement candidate profile, pipeline, and stage-transition management | S07-001 |
| S07-003 | Story | P0 | Implement interview coordination and offer approval workflow | S07-002, S01-009, S02-002 |
| S07-004 | Story | P1 | Implement recruitment workspace and candidate pipeline screens | S07-002, S07-003 |
| S07-005 | Story | P0 | Implement goal libraries, goal assignment, and review-cycle configuration | Sprint 06 complete, S02-003 |
| S07-006 | Story | P0 | Implement self, manager, and reviewer submission flows with visibility rules | S07-005, S02-002 |
| S07-007 | Story | P1 | Implement goals and review-cycle screens for employees and managers | S07-005, S07-006 |
| S07-008 | Story | P0 | Implement learning catalog, assignment, and completion tracking baseline | Sprint 06 complete, S02-003 |
| S07-009 | Story | P1 | Implement learner and admin learning experience UI | S07-008 |
| S07-010 | Story | P1 | Publish recruitment, performance, and learning OpenAPI contracts | S07-003, S07-006, S07-008, S01-013 |

## Ticket Details

### S07-001: Implement requisition setup and hiring workflow baseline

Type: Story  
Priority: P0

Description:

Create the requisition baseline that defines open roles, hiring approvals, and ownership before candidate movement begins.

Dependencies:

- Sprint 06 complete
- S01-009

Acceptance Criteria:

- Requisitions can be created with the v1 data required for hiring approval and downstream candidate tracking
- Approval-sensitive requisition states are auditable and workflow-aware
- Requisition access remains tenant-scoped and permission-controlled

### S07-002: Implement candidate profile, pipeline, and stage-transition management

Type: Story  
Priority: P0

Description:

Create the candidate system of record and the stage model that supports a controlled recruiting pipeline.

Dependencies:

- S07-001

Acceptance Criteria:

- Candidate records can be created and updated with pipeline state
- Stage transitions preserve historical movement and auditability
- Unauthorized users cannot access candidate data outside approved scope

### S07-003: Implement interview coordination and offer approval workflow

Type: Story  
Priority: P0

Description:

Implement the coordination flows around interviews and offers, including approval-sensitive actions.

Dependencies:

- S07-002
- S01-009
- S02-002

Acceptance Criteria:

- Interviews can be scheduled and tracked against candidates and requisitions
- Offer actions can follow the approved workflow baseline
- Decision history and notifications are retained and auditable

### S07-004: Implement recruitment workspace and candidate pipeline screens

Type: Story  
Priority: P1

Description:

Create the recruiter-facing UI for requisitions, candidate movement, interview coordination, and approvals.

Dependencies:

- S07-002
- S07-003

Acceptance Criteria:

- Recruiters can review requisitions, candidates, and stage movement through filtered list and detail views
- Pipeline actions surface validation and workflow state clearly in the UI
- Loading, empty, blocked, and permission-denied states are covered in frontend tests

### S07-005: Implement goal libraries, goal assignment, and review-cycle configuration

Type: Story  
Priority: P0

Description:

Create the configuration baseline for goals and performance cycles before employee reviews begin.

Dependencies:

- Sprint 06 complete
- S02-003

Acceptance Criteria:

- Goal templates or libraries can be defined for the v1 scope
- Review cycles can be configured with dates and participant rules
- Goal and cycle access remains permission-aware and auditable

### S07-006: Implement self, manager, and reviewer submission flows with visibility rules

Type: Story  
Priority: P0

Description:

Create the performance-review execution flow with controlled visibility into self, manager, and reviewer input.

Dependencies:

- S07-005
- S02-002

Acceptance Criteria:

- Employees and managers can submit review content within allowed states
- Visibility rules determine who can read or edit specific review artifacts
- Review submissions and status changes are auditable

### S07-007: Implement goals and review-cycle screens for employees and managers

Type: Story  
Priority: P1

Description:

Create the UI for viewing goals, completing reviews, and managing review-cycle tasks.

Dependencies:

- S07-005
- S07-006

Acceptance Criteria:

- Employees can view assigned goals and complete permitted review tasks through the UI
- Managers can track review progress, pending items, and visibility-restricted feedback appropriately
- UI covers draft, submitted, locked, and access-denied states

### S07-008: Implement learning catalog, assignment, and completion tracking baseline

Type: Story  
Priority: P0

Description:

Create the learning-management baseline for content cataloging, assignment, and completion tracking.

Dependencies:

- Sprint 06 complete
- S02-003

Acceptance Criteria:

- Learning items can be created and assigned to employees or groups
- Completion state is trackable and auditable
- Assignment visibility follows role and tenant permissions

### S07-009: Implement learner and admin learning experience UI

Type: Story  
Priority: P1

Description:

Create the learning UI for employees to view assignments and for admins to manage the catalog.

Dependencies:

- S07-008

Acceptance Criteria:

- Employees can review assigned learning items and completion progress in a self-service UI
- Admins can manage learning items and assignments through filtered list and detail screens
- UI states cover empty assignments, completed items, overdue items, and permission constraints

### S07-010: Publish recruitment, performance, and learning OpenAPI contracts

Type: Story  
Priority: P1

Description:

Publish the contract set for requisitions, candidates, interviews, reviews, goals, and learning APIs.

Dependencies:

- S07-003
- S07-006
- S07-008
- S01-013

Acceptance Criteria:

- Core recruitment, performance, and learning endpoints are documented
- Shared schema and error conventions remain consistent with prior sprint contracts
- Contract files are version-controlled, linted, and usable by frontend and QA teams
