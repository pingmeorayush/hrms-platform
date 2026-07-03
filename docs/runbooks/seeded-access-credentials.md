# Seeded Access Credentials

This runbook lists the deterministic role-based login accounts that are created by the local database seeders.

Use these credentials only for local development, QA, and demo environments. Do not reuse them in shared or production systems.

## Refresh the seeded accounts

From the API app directory:

```bash
php artisan migrate --seed --force
```

If the schema is already current and you only want to refresh the seeded accounts:

```bash
php artisan db:seed --force
```

## Default password

All seeded accounts use the same password:

```text
Password@12345
```

## Role matrix

| Role | Email | Password | Linked employee profile | Notes |
| --- | --- | --- | --- | --- |
| `platform.super_admin` | `admin@phoenixhrms.test` | `Password@12345` | Yes | Full platform governance, tenant oversight, and access administration. |
| `platform.support` | `platform.support@phoenixhrms.test` | `Password@12345` | Yes | Support-focused platform visibility without super-admin reach. |
| `platform.auditor` | `platform.auditor@phoenixhrms.test` | `Password@12345` | Yes | Audit and read-heavy governance coverage. |
| `tenant.admin` | `tenant.admin@phoenixhrms.test` | `Password@12345` | Yes | Broad tenant operations, setup, payroll, reporting, and access controls. |
| `hr.admin` | `hr.admin@phoenixhrms.test` | `Password@12345` | Yes | HR operations, employee lifecycle, attendance, leave, and compensation coverage. |
| `manager` | `manager@phoenixhrms.test` | `Password@12345` | Yes | Manager approvals, reviews, and team-level workflows. |
| `it.admin` | `it.admin@phoenixhrms.test` | `Password@12345` | Yes | IT, resilience, observability, release, integrations, and assets coverage. |
| `learning.admin` | `learning.admin@phoenixhrms.test` | `Password@12345` | Yes | Learning catalog, assignment, and L&D operations coverage. |
| `recruiter` | `recruiter@phoenixhrms.test` | `Password@12345` | Yes | Recruitment operations and candidate workflow coverage. |
| `interviewer` | `interviewer@phoenixhrms.test` | `Password@12345` | Yes | Interview-only routed access for hiring loops. |
| `employee` | `employee@phoenixhrms.test` | `Password@12345` | Yes | Employee self-service, attendance, leave, learning, and payslip coverage. |

## Seed relationships

- `manager@phoenixhrms.test` is the seeded reporting manager for:
  - `employee@phoenixhrms.test`
  - `interviewer@phoenixhrms.test`
- All seeded users belong to the `phoenix-demo` company.
- The seed also creates a baseline organization scaffold:
  - Bengaluru HQ location
  - Corporate Operations cost center
  - Leadership, People Operations, Engineering, Technology Operations, Talent Acquisition, and Learning and Development departments

## Recommended smoke accounts

- Use `admin@phoenixhrms.test` when you want to validate the broadest enterprise surface quickly.
- Use `tenant.admin@phoenixhrms.test` for day-to-day tenant administration flows.
- Use `manager@phoenixhrms.test` and `employee@phoenixhrms.test` when validating manager and self-service experiences.
