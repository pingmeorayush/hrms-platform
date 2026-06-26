# Reporting and Analytics

## Purpose

The Reporting and Analytics module turns trusted transactional state from earlier modules into governed KPIs, operational reports, dashboards, exports, and recurring distribution flows.

## Business Value

- Improves decision-making with trustworthy workforce and operational data
- Standardizes KPI definitions across HR, payroll, recruitment, performance, learning, and operations
- Gives managers and executives visibility into trends, risks, due states, and exceptions
- Creates a governed reporting layer that later analytics, AI, and integration work can safely consume

## In Scope

- KPI catalog and dataset registry
- Standard operational reports across core HR domains
- Dashboard summaries for HR, managers, payroll, recruiters, and leadership
- Export generation, saved views, subscriptions, and scheduled delivery baseline
- Sensitive-data masking, access control, freshness posture, and audit visibility for reporting surfaces

## Out Of Scope

- Predictive analytics and AI-generated commentary
- User-authored custom report builder and ad hoc SQL reporting
- External warehouse pipelines, reverse ETL, and real-time streaming analytics
- Cross-customer benchmarking and advanced data-science workbenches

## Primary Actors

- HR
- Payroll
- Recruiter
- Manager
- Executive Leadership
- Analyst

## Core Workflows

- KPI definition and certification
- Standard report generation with approved filters and drilldowns
- Dashboard rendering on governed dataset and freshness rules
- Export request, retrieval, and scheduled distribution
- Saved-view and subscription management with permission-aware delivery

## Key Rules

- Metrics must remain consistent with source-of-truth transactional modules
- Sensitive report access must respect tenant, permission, and domain-specific masking scope
- Large report generation should support background processing
- Dashboard drilldowns must resolve through the same governed dataset logic as direct report APIs
- Audit and compliance requirements apply to report access, export, subscription, and delivery actions

## Core Entities

- `kpi_definitions`
- `report_datasets`
- `dashboard_snapshots`
- `dashboard_widgets`
- `report_exports`
- `saved_report_views`
- `report_subscriptions`

## Planned Primary APIs

- `GET /api/v1/reporting/kpis`
- `GET /api/v1/reporting/datasets`
- `GET /api/v1/reporting/reports/{datasetKey}`
- `GET /api/v1/reporting/dashboards/{dashboardKey}`
- `POST /api/v1/reporting/exports`
- `GET /api/v1/reporting/exports/{reportExportId}`
- `POST /api/v1/reporting/saved-views`
- `POST /api/v1/reporting/subscriptions`

## Dependencies

- Stable data from employee, attendance, leave, payroll, recruitment, performance, learning, document, and asset modules
- Role-based access, tenant scoping, and audit logging from the platform foundation
- Notification and document-delivery primitives for scheduled distribution and file retrieval

## Sprint 08 Planning Notes

- Sprint 08 should focus on enterprise-grade operational reporting, not on predictive or AI-heavy analytics.
- Metric trust is the core product risk, so KPI lineage, dataset certification, masking, freshness, and drilldown parity must be explicit.
- Reporting is a consumer of prior-sprint state, which means earlier modules should not be reinterpreted loosely here; they should be consumed through durable, governed semantics.
- Saved views, subscriptions, and export lifecycle are part of enterprise reporting, not optional polish, because operational teams rely on recurring consumption patterns rather than only one-off exploration.

## Current Workspace Baseline

- A dedicated reporting backend module now exists in `apps/api` with governed KPI and dataset catalog APIs.
- A routed reporting workspace now exists in `apps/web` at `/reporting` with a command-center overview, workforce, team, payroll, recruitment, and executive dashboards, plus `/reporting/explorer`, `/reporting/exports`, and `/reporting/subscriptions`.
- Some useful adjacent patterns already exist:
  - payroll review and exception surfaces in Sprint 05
  - command-center style module overview pages across the web app
  - [Dashboard V2 Blueprint](../../apps/web/DASHBOARD_V2_BLUEPRINT.md) for top-level dashboard strategy
- Earlier sprints now provide sufficiently strong state for reporting:
  - attendance due-state and exception posture
  - leave balance, request, and workflow state
  - payroll run, item, and payslip lifecycle
  - recruitment stage, offer, and handoff state
  - performance calibration and review state
  - learning due, renewal, and completion evidence posture

## Planned Sprint 08 Delivery Status

- `S08-001` is now implemented in `apps/api`, establishing tenant-scoped KPI definitions and report datasets with stable keys, version-aware updates, source lineage, certification posture, and audit coverage.
- `S08-002` is now implemented in `apps/api`, providing governed report query APIs with approved-filter validation, pagination, sorting, drilldown payloads, and audit coverage for the current baseline dataset set.
- `S08-003` is now implemented in `apps/api`, adding governed report export requests, sync-versus-queued execution control, async processing, retention posture, requestor-scoped completion notifications, and controlled download flows for approved formats.
- `S08-004` is now implemented in `apps/api`, adding governed dashboard retrieval with persisted widget definitions, per-scope snapshot caching, freshness posture, and lineage back to certified KPI and dataset records.
- `S08-005` is now implemented in `apps/web`, introducing the routed reporting module with a command-center overview plus role-aware workforce, manager, payroll, recruiter, and leadership dashboards that surface stale-data, masked-data, blocked-widget, and permission-denied states using the shared command-center design language.
- `S08-006` is now implemented in `apps/web`, adding the governed explorer, saved-view creation and archive flow, export queue, and subscription center with demo/live workspace wiring, explicit no-results or blocked-state handling, and route coverage for `/reporting/explorer`, `/reporting/exports`, and `/reporting/subscriptions`.
- `S08-009` is now implemented in `apps/api`, adding governed saved report views, owner-scoped subscriptions, role or company share posture for reusable views, manual delivery triggering, and permission revalidation before each recurring export delivery.
- `S08-007` is now implemented in `apps/api`, hardening report consumption with tenant-scoped dataset resolution, role-sensitive field masking, drilldown-target access filtering, response-level visibility metadata, and audit payloads that record masked fields plus returned drilldown keys.
- `S08-008` is now implemented through focused Sprint 08 contract files for reporting governance/query, dashboards, and delivery flows, closing the final publication and consistency-hardening pass for the module.

## Current Implemented Dataset Query Baseline

- `workforce_headcount_snapshot`
- `attendance_daily_register`
- `leave_request_register`
- `payroll_run_register`
- `recruitment_candidate_pipeline`
- `performance_review_status`
- `learning_assignment_targets`

## Explicit Sprint 08 Deferrals

- Predictive attrition, headcount forecasting, and AI-generated recommendations
- Free-form custom report builder and user-authored formulas
- External BI warehouse feeds and reverse ETL pipelines
- Real-time streaming dashboards and observability-grade telemetry
- Natural-language analytics assistants, which belong to later AI scope

## Related Sprints

- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)
- [Sprint 09: Mobile, Integrations, and Globalization](../sprints/sprint-09-mobile-integrations-globalization.md)
- [Sprint 10: AI, Operations Hardening, and Release Readiness](../sprints/sprint-10-ai-ops-release-readiness.md)

## Source Specs

- `docs/files/PhoenixHRMS_Reporting_&_Analytics_Module_Specification.pdf`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
- `docs/files/PhoenixHRMS Non-Functional Requirements.txt`

## Notes

This module summary is derived from the reporting PDF section outline, current platform implementation state, and the normalized operational reporting direction established by Sprints 01 to 07.
