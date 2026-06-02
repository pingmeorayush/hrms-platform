# PhoenixHRMS Roadmap

This roadmap turns the detailed specification set in `docs/files` into an executable sprint sequence.

## Planning Principles

- Resolve architectural and scope conflicts before feature-heavy implementation.
- Deliver shared platform capabilities before dependent HR modules.
- Build the employee system of record before attendance, leave, and payroll.
- Treat AI, globalization expansion, and deep integrations as later-stage capabilities.

## Sprint Sequence

0. [Sprint 00: Program Alignment](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-00-program-alignment.md)
1. [Sprint 01: Auth, RBAC, and Tenant Foundation](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-01-auth-rbac-tenant-foundation.md)
2. [Sprint 02: Employee and Organization Management](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-02-employee-organization-management.md)
3. [Sprint 03: Attendance and Shift Operations](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-03-attendance-shift-operations.md)
4. [Sprint 04: Leave Management and Manager Workflows](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-04-leave-manager-workflows.md)
5. [Sprint 05: Payroll and Compensation](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-05-payroll-compensation.md)
6. [Sprint 06: Documents, Assets, ESS, and On/Offboarding](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-06-documents-assets-ess-onoffboarding.md)
7. [Sprint 07: Recruitment, Performance, and Learning](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-07-recruitment-performance-learning.md)
8. [Sprint 08: Reporting and Analytics](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-08-reporting-analytics.md)
9. [Sprint 09: Mobile, Integrations, and Globalization](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-09-mobile-integrations-globalization.md)
10. [Sprint 10: AI, Operations Hardening, and Release Readiness](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/sprint-10-ai-ops-release-readiness.md)

## Notes

- Multi-tenancy has been moved forward into Sprint 01 because later modules depend on tenant isolation, tenant configuration, and tenant-aware authorization.
- AI work has been moved toward the end of the roadmap because the detailed specs describe a wide scope that depends on stable operational data, mature permissions, and strong auditability.
- The sprint plans are expanded in [docs/sprints/README.md](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/sprints/README.md).
