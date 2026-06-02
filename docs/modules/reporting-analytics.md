# Reporting and Analytics

## Purpose

The Reporting and Analytics module provides operational reports, dashboards, KPI definitions, workforce analytics, predictive insights, and scheduled data exports across the HR platform.

## Business Value

- Improves decision-making with timely workforce data
- Standardizes KPI definitions across departments
- Gives managers and executives visibility into trends and exceptions
- Turns transactional module data into planning and compliance insight

## In Scope

- Operational reports across HR domains
- Recruitment, performance, learning, and asset reporting
- Executive dashboards and KPI frameworks
- Workforce, attrition, recruitment, payroll, performance, and learning analytics
- Predictive analytics and AI workforce intelligence
- Data warehouse feeds, custom report builder, real-time analytics, scheduled reports, and export

## Out Of Scope

- The detailed excluded scope requires confirmation from the original PDF text

## Primary Actors

- HR
- Payroll
- Recruiter
- Manager
- Executive Leadership
- Analyst

## Core Workflows

- Report generation from transactional and analytical data sources
- Dashboard rendering for operational and executive audiences
- KPI definition, governance, and visualization
- Scheduled report generation and export delivery
- Cross-module analytics for workforce planning and risk detection

## Key Rules

- Metrics must remain consistent with source-of-truth transactional modules
- Sensitive report access must respect tenant and permission scope
- Large report generation should support background processing
- Audit and compliance requirements apply to report access and export

## Core Entities

- `report_definitions`
- `dashboard_widgets`
- `kpi_definitions`
- `report_exports`
- `analytics_snapshots`
- `scheduled_reports`

## Primary APIs

- `GET /api/v1/reports/attendance`
- `GET /api/v1/reports/payroll`
- `GET /api/v1/reports/leave`
- `GET /api/v1/reports/recruitment`
- `GET /api/v1/reports/performance`
- `POST /api/v1/reports/export`

## Dependencies

- Stable data from employee, attendance, leave, payroll, recruitment, performance, and learning modules
- Role-based access, tenant scoping, and audit logging
- Optional data warehouse or analytical storage pattern

## Related Sprints

- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)
- [Sprint 10: AI, Operations Hardening, and Release Readiness](../sprints/sprint-10-ai-ops-release-readiness.md)

## Source Specs

- `docs/files/PhoenixHRMS_Reporting_&_Analytics_Module_Specification.pdf`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
- `docs/files/PhoenixHRMS Non-Functional Requirements.txt`

## Notes

This module summary is derived from the reporting PDF section outline and related API and NFR sources available in the workspace.
