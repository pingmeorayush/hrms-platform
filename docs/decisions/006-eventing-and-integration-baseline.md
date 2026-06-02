# DEC-006: Eventing and Integration Baseline

## Status

Proposed

## Context

The testing strategy references Kafka-style integration patterns, but the current application architecture only clearly commits to Laravel events and Redis-backed queues.

## Decision

PhoenixHRMS v1 will use:

- Internal domain events within the backend application
- Redis-backed queue workers for asynchronous processing
- Webhooks and scheduled sync jobs for initial external integrations

PhoenixHRMS v1 will not require Kafka or a dedicated external event bus.

## Rationale

- This matches the simpler modular-monolith target.
- It reduces platform complexity while still supporting asynchronous workflows and integration hooks.
- It leaves room for a future event bus if scale or integration patterns demand it.

## Consequences

- Testing strategy should remove hard assumptions about Kafka from v1.
- Integration and notification work should be built on queued jobs, events, and webhooks first.

## Affected Docs

- [Platform Foundation](../modules/platform-foundation.md)
- [Integrations Platform](../modules/integrations-platform.md)
- `docs/files/PhoenixHRMS Testing Strategy.txt`
- `docs/files/PhoenixHRMS Backend Architecture.txt`
