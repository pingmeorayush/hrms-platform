# DEC-002: Service Level Targets

## Status

Proposed

## Context

The current spec set contains conflicting availability targets:

- NFR target: `99.9%`
- DevOps target: `99.99%`
- Ops platform SLO: `99.99%`

This mismatch affects infrastructure design, staffing assumptions, support processes, and release criteria.

## Decision

For v1, PhoenixHRMS will adopt:

- Platform availability target: `99.9%`
- API availability target: `99.9%`
- Payroll processing success target: `99.95%` for scheduled production runs
- Authentication availability target: `99.9%`

Future target after post-v1 operational maturity:

- Consider raising critical-path targets toward `99.95%` or above after sustained production evidence supports the move

## Rationale

- `99.99%` implies stronger operational maturity, staffing, automation, and redundancy than the rest of the current v1 plan assumes.
- `99.9%` is more realistic for an initial release and still compatible with an enterprise-minded product if communicated clearly.
- Payroll processing is business-critical, so it gets a slightly stronger success target than general feature availability.

## Consequences

- NFR, DevOps, and Ops docs should be normalized to this target set.
- Platform engineering can design for strong resilience without prematurely overcommitting to ultra-high availability.

## Affected Docs

- [Requirements Analysis](../07-requirements-analysis.md)
- `docs/files/PhoenixHRMS Non-Functional Requirements.txt`
- `docs/files/PhoenixHRMS DevOps & Deployment Architecture Specification.txt`
- `docs/files/PhoenixHRMS Production Operations Runbook.txt`
