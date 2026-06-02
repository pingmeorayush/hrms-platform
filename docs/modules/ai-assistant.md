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

## Core Entities

- `ai_conversations`
- `ai_prompts`
- `ai_knowledge_sources`
- `ai_recommendations`
- `ai_feedback`
- `ai_audit_events`

## Primary APIs

- `POST /api/v1/ai/chat`
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
