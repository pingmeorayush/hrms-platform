# Release Incident Response Runbook

## Purpose

Use this runbook when a launch or post-launch production issue needs coordinated incident response instead of ad hoc troubleshooting.

## Trigger Conditions

- A Sev1 or Sev2 issue is detected during launch review or immediately after promotion
- Observability alerts indicate critical service degradation or blocked release-critical workflows
- Customer-facing payroll, self-service, integration, or authentication paths are materially impacted

## Accountable Roles

- `platform.super_admin`: incident commander and final escalation owner
- `platform.support`: technical triage lead and telemetry coordinator
- `tenant.admin`: business impact reviewer and launch approval delegate
- `it.admin`: environment, deployment, and rollback operator

## Response Flow

1. Declare the incident, assign the incident commander, and freeze any further promotion or risky configuration changes.
2. Open the incident channel and capture the start time, affected workflows, current release window, and user impact summary.
3. Review `/operations/observability`, `/operations/readiness`, and `/operations/release` to confirm alert posture, blockers, and the latest go or no-go decision context.
4. Classify the issue as mitigation-in-place, rollback-required, or launch-hold, then assign owners for each workstream.
5. Validate the critical workflows most likely to be impacted: authentication, employee search, payroll review or payslip access, and any launch-specific integrations.
6. If rollback is required, switch to the rollback runbook immediately and keep this incident record open until rollback verification is complete.
7. Update the release-readiness decision record with blocker ownership, mitigation status, and the final go, conditional, or no-go outcome.

## Evidence To Capture

- Incident declaration timestamp and channel reference
- Named incident commander and technical leads
- Links to alert evidence, log excerpts, and operator screenshots
- Decision on mitigation versus rollback, with accountable owner names
- Validation notes for the critical workflows rechecked during the incident
- Final closure summary and follow-up actions
