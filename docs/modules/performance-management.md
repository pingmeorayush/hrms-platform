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

## Dependencies

- Employee hierarchy and role visibility
- Workflow, notification, and audit services
- Compensation planning and reporting consumers

## Related Sprints

- [Sprint 07: Recruitment, Performance, and Learning](../sprints/sprint-07-recruitment-performance-learning.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Performance Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
