# Sprint 10 Launch Release Package

## Purpose

Use this package to close Sprint 10 cleanly across merge planning, release communication, and final smoke verification.

## Scope Reference

- [Sprint 10 Plan](../sprints/sprint-10-ai-ops-release-readiness.md)
- [Sprint 10 Delivery Backlog](../backlog/sprint-10-ai-ops-release-readiness.md)
- [Release Incident Response Runbook](../runbooks/release-incident-response.md)
- [Release Rollback Runbook](../runbooks/release-rollback.md)
- [Release Common Launch Issues](../runbooks/release-common-launch-issues.md)
- [Backup, Restore, and DR Validation Runbook](../runbooks/backup-restore-disaster-recovery-validation.md)

## Final Browser UAT

Completed on 02 Jul 2026 against the launch-critical web surfaces below. All five routes passed live render and basic operator-journey checks.

- `/assistant`
  Verified governed question composer behavior, cited-answer visibility, recommendation review controls, review analytics, and audit-trail visibility.
- `/operations/release`
  Verified release metrics, gate table visibility, selected-gate drill-in, promotion posture, and no-match search behavior.
- `/operations/observability`
  Verified service-health metrics, alert-routing lanes, selected-service drill-in, telemetry coverage, and no-match search behavior.
- `/operations/resilience`
  Verified recovery metrics, scenario table, validation history, runbook visibility, validation logging form, and no-match search behavior.
- `/operations/readiness`
  Verified readiness metrics, checklist drill-in, recommendation posture, blocker and runbook visibility, decision history, decision form, and no-match search behavior.

Launch note:
The demo readiness dataset intentionally contains active blockers and a `No-go` posture so launch-governance behavior can be reviewed end to end. That is expected demo data, not a broken UI state.

## Recommended Commit Grouping

Use these groupings when preparing the final merge history.

1. `feat(api): add governed AI assistant baseline and recommendation controls`
   Covers `S10-001` and `S10-002` across AI permissions, persistence, governed answer builders, recommendation generation, and human decision capture in `apps/api`.
2. `feat(web): ship governed assistant workspace with analytics and audit visibility`
   Covers `S10-003` and `S10-009` across `/assistant`, cited answers, feedback capture, review analytics, ranked citations, queue posture, and audit timeline behavior in `apps/web`.
3. `feat(ops): add release, observability, resilience, and readiness control tower`
   Covers `S10-004`, `S10-005`, `S10-006`, and `S10-007` across GitHub workflows, release and operations APIs, `/operations/release`, `/operations/observability`, `/operations/resilience`, `/operations/readiness`, and Sprint 10 runbooks.
4. `docs(contracts): publish Sprint 10 contracts and release handoff`
   Covers `S10-008`, Sprint 10 closeout, OpenAPI publication, and this release package.

If a single merge commit is preferred:

`feat: close Sprint 10 with governed AI assistant and launch-readiness control tower`

## Release Notes

### Highlights

- Shipped a governed AI assistant baseline with permission-aware answers, citations, recommendation guardrails, feedback capture, review analytics, and a visible audit trail at `/assistant`.
- Shipped a launch control tower across `/operations/release`, `/operations/observability`, `/operations/resilience`, and `/operations/readiness`.
- Added release-quality GitHub workflows for API, web, dependency audit, and promotion-gate summary enforcement.
- Published operational and AI assistant contracts in `apps/api/openapi` and completed the launch runbook set in `docs/runbooks`.

### API and Platform Surface

- Added governed AI assistant endpoints for workspace loading, chat, recommendation generation, and recommendation decisions.
- Added operational endpoints for release quality gates, observability overview, resilience readiness, validation-run logging, release readiness, and readiness decision capture.
- Added explicit permission scopes for AI, release, observability, and resilience management and review.

### Web Surface

- Added the `/assistant` workspace for cited answers, guarded recommendations, reviewer feedback, analytics, and audit history.
- Added the `/operations/release` workspace for blocking gates, promotion posture, and gate evidence.
- Added the `/operations/observability` workspace for service health, alert routing, telemetry coverage, and release-critical monitoring.
- Added the `/operations/resilience` workspace for recovery scenarios, validation history, runbook review, and evidence logging.
- Added the `/operations/readiness` workspace for launch checklist review, blockers, runbooks, recommendation posture, and go or no-go decisions.

### Operational Readiness

- Versioned incident response, rollback, common launch issue, and DR validation runbooks are now part of the repository review surface.
- Final browser UAT passed across all launch-critical Sprint 10 routes on 02 Jul 2026.

## End-to-End Smoke Checklist

### Quality Gates

- [ ] GitHub Actions `API CI` passes.
- [ ] GitHub Actions `Web CI` passes.
- [ ] GitHub Actions `Release Quality Gates` passes and reports both child workflows successful.

### API and Contracts

- [ ] In `apps/api`, run `composer validate --strict`.
- [ ] In `apps/api`, run `composer audit --locked`.
- [ ] In `apps/api`, run `npm run audit:dependencies`.
- [ ] In `apps/api`, run `php artisan migrate:fresh --seed --force`.
- [ ] In `apps/api`, run `composer lint`.
- [ ] In `apps/api`, run `composer analyse`.
- [ ] In `apps/api`, run `php artisan test`.
- [ ] In `apps/api`, run `npm run openapi:lint`.

### Web Build and Tests

- [ ] In `apps/web`, run `npm run audit:dependencies`.
- [ ] In `apps/web`, run `npm run typecheck`.
- [ ] In `apps/web`, run `npm run lint`.
- [ ] In `apps/web`, run `npm run test:run`.
- [ ] In `apps/web`, run `npm run build`.

### Browser UAT

- [ ] Open `/assistant` and confirm cited answers, recommendation controls, review analytics, and audit trail are visible.
- [ ] Enter text in the assistant question composer and confirm the `Ask assistant` button enables.
- [ ] Open `/operations/release` and confirm release metrics, gate table, selected-gate drill-in, and promotion posture load without error.
- [ ] Open `/operations/observability` and confirm service health, alert-routing lanes, telemetry baseline, and selected-service coverage load without error.
- [ ] Open `/operations/resilience` and confirm recovery scenarios, validation history, DR runbook, and validation-log form load without error.
- [ ] Open `/operations/readiness` and confirm checklist, blockers, runbooks, decision history, and go or no-go form load without error.
- [ ] On each `/operations/*` surface, enter a no-match search term and confirm the empty-state message renders cleanly.

### Runbook Readiness

- [ ] Review `docs/runbooks/release-incident-response.md` before production launch.
- [ ] Review `docs/runbooks/release-rollback.md` before protected promotion approval.
- [ ] Review `docs/runbooks/release-common-launch-issues.md` with the release operator set.
- [ ] Review `docs/runbooks/backup-restore-disaster-recovery-validation.md` and confirm latest resilience evidence is acceptable for the release window.
