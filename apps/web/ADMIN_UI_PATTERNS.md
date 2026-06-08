# Admin UI Patterns

This document defines the default design pattern for the admin panel in `apps/web`.

The goal is consistency first:

- every page should feel like part of the same enterprise product
- every module should follow the same navigation and layout logic
- pages should remove decorative or repeated chrome unless it helps a real decision

If a new screen conflicts with this document, prefer updating the screen to match this system instead of inventing a one-off layout.

## Core Principle

One page should do one job.

Good examples:

- `Employees / Directory` for roster browsing
- `Employees / Lifecycle watch` for lifecycle exceptions
- `Organization / Locations` for location registry management
- `Attendance / Operational review` for attendance decision work

Avoid mixed pages that combine unrelated surfaces like:

- summary dashboard
- table
- editor pane
- diagnostics panel
- tutorial copy

on the same route unless each block is clearly required for the same task.

## Product Shell

The shell should stay stable across all modules.

- Left sidebar is for module navigation only.
- Contextual submenus are allowed only for module-level routed sections.
- Record-level navigation must stay inside the record workspace, not in the global sidebar.
- Shell context should stay quiet: module, section, tenant, role.
- Do not add page-local debug facts like `route`, `visible modules`, or other system metadata into page headers.

## Page Anatomy

Default admin page structure:

1. Shell header
2. Page title + one short useful sentence
3. Compact page context strip only if it helps the task
4. Collection or record surface
5. Modal-based create/edit flows

Do not stack multiple hero sections or repeated fact strips on normal operational pages.

## Headers

Headers must be left-anchored and operational.

- Title and description align left.
- Badges and actions align right.
- Avoid centered section titles on content cards.
- Avoid promotional or dashboard-style copy.
- Keep descriptions short and task-oriented.

Good:

- `Addresses`
- `Residence and office address records.`

Bad:

- long explanatory paragraphs
- repeated module context already visible in the shell
- status chips that restate the page title

## Typography

Typography should use one shared scale across the entire admin panel.

Default scale:

- `Page title`: `30-34px`
- `Section title`: `18px`
- `Card title`: `16px`
- `Body strong`: `15px`
- `Body`: `14px`
- `Label / control text`: `13px`
- `Caption`: `12px`
- `Eyebrow / table head / badge label`: `11px`

Usage rules:

- Use `Page title` only for route-level headings.
- Use `Section title` for card headers, modal titles, and secondary workspace headings.
- Use `Card title` for compact summary titles and key inline labels.
- Use `Body` for the majority of readable UI copy.
- Use `Caption` for secondary metadata and helper lines.
- Use `Eyebrow` only for uppercase labels such as table headers, section kickers, and badge text.
- Do not introduce arbitrary font sizes like `13.5px`, `15.5px`, or one-off `text-[0.xxxrem]` classes unless absolutely necessary.
- Prefer shared primitive classes and tokens over route-local typography decisions.

## Spacing Density

Spacing should feel compact, deliberate, and uniform.

Default rhythm:

- `Page section gap`: `14-16px`
- `Card / surface padding`: `13-16px`
- `Toolbar padding`: `12-14px`
- `Table row cell padding`: `10-12px`
- `Modal body padding`: `15-16px`
- `Inline control gap`: `6-8px`

Usage rules:

- Tighten spacing through shared primitives first, not page-local one-off overrides.
- Prefer one consistent compact density across cards, toolbars, tables, and dialogs.
- Do not use ultra-tiny padding to fake density; dense enterprise UI should still breathe.
- Reduce vertical stacking before shrinking text.
- If a page feels cluttered, remove redundant surfaces before compressing spacing further.

## Tables

Tables are the primary pattern for admin data.

- Use real semantic table markup.
- Prefer full-width tables for registries and queues.
- Keep filters inside the collection toolbar, not in separate cards below the table.
- Keep actions inside an `Action` column.
- Row actions should open modals or navigate to a dedicated record workspace.
- Use side inspectors only for true review workflows where comparison or decision context matters.

Default collection toolbar:

- search
- 0-4 meaningful filters
- record count
- reset filters
- primary create action when applicable

Avoid:

- fake tables built from `div` or `article`
- empty split panes next to tables
- detached filter panels
- row hints like `select row` or `inspect` when a real action button should exist

## Record Workspaces

Employee-like detail pages should use a compact record header plus routed inner sections.

- Keep the top record strip thin.
- Show only the most useful facts.
- Use routed section tabs such as `Profile`, `Lifecycle`, `Documents`.
- Inside each section, prefer dense tables or summary grids.
- If editing happens in a modal, do not reserve page width for an inline editor pane.

## Forms and Mutations

All create and update flows should use modal-based forms by default.

- create: modal
- edit: modal
- destructive actions: confirmation dialog
- success, warning, and failure states: toasts

Do not open large inline forms under tables unless the workflow truly requires side-by-side comparison.

## Badge Usage

Badge color should be restrained.

- use color for real status
- use neutral badges for metadata
- do not create rows of decorative pills that add no decision value

Examples:

- `Active`, `Probation`, `Restricted`
- not `5 sections`, `current module`, `live APIs`, `control surface` unless truly needed

Implementation rule:

- use `subtle` badges for metadata like counts, domains, groups, personas, and labels
- use colored badges only for meaningful state such as `approved`, `pending`, `rejected`, `absent`, `terminated`, or `restricted`

## Copy Rules

Use enterprise product language.

- short
- precise
- operational

Prefer:

- `Protected payroll account records.`
- `Operational contact channels.`
- `Escalation and safety-readiness contacts.`

Avoid:

- promotional language
- sprint or rollout language
- tutorial phrasing
- decorative internal jargon

## Anti-Patterns To Avoid

Do not introduce these without a strong reason:

- giant hero sections on inner pages
- 50/50 split layouts for CRUD pages
- repeated tenant and session facts on every route
- centered card headings in dense admin screens
- stacked summary cards above every table
- local tabs when a module submenu should be a real route
- multiple unrelated datasets on the same route

## Decision Rules

When designing or refactoring a page, use this order:

1. What is the primary job of this page?
2. Can this page be split into dedicated routed sections?
3. Can the main content be expressed as a table or summary grid?
4. Can create/edit happen in a modal instead of inline?
5. Which header elements are actually useful for the task?
6. Which badges or facts are only visual noise?

If in doubt, choose the denser, calmer, more operational option.

## Current Direction

The admin panel should continue moving toward:

- routed module submenus
- full-width collection tables
- compact record headers
- left-aligned section headers
- modal-first editing
- minimal repeated chrome
- shared interaction patterns across all modules

For top-level module dashboard strategy, rollout order, and command-center content planning, see:

- [DASHBOARD_V2_BLUEPRINT.md](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/DASHBOARD_V2_BLUEPRINT.md)
