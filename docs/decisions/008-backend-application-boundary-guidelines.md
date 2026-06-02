# DEC-008: Backend Application Boundary Guidelines

## Status

Proposed

## Context

Sprint 01 introduced auth, workflow, notification, tenant, and audit services, but the project did not yet record a shared rule for when backend code should use events and listeners, constructor DI, Laravel facades, or extra abstraction layers.

Without that guidance, module services can drift into direct cross-module calls, while other areas may get unnecessary interfaces or ceremony that do not improve maintainability.

## Decision

PhoenixHRMS backend code will follow these rules:

- Constructor dependency injection is the default for controllers, services, listeners, and other application classes.
- Laravel contracts or concrete services should be injected for domain collaborators instead of resolving dependencies ad hoc inside methods.
- Domain events and listeners should be introduced when a state change in one module triggers side effects in another module, or when work needs a clean after-commit or asynchronous boundary.
- Laravel facades should be used only at framework boundaries where they improve clarity, such as transactions, hashing, password broker flows, caching, or rate limiting.
- SOLID should be applied pragmatically:
  - Keep services focused on one module responsibility.
  - Push cross-module side effects behind events/listeners.
  - Avoid adding interfaces, repositories, or events when a flow is local, synchronous, and already clear.

## Rationale

- This keeps module services easier to test and reason about.
- It preserves the modular-monolith boundary described in DEC-003 without introducing microservice-style complexity.
- It aligns implementation with DEC-006 by using internal Laravel events where cross-module orchestration actually exists.
- It prevents facade overuse from hiding dependencies while still allowing Laravel-native framework entry points where they are the clearest option.

## Consequences

- Workflow-to-notification side effects should flow through events/listeners instead of direct service coupling.
- Audit logging may remain direct where synchronous trace guarantees are part of the core business flow.
- New abstractions should be justified by reduced coupling, testability, or an explicit infrastructure boundary.

## Affected Docs

- [DEC-003: V1 Runtime Architecture](./003-v1-runtime-architecture.md)
- [DEC-006: Eventing and Integration Baseline](./006-eventing-and-integration-baseline.md)
- [Platform Foundation](../modules/platform-foundation.md)
- [Sprint 01: Auth, RBAC, and Tenant Foundation](../sprints/sprint-01-auth-rbac-tenant-foundation.md)
