# Mobile Platform

## Purpose

The Mobile Platform extends PhoenixHRMS into secure, role-aware mobile experiences for employees, managers, HR, recruiters, and executives.

## Business Value

- Increases workforce access and responsiveness
- Improves self-service adoption
- Supports remote and field-based work
- Delivers approvals and alerts outside the desktop experience

## In Scope

- React Native mobile applications for iOS and Android
- Secure authentication, MFA, biometrics, and device trust
- Employee self-service for profile, attendance, leave, payslips, documents, notifications, and AI access
- Manager, HR, recruiter, and executive role-based mobile experiences
- Offline-first storage and selected offline workflows
- Push notifications and deep-link navigation

## Out Of Scope

- Full desktop parity in the first mobile release

## Primary Actors

- Employee
- Manager
- HR
- Recruiter
- Executive
- Contractor

## Core Workflows

- Mobile login with MFA or biometrics
- Mobile attendance and leave actions
- Payslip and document access
- Approval and notification handling
- Offline capture with later synchronization where supported

## Key Rules

- Sensitive data must use secure local storage and session protection
- Device trust and remote logout controls are required
- Permission scope must match backend authorization, not only UI visibility
- Offline capabilities must reconcile safely back to authoritative APIs

## Core Entities

- Mobile uses backend module entities and adds device registrations, trusted sessions, and push tokens

## Primary APIs

- Mobile experiences consume the existing HRMS API set rather than a separate domain-specific API surface

## Dependencies

- Stable web APIs and permission enforcement
- Notification services and document access
- Mobile security controls and observability

## Related Sprints

- [Sprint 09: Mobile, Integrations, and Globalization](../sprints/sprint-09-mobile-integrations-globalization.md)

## Source Specs

- `docs/files/PhoenixHRMS Mobile App Platform Specification.txt`
