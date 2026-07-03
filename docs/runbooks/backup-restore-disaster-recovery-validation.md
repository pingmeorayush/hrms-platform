# Backup, Restore, and Disaster-Recovery Validation Runbook

## Purpose

This runbook defines how Phoenix HRMS operators validate that backups, restore paths, and disaster-recovery procedures are real, repeatable, and evidenced before launch readiness is approved.

## Scope

- Production application and database backup verification
- Staging restore rehearsals for launch-critical data paths
- Payroll artifact restore validation
- Regional failover and failback drills

## Roles

- `platform.super_admin`: incident command and final readiness sign-off
- `platform.support`: backup verification, infrastructure coordination, and evidence capture
- `tenant.admin`: launch-critical business smoke checks and remediation ownership
- `it.admin`: restore execution, failover sequencing, and technical recovery evidence

## Scenario Catalog

### Daily application backup

- Type: `backup`
- Environment: `production`
- Cadence: every 1 day
- Objective: prove nightly backups complete with retained and encrypted artifacts
- Target: `RPO 60 min`, `RTO 240 min`

### Monthly database restore validation

- Type: `restore`
- Environment: `staging`
- Cadence: every 30 days
- Objective: restore the governed production backup and verify auth, employee search, and reporting read paths
- Target: `RPO 60 min`, `RTO 180 min`

### Payroll artifact restore rehearsal

- Type: `restore`
- Environment: `staging`
- Cadence: every 14 days
- Objective: restore protected payroll artifacts and confirm access controls plus metadata remain intact
- Target: `RPO 30 min`, `RTO 120 min`

### Regional failover drill

- Type: `disaster_recovery`
- Environment: `secondary_region`
- Cadence: every 90 days
- Objective: validate failover sequencing, smoke checks, and failback posture across the secondary region
- Target: `RPO 15 min`, `RTO 120 min`

## Standard Sequence

### 1. Declare the scenario

- Confirm the scenario being exercised and expected scope
- Assign incident command, communications owner, and technical executor
- Pause unsafe release or data-mutation activity when required

Evidence:

- declaration timestamp
- named operator roles

### 2. Stabilize writes and confirm the recovery source

- Freeze protected writes if the scenario requires restore or failover
- Capture the exact snapshot, backup job id, or archive reference
- Verify checksum and retention posture

Evidence:

- backup or snapshot identifier
- checksum or integrity proof

### 3. Execute restore or failover

- Restore the governed dataset into the approved environment or initiate regional failover
- Record start and finish timestamps
- Capture actual `RPO` and `RTO` outcomes

Evidence:

- execution log
- restored environment or failover reference

### 4. Validate launch-critical workflows

- Verify sign-in and tenant access
- Verify employee directory search
- Verify payroll artifact access where relevant
- Verify release-critical monitoring and alert posture

Evidence:

- smoke-test checklist output
- screenshots, logs, or command evidence for each critical flow

### 5. Close with outcome and remediation

- Record the final outcome as `passed`, `issues_found`, `failed`, or `in_progress`
- Link all evidence artifacts
- Assign remediation owners and next validation date when issues remain

Evidence:

- operator notes
- remediation owner and follow-up target

## Evidence Rules

- Every non-`in_progress` validation requires at least one evidence reference.
- Failed or issue-bearing validations must include operator notes that explain the blocker and remediation path.
- Recovery evidence must be reviewable from the Sprint 10 resilience workspace or linked artifacts referenced there.

## Review Expectations

- Any `failed` scenario blocks launch readiness until a passing rerun is logged.
- Any `issues_found` scenario remains in attention until remediation evidence and rerun results are recorded.
- Any overdue scenario must be rerun before go-live review is approved.
