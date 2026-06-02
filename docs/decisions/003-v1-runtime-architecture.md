# DEC-003: V1 Runtime Architecture

## Status

Proposed

## Context

The source specs describe both a modular monolith and a microservices-style deployment. Engineering needs one runtime target for v1.

## Decision

PhoenixHRMS v1 will use a modular monolith architecture with:

- One primary backend application
- Separate worker processes for queues and scheduled jobs
- Shared PostgreSQL, Redis, and object storage
- Internal domain separation by module boundary, service layer, policies, events, and repositories

V1 will not require service extraction into separate deployable microservices.

## Rationale

- This aligns with the system architecture document more closely than the microservices description.
- It reduces operational complexity while preserving future service-extraction options.
- The current module and sprint design benefits from strong internal boundaries but does not require network boundaries yet.

## Consequences

- DevOps and deployment docs should stop describing v1 as a true microservices platform.
- Queue workers, cron jobs, and domain events remain first-class, but they run within the modular monolith deployment model.
- Service extraction remains a post-v1 architectural option.

## Affected Docs

- [Requirements Analysis](../07-requirements-analysis.md)
- [Platform Foundation](../modules/platform-foundation.md)
- `docs/files/PhoenixHRMS Architecture.txt`
- `docs/files/PhoenixHRMS DevOps & Deployment Architecture Specification.txt`
