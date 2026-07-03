# AI Assistant

## Purpose

The AI Assistant module provides role-aware conversational assistance, recommendations, summarization, anomaly detection, and insight generation across PhoenixHRMS.

## Business Value

- Reduces repetitive HR and manager workload
- Improves employee self-service experience
- Surfaces anomalies and planning insights earlier
- Makes knowledge and policy retrieval more accessible

## In Scope

- Employee, manager, HR, recruiter, payroll, and executive copilots
- Conversational Q and A over enterprise HR knowledge
- Role-aware content generation and summaries
- Analytics insight generation and anomaly detection
- Recommendation flows for learning, performance, hiring, retention, and succession
- Controlled workflow assistance with human approval for critical actions

## Out Of Scope

- Fully autonomous execution of critical HR actions
- Unapproved access to cross-tenant or unauthorized data

## Primary Actors

- Employee
- Manager
- HR
- Recruiter
- Payroll
- Executive

## Core Workflows

- User question to retrieval, context assembly, LLM response, and audit capture
- AI-assisted summary and report generation
- Recommendation generation with explanation and confidence
- Human-approved automation for selected low-risk or review-based tasks

## Key Rules

- AI recommendations must never execute automatically for critical actions
- Human approval is required for action-taking workflows
- Every recommendation should include explanation or reasoning context
- Predictions should include confidence scores
- Prompt management and AI activity must be versioned and auditable

## Current Delivery Baseline

- The current workspace now exposes a governed Sprint 10 AI Assistant HTTP surface in `apps/api` for `GET /api/v1/ai/workspace`, `POST /api/v1/ai/chat`, `POST /api/v1/ai/recommendations`, `POST /api/v1/ai/recommendations/{recommendationId}/decisions`, and `POST /api/v1/ai/interactions/{interactionId}/feedback`.
- Supported v1 answer use cases are leave balance, attendance summary, payslip summary, policy-document posture, and learning-summary posture only.
- Supported v1 recommendation scenarios are learning next-best action, policy acknowledgement follow-up, and attendance follow-up, all of which remain review-only and audit-captured.
- The routed web workspace now lives at `/assistant`, showing disclosure messaging, citations, recent interaction history, feedback capture, human decision controls for approved recommendation scenarios, ranked citation metadata, review analytics, and a visible assistant audit trail.

## Core Entities

- `ai_conversations`
- `ai_interactions`
- `ai_recommendations`
- `audit_logs`

## Implemented API Surface

- `GET /api/v1/ai/workspace`
- `POST /api/v1/ai/chat`
- `POST /api/v1/ai/recommendations`
- `POST /api/v1/ai/recommendations/{recommendationId}/decisions`
- `POST /api/v1/ai/interactions/{interactionId}/feedback`

## Planned Expansion Candidates

- `POST /api/v1/ai/resume-analysis`
- `POST /api/v1/ai/attendance-insights`
- `POST /api/v1/ai/attrition-prediction`

## Dependencies

- Strong permission enforcement and tenant isolation
- Reporting, document, and transactional module data
- Audit logging and AI governance policy

## Related Sprints

- [Sprint 09: Mobile, Integrations, and Globalization](../sprints/sprint-09-mobile-integrations-globalization.md)
- [Sprint 10: AI, Operations Hardening, and Release Readiness](../sprints/sprint-10-ai-ops-release-readiness.md)

## Source Specs

- `docs/files/PhoenixHRMS AI Copilot Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
