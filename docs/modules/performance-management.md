# Performance Management

## Purpose

The Performance Management module helps organizations set goals, track progress, run review cycles, evaluate competencies, and support development and succession planning.

## Business Value

- Improves alignment between employees and organizational goals
- Standardizes performance cycles and evidence capture
- Supports promotion and compensation decisions
- Creates structured talent and leadership insight

## In Scope

- Goal management, OKRs, and KPI tracking
- Continuous feedback and check-ins
- Review cycles, self-assessment, manager assessment, and 360 reviews
- Competency frameworks and gap analysis
- Development plans, calibration, talent review, and succession planning
- Performance analytics and AI insights

## Out Of Scope

- Payroll calculation
- Recruitment execution
- Attendance tracking

## Primary Actors

- Employee
- Manager
- HR
- Leadership Reviewer

## Core Workflows

- Goal creation, alignment, tracking, and closure
- Ongoing feedback and check-in discussions
- Review cycle launch, self-assessment, manager review, calibration, and publication
- Competency evaluation and development-plan generation
- Succession and talent-review support

## Key Business Rules

- Every goal requires an owner
- Goal weights must total 100 percent within the configured scope
- Finalized reviews become locked
- Visibility and anonymity for feedback depend on configured review settings

## Core Entities

- `goals`
- `goal_key_results`
- `kpis`
- `review_cycles`
- `reviews`
- `feedback_entries`
- `competencies`
- `development_plans`

## Primary APIs

- `GET /api/v1/performance/goals`
- `POST /api/v1/performance/goals`
- `GET /api/v1/performance/reviews`
- `POST /api/v1/performance/reviews`
- `POST /api/v1/performance/reviews/{id}/submit`
- `POST /api/v1/performance/reviews/{id}/calibrate`
- `POST /api/v1/performance/reviews/{id}/finalize`
- `POST /api/v1/performance/reviews/{id}/publish`
- `POST /api/v1/performance/reviews/{id}/reopen`

## Dependencies

- Employee hierarchy and role visibility
- Workflow, notification, and audit services
- Compensation planning and reporting consumers

## Sprint 07 V1 Planning Notes

- Sprint 07 performance delivery should cover more than just goal assignment and self-review submission. The v1 slice needs goal libraries, review-cycle templates, competency visibility, calibration-state handling, and locked-final review behavior.
- Enterprise-grade v1 performance depends on strong role visibility controls for self, manager, reviewer, HR, and leadership contexts, including confidential or restricted feedback sections where configured.
- Review states should be modeled explicitly enough for later reporting and compensation-adjacent decisions, with draft, self-assessment, manager review, calibration, finalized, published, and controlled reopen behavior treated as durable workflow state.

## Current Sprint 07 Delivery Status

- `S07-005` is now implemented in `apps/api` as the performance configuration baseline with tenant-scoped competencies, goal libraries, and review-cycle records.
- The current performance baseline now covers owner-bound goals with deadline and weight controls, competency scale-definition management, participant-rule configuration, review-template section weighting, and competency-visibility baselines for downstream review execution.
- `S07-006` is now implemented in `apps/api` as the performance review execution baseline with review creation, participant submissions, calibration, finalization, publication, and controlled reopen behavior.
- Runtime review states are now modeled as `draft`, `self_assessment`, `manager_review`, `calibration`, `finalized`, `published`, and `reopened`, while configuration-level review-cycle records remain `draft`, `scheduled`, `active`, and `archived`.
- The current execution slice now covers role-aware visibility for self, manager, reviewer, and HR sessions, anonymous peer feedback where configured, deadline enforcement, locked-final review controls, and auditable reopen behavior.
- The current goal types are intentionally limited to `library` for the v1 configuration slice so later employee-assigned goal execution can build on a stable source of truth instead of mixing configuration with in-flight review data.
- `S07-007` is now implemented in `apps/web` through routed `/performance/overview`, `/performance/goals`, `/performance/cycles`, and `/performance/reviews` workspaces.
- The current performance UI now covers goal-library visibility, competency and review-cycle administration, employee self-assessment, manager and reviewer input, calibration posture, finalization controls, and publication or reopen actions with demo-live workspace wiring.
- The performance module is now enterprise-routed end to end for HR, managers, reviewers, and employees.
- `S07-010` is now implemented as the focused performance contract publication in [apps/api/openapi/sprint-07-performance-management.yaml](../../apps/api/openapi/sprint-07-performance-management.yaml), covering goals, competencies, review cycles, review execution, calibration, publication, and reopen controls in one dedicated performance contract.

## Explicit Sprint 07 Deferrals

- Continuous feedback and recurring check-in meetings
- Development plans and pip plans
- Talent reviews and succession planning
- AI-generated performance insights and summaries

## Related Sprints

- [Sprint 07: Recruitment, Performance, and Learning](../sprints/sprint-07-recruitment-performance-learning.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Performance Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
