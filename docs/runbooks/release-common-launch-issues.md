# Common Launch Issues Playbook

## Purpose

Use this playbook for fast first-response guidance when the issue is familiar and the team needs a repeatable triage path before full incident escalation.

## Authentication Or Session Failures

- Symptoms: users cannot sign in, MFA verification loops, or self-service routes resolve to authorization errors.
- First checks: `/up` health, auth logs, recent secret or config changes, and route-level permission posture.
- Owner: `platform.support`
- Recovery path: restore the last known good auth configuration, verify tenant resolution, then re-run sign-in and self-service smoke checks.

## Integration Delivery Backlog

- Symptoms: webhook retries accumulate, downstream systems stop updating, or sync jobs remain queued past SLA.
- First checks: `/operations/integrations`, `/operations/observability`, subscription status, failed payloads, and retry error history.
- Owner: `integration.manage`
- Recovery path: retry or process the governed sync jobs, validate active subscriptions, and record any blocked downstream owners in the readiness decision.

## Payroll Or Payslip Access Issues

- Symptoms: payroll review lanes are blocked, gross-to-net review cannot proceed, or employees cannot access payslips.
- First checks: `/payroll/review`, blocked run state, payslip generation status, and role-based access behavior in self-service.
- Owner: `payroll.process`
- Recovery path: clear the blocking payroll control, re-run targeted payroll smoke checks, and hold launch approval if payout visibility is still impaired.

## Reporting Or Export Failures

- Symptoms: certified reports fail to export, subscriptions pause, or launch reporting evidence is incomplete.
- First checks: `/reporting/exports`, `/reporting/subscriptions`, and `/operations/observability` reporting signals.
- Owner: `reporting.manage`
- Recovery path: re-run the failed export or subscription, verify the certified dataset remains current, and document any remaining reporting blockers.

## Recovery Evidence Drift

- Symptoms: backup or DR evidence is overdue, missing, or failed during the active launch window.
- First checks: `/operations/resilience`, latest validation runs, runbook evidence links, and target RPO or RTO posture.
- Owner: `platform.super_admin`
- Recovery path: log the missing or failed recovery validation, schedule rerun evidence immediately, and record a no-go or conditional decision until evidence is refreshed.
