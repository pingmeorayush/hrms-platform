# PhoenixHRMS Dashboard V2 Blueprint

This document defines the next-level dashboard strategy for `apps/web`.

It translates the current admin UI patterns into a concrete `Operations Center`
system for top-level modules.

The goal is not to turn every route into a dashboard.

The goal is to give each major module:

- one premium landing page
- one consistent operational layout
- one clear summary of what needs attention now

Then keep submenu and detail pages lean, table-first, and task-focused.

## Core Product Decision

Every top-level module should have one dedicated `Command Center` or `Operations Center` page.

That page should answer:

- what is happening now
- what needs attention
- what should I do next

Submenu pages should answer:

- how do I complete this specific task

This distinction matters.

`Overview / Operations Center` pages are allowed to be richer.

`Submenu / task pages` should stay denser and quieter.

## Shared Layout Rules

The following pattern should be reused across all module landing pages.

### 1. Shell + global frame

- Keep the premium dark shell.
- Keep the light work canvas.
- Keep the page header concise and operational.
- Support global search and command-center entry from the top frame.

### 2. Module page header

Each module landing page should open with:

- eyebrow: module state + active section
- page title
- one short operational description
- right-side high-value actions only

Good examples:

- `New assignment`
- `Create request`
- `Run audit`
- `Import roster`

Avoid filler buttons and repeated metadata.

### 3. KPI card row

Every module command center should have a first row of `5-6` compact KPI cards.

Each card should contain:

- label
- primary number
- optional change / delta
- small semantic icon tile

Rules:

- only show metrics that drive decisions
- do not use more than 6 cards in one row
- use color sparingly
- use red / amber only for risk or attention

### 4. Needs Attention strip

Every command center should have a horizontal `Needs Attention` or `Action Center` strip.

This should highlight:

- expiring items
- conflicts
- unassigned / missing states
- items that need review
- sync / policy / compliance issues

Rules:

- keep items short
- each item should be actionable
- this strip should feel like triage, not decoration

### 5. Main collection surface

The main table must remain the dominant surface.

The command center should include:

- tabs or segmented views
- integrated filters
- search
- bulk actions
- dense semantic table
- action column

Rules:

- the table is still the center of gravity
- filters belong above the table, never in detached cards below
- row actions open modals or navigate deeper

### 6. Activity rail

The right rail should be used selectively on module landing pages.

It is appropriate for:

- recent activity
- audit stream
- latest changes
- recent approvals
- recent roster or assignment changes

Rules:

- narrow and secondary
- should not overpower the main table
- should not be used on dense submenu CRUD pages

### 7. Insights zone

The lower section can contain `2-4` compact analytics cards.

Examples:

- donut / distribution
- trend line
- breakdown card
- upcoming expirations
- compliance overview

Rules:

- only on top-level command centers
- use charts only where they clarify decisions
- avoid adding charts to pages that are primarily registry maintenance

### 8. Modal-first interactions

The command center should still obey the mutation system:

- create -> modal
- edit -> modal
- delete / risky action -> confirmation dialog
- feedback -> toast

Do not reintroduce permanent split editors for routine CRUD work.

## When To Use This Pattern

Use the full `Operations Center` layout for:

- top-level module landing pages
- high-level operational overview routes
- leadership or admin monitoring surfaces

Do not use the full pattern for:

- record detail pages
- deep submenu CRUD pages
- simple admin registries
- modal-only setup flows

Those pages should continue using the standard admin UI patterns in:

- [ADMIN_UI_PATTERNS.md](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/ADMIN_UI_PATTERNS.md)

## Module Command Centers

## Foundation Command Center

Route intent:

- global admin visibility
- workspace access
- environment and session awareness

Recommended sections:

- KPI cards:
  - visible workspaces
  - restricted workspaces
  - active role grants
  - visible routes
  - exposed actions
  - contract anomalies
- Needs attention:
  - restricted critical modules
  - contract mismatch
  - stale token / stale environment mode
  - hidden actions in key admin areas
- Main collection:
  - workspace catalog
  - tabs:
    - `Visible now`
    - `All workspaces`
    - `Restricted here`
- Activity rail:
  - recent session changes
  - persona switches
  - access policy updates
- Insights:
  - module exposure distribution
  - permission-domain coverage

## Organization Operations Center

Route intent:

- master-data health
- organizational structure quality
- recent changes

Recommended sections:

- KPI cards:
  - active departments
  - active designations
  - locations
  - cost centers
  - inactive records
  - unmapped records
- Needs attention:
  - inactive locations still assigned
  - cost centers without owner
  - designations with no employees
  - duplicate or stale structure records
- Main collection:
  - tabs:
    - `Structure`
    - `Locations`
    - `Cost centers`
    - `Company profile`
- Activity rail:
  - recent master-data updates
  - company profile changes
  - structure edits
- Insights:
  - department distribution
  - location usage
  - cost center coverage

## Employees Operations Center

Route intent:

- workforce visibility
- onboarding and lifecycle risk
- document health

Recommended sections:

- KPI cards:
  - total employees
  - active employees
  - on probation
  - incomplete onboarding
  - expiring documents
  - exits / notice period
- Needs attention:
  - probation ending soon
  - documents expiring
  - missing manager assignments
  - incomplete onboarding
  - termination actions pending
- Main collection:
  - tabs:
    - `Directory`
    - `Lifecycle watch`
    - `Onboarding`
    - `Documents`
    - `Audit`
- Activity rail:
  - recent hires
  - manager changes
  - document uploads
  - lifecycle events
- Insights:
  - headcount by department
  - onboarding completion
  - document expiry distribution
  - lifecycle trend

## Attendance Operations Center

Route intent:

- assignment, shift, and roster control
- operational conflicts
- workforce coverage

Recommended sections:

- KPI cards:
  - active policies
  - active shifts
  - assigned employees
  - unassigned employees
  - overlap conflicts
  - expiring rosters
- Needs attention:
  - expiring assignments
  - overlapping shifts
  - employees without assignment
  - holiday calendar sync issues
  - policy mismatch
- Main collection:
  - tabs:
    - `Assignments`
    - `Shifts`
    - `Rosters`
    - `Policies`
    - `Holiday calendars`
- Activity rail:
  - recent assignment changes
  - shift updates
  - roster publishes
  - policy changes
- Insights:
  - assignment coverage
  - utilization trend
  - department distribution
  - upcoming expirations

## Leave Operations Center

Route intent:

- leave demand, approval load, and policy health

Recommended sections:

- KPI cards:
  - pending approvals
  - approved this week
  - cancelled requests
  - employees on leave today
  - policy exceptions
  - upcoming team load
- Needs attention:
  - approval backlog
  - overlapping critical absences
  - policy violations
  - missing calendar sync
  - low balance exceptions
- Main collection:
  - tabs:
    - `Requests`
    - `Approvals`
    - `Policy admin`
- Activity rail:
  - recent approvals
  - recent requests
  - policy changes
  - leave calendar updates
- Insights:
  - leave type distribution
  - approval turnaround
  - balance risk
  - seasonal demand trend

## Access Operations Center

Route intent:

- governance clarity
- permission visibility
- contract health

Recommended sections:

- KPI cards:
  - visible routes
  - hidden routes
  - visible actions
  - suppressed actions
  - restricted domains
  - contract warnings
- Needs attention:
  - hidden critical routes
  - contract mismatch
  - dangerous grant combinations
  - policy drift
- Main collection:
  - tabs:
    - `Routes`
    - `Actions`
    - `Diagnostics`
- Activity rail:
  - permission changes
  - route exposure changes
  - recent governance events
- Insights:
  - exposure by domain
  - role coverage
  - hidden vs visible balance

## Information Architecture Rules

To keep this system clean:

- each module gets one command center
- submenus remain task-specific
- record pages remain record-specific
- charts and activity rails stay on module landing pages only

Recommended route pattern:

- `/foundation`
- `/admin/organization`
- `/employees`
- `/attendance`
- `/leave`
- `/access`

Then keep routed sections underneath for the actual work.

## Shared Component Set

To implement this well, the design system should support:

- `CommandCenterHeader`
- `KpiCard`
- `AttentionStrip`
- `ActivityRail`
- `InsightCard`
- `SmartTabs`
- `CollectionToolbarV2`
- `BulkActionBar`
- `RowActionMenu`

These should be shared primitives, not page-specific inventions.

## Rollout Priority

The rollout should happen in this order:

### Wave 1: highest impact

1. `Attendance Operations Center`
2. `Employees Operations Center`
3. `Leave Operations Center`

Why:

- these modules have the richest operational workflows
- they benefit most from KPIs, triage, and recent activity
- they are easiest for users to perceive as a major quality jump

### Wave 2: governance and admin

4. `Access Operations Center`
5. `Organization Operations Center`

Why:

- both benefit from command-center treatment
- both need more careful restraint so they do not become fake dashboards

### Wave 3: platform shell

6. `Foundation Command Center`

Why:

- valuable, but less emotionally impactful than workforce operations
- should be refined after the operational modules establish the pattern

## Implementation Sequence

For each wave, follow this order:

1. Define the shared command-center primitives.
2. Build the page layout skeleton.
3. Populate KPI and attention models with demo/live-safe data.
4. Reuse the existing table and modal systems.
5. Add activity and insights only after the main table is strong.

Do not start with charts.
Do not start with AI.
Do not start with decorative widgets.

Start with:

- hierarchy
- operational visibility
- actionability

## Non-Goals For V2

These are valid ideas, but not first-wave priorities:

- AI assistant workflows
- user personalization and favorites
- density mode switcher
- universal timeline views
- advanced slide-over panel system replacing every modal

They can be evaluated after the core command-center system is stable.

## Success Criteria

Dashboard V2 is successful if:

- each module landing page instantly communicates operational status
- users can see what needs action without opening multiple pages
- the table remains the core working surface
- overview pages feel premium without becoming cluttered
- submenu pages remain lean and consistent

## Final Principle

PhoenixHRMS should not become a generic chart-heavy dashboard.

It should become a high-trust enterprise operations console:

- beautiful
- actionable
- consistent
- calm
- intelligent

That is the bar for Dashboard V2.
