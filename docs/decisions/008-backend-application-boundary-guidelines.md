# DEC-008: Backend Application Boundary Guidelines

## Status

Accepted

## Context

Sprint 01 introduced auth, workflow, notification, tenant, and audit services, but the project did not yet record a shared rule for when backend code should use events and listeners, constructor DI, Laravel facades, or extra abstraction layers.

Without that guidance, module services can drift into direct cross-module calls, while other areas may get unnecessary interfaces or ceremony that do not improve maintainability.

## Decision

PhoenixHRMS backend code will follow these rules:

- Constructor dependency injection is the default for controllers, services, listeners, and other application classes.
- Laravel contracts or concrete services should be injected for domain collaborators instead of resolving dependencies ad hoc inside methods.
- Protected `FormRequest` classes must not default to `authorize(): true`; they should either map to route-declared permissions or apply explicit actor-ownership or participant rules.
- Route middleware defines the coarse permission boundary, while request authorization adds action-specific and actor-specific checks where a route supports multiple behaviors.
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
- Standard permission-only request flows may use a shared route-permission helper instead of repeating permission strings in every request class.
- Self-service and workflow-participant flows may keep service-level scope resolution so out-of-scope resources preserve existing not-found semantics.

## Implementation Notes

This decision is now reflected in the current implementation baseline:

- A shared request concern now derives authorization from route permission middleware for straightforward protected request flows.
- Performance and recruitment requests keep explicit action-aware authorization where route access alone is too broad.
- Employee self-service request flows preserve existing ownership and resource-scope behavior instead of blindly converting all out-of-scope access into `403` responses.
- Service-layer mutation guards remain in place for high-impact flows where request access alone is not a sufficient business safeguard.

## Affected Docs

- [DEC-003: V1 Runtime Architecture](./003-v1-runtime-architecture.md)
- [DEC-006: Eventing and Integration Baseline](./006-eventing-and-integration-baseline.md)
- [Platform Foundation](../modules/platform-foundation.md)
- [Sprint 01: Auth, RBAC, and Tenant Foundation](../sprints/sprint-01-auth-rbac-tenant-foundation.md)
- [Engineering Hardening Baseline](../architecture/engineering-hardening-baseline.md)
