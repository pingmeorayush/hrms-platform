# PhoenixHRMS Requirements Analysis

## Purpose

This document consolidates the detailed specifications in `docs/files` into a practical planning artifact with three goals:

1. Identify the main requirement gaps, conflicts, and open decisions.
2. Recommend an implementation roadmap based on module dependencies.
3. Normalize the specifications into a delivery-oriented backlog.

## Source Basis

- Primary input came from the detailed specification set in `docs/files`.
- The `.txt` files were used as the readable mirror of the Word specifications.
- The following modules exist only as PDF specifications in this package and were validated by section outline rather than full text extraction:
  - Employee Self-Service
  - Integrations Platform
  - Learning Management
  - Recruitment Management
  - Reporting & Analytics
- The numbered markdown files in `docs/files` are placeholder package files and should not be treated as authoritative requirements sources.

## Coverage Snapshot

The requirements set is strong on breadth and reasonably strong on structure.

Covered well:

- Product vision, objectives, personas, user stories, and business rules
- Core HR modules: employee, attendance, leave, payroll, performance, assets, documents
- Platform concerns: architecture, security, RBAC, audit, multi-tenancy, workflow, API standards
- Enterprise capabilities: mobile, globalization, DevOps, testing, production operations

Covered partially or at high level:

- Cross-document traceability from requirement to API, database, and tests
- Canonical MVP scope for v1
- Implementation sequencing across modules
- Clear decisions on unresolved architectural and operational tradeoffs

## Part 1: Gap Matrix

| ID | Area | Finding | Impact | Recommendation |
| --- | --- | --- | --- | --- |
| GAP-001 | Availability targets | NFR defines `99.9%` uptime, while DevOps and Ops target `99.99%`. | Teams cannot design SLAs, infra, or support processes consistently. | Approve one canonical service target and propagate it across NFR, DevOps, and Ops docs. |
| GAP-002 | Runtime architecture | System architecture presents a modular monolith, while DevOps describes a microservices-style Kubernetes deployment. | Delivery and platform teams may optimize for different operating models. | Lock v1 as either modular monolith deployed as a single app plus workers, or true service decomposition. |
| GAP-003 | Testing stack | Frontend architecture uses `Vitest`; testing strategy uses `Jest`. Backend testing strategy also implies `Jest`, despite Laravel/PHP conventions. | Tooling, CI templates, and test ownership will drift. | Standardize test tooling by layer and update QA, frontend, and backend docs together. |
| GAP-004 | Integration/event assumptions | Testing strategy assumes Kafka-style integrations, but backend and architecture docs only clearly commit to Laravel events and Redis-backed queues. | Platform work may be overdesigned too early or blocked by missing event infrastructure decisions. | Define the v1 eventing model explicitly: internal Laravel events, queue transport, and whether Kafka is post-v1. |
| GAP-005 | Employee code policy | PRD lists employee code as required input, but user stories describe automatic generation. | HR workflows, API validation, and imports will be inconsistent. | Decide whether employee codes are auto-generated, user-supplied, or configurable per tenant. |
| GAP-006 | API surface completeness | OpenAPI coverage does not yet reflect all first-class modules in the spec set. | API-first delivery is blocked for uncovered modules and admin/platform capabilities. | Create an API domain inventory and define contract ownership for every module in scope. |
| GAP-007 | Canonical source integrity | Many detailed docs reference numbered markdown documents that are placeholders in this package. | Readers may trust incomplete files and miss the real specifications. | Mark the detailed Word/PDF-derived docs as canonical and treat placeholder markdown files as summary only. |
| GAP-008 | Scope inflation | The corpus includes core HR, payroll, recruitment, learning, reporting, AI, mobile, global payroll, integrations, and enterprise ops in one pass. | A first release can become too broad to deliver reliably. | Define an explicit v1 scope, a v1.5 scope, and deferred enterprise extensions. |
| GAP-009 | Compliance geography | Globalization and payroll specs assume broad multi-country support, but no launch-country strategy is defined. | Payroll, leave, compliance, and data privacy work cannot be implemented correctly without a country baseline. | Choose launch geographies for v1 and treat other countries as expansion profiles. |
| GAP-010 | AI scope and governance | AI Copilot scope is ambitious, but guardrails, approval boundaries, evaluation criteria, and model governance are not yet concretely operationalized. | AI features may create security, compliance, and trust risks. | Restrict v1 AI to read-heavy, low-risk use cases with citations, approvals, and audit trails. |
| GAP-011 | Traceability | Stories, business rules, APIs, DB entities, and test cases are not cross-linked. | Change impact analysis and release readiness will be difficult. | Introduce a traceability matrix keyed by story, rule, endpoint, table, and test suite. |
| GAP-012 | Module dependency planning | Workflow, notifications, audit, RBAC, tenant config, and document storage are reused everywhere but not called out as prerequisite platform tracks. | Feature teams may start module delivery before the shared foundation exists. | Deliver shared platform foundations before or alongside high-dependency modules. |

## Decision Set Needed Before Build-Out

These decisions should be resolved before major implementation starts:

1. Canonical uptime/SLA target for v1
2. V1 runtime shape: modular monolith or service-oriented decomposition
3. Standard test stack by layer
4. Employee code generation and import policy
5. Launch countries and payroll/legal scope
6. Eventing and integration baseline
7. Canonical v1 module list
8. AI safety and approval policy

## Part 2: Recommended Roadmap

The specifications support a phased roadmap. The main sequencing rule is that shared platform capabilities should land before dependent business modules.

### Phase 0: Program Alignment

Goals:

- Resolve all critical decision items in the gap matrix
- Confirm v1 scope and launch geography
- Freeze the canonical architecture and testing stack
- Establish requirement traceability and ownership

Outputs:

- Approved decision log
- Canonical scope list
- Updated architecture and QA standards
- Traceability matrix template

### Phase 1: Shared Platform Foundation

Scope:

- Authentication
- RBAC and authorization
- Tenant resolution and tenant settings
- Organization structure masters
- Workflow engine MVP
- Notification framework
- Audit logging baseline
- Document storage foundation
- API standards and OpenAPI scaffolding

Why first:

- These capabilities are reused by nearly every downstream module.

Exit criteria:

- Users can authenticate securely
- Tenant isolation is enforced end to end
- Permission checks and audit logs exist for protected actions
- Approval workflows can be modeled for at least leave and employee actions

### Phase 2: Core HR Foundation

Scope:

- Employee management
- Organization management
- Onboarding and offboarding
- Document management
- Asset assignment basics
- ESS profile and document access basics

Why here:

- Employee master data is the system of record for attendance, leave, payroll, performance, and AI.

Exit criteria:

- Employee lifecycle can be created, updated, transferred, terminated, and audited
- Core employee documents are stored securely
- Basic self-service profile updates exist

### Phase 3: Workforce Time Operations

Scope:

- Attendance management
- Shift management
- Rosters
- Attendance corrections
- Leave management
- Manager self-service approvals
- Holiday calendars

Why here:

- Payroll depends directly on finalized attendance and leave data.

Exit criteria:

- Employees can check in and out
- Managers and HR can resolve exceptions
- Leave balances, accruals, requests, and approvals work end to end

### Phase 4: Payroll and Compensation

Scope:

- Payroll management
- Compensation and benefits
- Salary structures and components
- LOP, overtime, reimbursements, arrears, and final settlement
- Payslip generation

Why here:

- Payroll is one of the most compliance-sensitive flows and depends on Phases 1 to 3.

Exit criteria:

- Payroll can be run for the target employee population
- Audit trails and locking rules are enforced
- Payslips and payroll reports are generated successfully

### Phase 5: Talent and Growth Modules

Scope:

- Recruitment
- Performance
- Learning management
- Advanced ESS and MSS experiences

Why here:

- These modules are important but not foundational for the HR system of record.

Exit criteria:

- Hiring pipeline works from requisition through offer
- Performance reviews and goal cycles are operational
- Learning catalog and assignments are available

### Phase 6: Insights, AI, and Enterprise Expansion

Scope:

- Reporting and analytics
- Integrations platform
- AI Copilot
- Mobile applications
- Globalization and localization expansion
- Advanced compliance and enterprise operations hardening

Why last:

- These features depend on stable operational data and mature platform controls.

Exit criteria:

- Dashboards and reports are sourced from trustworthy data
- AI features are grounded, auditable, and permission-aware
- External integrations are governed and monitored

## Recommended V1 Scope

To reduce delivery risk, the recommended v1 scope is:

- Authentication and RBAC
- Multi-tenancy foundation
- Employee management
- Organization management
- Workflow and approvals MVP
- Audit and notifications baseline
- Attendance
- Leave
- Payroll
- ESS and MSS core flows
- Reporting for operational HR, attendance, leave, and payroll

Recommended defer or limit for v1:

- Advanced AI automation
- Broad multi-country payroll
- Full learning suite
- Integration marketplace
- Deep mobile parity with web
- Predictive analytics beyond operational dashboards

## Part 3: Normalized Backlog

The backlog below groups the requirements into delivery-oriented work items. Priority is normalized as `P0`, `P1`, `P2`, and `P3`.

| ID | Priority | Capability | Notes |
| --- | --- | --- | --- |
| DEC-001 | P0 | Finalize v1 scope and launch markets | Required before payroll, compliance, and localization design can stabilize. |
| DEC-002 | P0 | Confirm runtime model and deployment shape | Must reconcile modular monolith vs microservices assumptions. |
| DEC-003 | P0 | Standardize testing stack | Align frontend, backend, API, and E2E tools before CI is built. |
| DEC-004 | P0 | Approve employee code policy | Affects HR workflows, imports, and API contracts. |
| DEC-005 | P0 | Define AI approval and safety policy | Needed before any action-taking AI feature is implemented. |
| PLAT-001 | P0 | Authentication and session management | Login, logout, password reset, MFA, lockout, session timeout. |
| PLAT-002 | P0 | Tenant resolution and isolation | Global tenant scope, context loading, tenant-aware data access. |
| PLAT-003 | P0 | Role, permission, and policy engine | RBAC baseline with row and field-level enforcement where needed. |
| PLAT-004 | P0 | Audit logging framework | Immutable audit events for auth, employee, leave, payroll, and admin actions. |
| PLAT-005 | P0 | Workflow engine MVP | Sequential approvals, delegation, SLA, reminders, and escalation basics. |
| PLAT-006 | P0 | Notification framework | Email and in-app notifications for approvals, alerts, and lifecycle events. |
| PLAT-007 | P1 | Document storage and access controls | Secure uploads, downloads, retention, and signed URLs. |
| PLAT-008 | P1 | OpenAPI baseline and contract governance | Canonical API domains, schema rules, and validation in CI. |
| ORG-001 | P0 | Company, department, designation, location, and hierarchy masters | Foundational reference data for employee, attendance, leave, and payroll. |
| EMP-001 | P0 | Employee master record lifecycle | Create, update, transfer, promote, terminate, archive. |
| EMP-002 | P1 | Employee contacts, addresses, emergency contacts, and bank data | Includes encryption and restricted access for sensitive fields. |
| EMP-003 | P1 | Employee document management | Government IDs, contracts, educational records, and audit trail. |
| EMP-004 | P1 | Employee onboarding checklist | Documents, bank details, assets, training, and policy acceptance. |
| ESS-001 | P1 | Employee profile self-service | Profile view and controlled self-service updates. |
| MSS-001 | P1 | Manager approvals and team visibility | Team directory, leave approvals, and attendance exception handling. |
| ATT-001 | P1 | Attendance capture | Check-in, check-out, timestamps, device and location metadata. |
| ATT-002 | P1 | Shift and roster management | Shift definitions, assignments, rotation, and effective dating. |
| ATT-003 | P1 | Attendance calculation and policy engine | Late rules, half day, overtime, holiday and weekend logic. |
| ATT-004 | P1 | Attendance correction workflow | Preserve original values, approval history, and auditability. |
| LEAVE-001 | P1 | Leave policy and leave type engine | Eligibility, accrual, carry forward, encashment, and restrictions. |
| LEAVE-002 | P1 | Leave balance and request lifecycle | Balance validation, overlapping checks, workflow, and cancellations. |
| LEAVE-003 | P2 | Advanced leave logic | Hourly leave, sandwich rules, auto-approval, and delegate support. |
| PAY-001 | P1 | Payroll periods, runs, and locking | Draft to locked lifecycle with audit control. |
| PAY-002 | P1 | Salary structures and formula engine | Earnings, deductions, revisions, and versioning. |
| PAY-003 | P1 | Attendance and leave payroll inputs | Working days, LOP, overtime, unpaid leave, and encashment. |
| PAY-004 | P1 | Payslip generation and payroll reports | Downloadable payslips and standard finance-facing outputs. |
| PAY-005 | P2 | Advanced payroll adjustments | Arrears, retro payroll, reimbursements, loans, and final settlement. |
| PERF-001 | P2 | Goals and review cycle | Goal definitions, feedback, review workflow, and history. |
| REC-001 | P2 | Recruitment requisition to offer flow | Requisition approval, jobs, candidates, interviews, offers. |
| LMS-001 | P3 | Learning catalog and assignments | Course catalog, assignments, and learning progress. |
| ASSET-001 | P2 | Asset assignment and return | Core tracking, ownership, issue/return, and audit history. |
| DOC-001 | P2 | General document management | Cross-module document repository and access control. |
| RPT-001 | P1 | Operational reporting baseline | Employee, attendance, leave, payroll, and approval reports. |
| RPT-002 | P2 | Analytics dashboards | Executive and operational dashboards with KPI definitions. |
| INT-001 | P2 | Integrations platform MVP | Webhooks, outbound APIs, sync jobs, error handling, and monitoring. |
| MOB-001 | P2 | Mobile ESS MVP | Leave, attendance, notifications, payslips, and profile access. |
| GLO-001 | P2 | Localization baseline | Time zone, locale, language, and currency formatting. |
| GLO-002 | P3 | Multi-country compliance profiles | Country-specific payroll and legal rule sets beyond launch markets. |
| AI-001 | P2 | Read-only AI Copilot MVP | Policy Q&A, leave balance lookup, document retrieval, and report summarization. |
| AI-002 | P3 | Predictive and action-taking AI | Attrition risk, recommendations, automation, and workflow actions after governance maturity. |
| OPS-001 | P1 | CI/CD and quality gates | Lint, test, security scan, contract validation, and deployment gates. |
| OPS-002 | P1 | Observability baseline | Metrics, logs, tracing, alert routing, and dashboard setup. |
| OPS-003 | P2 | DR, backup, and runbook hardening | Backups, restore testing, incident drills, and continuity procedures. |

## Suggested First Three Delivery Tracks

If work starts immediately, the first three parallel tracks should be:

1. Platform foundation
   - `PLAT-001` to `PLAT-006`
   - `DEC-001` to `DEC-005`

2. Core HR system of record
   - `ORG-001`
   - `EMP-001` to `EMP-004`
   - `ESS-001`

3. Workforce operations
   - `ATT-001` to `ATT-004`
   - `LEAVE-001` to `LEAVE-003`
   - `MSS-001`

Payroll should start only after the workforce operations track is stable enough to provide trusted inputs.

## Immediate Next Steps

1. Resolve the `P0` decision backlog.
2. Mark canonical source documents in `docs/files`.
3. Create a traceability matrix linking stories, rules, APIs, and tests.
4. Expand the OpenAPI inventory to every in-scope module.
5. Convert this roadmap into sprint-ready tickets.
