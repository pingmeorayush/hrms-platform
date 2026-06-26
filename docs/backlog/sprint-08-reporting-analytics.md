# Sprint 08 Backlog: Reporting and Analytics

## Scope Reference

- [Sprint 08 Plan](../sprints/sprint-08-reporting-analytics.md)
- [Frontend Delivery Order for Sprint 08](./frontend-delivery-order-sprint-08.md)
- [Reporting and Analytics Module](../modules/reporting-analytics.md)
- [Platform Foundation Module](../modules/platform-foundation.md)

## Planning Refresh Outcome

- Sprint 08 currently has no backend or frontend reporting scaffold in the workspace, so this backlog now reflects a real backend-first implementation order instead of an abstract BI wish list.
- The original reporting scope was too broad because it mixed trustworthy operational reporting with later-stage predictive, AI, warehouse, and custom-builder ambitions.
- Saved views and subscriptions were implied in the UI story but were not explicitly backed by a governed persistence story, so the sprint now treats them as a first-class backend capability.
- Metric trust is the highest-risk dimension of Sprint 08, so KPI lineage, dataset certification, masking, freshness posture, and drilldown parity are now explicit requirements instead of assumptions.
- Frontend delivery should follow a dedicated command-center and explorer rollout order so reporting lands as a coherent product, not just a chart collection.

## Epics

### EPIC S08-E1: Metric Governance and Dataset Foundation

Delivers KPI definitions, dataset registry, certification posture, and the semantic reporting layer that later dashboards and exports rely on.

### EPIC S08-E2: Reporting Query, Export, and Distribution Runtime

Delivers governed report retrieval, export jobs, scheduled distribution, and saved-view or subscription persistence.

### EPIC S08-E3: Dashboard and Explorer Experience

Delivers reporting command centers, role-specific dashboards, report explorer, and export-consumption flows in the web app.

### EPIC S08-E4: Security, Freshness, and Contract Publication

Delivers masking, row-level scope, audit posture, freshness visibility, and version-controlled API contracts.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S08-001 | Story | P0 | Implement KPI catalog, dataset registry, lineage, and certification baseline | Sprint 07 complete |
| S08-002 | Story | P0 | Implement standard reporting query APIs with filtering, drilldown semantics, and pagination | S08-001 |
| S08-003 | Story | P1 | Implement export generation, async execution, and scheduled distribution baseline | S08-002, S08-007 |
| S08-004 | Story | P0 | Implement dashboard aggregation, snapshotting, caching, and freshness controls | S08-001, S08-002 |
| S08-005 | Story | P1 | Implement HR, manager, payroll, recruiter, and leadership dashboard screens | S08-004, S08-007 |
| S08-006 | Story | P1 | Implement report explorer, filter, saved-view, and export-consumption UI | S08-002, S08-003, S08-007, S08-009 |
| S08-007 | Story | P0 | Implement reporting access control, audit visibility, and sensitive-data masking | S08-002, S01-012 |
| S08-008 | Story | P1 | Publish reporting and analytics OpenAPI contracts | S08-002, S08-003, S08-004, S08-009, S01-013 |
| S08-009 | Story | P1 | Implement saved views, subscriptions, and delivery-target governance | S08-002, S08-003 |

## Ticket Details

### S08-001: Implement KPI catalog, dataset registry, lineage, and certification baseline

Type: Story  
Priority: P0

Description:

Create the reporting semantic layer that defines what each KPI and standard dataset means before dashboards and explorer flows consume those results.

Dependencies:

- Sprint 07 complete

Acceptance Criteria:

- KPI definitions have stable keys, human-readable descriptions, formulas, owners, source references, grain, and certification posture
- Standard report datasets define approved fields, filters, drilldown paths, and masking posture for the Sprint 08 v1 scope
- Dataset and KPI changes are version-aware, reviewable, and suitable for later contract and analytics governance

### S08-002: Implement standard reporting query APIs with filtering, drilldown semantics, and pagination

Type: Story  
Priority: P0

Description:

Implement the governed report retrieval APIs used by dashboards, explorer views, and export jobs.

Dependencies:

- S08-001

Acceptance Criteria:

- Standard reports can be requested with approved filters, server-driven pagination, sorting, and drilldown-safe result shapes
- Report results remain tenant-scoped, permission-controlled, and consistent with the approved dataset registry
- Query behavior is strong enough to support dashboards, explorer views, and later exports without reimplementing report logic per consumer

### S08-003: Implement export generation, async execution, and scheduled distribution baseline

Type: Story  
Priority: P1

Description:

Provide exportable report outputs and the first governed distribution path for recurring reporting use cases.

Dependencies:

- S08-002
- S08-007

Acceptance Criteria:

- Supported report types can be exported in approved formats through controlled synchronous or asynchronous flows
- Large report generation can fall back to queued processing with visible lifecycle status, retention posture, and download controls
- Scheduled distribution remains permission-aware, auditable, and limited to approved delivery targets and formats

### S08-004: Implement dashboard aggregation, snapshotting, caching, and freshness controls

Type: Story  
Priority: P0

Description:

Build the aggregation and snapshot layer that powers summary dashboards without drifting from the governed reporting datasets.

Dependencies:

- S08-001
- S08-002

Acceptance Criteria:

- Dashboard metrics map directly to defined KPI and dataset sources instead of bespoke hidden calculations
- Aggregated results remain repeatable for the same snapshot inputs and display freshness posture explicitly
- Caching or pre-aggregation does not exceed approved freshness tolerances and does not bypass access control or masking rules

### S08-005: Implement HR, manager, payroll, recruiter, and leadership dashboard screens

Type: Story  
Priority: P1

Description:

Create role-specific dashboard experiences for high-value operational and executive metrics.

Dependencies:

- S08-004
- S08-007

Acceptance Criteria:

- Dashboard widgets reflect the approved KPI set for each persona and drill into governed report detail views
- Users only see cards, measures, deltas, and drilldowns permitted by role and data scope
- Loading, stale-data, masked-data, empty, and permission-denied states are covered in frontend tests

### S08-006: Implement report explorer, filter, saved-view, and export-consumption UI

Type: Story  
Priority: P1

Description:

Create the web experience for browsing reports, applying filters, saving common views, and consuming export outcomes.

Dependencies:

- S08-002
- S08-003
- S08-007
- S08-009

Acceptance Criteria:

- Users can discover approved reports, apply governed filters, inspect drilldowns, and request allowed exports through the UI
- Saved views preserve approved filter state and presentation preferences without bypassing current permission or masking rules
- UI states cover no-results, stale-data, queued-export, completed-export, expired-export, and blocked-export scenarios

### S08-007: Implement reporting access control, audit visibility, and sensitive-data masking

Type: Story  
Priority: P0

Description:

Harden reporting access so sensitive measures and drilldowns remain masked or unavailable outside approved scopes.

Dependencies:

- S08-002
- S01-012

Acceptance Criteria:

- Sensitive fields are masked, excluded, aggregated, or denied based on the caller’s reporting role and domain permissions
- Report access, export actions, dashboard drilldowns, and subscription delivery events are auditable
- Dashboard and explorer consumers follow the same row-level scope and masking rules as the direct reporting APIs

### S08-008: Publish reporting and analytics OpenAPI contracts

Type: Story  
Priority: P1

Description:

Publish the contract set for reporting datasets, query endpoints, dashboards, exports, saved views, and subscriptions.

Dependencies:

- S08-002
- S08-003
- S08-004
- S08-009
- S01-013

Acceptance Criteria:

- Core reporting and analytics endpoints are documented in focused contract files rather than one oversized mixed reporting specification
- Shared schema conventions remain aligned with prior sprint contracts while allowing reporting-specific freshness, masking, export, and subscription state models
- Contract files are version-controlled, linted, and usable by frontend, QA, and later analytics or AI consumers

### S08-009: Implement saved views, subscriptions, and delivery-target governance

Type: Story  
Priority: P1

Description:

Provide the persistence and governance layer behind saved report views and recurring report delivery.

Dependencies:

- S08-002
- S08-003

Acceptance Criteria:

- Users can persist saved views against approved datasets with governed filter state, ownership, and share posture
- Report subscriptions reference approved datasets or saved views, re-evaluate permission scope at delivery time, and can target only approved channels
- Saved-view and subscription records are auditable, revocable, and safe against later role, scope, or hierarchy changes
