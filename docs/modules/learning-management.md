# Learning Management

## Purpose

The Learning Management module supports course delivery, learning paths, assessments, certifications, skill development, compliance training, and learning analytics.

## Business Value

- Improves employee development and skill visibility
- Supports compliance and certification tracking
- Enables structured training assignment and completion reporting
- Provides input into performance and career-growth decisions

## In Scope

- Learning catalog and course management
- Learning content and learning paths
- Enrollment, assignments, and instructor management
- Classroom and virtual training
- Assessments, quizzes, certifications, and compliance training
- Skill framework, skill assessment, recommendations, analytics, reports, and notifications

## Out Of Scope

- The detailed excluded scope requires confirmation from the original PDF text

## Primary Actors

- Employee
- Manager
- HR
- Instructor
- Learning Administrator

## Core Workflows

- Course creation and publication
- Assignment or self-enrollment into learning paths
- Training participation, assessment completion, and certification issuance
- Compliance tracking and renewal reminders
- Skill-gap and recommendation support

## Key Rules

- Completion, certification, and compliance states should be traceable
- Manager and HR visibility must respect role and tenant scope
- Learning recommendations should remain explainable and reviewable

## Core Entities

- `courses`
- `learning_paths`
- `enrollments`
- `assignments`
- `assessments`
- `quizzes`
- `certifications`
- `skill_profiles`

## Primary APIs

- LMS APIs are referenced in the source PDF and should be defined into the OpenAPI inventory during implementation

## Dependencies

- Employee data
- Notifications and document access
- Performance integration and analytics

## Related Sprints

- [Sprint 07: Recruitment, Performance, and Learning](../sprints/sprint-07-recruitment-performance-learning.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS_Learning_Management_Module_Specification.pdf`

## Notes

This module summary is derived from the learning PDF section outline available in the workspace.
