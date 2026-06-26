# Sprint 07: Recruitment, Performance, and Learning

## Objective

Extend the platform from core HR operations into hiring, employee growth, and performance enablement.

## Status

Completed

## Primary Backlog IDs

- `REC-001`
- `PERF-001`
- `LMS-001`

## Module References

- [Recruitment](../modules/recruitment.md)
- [Performance Management](../modules/performance-management.md)
- [Learning Management](../modules/learning-management.md)

## Backlog Detail

- [Sprint 07 Delivery Backlog](../backlog/sprint-07-recruitment-performance-learning.md)
- [Frontend Delivery Order for Sprint 07](../backlog/frontend-delivery-order-sprint-07.md)

## Scope

- Recruitment from requisition through offer
- Candidate documents, stage history, and hire handoff into onboarding
- Candidate pipeline and interview coordination
- Goal setting, competency-backed review cycles, and controlled review visibility
- Learning catalog, assignments, compliance due states, and completion tracking baseline

## Delivery Items

- Recruitment workflow, candidate-management, interview, offer, and hire-handoff APIs
- Recruiter, hiring-manager, interviewer, employee, reviewer, and learning-admin web experiences
- Goal, competency, and review-cycle services with calibration and finalization controls
- Learning catalog, audience assignment, due-state, renewal, and completion-evidence MVP

## Dependencies

- Sprints 01 and 02 for workflow, audit, tenant, and employee foundations
- Sprint 06 document, asset, and onboarding-offboarding foundations for resumes, offers, certificates, and hire handoff
- Reporting baseline may be partially reused later for funnel, review, and compliance visibility

## Acceptance Criteria

- Recruiters can move candidates through a controlled pipeline with requisition approval, resume governance, interview scorecards, offer approval, and accepted-offer handoff coverage
- Managers and HR can create, execute, calibrate, and publish a controlled review cycle with role-aware visibility and final lock semantics
- Learning content can be assigned to employees or organization scopes with due-state, completion, and renewal tracking visible to the correct audiences

## Test Focus

- Candidate duplication, stage transitions, interview scorecard submission, and offer expiry workflow
- Review visibility, weight validation, calibration-state control, final lock, and reopen behavior
- Learning assignment targeting, overdue and renewal state, completion evidence, and permission boundaries

## Risks and Open Questions

- Recruitment and learning specifications still rely partly on PDF-only source material, so the sprint must keep explicit MVP boundaries in the backlog
- Combining three large talent domains in one sprint is only viable if execution stays backend-first and module-sequenced rather than trying to ship all three frontends in parallel
- Candidate-to-employee conversion, confidential review visibility, and compliance-learning due-state behavior need to be modeled carefully so later reporting and AI sprints are not forced to reinterpret weak state

## Current Workspace Baseline

- Sprint 07 recruitment backend implementation is now started in `apps/api` with the `S07-001` requisition baseline.
- The recruitment, performance, and learning backend scaffolds are now live in `apps/api`.
- The routed recruitment, performance, and learning modules are now live in `apps/web`.
- The Sprint 07 OpenAPI contracts are now published in `apps/api/openapi` as `sprint-07-recruitment-operations.yaml`, `sprint-07-performance-management.yaml`, and `sprint-07-learning-management.yaml`.

## Delivered So Far

- `S07-001` now provides requisition create, list, view, update, submit, approval, on-hold, resume, and close behavior through `/api/v1/recruitment/requisitions`.
- Requisition approval is wired through the shared workflow engine using a hiring-manager stage resolved from requisition payload plus an HR review stage.
- Tenant-scoped access, recruiter ownership, hiring-manager visibility, and automated backend coverage are in place for the requisition baseline.
- `S07-002` now provides candidate create, list, view, update, resume-version upload, resume download, and stage-transition behavior through `/api/v1/recruitment/candidates`.
- Candidate intake is currently restricted to approved requisitions, resume artifacts are immutable after upload with version history preserved, and stage movement is captured as first-class transition history instead of being inferred from candidate notes.
- `S07-003` now provides interview create, list, view, cancel, and scorecard-submission behavior through `/api/v1/recruitment/interviews`, plus offer create, list, view, submit, approval, send, expiry, and candidate-decision flows through `/api/v1/recruitment/offers`.
- Interview scheduling now enforces explicit interviewer assignment, overlap protection for the same interviewer or candidate, immutable scorecard submission, and recruiter notification when feedback is submitted.
- Offer approval now uses the shared workflow engine with hiring-manager plus HR routing, durable decision-history retention, configurable expiry control, and a guard against multiple active offers for the same candidate.
- `S07-011` now provides accepted-offer conversion and onboarding handoff behavior through `/api/v1/recruitment/offers/{offerId}/handoff` and `/api/v1/recruitment/handoffs`.
- Hire handoff now creates a durable recruitment-to-employee conversion record, preserves offer, candidate, requisition, and resume references, creates the employee without re-entering core offer data, moves the candidate into the `hired` stage, and can queue onboarding lifecycle tasks from active templates.
- `S07-004` now provides the recruiter-facing web workspace through routed `/recruitment` overview, requisition, candidate-board, and candidate-detail screens in `apps/web`.
- The recruitment UI now covers requisition posture, filtered pipeline movement, candidate timelines, resume versions, interview actions, scorecard capture, offer workflow visibility, and accepted-offer handoff creation with persona-aware route guards.
- `S07-005` now provides the performance configuration baseline through `/api/v1/performance/goals`, `/api/v1/performance/competencies`, and `/api/v1/performance/review-cycles`.
- The performance backend now covers goal libraries with owner, deadline, and weight controls, competency scale definitions, participant rules, review-template weight validation, competency-visibility baselines, tenant scoping, and audit coverage for later review execution.
- `S07-006` now provides performance review creation, list, detail, submission, calibration, finalization, publication, and reopen behavior through `/api/v1/performance/reviews` and its action endpoints.
- Performance review execution now covers employee, manager, reviewer, and HR participation, role-aware submission visibility, configured peer-feedback anonymity, due-date enforcement, calibration payloads, locked-final review controls, and auditable reopen behavior on top of the Sprint 7 review-cycle configuration baseline.
- `S07-007` now provides the routed performance web workspace through `/performance/overview`, `/performance/goals`, `/performance/cycles`, and `/performance/reviews` in `apps/web`.
- The performance UI now covers goal-library visibility, competency and cycle administration, employee self-assessment, manager and reviewer submission posture, calibration review, finalization, publication, reopen controls, and persona-aware performance route guards.
- `S07-008` now provides the learning backend baseline through `/api/v1/learning/items`, `/api/v1/learning/assignments`, `/api/v1/learning/targets`, `/api/v1/learning/my-assignments`, and `/api/v1/learning/targets/{learningAssignmentTargetId}/complete`.
- The learning backend now covers catalog items with evidence and renewal rules, audience-scoped assignment to employees or organization scopes, durable employee-level assignment targets, explicit due-state and renewal-posture derivation, completion-evidence capture, manager-direct-report visibility, and auditable completion history.
- `S07-009` now provides the routed learning web workspace through `/learning/overview`, `/learning/catalog`, `/learning/assignments`, and `/learning/my-learning` in `apps/web`.
- The learning UI now covers admin catalog management, assignment targeting, visible compliance posture, learner overdue and renewal visibility, and evidence-backed completion actions with persona-aware route guards and demo-live workspace state.
- `S07-010` now publishes separate focused OpenAPI contracts for recruitment, performance, and learning in `apps/api/openapi` instead of one oversized mixed talent contract.
- Sprint 07 is now complete across backend, frontend, contract publication, and traceability for recruitment, performance, and learning.

## Planning Refresh

This sprint needed a deeper planning pass because the original plan was directionally correct but too generic for enterprise delivery. The current planning refresh closes the main gaps:

- Recruitment now explicitly includes resume versioning, duplicate detection, interviewer scorecards, configurable offer expiry, and accepted-offer handoff into onboarding.
- Performance now explicitly includes competency-backed review setup, calibration-state handling, locked-final review semantics, and controlled reopen behavior.
- Learning now explicitly includes audience-based assignment, due-state and renewal tracking, and completion-evidence handling rather than only a thin catalog list.
- Frontend delivery now has a dedicated Sprint 07 rollout order so recruiter, reviewer, employee, and admin experiences can be built coherently.

## Recommended Delivery Order

1. Recruitment backend foundation
   `S07-001` to `S07-003`
2. Hire handoff baseline
   `S07-011`
3. Recruitment operations UI
   `S07-004`
4. Performance backend foundation
   `S07-005` to `S07-006`
5. Performance execution UI
   `S07-007`
6. Learning backend foundation
   `S07-008`
7. Learning admin and learner UI
   `S07-009`
8. Separate module contracts and final hardening
   `S07-010`

## Sprint 07 MVP Boundaries

### In Scope

- Internal recruiter-led hiring operations
- Structured candidate pipeline history and interview scorecards
- Offer approval and accepted-offer operational handoff
- Goal libraries, review cycles, reviewer visibility, and calibration baseline
- Learning assignment, due-state, renewal, and completion tracking baseline

### Explicitly Deferred

- Public career portal and external candidate self-service
- AI screening, resume scoring, and interview-question generation
- Referral, agency, campus-hiring, and vendor background-verification integrations
- Continuous feedback, development plans, succession planning, and compensation-linked talent reviews
- Full LMS assessments, quizzes, SCORM delivery, instructor-led classroom orchestration, and recommendation engines

## Enterprise-Grade V1 Expectations

- Workflow-backed approval and reopen controls for requisitions, offers, and finalized reviews
- Private document handling and version history for resumes, offers, and completion artifacts
- Role-specific visibility for recruiter, hiring manager, interviewer, employee, reviewer, manager, HR, and learning administrator sessions
- Audit trails, state timelines, and aging or due-state indicators that later reporting can consume directly
- Accepted-offer handoff into onboarding without manual re-entry of core candidate and offer fields
- Separate version-controlled OpenAPI contracts for recruitment, performance, and learning so downstream teams can review smaller, focused integration surfaces
