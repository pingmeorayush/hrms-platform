# Employee Management

## Purpose

The Employee Management module is the system of record for workforce identity, employment state, organizational placement, contacts, documents, and lifecycle events.

## Business Value

- Centralizes employee records
- Reduces manual HR administration
- Supports downstream attendance, leave, payroll, and analytics
- Preserves auditability and compliance history

## In Scope

- Employee master data
- Employment information and reporting manager assignment
- Contacts, addresses, emergency contacts, and bank details
- Employee documents and profile sections
- Onboarding, transfers, promotions, termination, and archival
- Employee directory, search, bulk import, and bulk export

## Out Of Scope

- Attendance processing
- Leave processing
- Payroll calculation
- Performance workflows
- Recruitment operations

## Primary Actors

- Tenant Administrator
- HR Manager
- HR Executive
- Manager
- Employee

## Core Workflows

- Employee creation with validation, approval, account creation, notification, and audit entry
- Employee onboarding with checklist completion and readiness tracking
- Employee profile updates with configurable editable fields and optional approval
- Department transfer and promotion with effective-date history
- Resignation, termination, and archive without hard deletion

## Key Business Rules

- Employee code must be unique
- Employee email must be unique within a tenant
- Mandatory creation fields include name, email, joining date, department, designation, and employment type
- Employee records must not be physically deleted after termination
- Department changes must preserve historical records
- Multiple address types must be supported per employee
- Emergency contacts must support create and update flows with validation
- Onboarding progress must be derived consistently from checklist task state
- Employee documents must stay on private storage and honor approved upload types
- Employee lifecycle audit history must preserve before and after state where applicable
- Bulk intake validation must report row-level success and failure counts without silently creating invalid employees
- Bank information must be encrypted at rest and access-restricted
- Employee directory search must stay within `NFR-PERF-003` using indexed prefix-search attributes for standard lookup flows

## Core Entities

- `employees`
- `employee_contacts`
- `employee_addresses`
- `employee_emergency_contacts`
- `employee_bank_accounts`
- `employee_documents`
- `employment_history`
- `employee_onboarding_tasks`

## Primary APIs

- `GET /api/v1/employees`
- `POST /api/v1/employees`
- `POST /api/v1/employees/bulk-import/validate`
- `GET /api/v1/employees/{id}`
- `GET /api/v1/employees/{id}/audit-history`
- `PATCH /api/v1/employees/{id}`
- `GET /api/v1/employees/{id}/contacts`
- `POST /api/v1/employees/{id}/contacts`
- `PATCH /api/v1/employees/{id}/contacts/{contactId}`
- `GET /api/v1/employees/{id}/addresses`
- `POST /api/v1/employees/{id}/addresses`
- `PATCH /api/v1/employees/{id}/addresses/{addressId}`
- `GET /api/v1/employees/{id}/emergency-contacts`
- `POST /api/v1/employees/{id}/emergency-contacts`
- `PATCH /api/v1/employees/{id}/emergency-contacts/{emergencyContactId}`
- `GET /api/v1/employees/onboarding-status`
- `GET /api/v1/employees/{id}/onboarding-tasks`
- `POST /api/v1/employees/{id}/onboarding-tasks`
- `PATCH /api/v1/employees/{id}/onboarding-tasks/{taskId}`
- `GET /api/v1/employees/{id}/bank-accounts`
- `POST /api/v1/employees/{id}/bank-accounts`
- `PATCH /api/v1/employees/{id}/bank-accounts/{bankAccountId}`
- `GET /api/v1/employees/{id}/documents`
- `POST /api/v1/employees/{id}/documents`
- `GET /api/v1/employees/{id}/documents/{documentId}/download`
- `DELETE /api/v1/employees/{id}`
- `GET /api/v1/employees/{id}/attendance`
- `GET /api/v1/employees/{id}/leave-balance`
- `GET /api/v1/employees/{id}/payroll`

## Dependencies

- Organization structure and reporting hierarchy
- Tenant-aware authorization and field security
- Workflow engine for approvals
- Document storage and notifications
- Audit logging
- Published Sprint 02 contract: `apps/api/openapi/sprint-02-employee-organization-management.yaml`

## Related Sprints

- [Sprint 02: Employee and Organization Management](../sprints/sprint-02-employee-organization-management.md)
- [Sprint 06: Documents, Assets, ESS, and On/Offboarding](../sprints/sprint-06-documents-assets-ess-onoffboarding.md)

## Source Specs

- `docs/files/PhoenixHRMS Employee Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
