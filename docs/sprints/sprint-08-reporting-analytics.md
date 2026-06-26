# Sprint 08: Reporting and Analytics

## Objective

Provide trustworthy operational reporting and first-line analytics on top of the now-stabilized transactional platform without weakening metric trust, tenant isolation, or sensitive-data controls.

## Status

In Progress

## Primary Backlog IDs

- `RPT-001`
- `RPT-002`
- `RPT-003`

## Module References

- [Reporting and Analytics](../modules/reporting-analytics.md)
- [Platform Foundation](../modules/platform-foundation.md)
- [Payroll](../modules/payroll.md)
- [Performance Management](../modules/performance-management.md)
- [Learning Management](../modules/learning-management.md)

## Backlog Detail

- [Sprint 08 Delivery Backlog](../backlog/sprint-08-reporting-analytics.md)
- [Frontend Delivery Order for Sprint 08](../backlog/frontend-delivery-order-sprint-08.md)

## Scope

- KPI catalog, dataset registry, metric lineage, and certification posture
- Standard operational reports across employee, attendance, leave, payroll, recruitment, performance, learning, documents, and assets
- Persona-scoped dashboards for HR, managers, payroll, recruiters, and leadership
- Export generation, saved views, subscriptions, and scheduled distribution baseline
- Access control, field masking, freshness controls, and audit visibility for reporting surfaces

## Delivery Items

- Reporting backend foundation for dataset definitions, KPI definitions, and governed query APIs
- Dashboard aggregation and freshness layer with repeatable summary metrics
- Report explorer and role-specific dashboard workspaces in `apps/web`
- Export and subscription flows with async handling for large report requests
- Version-controlled Sprint 08 reporting contracts for downstream frontend, QA, and analytics consumers

## Dependencies

- Stable tenant, permission, and audit controls from Sprint 01
- Durable employee, attendance, leave, payroll, recruitment, performance, learning, document, and asset state from Sprints 02 to 07
- Shared notification and file-delivery foundations for scheduled report distribution and export retrieval

## Acceptance Criteria

- KPI and report results are explainable, version-aware, and traceable back to approved source datasets
- Standard reports and dashboards remain tenant-scoped, permission-controlled, and sensitive-field-aware
- Interactive report and dashboard queries stay within approved low-latency targets for standard filtered scopes, with asynchronous fallback where heavy exports or broad result sets exceed the synchronous threshold
- Dashboards display freshness posture clearly and drilldowns resolve to the same governed dataset logic as the underlying report APIs

## Test Focus

- Metric accuracy against source transactional state
- Dataset, KPI, and dashboard freshness consistency
- Field masking, row-level scope, and export audit enforcement
- Async export and scheduled distribution behavior under large-result conditions
- Frontend empty, stale-data, permission-denied, and blocked-export states

## Risks and Open Questions

- Reporting trust will collapse if formulas, drilldowns, or masked views diverge from the transactional source of truth
- Large exports and dashboard aggregation may need queue-backed execution from day one instead of being retrofitted later
- The sprint becomes too broad if predictive analytics, warehouse feeds, and custom report-builder ambitions are not explicitly deferred from the v1 slice
- Saved views and subscriptions need ownership and permission-revalidation rules so a saved configuration never becomes a backdoor around later access changes

## Current Workspace Baseline

- A dedicated reporting backend module now exists in `apps/api` with the Sprint 08 KPI and dataset governance baseline.
- A routed reporting module now exists in `apps/web` with `/reporting/overview`, `/reporting/workforce`, `/reporting/team`, `/reporting/payroll`, `/reporting/recruitment`, and `/reporting/executive`.
- Some adjacent UI patterns already exist and should be reused instead of reinvented:
  - payroll review in Sprint 05
  - overview and command-center style module landing pages in Sprints 02 to 07
  - [Dashboard V2 Blueprint](../../apps/web/DASHBOARD_V2_BLUEPRINT.md) for top-level operational landing-page strategy
- Earlier sprints intentionally preserved durable workflow state, audit trails, due-state posture, and timeline fidelity so Sprint 08 can consume strong state instead of reconstructing analytics from notes or notifications.

## Planning Refresh

The planning pass for Sprint 08 surfaced several important corrections:

- The original sprint wording was too broad and drifted into AI, predictive analytics, and warehouse ambitions that belong later in the program.
- There was no dedicated frontend rollout plan, which would have made dashboards and report explorer work arrive as disconnected screens rather than a coherent reporting product.
- The backlog mentioned saved views in the UI but did not explicitly give them a governed backend home, so saved-view and subscription persistence now needs its own explicit story.
- Enterprise-grade reporting requires more than query endpoints: metric lineage, certification state, freshness timestamps, row-level scope, masking rules, and export auditability all need to be first-class from the start.
- The current workspace has no reporting scaffold yet, so Sprint 08 should remain strongly backend-first and avoid designing report UIs before dataset and permission contracts exist.

## Recommended Delivery Order

1. `S08-001` KPI catalog, dataset registry, metric lineage, and certification baseline
   Status: Implemented in `apps/api` with tenant-scoped KPI and dataset catalog APIs, immutable stable keys, version bumps, audit trails, and certification metadata.
2. `S08-002` governed reporting query APIs with approved filters, masking, and drilldown semantics
   Status: Implemented in `apps/api` with governed dataset query retrieval, approved-filter enforcement, server-driven pagination, sorting, drilldown payloads, and domain-aware access scope for the current baseline dataset set.
3. `S08-007` reporting access control, sensitive-field masking, and audit hardening
   Status: Implemented in `apps/api` with tenant-scoped dataset resolution, sensitive-field masking metadata, drilldown-path access filtering, row-level scope parity, and audit payloads that now record masked fields and returned drilldown keys.
4. `S08-004` dashboard aggregation, caching, snapshotting, and freshness controls
   Status: Implemented in `apps/api` with governed dashboard keys, persisted widget definitions, per-scope snapshot caching, freshness posture derived from certified dataset tolerances, and widget lineage that points back to KPI and dataset governance records.
5. `S08-003` export generation and scheduled distribution baseline
   Status: Implemented in `apps/api` with governed export requests, approved `csv` and `json` formats, sync-versus-queued execution control, async processing, retention-aware download lifecycle, and requestor-scoped in-app completion notifications.
6. `S08-009` saved views, subscriptions, and delivery-target governance
   Status: Implemented in `apps/api` with governed saved views, role or company sharing posture, owner-scoped subscriptions, current-scope validation before delivery, manual subscription dispatch, and blocked-state handling when certification or hierarchy posture changes.
7. `S08-005` role-specific dashboard screens
   Status: Implemented in `apps/web` with a routed reporting command center, persona-aware dashboard sections, demo/live governed dashboard wiring, and explicit stale-data, masked-data, empty, and permission-denied states.
8. `S08-006` report explorer, saved-view, and export-consumption UI
9. `S08-008` contract publication and final hardening

## Sprint 08 MVP Boundaries

### In Scope

- Certified KPI catalog and governed report-dataset registry
- Standard operational reports for workforce, attendance, leave, payroll, recruitment, performance, learning, documents, and assets
- HR, manager, payroll, recruiter, and leadership dashboard experiences
- Export requests, saved views, subscriptions, and scheduled delivery baseline
- Sensitive-data masking, freshness indicators, and audit visibility for reporting actions

### Explicitly Deferred

- Predictive attrition, headcount forecasting, anomaly scoring, and AI-generated commentary
- Custom report builder and ad hoc user-authored metrics
- External BI warehouse feeds, reverse ETL, and real-time streaming analytics
- Public benchmarking datasets and multi-tenant cross-customer analytics
- Narrative AI summaries and recommendation systems, which belong to Sprint 10 scope hardening

## Enterprise-Grade V1 Expectations

- Every KPI must have an owner, formula reference, source dataset, version posture, and certification state
- Every dashboard card must expose freshness timing and drill down into a governed report path instead of a disconnected query
- Sensitive measures such as payroll, bank-adjacent compensation, and restricted review data must support role-specific masking, exclusion, or denial instead of one-size-fits-all visibility
- Heavy report exports must be queue-aware, auditable, retrievable, and retention-governed
- Saved views and subscriptions must remain permission-aware after role changes, scope changes, or employee reassignment
- Reporting contracts should be reviewable as focused files so frontend, QA, and downstream analytics work can reason about datasets and exports without parsing an oversized mixed API specification
