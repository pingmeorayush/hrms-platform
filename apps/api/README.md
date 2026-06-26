# PhoenixHRMS API

Laravel API application for PhoenixHRMS.

## Daily Commands

```bash
composer install
php artisan serve
```

## Quality Gates

```bash
composer lint
composer analyse
composer test
composer ci
```

What each command does:

- `composer lint`: runs `Pint`
- `composer analyse`: runs `Larastan` and `PHPStan`
- `composer test`: runs the Laravel test suite
- `composer ci`: runs lint, static analysis, and tests together

## Engineering Baseline

- Static analysis is enforced at PHPStan level `6`.
- Protected request classes should not default to `authorize(): true` unless the route is intentionally public.
- Straightforward protected request flows should derive authorization from route permission middleware.
- Nuanced self-service, workflow-participant, and mixed-action flows may use explicit request or service-level scope checks.
- Constructor dependency injection is the default for controllers, services, and listeners.

## Useful Local Debugging

If you want direct static-analysis output during local debugging:

```bash
./vendor/bin/phpstan analyse --memory-limit=1G --debug
```

## Related Docs

- [DEC-004: Engineering Test Toolchain](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/decisions/004-engineering-test-toolchain.md)
- [DEC-008: Backend Application Boundary Guidelines](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/decisions/008-backend-application-boundary-guidelines.md)
- [Engineering Hardening Baseline](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/architecture/engineering-hardening-baseline.md)
