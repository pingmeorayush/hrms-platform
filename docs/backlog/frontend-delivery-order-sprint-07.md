# Frontend Delivery Order: Sprint 07

## Purpose

Turn Sprint 07 talent stories into a practical frontend rollout order so recruitment, performance, and learning land as coherent enterprise workspaces instead of isolated screens.

## Current Workspace Baseline

- The routed recruitment module is now live in `apps/web` with overview, requisitions, candidate-board, and candidate-detail flows.
- The routed performance and learning modules are now live in `apps/web`.
- Talent-specific API wiring, demo datasets, route guards, and published Sprint 07 contracts now exist for recruitment, performance, and learning and should be reused as the baseline for later talent hardening work.

## Planning Principles

- Build each talent area as a role-aware workspace, not a collection of unrelated CRUD screens.
- Ship backend state models before building dense operational UIs such as pipeline boards, calibration queues, or compliance views.
- Reuse Sprint 06 document and self-service patterns for resumes, offers, certificates, and restricted review artifacts.
- Preserve one consistent visual language for list, board, detail, timeline, scorecard, and task-state patterns across all talent modules.
- Treat recruiter, hiring manager, interviewer, employee, reviewer, manager, HR, and learning-admin experiences as distinct access contexts from the beginning.

## Recommended Delivery Waves

| Wave | Scope | Stories | Why This Comes Here |
| --- | --- | --- | --- |
| 0 | Talent route and state foundation | Cross-story prerequisite | Introduce routed shells, nav sections, permission guards, and shared timeline, board, and reviewer-state patterns before feature screens split into three domains. |
| 1 | Recruitment operations baseline | `S07-004` after `S07-001` and `S07-002` | Requisition lists, candidate boards, and recruiter filters are the highest-value talent operations surface and establish the board/detail interaction model used nowhere else today. |
| 2 | Candidate detail and interview-offer workflow UI | `S07-004` and `S07-011` after `S07-003` | Candidate timeline, resume versions, interview scorecards, offer states, and hire handoff need the underlying workflow and document state to exist first. |
| 3 | Performance admin baseline | `S07-007` after `S07-005` | Goal libraries, cycle setup, competency configuration, and review templates should be visible before employee review execution starts. |
| 4 | Performance execution workspace | `S07-007` after `S07-006` | Employee self-assessment, manager review, reviewer visibility, and calibration-progress surfaces depend on cycle and state rules already being stable. |
| 5 | Learning admin baseline | `S07-009` after `S07-008` | Course catalog, assignment targeting, due-state management, and renewal controls should exist before learner-facing completion views are introduced. |
| 6 | Learner and manager learning usage | `S07-009` | Employee assignment progress, overdue compliance, and manager visibility become meaningful only after the catalog and targeting model are in place. |
| 7 | Contract-driven hardening pass | `S07-010` | Final UI alignment should happen after the recruitment, performance, and learning OpenAPI contracts are published and reviewed. |

## Current Delivery Status

- Wave 1 is now implemented through the routed recruitment overview, requisition review, and candidate pipeline board in `apps/web`.
- Wave 2 is now implemented through candidate detail, resume version visibility, interview coordination, offer-state rendering, and accepted-offer handoff actions.
- Wave 3 is now implemented through the routed performance overview, goals, and cycle administration workspace in `apps/web`.
- Wave 4 is now implemented through the performance review cockpit for employees, managers, reviewers, and HR calibration sessions.
- Wave 5 is now implemented through the routed learning catalog and assignment administration workspace in `apps/web`.
- Wave 6 is now implemented through the learner self-service route with due-state, renewal, and completion-evidence flows.
- Wave 7 is now implemented through the published recruitment, performance, and learning API contracts plus route-level UI alignment and verification.
- The next frontend move after Sprint 07 is to reuse these talent workspace patterns during Sprint 08 reporting and analytics delivery.

## Wave Details

### Wave 0: Talent Route and State Foundation

Suggested implementation scope:

- Add routed module shells for `/recruitment`, `/performance`, and `/learning`
- Add talent-specific navigation groups and persona-aware route guards
- Introduce shared UI patterns for:
  - pipeline board columns
  - candidate and review timelines
  - scorecards and matrix summaries
  - due-state and SLA badges
  - restricted reviewer and confidential-document empty states
- Add shared talent API and mutation conventions

### Wave 1: Recruitment Operations Baseline

Ship first:

- Requisition list and detail routes
- Candidate pipeline board
- Recruiter filters for requisition, stage, owner, and aging
- Candidate movement actions with audit and validation feedback

Why first:

- Recruitment is the most operationally dense Sprint 07 surface and benefits the most from a strong board-detail baseline.

### Wave 2: Candidate Detail, Interview, Offer, and Handoff UI

Ship second:

- Candidate detail timeline
- Resume-version visibility
- Interview schedule and scorecard surfaces
- Offer review, expiry, and workflow-state presentation
- Accepted-offer to onboarding handoff state

Why second:

- The candidate-level screen becomes the system-of-record view for recruiters and hiring managers once requisition and board state are already navigable.

### Wave 3: Performance Admin Baseline

Ship third:

- Goal library and template setup
- Competency and review-template baseline
- Review-cycle configuration
- Participant and visibility settings

Why third:

- Performance execution screens are hard to validate without visible cycle configuration and shared review semantics already in the product.

### Wave 4: Performance Execution Workspace

Ship fourth:

- Employee goal and self-review route
- Manager and reviewer assessment route
- Calibration and progress visibility for HR and leadership
- Locked, published, and reopened review state rendering

Why fourth:

- This is where the cycle rules become real for end users, so the admin baseline must exist first.

### Wave 5: Learning Admin Baseline

Ship fifth:

- Learning catalog route
- Assignment targeting and due-date controls
- Renewal and compliance posture views
- Completion evidence visibility

Why fifth:

- The admin catalog and assignment model has to exist before learner self-service becomes trustworthy or useful.

### Wave 6: Learner and Manager Learning Usage

Ship sixth:

- Learner assignment dashboard
- Completion and overdue states
- Certificate or completion-evidence visibility
- Manager progress rollup where allowed

Why sixth:

- Learning becomes meaningful to employees only after assignment and compliance logic are stable.

### Wave 7: Contract-Driven Hardening

Ship seventh:

- Align request and response shapes with Sprint 07 contracts
- Tighten permission-denied, blocked, and empty states
- Validate that route guards, state transitions, and document actions match the published API contracts

Why seventh:

- Sprint 07 spans three large domains, so a contract-driven hardening pass is necessary to keep the talent UI coherent before Sprint 08 reporting consumes these states.

## Suggested Route Groups

- `/recruitment/requisitions`
- `/recruitment/candidates`
- `/recruitment/candidates/:candidateId`
- `/performance/cycles`
- `/performance/goals`
- `/performance/reviews`
- `/learning/catalog`
- `/learning/assignments`
- `/learning/my-learning`

## Suggested Module Layout

- `apps/web/src/modules/recruitment`
- `apps/web/src/modules/performance`
- `apps/web/src/modules/learning`

Suggested per-module subfolders:

- `api`
- `components`
- `pages`
- `hooks`
- `types`
- `data`

## Recommended First Build

If implementation starts immediately, the first Sprint 07 frontend sequence should be:

1. Wave 0 talent route and state foundation
2. Wave 1 recruitment operations baseline
3. Wave 2 candidate detail, interview, offer, and handoff workspace

That path creates the strongest operational entry point and proves the shared talent interaction patterns before performance and learning are added.
