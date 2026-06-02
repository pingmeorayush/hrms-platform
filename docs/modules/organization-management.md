# Organization Management

## Purpose

The Organization Management module models the structural hierarchy that all employees, workflows, policies, payroll allocations, and reports depend on.

## Business Value

- Provides a consistent organizational model across the platform
- Supports multi-company and multi-location operations
- Enables reporting hierarchies and cost allocation
- Improves governance and segmentation

## In Scope

- Companies, legal entities, business units, divisions, departments, and teams
- Locations, geographic hierarchy, and cost centers
- Reporting structures and matrix organizations
- Organization charts and workforce planning structures

## Out Of Scope

- Employee personal records
- Payroll processing
- Recruitment execution

## Primary Actors

- Tenant Administrator
- HR Director
- HR Manager
- Finance Manager

## Core Workflows

- Organizational hierarchy setup per tenant
- Legal entity and cost-center creation for payroll and compliance usage
- Reporting-line maintenance with history and temporary delegation
- Multi-company modeling for shared services and cross-company transfer readiness

## Key Rules

- Organizational hierarchy is the root reference model for business data
- Reporting structures must preserve historical changes
- Location, timezone, and currency attributes must support downstream modules
- Tenant isolation applies to all structure data
- Structure changes must be auditable with before and after state where applicable

## Core Entities

- `companies`
- `legal_entities`
- `business_units`
- `divisions`
- `departments`
- `teams`
- `locations`
- `cost_centers`

## Primary APIs

- `GET /api/v1/organization/company-profile`
- `PATCH /api/v1/organization/company-profile`
- `GET /api/v1/organization/departments`
- `POST /api/v1/organization/departments`
- `PATCH /api/v1/organization/departments/{departmentId}`
- `GET /api/v1/organization/designations`
- `POST /api/v1/organization/designations`
- `PATCH /api/v1/organization/designations/{designationId}`
- `GET /api/v1/organization/locations`
- `POST /api/v1/organization/locations`
- `PATCH /api/v1/organization/locations/{locationId}`
- `GET /api/v1/organization/cost-centers`
- `POST /api/v1/organization/cost-centers`
- `PATCH /api/v1/organization/cost-centers/{costCenterId}`
- `GET /api/v1/organization/audit-history`

## Dependencies

- Tenant model and authorization foundation
- Employee management
- Payroll, reporting, and localization consumers
- Published Sprint 02 contract: `apps/api/openapi/sprint-02-employee-organization-management.yaml`

## Related Sprints

- [Sprint 02: Employee and Organization Management](../sprints/sprint-02-employee-organization-management.md)
- [Sprint 09: Mobile, Integrations, and Globalization](../sprints/sprint-09-mobile-integrations-globalization.md)

## Source Specs

- `docs/files/PhoenixHRMS Organization Management Module Specification.txt`
- `docs/files/PhoenixHRMS Multi-Tenancy Design.txt`
- `docs/files/PhoenixHRMS RBAC & Authorization Platform Specification.txt`
