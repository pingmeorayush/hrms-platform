# Recruitment

## Purpose

The Recruitment module manages the hiring journey from workforce planning and requisition approval through candidate pipeline, interviews, offers, and hire readiness.

## Business Value

- Improves recruiter productivity
- Standardizes hiring approvals and stages
- Increases visibility into pipeline and hiring outcomes
- Creates structured data for analytics and AI-assisted screening

## In Scope

- Workforce planning support
- Job requisitions and requisition approvals
- Job openings and career portal support
- Candidate management and resume storage
- Resume parsing and talent pool management
- Screening, interviews, interview feedback, and evaluation
- Offer management, background verification, referral, and campus hiring
- Recruitment analytics, reports, notifications, and AI-assisted features

## Out Of Scope

- Core employee record management after hire
- Payroll processing

## Primary Actors

- Recruiter
- Hiring Manager
- Interview Panel Member
- HR
- Candidate

## Core Workflows

- Requisition creation, approval, and job publication
- Candidate application, sourcing, and resume intake
- Screening, interview scheduling, evaluation, and recommendation capture
- Offer generation, approval, expiry management, and acceptance tracking
- Handover to onboarding after successful hire

## Key Business Rules

- Candidate email must be unique
- Resumes become immutable after submission and updates create versions
- Interview evaluations require score, comments, and recommendation
- Offer letters expire after a configurable deadline

## Core Entities

- `job_requisitions`
- `jobs`
- `candidates`
- `candidate_resumes`
- `candidate_pipelines`
- `interviews`
- `interview_feedback`
- `offers`

## Primary APIs

- `GET /api/v1/recruitment/requisitions`
- `POST /api/v1/recruitment/requisitions`
- `PATCH /api/v1/recruitment/requisitions/{id}`
- `GET /api/v1/recruitment/candidates`
- `POST /api/v1/recruitment/candidates`
- `PATCH /api/v1/recruitment/candidates/{id}`
- `POST /api/v1/recruitment/candidates/{id}/resumes`
- `POST /api/v1/recruitment/candidates/{id}/stage-transitions`
- `GET /api/v1/recruitment/interviews`
- `POST /api/v1/recruitment/interviews`
- `POST /api/v1/recruitment/interviews/{id}/feedback`
- `GET /api/v1/recruitment/offers`
- `POST /api/v1/recruitment/offers`
- `PATCH /api/v1/recruitment/offers/{id}`

## Dependencies

- Organization structure and approval workflows
- Document management for resumes and offers
- Notification delivery
- Reporting and analytics

## Sprint 07 V1 Planning Notes

- Sprint 07 should not stop at requisition and candidate CRUD. The v1 slice needs requisition approval posture, candidate pipeline history, resume versioning, duplicate detection, interview scorecards, offer expiry, and accepted-offer handoff into onboarding.
- Enterprise-grade v1 recruitment should preserve role boundaries between recruiter, hiring manager, interviewer, and HR sessions, including confidential interview-feedback visibility and auditable stage ownership.
- Candidate state should be strong enough for later analytics and AI features, which means pipeline movement, scorecard outcomes, and offer decisions must be persisted as first-class state rather than inferred from comments or notification history.

## Current Sprint 07 Delivery Status

- `S07-001` is now implemented as a requisition baseline in `apps/api` with tenant-scoped requisition records, recruiter assignment, hiring-manager ownership, workflow-backed approval, hold and close controls, and audit coverage.
- `S07-002` is now implemented as a candidate baseline in `apps/api` with approved-requisition candidate intake, immutable resume version uploads, duplicate-email detection, and auditable stage-transition history.
- `S07-003` is now implemented as the interview-and-offer baseline in `apps/api` with explicit interviewer assignment, overlap-protected scheduling, immutable scorecard feedback, workflow-backed offer approval, configurable expiry handling, stakeholder notifications, and durable offer decision history.
- `S07-011` is now implemented as the accepted-offer handoff baseline in `apps/api` with durable hire-handoff records, employee creation from accepted-offer context, candidate conversion to `hired`, resume-reference preservation, and optional onboarding-template orchestration.
- The current requisition states are `draft`, `submitted`, `approved`, `on_hold`, `closed`, `rejected`, and `changes_requested`.
- The current candidate stages are `applied`, `screening`, `shortlisted`, `interview`, `offer`, `hired`, `rejected`, and `withdrawn`, with resulting candidate status derived as `active`, `hired`, `rejected`, or `withdrawn`.
- The current interview statuses are `scheduled`, `completed`, and `cancelled`, and the baseline now blocks overlapping interview slots for the same interviewer or candidate.
- The current offer statuses are `draft`, `submitted`, `approved`, `rejected`, `changes_requested`, `sent`, `accepted`, `declined`, and `expired`, with active-offer protection preventing parallel draft or approval-state offers for the same candidate.
- Approval resolution now supports payload-bound approvers for cases like hiring-manager review, which lets the shared workflow engine assign approval tasks from requisition ownership instead of only static roles.
- `S07-004` is now implemented in `apps/web` as the recruiter-facing recruitment workspace with routed overview, requisition review, candidate board, and candidate-detail screens backed by demo and live data wiring.
- The current recruitment UI baseline now covers requisition posture, filtered candidate pipeline movement, resume-version visibility, interview coordination, offer workflow state, and accepted-offer handoff creation for visible sessions.
- The current routed recruitment sections are `/recruitment/overview`, `/recruitment/requisitions`, `/recruitment/candidates`, and `/recruitment/candidates/{candidateId}` with recruiter, hiring-manager, and approver-aware route guards.
- `S07-010` is now implemented as the focused recruitment contract publication in [apps/api/openapi/sprint-07-recruitment-operations.yaml](../../apps/api/openapi/sprint-07-recruitment-operations.yaml), covering requisitions, candidates, resumes, interviews, offers, and hire handoff in a dedicated module contract instead of a mixed talent file.

## Explicit Sprint 07 Deferrals

- Public career portal and external candidate self-service
- AI screening and resume ranking
- Referral, agency, campus-hiring, and background-verification integrations
- External calendar and email-suite interview scheduling integrations

## Related Sprints

- [Sprint 07: Recruitment, Performance, and Learning](../sprints/sprint-07-recruitment-performance-learning.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)
- [Sprint 10: AI, Operations Hardening, and Release Readiness](../sprints/sprint-10-ai-ops-release-readiness.md)

## Source Specs

- `docs/files/PhoenixHRMS_Recruitment_Management_Module_Specification.pdf`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`

## Notes

This module summary is derived from the recruitment PDF section outline plus related PRD, business-rule, story, and API sources available in the workspace.
