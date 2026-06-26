# Engineering Hardening Baseline

## Purpose

This note records the engineering hardening work completed in the current workspace so the team can treat it as the active baseline for day-to-day sprint delivery.

## Current Baseline

### Frontend

- `apps/web` runs on strict TypeScript with zero-warning ESLint.
- `apps/web/package.json` exposes a single `npm run quality` gate that runs:
  - `npm run typecheck`
  - `npm run lint`
  - `npm run test:run`
  - `npm run build`
- The web CI flow is expected to keep that quality gate green.
- Oversized React workspaces should be split by concern. The current reference example is:
  - orchestration and route state in [AttendanceAdminWorkspace.tsx](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/src/modules/attendance/components/AttendanceAdminWorkspace.tsx)
  - editor and form implementations in [AttendanceAdminEditors.tsx](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/src/modules/attendance/components/AttendanceAdminEditors.tsx)

### Backend

- `apps/api` enforces Pint, PHPUnit, Larastan, and PHPStan.
- `apps/api/composer.json` exposes:
  - `composer lint`
  - `composer analyse`
  - `composer test`
  - `composer ci`
- The backend static-analysis baseline is PHPStan level `6`.
- The major business modules and platform module are covered by the enforced static-analysis scope.

### Authorization

- Protected request classes should not use `authorize(): true` unless the route is intentionally public.
- Straightforward protected request flows should derive authorization from the route permission middleware.
- Action-aware and participant-aware flows should keep explicit authorization logic where a single route supports multiple behaviors.
- Self-service and workflow-owned resources may preserve existing service-level scope resolution when the API contract intentionally returns `404` for out-of-scope resources.

## Practical Commands

### Frontend

```bash
cd apps/web
npm run quality
```

### Backend

```bash
cd apps/api
composer ci
```

For direct static-analysis output during local debugging:

```bash
cd apps/api
./vendor/bin/phpstan analyse --memory-limit=1G --debug
```

## Non-Blocking Follow-Ups

- Continue decomposing the largest remaining React workspaces, especially employee and payroll admin surfaces.
- Prefer moving more nuanced authorization into explicit request or policy flows before adding new mixed-action endpoints.
- Keep new modules and request classes aligned with the accepted decisions in `DEC-004` and `DEC-008`.
