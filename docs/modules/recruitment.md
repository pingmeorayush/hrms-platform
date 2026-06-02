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

- `GET /api/v1/recruitment/jobs`
- `POST /api/v1/recruitment/jobs`
- `GET /api/v1/recruitment/candidates`
- `POST /api/v1/recruitment/candidates`
- `POST /api/v1/recruitment/interviews`
- `POST /api/v1/recruitment/offers`

## Dependencies

- Organization structure and approval workflows
- Document management for resumes and offers
- Notification delivery
- Reporting and analytics

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
