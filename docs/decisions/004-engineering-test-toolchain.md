# DEC-004: Engineering Test Toolchain

## Status

Accepted

## Context

The current specifications conflict on frontend and backend test tooling. A shared toolchain is needed for CI, templates, and developer workflow.

## Decision

PhoenixHRMS v1 will standardize on:

- Frontend unit and component tests: `Vitest` + `React Testing Library`
- Backend unit and integration tests: `PHPUnit`
- API contract validation: `OpenAPI validation` and request/response schema checks
- API scenario tests: `Postman/Newman` or equivalent scripted API regression suite
- E2E tests: `Playwright`
- Static analysis: framework-native linters plus type and code-quality tooling per layer

The platform will not standardize on `Jest` for new work.

## Rationale

- `Vitest` is a better fit for the documented frontend stack.
- `PHPUnit` is the natural baseline for Laravel/PHP backend services.
- `Playwright` supports realistic critical-path regression coverage.

## Consequences

- Testing strategy, frontend architecture, and CI documentation should be updated to match this baseline.
- Existing placeholder or legacy references to `Jest` for backend work should be removed or marked legacy-only.
- New work is expected to keep the enforced quality gates green before merge.

## Implementation Notes

This decision is now implemented in the current workspace baseline:

- `apps/web` uses `Vitest`, `React Testing Library`, strict TypeScript, and zero-warning ESLint.
- `apps/web/package.json` exposes `typecheck`, `lint`, `test:run`, `build`, and `quality`.
- Web CI enforces the frontend quality gate through typecheck, lint, test, and production build validation.
- `apps/api` uses `PHPUnit`, `Pint`, `Larastan`, and `PHPStan`.
- `apps/api/composer.json` exposes `lint`, `analyse`, `test`, and `ci`.
- Backend static analysis is enforced at PHPStan level `6`.
- API request authorization and module changes are expected to keep both static analysis and test suites green.

## Affected Docs

- [Requirements Analysis](../07-requirements-analysis.md)
- `docs/files/PhoenixHRMS Frontend Architecture.txt`
- `docs/files/PhoenixHRMS Testing Strategy.txt`
- `docs/files/PhoenixHRMS Backend Architecture.txt`
- [Engineering Hardening Baseline](../architecture/engineering-hardening-baseline.md)
