# Release Rollback Runbook

## Purpose

Use this runbook when the approved launch state becomes unsafe or non-compliant and the release must be reversed in a controlled, evidenced way.

## Preconditions

- Incident commander or authorized release owner has declared rollback necessary
- Protected promotion is frozen and stakeholder communications are active
- The rollback target version, database posture, and integration side effects are understood

## Rollback Steps

1. Record the rollback decision in the launch incident log and note the exact release window, target environment, and trigger reason.
2. Confirm the last known good application version, infrastructure state, feature-flag posture, and any affected data or integration jobs.
3. Stop or isolate background processing that could amplify the incident while the rollback is executed.
4. Revert application deployment, infrastructure configuration, or feature flags using the approved release mechanism for the affected environment.
5. If data recovery is required, coordinate with the backup and DR validation runbook before restoring anything mutable.
6. Re-run smoke checks for authentication, employee directory access, payroll review or payslip access, and other workflows called out in the active release-readiness checklist.
7. Re-open or update the release-readiness decision record with the rollback outcome, remaining blockers, and next review time.

## Verification Checklist

- Observability no longer shows the rollback-triggering critical failure
- Release-quality and protected-environment posture are re-evaluated after rollback
- Critical workflow smoke tests pass or any remaining blockers are explicitly logged
- External integrations are either recovered or documented with owner and follow-up timing

## Exit Criteria

- Rollback completion time is recorded
- Business and technical owners agree on current service posture
- A new go-live review is scheduled before any future promotion attempt
