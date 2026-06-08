# Frontend Delivery Order: Sprints 02 to 04

## Purpose

Turn the Sprint 02 to 04 UI stories into a practical implementation sequence for the web app so frontend work lands as coherent vertical slices instead of disconnected screens.

## Current Workspace Baseline

- [apps/web/src/App.tsx](../../apps/web/src/App.tsx) currently mounts only the access workbench.
- The reusable frontend baseline today is the Sprint 01 access contract, Redux store wiring, providers, and a small shared UI primitive set under [apps/web/src/shared/ui](../../apps/web/src/shared/ui).
- There is not yet a routed application shell, feature navigation, authenticated page layout, or feature module structure for employee, attendance, or leave operations.

## Planning Principles

- Build the web shell once, then add feature modules behind the existing access contract.
- Deliver admin setup screens before downstream employee and manager workflows that depend on that setup.
- Favor end-to-end slices that can be demoed with real backend contracts instead of placeholder-only screens.
- Reuse one route, form, and data-fetching pattern across Sprints 02 to 04 so later modules do not re-litigate frontend architecture.

## Recommended Delivery Waves

| Wave | Scope | Stories | Why This Comes Here |
| --- | --- | --- | --- |
| 0 | Web shell and feature foundation | Cross-sprint prerequisite using Sprint 01 access contract | The current web app needs routing, authenticated layout, navigation, feature-module structure, API wiring, and guard patterns before business screens can land cleanly. |
| 1 | Organization admin baseline | `S02-013` | Organization masters are a low-risk admin surface and establish the CRUD, forms, and permissions pattern used by every later sprint. |
| 2 | Employee discovery baseline | `S02-014` | Employee directory and detail views create the first operational HR workspace and unlock navigation into employee-scoped actions. |
| 3 | Employee operational detail | `S02-015` | Lifecycle, onboarding, and documents should build on top of the employee detail workspace instead of duplicating page structure. |
| 4 | Attendance admin setup | `S03-008` | Attendance policy, holidays, shifts, and rosters are setup-heavy and should exist before broad attendance usage moves into the UI. |
| 5 | Employee attendance usage | `S03-009` | Employee check-in, check-out, and history can then ship against stable scheduling and policy configuration. |
| 6 | Attendance exception operations | `S03-010` | HR and manager review surfaces depend on capture and calculation visibility already existing in the product. |
| 7 | Leave admin setup | `S04-008` | Leave policy and calendar administration should be visible in the UI before employee leave usage starts. |
| 8 | Employee leave usage | `S04-006` | Employees need live balances, request flows, and history before managers can operate a meaningful queue. |
| 9 | Manager leave operations | `S04-007` | Approval queues and team availability become highest-value once requests are already flowing through the employee UI. |

## Wave Details

### Wave 0: Web Shell and Feature Foundation

This is the only recommended prerequisite that is not already captured as a dedicated Sprint 02 to 04 UI story.

Suggested implementation scope:

- Add a routed app shell with feature navigation and page slots
- Reuse the Sprint 01 access contract for route and action guards
- Introduce a consistent feature-module structure under `modules/organization`, `modules/employees`, `modules/attendance`, and `modules/leave`
- Add a shared API client and request-state conventions for list, detail, create, and update flows
- Add shared page patterns for loading, empty, error, and permission-denied states

Suggested frontend output:

- App shell and navigation
- Route guard baseline
- Shared query and mutation utilities
- Form validation and server-error mapping pattern

### Wave 1: `S02-013` Organization Master Admin Workspace

Ship first:

- Department list and create-edit flow
- Designation list and create-edit flow
- Location list and create-edit flow
- Cost-center list and create-edit flow

Why first:

- It is admin-only, low ambiguity, and a strong proving ground for forms, CRUD tables, and permission-based action visibility.

### Wave 2: `S02-014` Employee Directory, Filter, and Detail Workspace

Ship second:

- Employee list route
- Search and filter controls
- Directory row actions and role-aware navigation
- Employee summary detail page shell

Why second:

- It creates the core operational entry point for HR and managers and gives the rest of Sprint 02 a stable navigation anchor.

### Wave 3: `S02-015` Employee Profile, Lifecycle, Onboarding, and Document Screens

Ship third:

- Employee profile sections
- Lifecycle actions such as transfer, promotion, and termination
- Onboarding progress panel
- Document list and upload-download controls
- Sensitive section gating for bank details and restricted records

Why third:

- These screens are structurally dependent on the employee detail workspace from Wave 2 and reuse the same permission and audit-history context.

### Wave 4: `S03-008` Attendance Policy, Holiday, Shift, and Roster Admin Screens

Ship fourth:

- Attendance policy settings
- Holiday calendar management
- Shift-definition list and forms
- Assignment and roster scheduling flows

Why fourth:

- Attendance admin setup is necessary for trustworthy operational rollout and uses the same admin conventions already proven in Sprint 02.

### Wave 5: `S03-009` Employee Attendance Capture and Personal History Screens

Ship fifth:

- Check-in and check-out action surface
- Personal attendance history list
- Derived status visibility
- Correction entry point where available

Why fifth:

- Once policies and schedules are visible in the product, employee attendance usage becomes easier to demo and support.

Current workspace note:

- This wave is now implemented in `apps/web` through the shared `/attendance` route, which exposes employee self-service history and capture alongside the admin attendance setup tabs.

### Wave 6: `S03-010` Correction Queue and Operational Attendance Review Workspace

Ship sixth:

- HR operational review dashboard
- Manager pending-exceptions queue
- Correction request review details
- Role-scoped exception and recalculation visibility

Why sixth:

- These views only become operationally meaningful after capture, history, and daily attendance outcomes are already present in the UI.

Current workspace note:

- This wave is now implemented in `apps/web` through the shared `/attendance` route, which exposes manager and HR operational review, scoped exception queues, correction decision history, and role-aware approve, reject, and request-changes actions alongside self-service and admin setup tabs.

### Wave 7: `S04-008` HR Leave Policy and Leave-Calendar Admin Screens

Ship seventh:

- Leave-type and policy setup
- Balance-rule forms
- Leave calendar administration

Why seventh:

- Leave operations need visible setup before employee request screens can be trusted or demoed end to end.

Current workspace note:

- This wave is now implemented in `apps/web` through the shared `/leave` route, which exposes leave types, balance-rule editing, and organization leave-calendar administration in both demo and live API mode against the Sprint 04 backend contracts.

### Wave 8: `S04-006` Employee Leave Balance, Request, and History Screens

Ship eighth:

- Balance summary
- Leave request form
- Request history and status list
- Cancellation flow where allowed

Why eighth:

- Employee leave usage should arrive before manager approvals so there is a real request stream to review.

Current workspace note:

- This wave is now implemented in `apps/web` through the shared `/leave` route, which exposes employee leave balances, request submission with overlap and balance validation, cancellation where allowed, and history in both demo and live API mode.

### Wave 9: `S04-007` Manager Approval Queue, Team Availability, and Leave Calendar Screens

Ship ninth:

- Manager leave approval queue
- Team leave visibility
- Team availability or calendar views

Why ninth:

- Manager workflows are highest-value once employee leave requests are already live and HR setup is complete.

Current workspace note:

- This wave is now implemented in `apps/web` through the shared `/leave` route, which exposes manager approval decisions, hierarchy-scoped team availability, and the pending leave queue in both demo and live API mode.

## Suggested Route Groups

- `/admin/organization`
- `/employees`
- `/employees/:employeeId`
- `/attendance`
- `/attendance/review`
- `/leave`
- `/leave/approvals`

## Suggested Module Layout

- `apps/web/src/modules/organization`
- `apps/web/src/modules/employees`
- `apps/web/src/modules/attendance`
- `apps/web/src/modules/leave`

Suggested per-module subfolders:

- `api`
- `components`
- `pages`
- `hooks`
- `types`

## Recommended Milestone Cuts

- Milestone A: Waves 0 to 2
  Delivers the shell, navigation, organization admin, and employee directory foundation.
- Milestone B: Waves 3 to 5
  Delivers employee detail operations and first employee-facing attendance usage.
- Milestone C: Waves 6 to 9
  Delivers manager and HR operational attendance plus full leave UI baseline.

## Do Not Start Out of Order

- Do not start `S02-015` before the Wave 2 employee detail shell exists.
- Do not start `S03-010` before `S03-009` because exception review is hard to validate without visible employee attendance history.
- Do not start `S04-007` before `S04-006` because manager leave queues have little value until employee requests are flowing through the UI.

## Recommended First Build

If the team starts implementation immediately, the first frontend build sequence should be:

1. Wave 0 app shell and guard baseline
2. `S02-013` organization master admin workspace
3. `S02-014` employee directory and detail shell

That path creates the fastest stable base for everything else in Sprints 02 to 04.
