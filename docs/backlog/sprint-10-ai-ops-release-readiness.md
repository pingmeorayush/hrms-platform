# Sprint 10 Backlog: AI, Operations Hardening, and Release Readiness

## Scope Reference

- [Sprint 10 Plan](../sprints/sprint-10-ai-ops-release-readiness.md)
- [AI Assistant Module](../modules/ai-assistant.md)
- [Platform Foundation Module](../modules/platform-foundation.md)
- [Reporting and Analytics Module](../modules/reporting-analytics.md)

## Epics

### EPIC S10-E1: AI Copilot and Guardrails

Delivers the low-risk AI assistant baseline together with safety, citation, and permission controls.

### EPIC S10-E2: Operations Hardening and Reliability

Delivers CI/CD quality gates, observability, backup validation, and restore readiness.

### EPIC S10-E3: AI and Operations UI Experience

Delivers the user and operator interfaces required for trustworthy AI and operational visibility.

### EPIC S10-E4: Release Governance and Launch Readiness

Delivers runbooks, go-live review controls, and final release baselines.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S10-001 | Story | P0 | Implement read-focused AI copilot baseline with permission and citation controls | Sprint 09 complete, Sprint 00 AI governance complete |
| S10-002 | Story | P1 | Implement controlled AI recommendations and approval-gated action patterns | S10-001, S01-009 |
| S10-003 | Story | P1 | Implement AI assistant UI with citations, feedback, and guardrail messaging | S10-001, S10-002 |
| S10-004 | Story | P0 | Implement CI-CD quality gates, security scanning, and contract validation baseline | Sprint 09 complete |
| S10-005 | Story | P0 | Implement observability dashboards, alerting, and operational telemetry | S10-004 |
| S10-006 | Story | P0 | Implement backup, restore, and disaster-recovery validation workflow | S10-004, S10-005 |
| S10-007 | Story | P1 | Implement release-readiness checklist, runbooks, and go-no-go workflow | S10-004, S10-005, S10-006 |
| S10-008 | Story | P1 | Publish AI and operational control contracts and release interfaces | S10-001, S10-004, S01-013 |
| S10-009 | Story | P1 | Implement assistant review analytics, ranked citations, and audit trail visibility | S10-001, S10-002, S10-003 |

## Ticket Details

### S10-001: Implement read-focused AI copilot baseline with permission and citation controls

Type: Story  
Priority: P0

Description:

Create the first low-risk AI copilot baseline for approved read-heavy queries against governed business data.

Dependencies:

- Sprint 09 complete
- Sprint 00 AI governance complete

Acceptance Criteria:

- AI responses are grounded in approved sources and only expose data the requesting user is permitted to access
- Responses include citations or source references appropriate to the supported experience
- AI interactions are logged, reviewable, and bounded to the approved v1 use cases

Current Workspace Note:

- The current implementation is complete in `apps/api` through `/api/v1/ai/workspace` plus `/api/v1/ai/chat`, tenant-scoped AI persistence and audit capture, AI-specific permissions, and governed answer builders for leave, attendance, payslip, policy, and learning use cases.

### S10-002: Implement controlled AI recommendations and approval-gated action patterns

Type: Story  
Priority: P1

Description:

Extend the AI baseline into recommendation scenarios without allowing ungoverned autonomous mutation.

Dependencies:

- S10-001
- S01-009

Acceptance Criteria:

- AI can generate recommendations only for explicitly approved scenarios
- Mutating actions remain approval-gated or human-confirmed
- Recommendation acceptance or rejection is auditable

Current Workspace Note:

- The current implementation is complete in `apps/api` through `/api/v1/ai/recommendations`, `/api/v1/ai/recommendations/{recommendationId}/decisions`, approved scenario enforcement, subject-scope validation, review-only action metadata, and auditable human acceptance or rejection recording.

### S10-003: Implement AI assistant UI with citations, feedback, and guardrail messaging

Type: Story  
Priority: P1

Description:

Create the user-facing AI experience with clear citations, permission-aware response states, and feedback capture.

Dependencies:

- S10-001
- S10-002

Acceptance Criteria:

- Users can view AI answers, citations, and approved follow-up actions through a dedicated UI surface
- Guardrail messages are explicit when the assistant cannot answer or act due to policy boundaries
- Feedback or quality signals can be captured for later review

Current Workspace Note:

- The current implementation is complete in `apps/web` through the routed `/assistant` workspace, demo or live assistant data hooks, governed question and recommendation composers, citation inspection, guardrail panels, feedback capture, and human decision controls for review-only recommendations.

### S10-004: Implement CI-CD quality gates, security scanning, and contract validation baseline

Type: Story  
Priority: P0

Description:

Create the release-engineering baseline that blocks unsafe or unverified changes before production.

Dependencies:

- Sprint 09 complete

Acceptance Criteria:

- Release-critical pipelines run automated tests, security checks, and contract validation
- Failing gates block promotion according to the approved policy
- Quality-gate status is visible and reviewable by authorized operators

Current Workspace Note:

- The current implementation is complete in `.github/workflows`, `apps/api`, and `apps/web` through reusable API and web CI workflows, a top-level release-quality gate workflow, dependency-security audits, OpenAPI validation, release permissions, `GET /api/v1/release/quality-gates`, and the routed `/operations/release` operator workspace.

### S10-005: Implement observability dashboards, alerting, and operational telemetry

Type: Story  
Priority: P0

Description:

Create the monitoring baseline for application health, integration failures, and release-critical workflows.

Dependencies:

- S10-004

Acceptance Criteria:

- Operational dashboards expose agreed service health and failure indicators
- Alerts are routed for approved severity conditions
- Monitoring coverage includes release-critical workflows and integrations

Current Workspace Note:

- The current implementation is complete in `apps/api` and `apps/web` through dedicated observability permissions, `GET /api/v1/observability/overview`, config-backed alert routing, live telemetry derived from integrations, workflow SLA pressure, payroll blockers, notification failures, reporting delivery posture, release-gate blockers, and the routed `/operations/observability` operator workspace.

### S10-006: Implement backup, restore, and disaster-recovery validation workflow

Type: Story  
Priority: P0

Description:

Validate that backup and recovery processes are real, repeatable, and ready before launch.

Dependencies:

- S10-004
- S10-005

Acceptance Criteria:

- Backup execution and restore validation are documented and tested
- Disaster-recovery procedures define roles, sequencing, and evidence requirements
- Recovery outcomes are reviewable and tracked

Current Workspace Note:

- The current implementation is complete in `apps/api`, `apps/web`, and `docs` through dedicated resilience permissions, tenant-scoped validation-run tracking, `GET /api/v1/resilience/readiness`, `POST /api/v1/resilience/validation-runs`, the routed `/operations/resilience` workspace, and the versioned runbook in `docs/runbooks/backup-restore-disaster-recovery-validation.md`.

### S10-007: Implement release-readiness checklist, runbooks, and go-no-go workflow

Type: Story  
Priority: P1

Description:

Create the final release-governance layer so launch readiness is reviewed intentionally rather than assumed.

Dependencies:

- S10-004
- S10-005
- S10-006

Acceptance Criteria:

- Release-readiness checks cover testing, security, contracts, backups, monitoring, and critical workflow verification
- Go-live decisions and blockers are documented with accountable owners
- Runbooks exist for incident response, rollback, and common launch issues

Current Workspace Note:

- The current implementation is complete in `apps/api`, `apps/web`, and `docs` through `GET /api/v1/release/readiness`, `POST /api/v1/release/readiness/decisions`, persisted go or no-go decision tracking, the routed `/operations/readiness` launch-governance workspace, and versioned incident-response, rollback, and common-launch-issues runbooks.

### S10-008: Publish AI and operational control contracts and release interfaces

Type: Story  
Priority: P1

Description:

Publish the contract set for AI assistant endpoints, operational status surfaces, and any approved release-control interfaces.

Dependencies:

- S10-001
- S10-004
- S01-013

Acceptance Criteria:

- AI and operational interfaces are documented where they are part of the supported product or platform surface
- Contract files are version-controlled, linted, and reviewable
- Release engineering and frontend teams have a stable reference for supported Sprint 10 interfaces

Current Workspace Note:

- The current implementation now publishes both `apps/api/openapi/sprint-10-operations-release-controls.yaml` and `apps/api/openapi/sprint-10-ai-assistant.yaml`, giving frontend, QA, and release engineering stable contract assets for the implemented operational-control and AI-assistant surfaces.

### S10-009: Implement assistant review analytics, ranked citations, and audit trail visibility

Type: Story  
Priority: P1

Description:

Harden the governed AI assistant baseline with review analytics, more explicit citation ranking, and visible audit history so trust can be evaluated after the first answer is generated.

Dependencies:

- S10-001
- S10-002
- S10-003

Acceptance Criteria:

- The assistant workspace exposes review analytics for citation coverage, feedback quality, and recommendation queue posture
- Citations surface ranking or evidence metadata that help reviewers understand why one source is shown before another
- Assistant audit events for answers, feedback, and recommendation decisions are visible in the supported API and UI surfaces

Current Workspace Note:

- The current implementation is complete in `apps/api`, `apps/web`, `docs`, and `apps/api/openapi` through enriched `/api/v1/ai/workspace` analytics, ranked citation metadata on assistant answers and recommendations, a visible assistant audit timeline, and the hardened `/assistant` review workspace that exposes quality signals and queue posture instead of only the raw conversation flow.
