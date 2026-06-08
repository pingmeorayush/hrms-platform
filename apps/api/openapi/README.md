# PhoenixHRMS API Contract Inventory

This directory holds the version-controlled OpenAPI contract files published by sprint and validated in CI.

## Published Contracts

- `sprint-01-platform-foundation.yaml`
  - Auth, RBAC, tenant context, workflow, notifications, and audit endpoints
- `sprint-02-employee-organization-management.yaml`
  - Organization masters, employee directory, lifecycle, profile details, documents, onboarding, audit history, and bulk import validation
- `sprint-03-attendance-shift-operations.yaml`
  - Attendance capture, calculation, operational review, correction, policy configuration, holiday calendars, shifts, assignments, and roster APIs
- `sprint-04-leave-manager-workflows.yaml`
  - Leave types, leave policies, accrual preview, balance ledger, leave requests, and workflow-backed approval APIs

## Contract Conventions

- OpenAPI version: `3.1.0`
- Shared success and error envelope schemas are reused through `components`
- Contract files are committed in the repository and reviewed as code changes
- `npm run openapi:lint` validates every `openapi/*.yaml` file in this directory

## Review Workflow

1. Update the relevant sprint contract file when an endpoint or schema changes.
2. Run `npm run openapi:lint` from `apps/api`.
3. Include contract diffs alongside backend changes so frontend and integration work can review the same source of truth.
