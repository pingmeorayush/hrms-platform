# Learning Management

## Purpose

The Learning Management module supports course delivery, learning paths, assessments, certifications, skill development, compliance training, and learning analytics.

## Business Value

- Improves employee development and skill visibility
- Supports compliance and certification tracking
- Enables structured training assignment and completion reporting
- Provides input into performance and career-growth decisions

## In Scope

- Learning catalog and course management
- Learning content and learning paths
- Enrollment, assignments, and instructor management
- Classroom and virtual training
- Assessments, quizzes, certifications, and compliance training
- Skill framework, skill assessment, recommendations, analytics, reports, and notifications

## Out Of Scope

- The detailed excluded scope requires confirmation from the original PDF text

## Primary Actors

- Employee
- Manager
- HR
- Instructor
- Learning Administrator

## Core Workflows

- Course creation and publication
- Assignment or self-enrollment into learning paths
- Training participation, assessment completion, and certification issuance
- Compliance tracking and renewal reminders
- Skill-gap and recommendation support

## Key Rules

- Completion, certification, and compliance states should be traceable
- Manager and HR visibility must respect role and tenant scope
- Learning recommendations should remain explainable and reviewable

## Core Entities

- `courses`
- `learning_paths`
- `enrollments`
- `assignments`
- `assessments`
- `quizzes`
- `certifications`
- `skill_profiles`

## Primary APIs

- `GET /api/v1/learning/items`
- `POST /api/v1/learning/items`
- `PATCH /api/v1/learning/items/{id}`
- `GET /api/v1/learning/assignments`
- `POST /api/v1/learning/assignments`
- `GET /api/v1/learning/assignments/{id}`
- `GET /api/v1/learning/targets`
- `GET /api/v1/learning/my-assignments`
- `PATCH /api/v1/learning/targets/{id}/complete`

## Dependencies

- Employee data
- Notifications and document access
- Performance integration and analytics

## Sprint 07 V1 Planning Notes

- Sprint 07 learning delivery should focus on an enterprise compliance-ready baseline: catalog items, audience assignment, due dates, overdue posture, renewal posture, and completion-evidence tracking.
- Enterprise-grade v1 learning should support employee, manager, HR, and learning-admin visibility without relying on weak inferred completion state.
- Assignment and completion state should be durable enough for later reporting, compliance, and performance integration, which means due-state, renewal-state, and evidence presence need to be first-class fields.

## Current Sprint 07 Delivery Status

- `S07-008` is now implemented in `apps/api` as the learning-management baseline with tenant-scoped catalog items, audience assignments, employee-level assignment targets, and auditable completion tracking.
- The current learning entities are `learning_items`, `learning_assignments`, and `learning_assignment_targets`, which means assignment visibility and compliance posture are tracked per employee instead of being inferred from loose audience rules after the fact.
- The current learning item baseline now covers code-based catalog control, delivery modes, duration, evidence requirements, optional renewal frequency, default due-day posture, and active or archived lifecycle status.
- The current assignment baseline now supports `employee`, `department`, `designation`, and `all_active` audience scopes, with explicit target resolution limited to active employees in the tenant at assignment time.
- The current target baseline now exposes durable `assigned`, `completed`, due-state, renewal-posture, evidence-present, and completion-timestamp visibility for employee, manager, HR, tenant-admin, and learning-admin sessions based on permission scope.
- Employees can currently review their own learning obligations through `/api/v1/learning/my-assignments`, while managers can review direct-report targets and HR or learning admins can manage catalog items plus assignment posture through the shared `/api/v1/learning` API surface.
- `S07-009` is now implemented in `apps/web` as a routed `/learning` module with overview, catalog, assignments, and learner self-service routes.
- The learning UI now covers admin catalog management, audience-targeted assignment creation, compliance and renewal posture review, and learner completion with optional evidence capture using shared demo and live workspace wiring.
- The current learning module now serves three access contexts from one route family: learning-admin sessions can manage catalog and assignments, manager or HR sessions can review visible learning posture, and employee-linked sessions can complete assigned work through `/learning/my-learning`.
- `S07-010` is now implemented as the focused learning contract publication in [apps/api/openapi/sprint-07-learning-management.yaml](../../apps/api/openapi/sprint-07-learning-management.yaml), covering catalog items, assignments, resolved targets, due-state posture, renewal posture, and learner completion flows in a dedicated learning contract.

## Explicit Sprint 07 Deferrals

- Full learning paths, self-enrollment, and recommendation engines
- Assessments, quizzes, and question banks
- SCORM or media-package playback orchestration
- Instructor-led classroom and virtual-session logistics

## Related Sprints

- [Sprint 07: Recruitment, Performance, and Learning](../sprints/sprint-07-recruitment-performance-learning.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS_Learning_Management_Module_Specification.pdf`

## Notes

This module summary is derived from the learning PDF section outline available in the workspace.
