# DEC-007: AI Safety and Approval Policy

## Status

Proposed

## Context

The AI specification includes both low-risk read experiences and higher-risk action or prediction features. V1 needs a clear safety boundary.

## Decision

PhoenixHRMS v1 AI policy:

- Allowed in v1:
  - Policy Q and A
  - Leave balance and attendance queries
  - Payslip and document retrieval summaries
  - Report summarization
  - Resume summarization or ranking as recruiter decision support
- Not allowed to auto-execute in v1:
  - Critical approvals
  - Payroll changes
  - Employee status changes
  - Compensation changes
  - Termination or disciplinary actions
- All AI responses must:
  - Respect tenant and permission scope
  - Be auditable
  - Provide grounding or source context where available
  - Provide confidence indicators for prediction-oriented outputs

## Rationale

- This enables visible product value without creating unacceptable compliance and trust risk in the first release.
- It aligns with the existing business rule that AI recommendations must not execute automatically.

## Consequences

- AI implementation should start with read-heavy assistant experiences only.
- Action-taking automation must wait for a later decision and stronger governance model.

## Affected Docs

- [AI Assistant](../modules/ai-assistant.md)
- [Sprint 10](../sprints/sprint-10-ai-ops-release-readiness.md)
- `docs/files/PhoenixHRMS AI Copilot Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
