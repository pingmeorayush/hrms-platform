# Frontend Delivery Order: Sprint 08

## Purpose

Turn Sprint 08 reporting and analytics scope into a practical frontend rollout so dashboards, explorer flows, exports, and saved views land as one coherent reporting product rather than scattered chart pages.

## Current Workspace Baseline

- A routed reporting module now exists in `apps/web` with dashboard routes plus the governed explorer, export queue, and subscription center.
- Several adjacent patterns already exist and should be reused:
  - module command-center pages across recruitment, performance, learning, payroll, attendance, employees, and operations
  - dense table and detail patterns from the current admin UI system
  - targeted review-style reporting surfaces such as payroll review
- [Dashboard V2 Blueprint](../../apps/web/DASHBOARD_V2_BLUEPRINT.md) already defines the right visual and interaction philosophy for top-level module reporting pages: premium landing pages, compact KPI rows, attention strips, table-first work areas, and restrained charts.

## Planning Principles

- Build reporting as a governed consumption surface, not as generic chart wallpaper.
- Keep top-level reporting landing pages richer, but keep deeper explorer pages table-first and task-focused.
- Every KPI card should have a meaningful drilldown or explanation path.
- Freshness posture, staleness, and masked-data states should be visible in the UI instead of hidden in implementation details.
- Reuse one shared pattern set for:
  - KPI cards
  - filter bars
  - drilldown tables
  - export status chips
  - saved-view selectors
  - stale-data and permission-denied states
- Treat HR, payroll, recruiter, manager, executive, and analyst sessions as distinct reporting personas from the start.

## Recommended Delivery Waves

| Wave | Scope | Stories | Why This Comes Here |
| --- | --- | --- | --- |
| 0 | Reporting route and state foundation | Cross-story prerequisite | Introduce routed reporting shells, dataset picker patterns, filter persistence, freshness badges, export-status components, and shared drilldown conventions before module-specific dashboards appear. |
| 1 | Reporting command-center baseline | `S08-005` after `S08-004` | Start with a high-value `Reporting Command Center` that shows KPI posture, stale-data alerts, and top attention areas before expanding into report exploration. |
| 2 | HR and leadership dashboard views | `S08-005` | These are the broadest, highest-leverage summary surfaces and will validate KPI and freshness posture early. |
| 3 | Manager, payroll, and recruiter dashboard views | `S08-005` | Persona-specific dashboards should follow once the shared card, drilldown, and role-visibility rules are stable. |
| 4 | Report explorer baseline | `S08-006` after `S08-002` | Users need a consistent report table, filter, and drilldown experience before export or subscription UX becomes credible. |
| 5 | Export, saved-view, and subscription UX | `S08-006` after `S08-003` and `S08-009` | Export queues, saved views, and subscriptions become usable only when backend delivery, ownership, and status models are already stable. |
| 6 | Contract-driven hardening and masked-state polish | `S08-008` | Final UI hardening should happen only after reporting, export, and dashboard contracts are published and reviewed. |

## Current Delivery Status

- `S08-005` is implemented in `apps/web`, which means Wave 0 route/state foundation plus Waves 1 to 3 of the dashboard rollout are live.
- `S08-006` is now implemented in `apps/web`, bringing the explorer, saved-view consumption, export queue, and subscription-delivery surfaces online with demo/live workspace wiring.
- `S08-008` is now implemented through published Sprint 08 reporting contracts plus final reporting command-center copy hardening so the workspace messaging matches the already-live export and subscription flows.

## Wave Details

### Wave 0: Reporting Route and State Foundation

Suggested implementation scope:

- Add routed module shells for `/reporting`
- Add reporting-specific navigation groups and persona-aware route guards
- Introduce shared UI patterns for:
  - dataset selector and saved-view switcher
  - KPI card rows with freshness chips
  - attention strip for stale or blocked metrics
  - dense report tables with drilldown actions
  - export lifecycle badges and download states
  - masked-field and restricted-drilldown empty states

### Wave 1: Reporting Command Center Baseline

Ship first:

- `/reporting/overview`
- cross-domain KPI summary row
- action-center strip for stale datasets, failed exports, and high-risk signals
- recent export and subscription activity rail

Why first:

- Reporting needs one clear operational landing page so the module feels intentional instead of becoming a pile of unrelated charts.

### Wave 2: HR and Leadership Dashboards

Ship second:

- workforce and attrition posture
- attendance and leave trend summaries
- payroll release and variance posture
- recruiting funnel and performance-cycle executive summaries

Why second:

- These personas consume the widest cross-domain summaries and are the best first proof that KPI governance is working.

### Wave 3: Manager, Payroll, and Recruiter Dashboards

Ship third:

- manager team health and due-state views
- payroll exception and release-readiness dashboard
- recruiter funnel, aging, and offer-conversion dashboard

Why third:

- Role-specific dashboards should build on the already-stable KPI, card, and drilldown patterns from the broader command-center rollout.

### Wave 4: Report Explorer Baseline

Ship fourth:

- report explorer route
- dataset discovery and filter controls
- drilldown table with pagination
- masked-field rendering and no-results state

Why fourth:

- Explorer pages are where reporting becomes operational, but they should inherit shared dataset and freshness behavior rather than invent their own state model.

### Wave 5: Export, Saved Views, and Subscriptions

Ship fifth:

- export request flow
- export queue history
- saved-view create, update, and default selection
- subscription setup and delivery-status visibility

Why fifth:

- These are high-trust enterprise features and should arrive only after the core report and permission models are stable.

### Wave 6: Contract-Driven Hardening

Ship sixth:

- align request and response shapes with published Sprint 08 contracts
- tighten stale-data messaging and masked states
- verify role-aware drilldowns and saved-view access behavior

Why sixth:

- Reporting is especially sensitive to small consistency failures, so a contract-driven hardening pass is a must before implementation is considered done.

## Suggested Route Groups

- `/reporting/overview`
- `/reporting/workforce`
- `/reporting/payroll`
- `/reporting/recruitment`
- `/reporting/performance`
- `/reporting/learning`
- `/reporting/explorer`
- `/reporting/exports`
- `/reporting/subscriptions`

## Suggested Module Layout

- `apps/web/src/modules/reporting`

Suggested subfolders:

- `api`
- `components`
- `pages`
- `hooks`
- `types`
- `data`

## Recommended First Build

If implementation starts immediately, the first Sprint 08 frontend sequence should be:

1. Wave 0 reporting route and state foundation
   Status: Implemented through the routed `/reporting` module shell and persona-aware section navigation.
2. Wave 1 reporting command center baseline
   Status: Implemented through `/reporting/overview`.
3. Wave 4 report explorer baseline
   Status: Implemented through `/reporting/explorer` with governed dataset filters, no-results handling, saved-view consumption, and controlled export requests.
4. Wave 5 export, saved-view, and subscription UX
   Status: Implemented through `/reporting/exports` and `/reporting/subscriptions` with queued, completed, expired, and blocked-state visibility plus recurring delivery controls.

That gives us the strongest operational shape early while keeping dashboard growth disciplined and avoiding premature chart sprawl.
