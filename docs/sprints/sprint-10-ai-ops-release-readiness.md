# Sprint 10: AI, Operations Hardening, and Release Readiness

## Objective

Finish the program with controlled AI capabilities, production hardening, and release-readiness checks.

## Status

Completed

## Primary Backlog IDs

- `AI-001`
- `AI-002`
- `OPS-001`
- `OPS-002`
- `OPS-003`

## Module References

- [AI Assistant](../modules/ai-assistant.md)
- [Platform Foundation](../modules/platform-foundation.md)
- [Reporting and Analytics](../modules/reporting-analytics.md)

## Backlog Detail

- [Sprint 10 Delivery Backlog](../backlog/sprint-10-ai-ops-release-readiness.md)
- [Sprint 10 Launch Release Package](../releases/sprint-10-launch-release-package.md)

## Scope

- Read-heavy AI Copilot MVP
- Controlled AI recommendations where approved
- CI/CD quality gates, observability, backups, and DR validation
- Production runbook completion and launch readiness review

## Delivery Items

- AI Copilot for low-risk employee and HR queries
- Citation, audit, and approval controls for AI responses and actions
- Release gates for testing, security scanning, contract validation, and deployment
- Monitoring dashboards, alerting, backup verification, and incident runbooks

## Current Delivery Focus

- Sprint 10 now has a real AI baseline in flight across `apps/api`, `apps/web`, and `docs`, while release engineering, observability, resilience, and launch-governance controls remain the other production-readiness pillars.
- `S10-001`, `S10-002`, and `S10-003` are now implemented through a governed `/api/v1/ai/*` backend surface, dedicated AI persistence tables, AI-specific permissions, audit capture, approved answer and recommendation boundaries, a routed `/assistant` web workspace, and demo or live interaction flows with citations, guardrail messaging, recommendation decisions, and feedback capture.
- `S10-004` is now implemented across `.github/workflows`, `apps/api`, and `apps/web` through reusable API and web CI workflows, a top-level release-quality gate workflow, dependency-security checks, contract validation, release permissions, and the routed `/operations/release` operator workspace.
- `S10-005` is now implemented across `apps/api` and `apps/web` through a governed observability contract, routed alert lanes, live telemetry for integrations, workflow SLAs, payroll blockers, notification failures, reporting delivery, and the routed `/operations/observability` workspace.
- `S10-006` is now implemented across `apps/api`, `apps/web`, and `docs` through a governed resilience contract, tenant-scoped validation-run tracking, routed recovery-readiness visibility, and an evidence-first backup or DR runbook that later go-live review can rely on.
- `S10-007` is now implemented across `apps/api`, `apps/web`, and `docs` through a governed release-readiness checklist, persisted go or no-go decisions, routed launch-governance visibility, and versioned incident-response plus rollback runbooks that keep final launch review reviewable instead of tribal.
- `S10-008` now publishes both `apps/api/openapi/sprint-10-operations-release-controls.yaml` and `apps/api/openapi/sprint-10-ai-assistant.yaml`, keeping the operational-control and AI-assistant HTTP surfaces versioned and reviewable side by side.
- `S10-009` now hardens the governed assistant review loop across `apps/api`, `apps/web`, `docs`, and OpenAPI through ranked citation metadata, assistant review analytics, and a visible audit trail that keeps answer quality and human decision posture reviewable after launch.

## Completion Summary

- Sprint 10 is now complete across governed AI assistant delivery, release engineering, observability, resilience, launch governance, and contract publication.
- A final browser UAT pass on 02 Jul 2026 passed for `/assistant`, `/operations/release`, `/operations/observability`, `/operations/resilience`, and `/operations/readiness`, including launch-critical content checks and no-match filter-state validation.
- Final merge and release handoff guidance now lives in [Sprint 10 Launch Release Package](../releases/sprint-10-launch-release-package.md).

## Implemented So Far

- `.github/workflows/api-ci.yml` now runs deterministic Composer and npm installs, Composer manifest validation, dependency security audit, backend quality checks, and OpenAPI linting as one reusable API gate.
- `.github/workflows/web-ci.yml` now runs deterministic web installs, runtime dependency audit, typecheck, lint, tests, and production build validation as one reusable web gate.
- `.github/workflows/release-quality-gates.yml` now orchestrates the blocking release baseline on push, pull request, and manual dispatch, then emits a promotion-gate summary that fails whenever a required child workflow does not pass.
- `apps/api` now exposes `GET /api/v1/release/quality-gates`, giving authorized operators a permission-aware release-quality baseline with gate status, workflow ownership, contract evidence, and protected-environment promotion posture.
- `apps/web` now ships the routed `/operations/release` workspace, surfacing blocking gates, dependency-security posture, required workflows, protected-branch policy, environment promotion state, and selected-gate drill-in evidence in the same operations control tower as integrations and lifecycle work.
- Sprint 10 release-quality visibility now uses dedicated `release.view` and `release.manage` permissions so access stays explicit instead of piggybacking on unrelated admin scopes.
- `apps/api` now exposes `GET /api/v1/observability/overview`, combining config-backed alert routes with live counts from integration sync jobs, overdue workflow tasks, blocked payroll runs, failed notifications, reporting delivery failures, and release-gate blockers.
- `apps/web` now ships the routed `/operations/observability` workspace, surfacing service-health posture, routed severity lanes, selected-service signal drill-ins, and release-critical workflow plus integration coverage inside the same operations control tower.
- Sprint 10 observability visibility now uses dedicated `observability.view` and `observability.manage` permissions so monitoring access and alert-route ownership stay explicit.
- `apps/api` now exposes `GET /api/v1/resilience/readiness` plus `POST /api/v1/resilience/validation-runs`, giving authorized operators a tenant-scoped recovery-readiness baseline with documented scenarios, recent outcomes, actual recovery timing, and evidence capture.
- `apps/web` now ships the routed `/operations/resilience` workspace, surfacing backup cadence, restore validation posture, disaster-recovery sequencing, scenario drill-ins, and reviewable validation history in the same operations control tower as release and observability.
- Sprint 10 resilience visibility now uses dedicated `resilience.view` and `resilience.manage` permissions so recovery readiness and evidence ownership stay explicit instead of hiding inside tribal runbooks.
- `apps/api` now exposes `GET /api/v1/release/readiness` plus `POST /api/v1/release/readiness/decisions`, giving authorized operators a governed launch-review baseline with checklist areas, accountable blockers, runbook references, and persisted go or no-go decisions.
- `apps/web` now ships the routed `/operations/readiness` workspace, surfacing the release checklist, recommendation posture, latest decision, blocker ownership, runbooks, and decision-history workflow in the same operations control tower as release, observability, and resilience.
- Sprint 10 launch governance now keeps incident response, rollback, and common launch issue procedures versioned in `docs/runbooks` so the final go-live review has executable operator guidance instead of scattered notes.
- `apps/api/openapi` now publishes `sprint-10-operations-release-controls.yaml`, giving frontend, QA, and release engineering one version-controlled contract for Sprint 10 operational status and release-control endpoints instead of scattering that reference across route files and UI hooks.
- `apps/api/openapi` now also publishes `sprint-10-ai-assistant.yaml`, documenting the implemented assistant workspace, cited answer, recommendation, decision, and feedback interfaces instead of leaving frontend teams to infer the Sprint 10 AI surface from route files alone.
- `apps/api` now persists AI conversations, interactions, and recommendations with tenant scoping, approval guardrails, and audit capture, while `apps/web` now exposes the `/assistant` route for cited answers, feedback, and review-only recommendation decisions.
- `apps/api` now enriches the assistant workspace with review analytics and recent AI audit activity, while `apps/web` now exposes that governed review layer in `/assistant` with ranked citation badges, queue posture, and audit-timeline visibility.
- Sprint 10 is now complete across backend, frontend, contracts, runbooks, and launch-critical browser UAT for the governed assistant and operations control tower.

## Dependencies

- Stable business data and permissions from previous sprints
- Approved AI governance from Sprint 00

## Acceptance Criteria

- AI features operate only within approved permission and safety boundaries
- CI/CD and observability meet the agreed release baseline
- Restore and incident response procedures are documented and validated
- Launch readiness review identifies no unresolved critical blockers

## Test Focus

- AI grounding and permission boundaries
- Security and contract validation in CI
- Alerting, backup, and restore verification
- End-to-end regression on release-critical workflows

## Risks and Open Questions

- AI scope can expand too quickly without evaluation discipline
- Release readiness will fail if operational hardening is left to the final days instead of built progressively
