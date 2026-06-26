# Sprint 07 Backlog: Recruitment, Performance, and Learning

## Scope Reference

- [Sprint 07 Plan](../sprints/sprint-07-recruitment-performance-learning.md)
- [Frontend Delivery Order for Sprint 07](./frontend-delivery-order-sprint-07.md)
- [Recruitment Module](../modules/recruitment.md)
- [Performance Management Module](../modules/performance-management.md)
- [Learning Management Module](../modules/learning-management.md)

## Planning Refresh Outcome

- Sprint 07 currently has no backend or frontend implementation scaffold in the workspace, so this backlog now reflects a true backend-first execution order instead of parallelized wish-list delivery.
- Recruitment is now treated as incomplete unless it covers accepted-offer handoff into onboarding, because the module purpose explicitly includes hire readiness rather than stopping at offer creation.
- Performance is now treated as incomplete unless it covers competency-aware setup, calibration-state control, and locked-final review behavior, because a simple self-review form is not enterprise-grade performance management.
- Learning is now treated as incomplete unless it covers due-state, renewal posture, and completion evidence, because assignment-only tracking is too weak for compliance-driven learning operations.
- Frontend delivery should follow the dedicated Sprint 07 talent UI rollout order so recruiter, reviewer, employee, and admin experiences remain coherent.

## Epics

### EPIC S07-E1: Recruitment Pipeline Foundation

Delivers requisitions, candidate flow, interview coordination, and offer approval support.

### EPIC S07-E2: Performance Cycle Foundation

Delivers goal management, review-cycle setup, review submission, and visibility controls.

### EPIC S07-E3: Learning Catalog and Assignment Foundation

Delivers learning content assignment and completion tracking baseline.

### EPIC S07-E4: Talent UI Experience and Contract Publication

Delivers recruiter, manager, employee, and admin web experiences together with published contracts.

### EPIC S07-E5: Talent Handoff and Hardening

Delivers accepted-offer handoff into onboarding and the state fidelity needed for later analytics, reporting, and AI use cases.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S07-001 | Story | P0 | Implement requisition setup and hiring workflow baseline | Sprint 06 complete, S01-009 |
| S07-002 | Story | P0 | Implement candidate profile, resume versioning, duplicate handling, and stage-transition management | S07-001 |
| S07-003 | Story | P0 | Implement interview coordination, scorecards, and offer approval workflow | S07-002, S01-009, S02-002 |
| S07-004 | Story | P1 | Implement recruitment workspace and candidate pipeline screens | S07-002, S07-003, S07-011 |
| S07-005 | Story | P0 | Implement goal libraries, competency visibility, and review-cycle configuration | Sprint 06 complete, S02-003 |
| S07-006 | Story | P0 | Implement self, manager, and reviewer submission, calibration, and finalization flows | S07-005, S02-002 |
| S07-007 | Story | P1 | Implement goals and review-cycle screens for employees and managers | S07-005, S07-006 |
| S07-008 | Story | P0 | Implement learning catalog, assignment, due-state, and completion tracking baseline | Sprint 06 complete, S02-003 |
| S07-009 | Story | P1 | Implement learner and admin learning experience UI | S07-008 |
| S07-010 | Story | P1 | Publish recruitment, performance, and learning OpenAPI contracts | S07-003, S07-006, S07-008, S07-011, S01-013 |
| S07-011 | Story | P0 | Implement accepted-offer conversion and onboarding handoff baseline | S07-003, S02-003, S06-004, S06-005 |

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

- Requisitions can be created with the v1 data required for hiring approval, recruiter assignment, hiring-manager ownership, openings count, and downstream candidate tracking
- Requisition states support enterprise review posture such as draft, submitted, approved, on hold, and closed with audit coverage and workflow awareness
- Requisition access remains tenant-scoped, permission-controlled, and suitable for later funnel and aging visibility

### S07-002: Implement candidate profile, pipeline, and stage-transition management

Type: Story  
Priority: P0

Description:

Create the candidate system of record and the stage model that supports a controlled recruiting pipeline.

Dependencies:

- S07-001

Acceptance Criteria:

- Candidate records can be created and updated with pipeline state while enforcing tenant-scoped candidate-email uniqueness
- Resume artifacts remain immutable after submission, updates create versions, and duplicate candidates surface before a conflicting record is created
- Stage transitions preserve historical movement, timestamps, actor attribution, and auditability

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

- Interviews can be scheduled and tracked against candidates and requisitions with explicit interviewer assignment and scorecard feedback requirements
- Interview evaluations preserve rating, comments, and recommendation fields and remain auditable after submission
- Offer actions support configurable expiry, workflow-backed approval, and notification plus decision-history retention

### S07-004: Implement recruitment workspace and candidate pipeline screens

Type: Story  
Priority: P1

Description:

Create the recruiter-facing UI for requisitions, candidate movement, interview coordination, and approvals.

Dependencies:

- S07-002
- S07-003
- S07-011

Acceptance Criteria:

- Recruiters and hiring managers can review requisitions, candidates, pipeline movement, interviews, offers, and handoff posture through filtered list, board, and detail views
- Pipeline, interview, offer, and handoff actions surface validation, workflow state, blockers, and document posture clearly in the UI
- Loading, empty, blocked, confidential, and permission-denied states are covered in frontend tests

### S07-005: Implement goal libraries, goal assignment, and review-cycle configuration

Type: Story  
Priority: P0

Description:

Create the configuration baseline for goals and performance cycles before employee reviews begin.

Dependencies:

- Sprint 06 complete
- S02-003

Acceptance Criteria:

- Goal templates or libraries can be defined for the v1 scope with owner, deadline, and weight controls
- Review cycles can be configured with dates, participant rules, review templates, and competency visibility baselines
- Goal and cycle access remains permission-aware, auditable, and suitable for later calibration and publication flows

### S07-006: Implement self, manager, and reviewer submission flows with visibility rules

Type: Story  
Priority: P0

Description:

Create the performance-review execution flow with controlled visibility into self, manager, and reviewer input.

Dependencies:

- S07-005
- S02-002

Acceptance Criteria:

- Employees, managers, and configured reviewers can submit review content within allowed states and deadlines
- Visibility rules determine who can read or edit specific review artifacts, including confidential or reviewer-restricted sections
- Review submissions support calibration, finalization, locked-final review state, and auditable reopen behavior through controlled admin or workflow actions

### S07-007: Implement goals and review-cycle screens for employees and managers

Type: Story  
Priority: P1

Description:

Create the UI for viewing goals, completing reviews, and managing review-cycle tasks.

Dependencies:

- S07-005
- S07-006

Acceptance Criteria:

- Employees can view assigned goals, progress, competencies, and permitted review tasks through the UI
- Managers and HR reviewers can track review progress, pending items, calibration posture, and visibility-restricted feedback appropriately
- UI covers draft, self-assessment, manager review, calibration, finalized, published, reopened, and access-denied states

### S07-008: Implement learning catalog, assignment, and completion tracking baseline

Type: Story  
Priority: P0

Description:

Create the learning-management baseline for content cataloging, assignment, and completion tracking.

Dependencies:

- Sprint 06 complete
- S02-003

Acceptance Criteria:

- Learning items can be created and assigned to employees or organization scopes with due dates and optional renewal posture
- Completion state, completion evidence, and renewal or overdue state are trackable and auditable
- Assignment visibility follows role and tenant permissions and can later support compliance reporting without reinterpreting raw state

### S07-009: Implement learner and admin learning experience UI

Type: Story  
Priority: P1

Description:

Create the learning UI for employees to view assignments and for admins to manage the catalog.

Dependencies:

- S07-008

Acceptance Criteria:

- Employees can review assigned learning items, due states, renewal posture, and completion progress in a self-service UI
- Admins can manage learning items and assignments through filtered list and detail screens with audience, due-state, and compliance posture visibility
- UI states cover empty assignments, completed items, overdue items, renewal items, evidence-present items, and permission constraints

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

- Core recruitment, performance, and learning endpoints are documented in focused contract files rather than one oversized mixed talent contract
- Shared schema and error conventions remain consistent with prior sprint contracts while allowing talent-module-specific enums and status models
- Contract files are version-controlled, linted, and usable by frontend, QA, and future reporting teams

### S07-011: Implement accepted-offer conversion and onboarding handoff baseline

Type: Story  
Priority: P0

Description:

Create the enterprise handoff between recruitment and onboarding so accepted offers become hire-ready records instead of ending as dead-end offer states.

Dependencies:

- S07-003
- S02-003
- S06-004
- S06-005

Acceptance Criteria:

- Accepted offers can be converted into hire-ready handoff records without re-entering core candidate, requisition, or offer data
- Handoff preserves requisition, candidate, offer, and document references for downstream onboarding and audit review
- Onboarding or lifecycle-task orchestration can be triggered or queued from the accepted-offer handoff event

## Recommended Execution Order

1. `S07-001`
2. `S07-002`
3. `S07-003`
4. `S07-011`
5. `S07-004`
6. `S07-005`
7. `S07-006`
8. `S07-007`
9. `S07-008`
10. `S07-009`
11. `S07-010`

## Explicitly Deferred from Sprint 07

- Public career portal and external candidate self-service
- AI screening, candidate ranking, and interview-question generation
- Referral, agency, campus-hiring, and background-verification vendor integrations
- Continuous feedback, check-in meetings, development plans, talent reviews, and succession planning
- Full LMS assessments, quizzes, SCORM delivery, instructor-led scheduling, and recommendation engines
