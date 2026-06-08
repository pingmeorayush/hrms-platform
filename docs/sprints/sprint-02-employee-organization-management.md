# Sprint 02: Employee and Organization Management

## Objective

Establish the employee system of record and the organization master data used by downstream modules.

## Status

Backend slice completed and verified. UI companion backlog is now defined, but the Sprint 02 web experience is not implemented in the current workspace.

Backend implementation now covers tenant-scoped organization masters, employee create/read, employee-code policy enforcement, benchmarked employee directory search, bulk import validation, lifecycle update/transfer/promotion/termination APIs, employee contact/address/emergency-contact management, employee document attachment/download baseline, onboarding checklist progress tracking, employee and structure audit-history access, encrypted bank-detail handling with audit coverage, and the published Sprint 02 OpenAPI contract inventory.

## Primary Backlog IDs

- `ORG-001`
- `EMP-001`
- `EMP-002`
- `EMP-003`
- `EMP-004`

## Module References

- [Organization Management](../modules/organization-management.md)
- [Employee Management](../modules/employee-management.md)

## Backlog Detail

- [Sprint 02 Delivery Backlog](../backlog/sprint-02-employee-organization-management.md)
- [Frontend Delivery Order for Sprints 02 to 04](../backlog/frontend-delivery-order-sprints-02-to-04.md)

## Scope

- Company, department, designation, location, and reporting structure masters
- Employee creation, update, transfer, promotion, termination, and archival
- Employee contact, address, emergency contact, and bank detail management
- Employee document attachment to the master record
- Employee onboarding checklist baseline

## Delivery Items

- Employee and organization APIs
- Employee lifecycle workflows
- Sensitive-field access restrictions
- Core employee search and directory support
- Initial onboarding checklist experience
- Published and linted Sprint 02 API contracts

## Dependencies

- Sprint 01 tenant, auth, RBAC, workflow, and audit foundation

## Acceptance Criteria

- HR users can create and manage employee records with the approved validation rules
- Organization structures can be configured per tenant
- Sensitive employee fields are permission-restricted and audited
- Terminated employees are preserved as historical records, not hard-deleted

## Test Focus

- Employee validation and uniqueness rules
- Lifecycle state transitions
- Role-based access to sensitive fields
- Audit history on employee changes
- Employee directory benchmark evidence against `NFR-PERF-003`
- OpenAPI contract linting in CI

## Verification Notes

- On 2026-06-01, `php artisan benchmark:employee-directory --employees=100000 --iterations=5 --keep-dataset` ran against an isolated SQLite benchmark database and returned a worst `P95` of `64.34 ms`, with `62.34 ms` for email prefix search, `64.34 ms` for full-name prefix search, and `18.09 ms` for indexed filtered listing.
- `npm run openapi:lint` now validates every contract file under `apps/api/openapi`, and the published Sprint 02 contract inventory is documented in `apps/api/openapi/README.md`.

## Risks and Open Questions

- Employee code policy must already be settled
- Poor master-data modeling here will cascade into attendance, leave, and payroll defects
- Benchmark evidence recorded on 2026-06-01 should be refreshed if the employee directory query shape changes materially
