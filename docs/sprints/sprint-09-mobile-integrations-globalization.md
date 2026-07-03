# Sprint 09: Mobile, Integrations, and Globalization

## Objective

Extend the core platform into external systems, mobile access, and regional readiness after the web product is stable.

## Status

In Progress

## Primary Backlog IDs

- `INT-001`
- `MOB-001`
- `GLO-001`
- `GLO-002`

## Module References

- [Mobile Platform](../modules/mobile-platform.md)
- [Integrations Platform](../modules/integrations-platform.md)
- [Globalization and Localization](../modules/globalization-localization.md)
- [AI Assistant](../modules/ai-assistant.md)

## Backlog Detail

- [Sprint 09 Delivery Backlog](../backlog/sprint-09-mobile-integrations-globalization.md)

## Scope

- Mobile ESS MVP for high-frequency workflows
- Integration hooks, webhooks, and sync jobs
- Localization baseline: timezone, locale, language, and currency formatting
- Launch-country and expansion-country configuration support

## Delivery Items

- Mobile ESS flows for attendance, leave, payslips, notifications, and profile access
- Integration adapters or webhook endpoints for selected external systems
- Localization services and regional configuration controls

## Current Delivery Focus

- Sprint 09 is currently running website-first for globalization so the web product, reporting surfaces, and tenant configuration baseline are stable before mobile-specific regional UX is layered in.
- `S09-006` is now implemented across `apps/api` and `apps/web` with tenant regional settings, shared localization configuration, and website-wide date, time, number, and currency formatting utilities.
- `S09-007` is now implemented for the website slice through authenticated user regional overrides, self-service regional preference management, and demo-mode parity in `apps/web`.
- `S09-008` now publishes the Sprint 09 consumer contract in `apps/api/openapi/sprint-09-mobile-ess-globalization.yaml`, aggregating the live mobile-consumable ESS APIs plus the new localization endpoints into one reviewable source of truth.
- `S09-004` and `S09-005` are now implemented in `apps/api` and `apps/web` through a governed integrations baseline with connection setup, webhook subscriptions, outbound event dispatch, signed inbound webhook ingestion, sync-job monitoring, retry controls, and the routed `/operations/integrations` workspace.
- Current launch and placeholder market presets include India, United States, United Kingdom, Germany, United Arab Emirates, and Singapore.

## Implemented So Far

- `S09-006` now provides shared regional configuration through `GET /api/v1/localization`, expanded tenant context in `GET /api/v1/auth/me`, and regional tenant profile updates through `PATCH /api/v1/organization/company-profile`.
- `S09-007` now adds `PATCH /api/v1/localization/preferences`, allowing authenticated users to apply or clear personal overrides for locale, language, timezone, currency, and time format while preserving tenant defaults as the fallback source.
- `S09-008` now publishes `apps/api/openapi/sprint-09-mobile-ess-globalization.yaml`, which reuses the already-reviewed auth, attendance, leave, payslip, notification, task-center, policy acknowledgement, self-service, and company-profile paths while documenting the Sprint 09 regional response shapes inline.
- `S09-004` now adds `GET /api/v1/integrations/catalog`, connection and webhook-subscription management endpoints, `POST /api/v1/integrations/events/dispatch`, and the public signed inbound webhook endpoint `POST /api/v1/integrations/webhooks/{subscriptionKey}` for the approved v1 external systems baseline.
- `S09-005` now adds `/api/v1/integrations/sync-jobs` monitoring plus `POST /api/v1/integrations/sync-jobs/{integrationSyncJobId}/process` and `/retry`, preserving payload history, failure state, retry evidence, manual queue processing, and audit visibility for operators.
- `apps/web` now ships an operations-integrations command surface at `/operations/integrations`, including connection setup, webhook subscription setup, monitored sync-job state badges, manual queue processing, selected-job payload and header review, retry actions, and governed demo or live outbound event dispatch.
- Sprint 09 now also publishes `apps/api/openapi/sprint-09-integrations.yaml`, separating the integration control surface from the mobile and globalization contract so review stays focused for operators and downstream consumers.
- Company records now store `country_code`, `locale`, `language`, `time_format`, and `expansion_country_codes`, while user records can carry regional overrides for locale, language, timezone, currency, and time format.
- `apps/web` now ships a shared regionalization provider plus formatter utilities that are consumed by organization, payroll, reporting, recruitment, performance, learning, attendance, employee, self-service, leave, and operations website surfaces.
- The organization admin company-profile page now exposes launch-country defaults, locale, language, timezone, currency, time-format, and expansion-placeholder controls with a live regional preview.
- The self-service profile page now exposes a dedicated Regional preferences surface and modal, including source badges, live formatting previews, and working behavior even when the session is not linked to an employee record.
- Backend and frontend tests now cover the localization API baseline and the core regional formatter or company-profile UI behavior for the website slice.
- Verification now also covers personal override persistence in `AuthApiTest` plus self-service regional preference behavior in `SelfServiceProfilePage.test.tsx`.
- The current Sprint 09 contract set is now split between `apps/api/openapi/sprint-09-mobile-ess-globalization.yaml` and `apps/api/openapi/sprint-09-integrations.yaml` so mobile consumers and integration operators can review separate but version-controlled surfaces.

## Dependencies

- Stable web APIs and reporting from previous sprints
- Launch-market decisions already closed

## Acceptance Criteria

- Mobile users can complete the selected ESS flows securely
- External integrations can exchange data through governed interfaces
- Timezone and locale handling is consistent across UI, APIs, and reports

## Test Focus

- Mobile auth and session handling
- Integration error handling and retries
- Locale and timezone conversion accuracy
- Permission parity between web and mobile

## Risks and Open Questions

- Mobile parity should stay intentionally narrow for the first cut
- Multi-country expansion should not outpace validated payroll and legal support
- Mobile globalization surfaces should consume the same `localization` contract that now powers the website instead of inventing a parallel mobile-only regional settings model
- Mobile-specific regional settings screens are intentionally deferred while Sprint 09 remains website-first, but they should reuse the same tenant and user override precedence that is now live on the web.
- Mobile-specific regional settings screens remain intentionally deferred, but they should continue consuming the now-stable localization contract rather than introduce a parallel mobile-only regional model.
