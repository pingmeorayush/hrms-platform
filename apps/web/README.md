# PhoenixHRMS Web

React, TypeScript, and Vite frontend for PhoenixHRMS.

## Daily Commands

```bash
npm install
npm run dev
```

## Quality Gates

```bash
npm run typecheck
npm run lint
npm run test:run
npm run build
npm run quality
```

`npm run quality` is the main frontend gate and runs typecheck, lint, tests, and production build validation together.

## Engineering Baseline

- TypeScript runs in strict mode.
- ESLint is enforced with zero warnings.
- `Vitest` and `React Testing Library` are the standard unit and component test baseline.
- Large workspace components should be split by concern instead of accumulating orchestration, modal flows, and editor forms in one file.
- The attendance admin workspace is the current reference split between:
  - [AttendanceAdminWorkspace.tsx](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/src/modules/attendance/components/AttendanceAdminWorkspace.tsx)
  - [AttendanceAdminEditors.tsx](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/apps/web/src/modules/attendance/components/AttendanceAdminEditors.tsx)

## Related Docs

- [DEC-004: Engineering Test Toolchain](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/decisions/004-engineering-test-toolchain.md)
- [Engineering Hardening Baseline](/Users/ayushchauhan/Documents/phoenix-hrms-boilerplate/docs/architecture/engineering-hardening-baseline.md)
