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
- Employee offboarding with checklist completion, ownership tracking, and approval hooks where required
- Employee profile updates with configurable editable fields and optional approval
- Department transfer and promotion with effective-date history
- Resignation, termination, and archive without hard deletion
- Employee task-center review and action on assigned policy, document, asset, onboarding, and offboarding requests

## Key Business Rules

- Employee code must be unique
- Employee email must be unique within a tenant
- Mandatory creation fields include name, email, joining date, department, designation, and employment type
- Employee records must not be physically deleted after termination
- Department changes must preserve historical records
- Multiple address types must be supported per employee
- Emergency contacts must support create and update flows with validation
- Onboarding progress must be derived consistently from checklist task state
- Lifecycle task templates must support onboarding and offboarding checklists without leaking across tenants
- Employee self-service actions must only expose items linked to the authenticated employee record
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
- `employee_lifecycle_task_templates`
- `policy_acknowledgements`

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
- `GET /api/v1/employees/lifecycle-task-status`
- `GET /api/v1/task-center`
- `PATCH /api/v1/task-center/lifecycle-tasks/{taskId}`
- `GET /api/v1/policy-acknowledgements`
- `POST /api/v1/policy-acknowledgements`
- `PATCH /api/v1/policy-acknowledgements/{id}/acknowledge`
- `GET /api/v1/policy-acknowledgements/{id}/download`
- `GET /api/v1/self-service/workspace`
- `GET /api/v1/self-service/employee-documents/{employeeDocumentId}/download`
- `GET /api/v1/employee-task-templates`
- `POST /api/v1/employee-task-templates`
- `PATCH /api/v1/employee-task-templates/{templateId}`
- `GET /api/v1/employees/{id}/lifecycle-tasks`
- `POST /api/v1/employees/{id}/lifecycle-tasks`
- `PATCH /api/v1/employees/{id}/lifecycle-tasks/{taskId}`
- `POST /api/v1/employees/{id}/lifecycle-tasks/apply-templates`
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

## Implementation Notes

- Sprint 06 now includes `S06-004`, `S06-005`, and `S06-006`, extending the employee module with lifecycle task templates, employee task-center aggregation, policy acknowledgements, and a linked-employee self-service workspace for profile review, approved document access, and assigned-asset visibility.
- The self-service API remains scoped to the authenticated employee record, reuses the existing employee profile shape for visible fields, keeps banking and other restricted panels hidden unless the session carries the required permission, and preserves auditable download plus acknowledgement flows.
- The current `apps/web` self-service module lives at `/self-service` and deliberately stays read only for Sprint 06, focusing on review, acknowledgement, and download flows rather than employee-initiated profile edits.
- Sprint 06 now also includes `S06-007`, adding `/operations/lifecycle` in `apps/web` so HR operators can switch between onboarding and offboarding progress views, inspect selected employee task detail, and move visible lifecycle items forward from one shared operations module.
- Sprint 06 now also includes `S06-008`, publishing the task-center, policy acknowledgement, and self-service workspace contract in `apps/api/openapi/sprint-06-documents-assets-ess-onoffboarding.yaml` so employee-facing consumers and admin workspaces can align on one reviewed API source.

## Related Sprints

- [Sprint 02: Employee and Organization Management](../sprints/sprint-02-employee-organization-management.md)
- [Sprint 06: Documents, Assets, ESS, and On/Offboarding](../sprints/sprint-06-documents-assets-ess-onoffboarding.md)

## Source Specs

- `docs/files/PhoenixHRMS Employee Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
