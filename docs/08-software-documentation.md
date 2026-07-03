# PhoenixHRMS Product Bible

Last updated: 2026-07-03

Companion document:

- [Technical Guide](./09-technical-guide.md)

## 1. What this document is

This is the non-technical, business-first single source of truth for PhoenixHRMS.

It exists to answer, in one place:

- what PhoenixHRMS is
- who it is for
- what each module does
- what every page is meant to achieve
- how the modules connect
- how to explain the product to customers, delivery teams, HR teams, operations teams, and leadership

If someone wants to understand the product without reading code, API contracts, or tests, this is the document they should read first.

If someone needs implementation detail, architecture, routes, APIs, test coverage, seed data, or setup instructions, they should use the companion [Technical Guide](./09-technical-guide.md).

## 2. PhoenixHRMS in one view

PhoenixHRMS is an enterprise HRMS and payroll platform that brings together people data, daily workforce operations, payroll, hiring, development, reporting, governance, and AI assistance in one controlled system.

The product is designed so that:

- employee and organization records become the shared foundation
- attendance, leave, and payroll work as one connected chain
- recruitment flows into onboarding instead of ending at offer acceptance
- learning and performance become structured talent systems instead of spreadsheets
- operations, reporting, and AI consume governed business data instead of disconnected copies
- access, audit, workflow, release, resilience, and observability are treated as core product capabilities, not afterthoughts

## 3. What business problem the product solves

Most HR teams do not struggle because they lack single-purpose tools. They struggle because:

- employee records live in one place
- attendance and leave live in another place
- payroll depends on incomplete or late data
- hiring stops at offer acceptance and becomes manual onboarding
- reporting is slow, untrusted, or scattered
- approvals and access controls are hard to govern
- teams cannot confidently show who changed what, when, and why

PhoenixHRMS is built to solve that fragmentation.

It gives customers one connected operating model where:

- the workforce record is trusted
- approvals are controlled
- visibility depends on role and scope
- auditability is always available
- self-service is protected
- downstream outputs such as reports, payslips, and AI answers are based on governed data

## 4. Who the product serves

### 4.1 Core user groups

| User group | What they need from the product |
| --- | --- |
| Platform administrators | Control identity, access, governance, shared roles, and platform-wide oversight |
| Tenant administrators | Set up the company, manage users, configure modules, and run tenant operations |
| HR administrators | Run employee, lifecycle, leave, performance, learning, and document processes |
| Managers | Approve, review, monitor teams, and act on visible workflow items |
| Employees | Use self-service for profile review, attendance, leave, documents, assets, learning, and pay visibility |
| Payroll teams | Prepare, review, approve, and release payroll accurately and repeatably |
| Recruiters | Manage requisitions, candidate pipelines, interviews, offers, and hire handoff |
| IT and operations teams | Manage assets, integrations, resilience, observability, release readiness, and operational controls |
| Leadership | Consume governed reports and business visibility without drilling into operational detail |
| Auditors and support teams | Review access, audit events, operational posture, and governed controls |

### 4.2 What this means in practice

PhoenixHRMS is not one single “HR page.”

It is a permission-aware workspace platform where each user sees a different product shape based on:

- role
- tenant context
- linked employee profile
- operational responsibility
- approval responsibility

That is why access, visibility, audit, and scope are part of the product story, not just technical plumbing.

## 5. How the product is entered and experienced

### 5.1 Secure sign-in is now a first-class product experience

PhoenixHRMS no longer treats sign-in as a side detail.

The platform now has dedicated access experiences for:

- sign in
- step-up verification when needed
- forgot-password initiation
- password reset and return to sign-in
- session checking before protected workspaces open

This matters because the product is now clearer to explain:

- there is a controlled front door
- there is a controlled recovery flow
- protected workspaces require a valid session
- the same secure perimeter is used for everyday users and high-governance users

### 5.2 The Foundation workspace

The Foundation workspace is the product’s orientation layer.

It helps users and internal teams understand:

- which workspace modules are currently visible
- which role or persona is active
- which environment is being used
- what access posture the session has
- what tenant context is loaded

In simple terms, Foundation answers the question:

“Where am I, what am I allowed to see, and how is this session behaving?”

### 5.3 The Access workspace

The Access workspace is now broader than a route-visibility explainer.

It is a governance workspace for:

- user administration
- role governance
- route visibility review
- action visibility review
- access diagnostics

This is where the product explains not only what a person can do, but why.

### 5.4 Guided demo sessions and live signed-in sessions

PhoenixHRMS can be shown in two different ways:

- guided demo sessions, which are useful for product walkthroughs and safe storytelling
- live signed-in sessions, which use real authenticated access and real backend behavior

Business meaning:

- demo mode is best when you want to explain the product without depending on live data
- live mode is best when you want to prove the real behavior of access, workflows, calculations, and auditability

## 6. Product map

PhoenixHRMS is easiest to understand in six groups.

### 6.1 Access and platform governance

- Sign in and password recovery
- Session control
- Roles and users
- Permission-aware route visibility
- Auditability
- Workflow and notification foundations
- Release, resilience, and observability posture

### 6.2 People core

- Organization Management
- Employee Management
- Employee Self-Service

### 6.3 Time, leave, and pay

- Attendance
- Leave
- Payroll and Compensation

### 6.4 Talent and growth

- Recruitment
- Performance
- Learning

### 6.5 Operations and control

- Documents
- Assets
- Integrations
- Lifecycle operations
- Release readiness
- Observability
- Resilience

### 6.6 Insight and guidance

- Reporting and Analytics
- AI Assistant
- Localization
- Mobile readiness

## 7. The modules, from a business point of view

## 7.1 Access, Identity, and Governance

### What this module is

This is the controlled front door and governance layer of the product.

It makes sure the right people can sign in, recover access safely, open only the workspaces they should see, and administer access without breaking tenant boundaries.

### Why customers care

Customers care about this area because it reduces risk.

It proves that:

- access is intentional
- platform and tenant roles are governed
- user administration is controlled
- MFA can be enforced
- hidden routes and hidden actions are not accidental
- the platform can explain who should see what

### Who uses it

- Platform administrators
- Tenant administrators
- Support teams
- Auditors
- Every end user who signs in

### Pages in this area

- `Sign in` at `/login`
  - This is the secure entry point to PhoenixHRMS.
  - It supports normal sign-in and step-up verification when the session requires it.
- `Password reset` at `/reset-password`
  - This is the controlled recovery flow for restoring access.
  - Users return to the same secure sign-in path after a successful reset.
- `Foundation` at `/foundation`
  - This is the orientation page for session posture, visible workspaces, tenant context, and access state.
- `Access` at `/access`
  - This is the governance workspace for access administration and visibility review.

### What the Access workspace contains

- `Users`
  - Shows the manageable user roster for the current session.
  - Supports user creation, update, status control, role assignment, and MFA posture review.
- `Roles`
  - Shows role definitions and permission scope.
  - Shared role changes are deliberately more restricted than day-to-day user assignment.
- `Routes`
  - Shows which product routes are visible to the current identity.
- `Actions`
  - Shows which governed actions are visible to the current identity.
- `Diagnostics`
  - Explains hidden route counts, hidden action counts, and backend enforcement posture.

### Important business rules

- A protected workspace should not open without a valid session.
- A session that expires should send the user back to sign-in cleanly.
- Tenant administrators can manage tenant users, but they should not be able to manage platform-super-admin identities.
- Shared role definitions are more heavily governed than everyday user assignment.
- MFA posture is part of access administration, not a side task.

### How this area connects to the rest of the product

Every other module depends on it.

If this layer is weak, then:

- route visibility becomes confusing
- data scope becomes unsafe
- approvals become unreliable
- customer trust drops

### How to show this area to a customer

1. Start with sign-in and explain that access is governed, not improvised.
2. Open Foundation and show how workspace visibility changes by role.
3. Open Access and explain the Users, Roles, Routes, Actions, and Diagnostics views.
4. Emphasize that the same product supports both secure end-user access and secure admin operations.

## 7.2 Organization Management

### What this module is

Organization Management defines the company structure that the rest of the platform uses.

It is the structural backbone for:

- departments
- designations
- locations
- cost centers
- company-level defaults

### Why customers care

This module gives customers a consistent operating model.

Without it, teams cannot reliably:

- place employees into the right structure
- assign hiring and reporting ownership
- configure payroll and location defaults
- segment reporting
- scale beyond one simple office layout

### Who uses it

- Tenant administrators
- HR administrators
- Finance and operations stakeholders

### Pages in this area

- `Overview` at `/admin/organization/overview`
  - Shows structure health and master-data posture.
- `Company profile` at `/admin/organization/company-profile`
  - Holds tenant identity and company-level defaults.
- `Structure` at `/admin/organization/structure`
  - Manages departments and designations.
- `Locations` at `/admin/organization/locations`
  - Manages sites, location identity, and region-specific defaults.
- `Cost centers` at `/admin/organization/cost-centers`
  - Supports financial and reporting allocation.

### How this area connects to the rest of the product

Organization data feeds:

- Employee Management
- Recruitment
- Attendance
- Leave
- Payroll
- Reporting
- Localization defaults

### How to show this area to a customer

1. Open Company profile and explain how tenant-level defaults work.
2. Move into Structure to show departments and designations.
3. Move into Locations to explain physical or regional operating context.
4. Finish with Cost centers to show downstream financial and reporting value.

## 7.3 Employee Management

### What this module is

Employee Management is the workforce system of record.

It is the place where the platform knows:

- who the employee is
- where they sit in the organization
- what their lifecycle state is
- what documents and contacts belong to them
- what onboarding or offboarding work is still open

### Why customers care

This is the module that turns people administration into structured data instead of manual spreadsheets and disconnected documents.

It supports:

- cleaner onboarding
- cleaner transfers and promotions
- safer termination handling
- better downstream payroll and reporting
- clearer employee-level audit trails

### Who uses it

- HR administrators
- Tenant administrators
- Managers with visible employee scope
- Employees indirectly through linked self-service

### Main workspace pages

- `Overview` at `/employees/overview`
  - A high-level people operations center.
- `Directory` at `/employees/directory`
  - The searchable employee roster.
- `Lifecycle watch` at `/employees/lifecycle-watch`
  - The monitoring view for employee records that need HR attention.
- `Onboarding` at `/employees/onboarding`
  - The workforce-wide onboarding posture page.
- `Documents` at `/employees/documents`
  - The operational view of employee-document coverage.
- `Audit` at `/employees/audit`
  - The protected view of employee-related audit activity.

### Employee detail pages

- `Profile` at `/employees/:employeeId/profile`
  - Identity, contact, address, and visible sensitive panels.
- `Lifecycle` at `/employees/:employeeId/lifecycle`
  - Transfers, promotions, and termination behavior.
- `Onboarding` at `/employees/:employeeId/onboarding`
  - Employee-specific onboarding progress and tasks.
- `Documents` at `/employees/:employeeId/documents`
  - Employee-specific document records.
- `History` at `/employees/:employeeId/history`
  - The employee-specific audit trail.

### What a customer should understand

This module is not just an HR directory.

It is the main system of record that later feeds:

- attendance
- leave
- payroll
- self-service
- reporting
- recruitment handoff
- learning visibility
- performance visibility

### How to show this area to a customer

1. Start with Directory to show the roster.
2. Open one employee and walk through Profile, Lifecycle, Onboarding, Documents, and History.
3. Explain that this one record becomes the anchor for the rest of the platform.

## 7.4 Employee Self-Service

### What this module is

Self-Service is the employee’s personal view of the platform.

It is intentionally narrower than admin workspaces and is designed to show only the information and actions that should belong to the linked employee profile.

### Why customers care

Customers want self-service because it:

- reduces routine HR questions
- gives employees transparency
- keeps access protected and scoped
- strengthens document, asset, and policy acknowledgement workflows

### Who uses it

- Employees

### Pages in this area

- `Profile` at `/self-service/profile`
  - Personal profile review plus regional preferences.
- `Documents` at `/self-service/documents`
  - Allowed employee documents, allowed repository documents, and policy acknowledgement work.
- `Assigned assets` at `/self-service/assets`
  - Current issued assets and handover expectations.

### How this area connects to the rest of the product

Self-Service pulls from:

- Employee Management
- Document Management
- Asset Management
- Localization
- Payroll self-service through the separate My Pay area

### How to show this area to a customer

1. Open Profile to show controlled personal visibility.
2. Open Documents to show download and acknowledgement behavior.
3. Open Assigned assets to show clear employee responsibility for equipment.

## 7.5 Attendance

### What this module is

Attendance manages daily timekeeping and attendance operations.

It combines:

- employee self-service capture
- manager and HR operational review
- policy and schedule administration

### Why customers care

Customers care because attendance is where daily workforce truth is established.

If attendance is weak:

- payroll becomes unreliable
- managers lose visibility
- exception handling becomes manual
- correction workflows become messy

### Who uses it

- Employees
- Managers
- HR administrators
- Operations teams
- Payroll-adjacent users

### Pages in this area

- `Overview` at `/attendance/overview`
  - The attendance operations center.
- `My attendance` at `/attendance/my-attendance`
  - The employee’s attendance space.
- `Operational review` at `/attendance/operational-review`
  - The scoped review queue for exceptions, decisions, and operational follow-up.
- `Admin setup` at `/attendance/admin-setup`
  - The attendance administration workspace.

### The My Attendance sections

- `History`
  - Shows the personal attendance ledger.
- `Correction requests`
  - Shows submitted corrections and their status.
- `Check in / out`
  - Handles current-day capture behavior.

### The Admin Setup sections

- `Policy`
  - Defines how attendance is evaluated.
- `Holiday calendars`
  - Holds non-working-day and holiday setup.
- `Shifts`
  - Defines shift patterns.
- `Assignments`
  - Controls who inherits which shift and when.
- `Rosters`
  - Handles more specific scheduled work dates.

### What a customer should understand

Attendance is not just a punch-in screen.

It is a governed operational system that turns daily work behavior into payroll-ready state.

### How this area connects to the rest of the product

- It depends on employee and organization data.
- It reacts to approved leave.
- It feeds payroll.
- It feeds reporting.

### How to show this area to a customer

1. Start with Admin Setup to explain the rules.
2. Show shifts, assignments, and rosters.
3. Switch to My Attendance and show check-in, check-out, and correction flow.
4. Finish with Operational review to show manager and HR exception handling.

## 7.6 Leave

### What this module is

Leave manages policy-driven employee time off.

It combines:

- leave types and rules
- balance logic
- request submission
- approvals
- visible operational posture

### Why customers care

Customers need leave to be governed because time off affects:

- employee planning
- manager staffing decisions
- attendance accuracy
- payroll readiness

### Who uses it

- Employees
- Managers
- HR administrators

### Pages in this area

- `Overview` at `/leave/overview`
  - The leave operations center.
- `Requests` at `/leave/requests`
  - The employee-facing request and history area.
- `Approvals` at `/leave/approvals`
  - The manager and HR review queue.
- `Policy admin` at `/leave/policy-admin`
  - The policy and balance rule administration area.

### What a customer should understand

Leave is not a simple request form.

It is a governed balance and approval system that:

- checks policy
- checks overlap
- checks availability
- records approvals
- influences attendance and payroll

### How this area connects to the rest of the product

- It depends on employee hierarchy.
- It uses approvals and notifications.
- It can update attendance outcomes.
- It can influence payroll input and reporting.

### How to show this area to a customer

1. Start in Policy admin to explain the rules.
2. Move to Requests to show employee balance-aware requests.
3. Move to Approvals to show manager and HR decisioning.
4. Return to Overview to explain the operating picture.

## 7.7 Payroll and Compensation

### What this module is

Payroll and Compensation turns trusted upstream data into governed pay outcomes.

It includes:

- payroll calendars and periods
- salary configuration
- compensation assignment
- payroll preparation
- payroll review
- payroll run control
- payslip release

### Why customers care

Customers buy this area because they need payroll to be:

- accurate
- repeatable
- reviewable
- locked when finalized
- clear to employees after release

### Who uses it

- Payroll teams
- Finance and HR administrators
- Employees for self-service pay access

### Pages in this area

- `Overview` at `/payroll/overview`
  - The payroll posture page.
- `Setup` at `/payroll/setup`
  - The payroll administration studio.
- `Review` at `/payroll/review`
  - The exception, variance, and review page.
- `Run console` at `/payroll/run-console`
  - The operational control page for payroll runs.
- `My pay` at `/payroll/my-pay`
  - The employee self-service pay page.

### What Setup covers

- payroll calendars
- payroll periods
- salary components
- salary structures
- employee compensation assignments

### What Run Console covers

- preparation
- calculation
- approval
- lock
- reopen
- manual adjustments
- blocked and failed runs

### What a customer should understand

Payroll is not isolated from the rest of the product.

It depends on:

- employee structure
- effective compensation
- approved leave
- finalized attendance

### How to show this area to a customer

1. Start with Setup to explain how payroll is configured and versioned.
2. Move to Overview to show operational readiness.
3. Move to Review to show exception handling.
4. Move to Run Console to show calculation, approval, lock, and reopen behavior.
5. Finish in My Pay to show the employee-facing result.

## 7.8 Recruitment

### What this module is

Recruitment manages the journey from hiring demand to accepted offer and handoff into employee creation.

### Why customers care

Customers want hiring to be structured because:

- requisitions need approval
- pipelines need visibility
- interviews need coordination
- offers need history and control
- handoff to onboarding should not become manual chaos

### Who uses it

- Recruiters
- Hiring managers
- HR administrators
- Interviewers in scoped workflow roles

### Pages in this area

- `Overview` at `/recruitment/overview`
  - The hiring operations center.
- `Requisitions` at `/recruitment/requisitions`
  - Hiring demand and approval posture.
- `Candidates` at `/recruitment/candidates`
  - The candidate pipeline.
- `Candidate detail` at `/recruitment/candidates/:candidateId`
  - The record for resumes, stage movement, interviews, offers, and handoff.

### What a customer should understand

Recruitment does not stop at “offer accepted.”

It continues into a structured hire handoff so that employee creation and onboarding begin cleanly.

### How this area connects to the rest of the product

- Organization defines requisition context.
- Documents hold resumes and related files.
- Workflow governs approvals.
- Employee Management receives accepted hires.
- Onboarding can be triggered from hire handoff.

### How to show this area to a customer

1. Start with Requisitions to explain approval-governed demand.
2. Move to Candidates to show the pipeline.
3. Open Candidate detail to show resume versions, interviews, offers, and handoff.
4. Explain the final conversion into employee and onboarding work.

## 7.9 Performance

### What this module is

Performance manages structured goals, competencies, review cycles, review execution, calibration, and publication.

### Why customers care

Customers need this area to:

- standardize reviews
- improve goal clarity
- create talent visibility
- separate review stages clearly
- avoid ungoverned performance records

### Who uses it

- Employees
- Managers
- HR administrators
- Leadership reviewers

### Pages in this area

- `Overview` at `/performance/overview`
  - Performance posture and review pressure.
- `Goals` at `/performance/goals`
  - Goal configuration and visible goal posture.
- `Cycles` at `/performance/cycles`
  - Competency and review-cycle administration.
- `Reviews` at `/performance/reviews`
  - The review execution cockpit.

### What a customer should understand

Performance is modeled as a staged, governed process.

It is not only a yearly form. It is a controlled journey through:

- goal structure
- cycle structure
- self input
- manager input
- calibration
- final publication

### How this area connects to the rest of the product

- It uses employee hierarchy for scope and review visibility.
- It can later inform compensation and leadership decisions.
- It feeds reporting and talent visibility.

### How to show this area to a customer

1. Start at Overview for the big picture.
2. Move to Goals to explain structure and ownership.
3. Move to Cycles to explain review design.
4. Finish in Reviews to show the actual employee and manager experience.

## 7.10 Learning

### What this module is

Learning manages catalog items, assignments, target resolution, completion, evidence, renewal posture, and learner visibility.

### Why customers care

Customers need this area when they want:

- compliance training
- role-based learning assignments
- visible completion posture
- renewal tracking
- learner self-service

### Who uses it

- Learning administrators
- HR teams
- Managers with scoped visibility
- Employees

### Pages in this area

- `Overview` at `/learning/overview`
  - Learning posture, renewal pressure, and overdue visibility.
- `Catalog` at `/learning/catalog`
  - Learning item administration.
- `Assignments` at `/learning/assignments`
  - Assignment creation and target posture.
- `My learning` at `/learning/my-learning`
  - The employee learner workspace.

### What a customer should understand

Learning is treated as an operational system, not a loose content list.

The product tracks:

- who was assigned
- who is overdue
- who completed
- whether evidence is present
- whether renewal is approaching or overdue

### How this area connects to the rest of the product

- It depends on employee and organization scope.
- It can support performance and compliance visibility.
- It feeds reporting and selected AI summaries.

### How to show this area to a customer

1. Start in Catalog to explain what can be governed.
2. Move to Assignments to show target resolution.
3. Move to My learning to show the learner’s side of the experience.
4. Return to Overview to show how operations teams monitor posture.

## 7.11 Document Management

### What this module is

Document Management provides a governed document repository and controlled employee-facing document access.

### Why customers care

Customers want documents to be:

- secure
- organized
- scoped
- retention-aware
- usable in self-service and policy acknowledgement flows

### Who uses it

- HR teams
- Compliance and operations teams
- Employees through scoped self-service

### Pages in this area

- `Operations Documents` at `/operations/documents`
  - The operator-facing document governance page.
- `Employee documents` inside the Employees workspace
  - The employee-specific document posture view.
- `Self-Service Documents` at `/self-service/documents`
  - The employee-facing document and acknowledgement view.

### What a customer should understand

The same product supports:

- controlled repository management for operators
- controlled, narrower access for employees
- policy acknowledgement without overexposing the repository

### How this area connects to the rest of the product

- Employee Management uses protected employee documents.
- Policy acknowledgement uses repository documents as governed content.
- Self-Service exposes only the allowed subset.
- Reporting and audit can later consume document activity and posture.

### How to show this area to a customer

1. Open Operations Documents for the operator view.
2. Open an employee record and show employee-specific document visibility.
3. Open Self-Service Documents and show the narrower employee experience.

## 7.12 Asset Management

### What this module is

Asset Management tracks organizational equipment and asset responsibility across assignment, issue, and return.

### Why customers care

Customers need this area to:

- reduce asset loss
- make equipment ownership visible
- support onboarding and offboarding
- give employees clarity about what is assigned to them

### Who uses it

- IT and operations teams
- HR administrators
- Employees through scoped self-service

### Pages in this area

- `Operations Assets` at `/operations/assets`
  - The asset operations workspace.
- `Self-Service Assets` at `/self-service/assets`
  - The employee view of assigned assets.

### What a customer should understand

Assets are not just an inventory list.

They are part of:

- onboarding readiness
- offboarding clearance
- operational visibility
- employee responsibility

### How this area connects to the rest of the product

- It depends on employee records.
- It supports lifecycle operations and offboarding.
- It feeds operations overview and employee self-service.

### How to show this area to a customer

1. Start in Operations Assets to show the operator queue.
2. Explain assignment, issue, and return stages.
3. Move to Self-Service Assets to show the employee’s side of accountability.

## 7.13 Operations Control Tower

### What this module is

Operations is a control tower that brings operator-facing work into one route family.

It is not one single backend module. It is a business-facing operating surface that combines several control-plane capabilities.

### Why customers care

Customers often need a place where HR and IT operations can see high-risk or high-impact work together.

That includes:

- documents
- assets
- integrations
- lifecycle work
- release posture
- resilience posture
- observability posture

### Who uses it

- IT administrators
- HR operations teams
- Tenant administrators
- Release and operations stakeholders

### Pages in this area

- `Overview` at `/operations/overview`
  - The cross-operations posture page.
- `Documents` at `/operations/documents`
  - Document governance.
- `Assets` at `/operations/assets`
  - Asset lifecycle control.
- `Integrations` at `/operations/integrations`
  - Integration operations and sync-job handling.
- `Release` at `/operations/release`
  - Release engineering quality-gate posture.
- `Readiness` at `/operations/readiness`
  - Go-live readiness and decision support.
- `Observability` at `/operations/observability`
  - Operational telemetry and alert posture.
- `Resilience` at `/operations/resilience`
  - Backup, restore, and recovery readiness.
- `Lifecycle` at `/operations/lifecycle`
  - Operational onboarding and offboarding work.

### What a customer should understand

Operations answers a simple business need:

“Can our teams run the product and the company safely?”

It helps customers see that PhoenixHRMS supports not only HR transactions but also operational readiness and controlled launch posture.

### How to show this area to a customer

1. Start with Operations Overview.
2. Walk through Documents, Assets, and Lifecycle as people-operations stories.
3. Walk through Integrations, Release, Observability, and Resilience as platform-operations stories.
4. Finish in Readiness to show launch governance.

## 7.14 Reporting and Analytics

### What this module is

Reporting turns governed business state into dashboards, governed exploration, exports, saved views, and recurring delivery.

### Why customers care

Customers care because leadership and operations teams need trusted visibility, not disconnected report files.

This area provides:

- dashboard views
- governed exploration
- exports
- subscriptions
- role-aware visibility

### Who uses it

- HR administrators
- Managers
- Payroll teams
- Recruiters
- Leadership
- Analysts

### Pages in this area

- `Overview` at `/reporting/overview`
  - The reporting command center.
- `Explorer` at `/reporting/explorer`
  - Governed report exploration.
- `Exports` at `/reporting/exports`
  - Report export posture and queue.
- `Subscriptions` at `/reporting/subscriptions`
  - Recurring report delivery.
- `Workforce` at `/reporting/workforce`
  - HR workforce dashboard.
- `Team` at `/reporting/team`
  - Team-scoped manager dashboard.
- `Payroll` at `/reporting/payroll`
  - Payroll posture dashboard.
- `Recruitment` at `/reporting/recruitment`
  - Recruitment posture dashboard.
- `Executive` at `/reporting/executive`
  - Leadership operating dashboard.

### What a customer should understand

Reporting is a governed consumer of business truth.

It is not meant to be a free-form reporting free-for-all.

Its value is that it reads from trusted records created earlier in the product and keeps visibility aligned with role and scope.

### How this area connects to the rest of the product

Reporting depends on data from:

- employees
- attendance
- leave
- payroll
- recruitment
- performance
- learning
- operations-related modules

### How to show this area to a customer

1. Start with Reporting Overview.
2. Open the dashboards most relevant to the audience you are speaking to.
3. Show Explorer to explain governed data exploration.
4. Show Exports and Subscriptions to explain practical operational consumption.

## 7.15 AI Assistant

### What this module is

The AI Assistant is a governed copilot for question answering and review-only recommendations.

### Why customers care

Customers want AI to be useful without becoming unsafe.

This module helps by:

- answering supported workforce questions
- giving citations
- keeping recommendation flows review-only
- recording feedback and decision history

### Who uses it

- Employees
- Managers
- HR teams
- Recruiters
- Payroll users
- Leadership users with permitted access

### Page in this area

- `Assistant` at `/assistant`
  - The governed AI workspace with cited answers, recent history, review analytics, and recommendation handling.

### What a customer should understand

The current AI Assistant is intentionally controlled.

It is not a free-running automation layer.

It is designed to:

- answer selected supported questions
- explain its sources
- suggest review-only next steps
- require human decisions when recommendations are involved

### How this area connects to the rest of the product

AI depends on governed access to business data from earlier modules.

It is downstream by design.

### How to show this area to a customer

1. Start by explaining the governance posture.
2. Ask a supported question.
3. Show the citations and answer history.
4. Show a recommendation scenario and emphasize human approval.

## 7.16 Globalization and Localization

### What this module is

Localization makes the product usable across regional settings.

It controls how:

- dates appear
- times appear
- currency appears
- locale behavior is applied
- tenant defaults and user overrides interact

### Why customers care

Customers need this because enterprise workforce tools fail quickly when everyone is forced into one formatting model.

### Who uses it

- Tenant administrators who set defaults
- Employees who adjust personal regional preferences
- Any user who reads time, date, or currency values

### Where it appears

Localization is cross-cutting, but its clearest employee-facing page is:

- `Self-Service Profile` at `/self-service/profile`

### What a customer should understand

Localization is not just cosmetic formatting.

It supports:

- clearer self-service
- clearer payroll values
- clearer time and attendance interpretation
- cleaner multi-country growth readiness

### How to show this area to a customer

1. Open Self-Service Profile.
2. Show regional preferences.
3. Explain the difference between tenant defaults and user overrides.

## 7.17 Mobile readiness

### What this module is

Mobile readiness is the product’s preparation for a future mobile client.

### Current product reality

There is not yet a separate shipped mobile app package in this repository.

What exists today is the contract and platform groundwork that allows mobile experiences to be built against:

- secure sign-in
- self-service
- attendance
- leave
- payslips
- notifications
- localization

### What a customer should understand

PhoenixHRMS is mobile-aware and mobile-prepared, but a standalone mobile client is not the primary shipped artifact in this workspace today.

## 8. The business journeys that tie the product together

## 8.1 Sign-in to governed work

1. A user signs in.
2. The session is checked.
3. The tenant context is resolved.
4. Only visible workspaces are shown.
5. Sensitive actions remain role-aware and scoped.

Why this matters:

- the platform feels trustworthy from the first click

## 8.2 Hire to onboard

1. A requisition is created and approved.
2. Candidates move through the pipeline.
3. Interviews and offers are managed.
4. An accepted offer becomes a structured hire handoff.
5. Employee creation begins.
6. Onboarding tasks, documents, assets, and policies can now be coordinated.

Why this matters:

- recruitment becomes an entry point into workforce operations, not a disconnected ending

## 8.3 Day worked to payslip

1. The employee exists in the right structure.
2. Attendance rules are configured.
3. The employee checks in and checks out.
4. Attendance outcomes are calculated.
5. Approved leave adjusts or explains attendance where needed.
6. Payroll reads governed inputs.
7. Payroll is prepared, reviewed, approved, and locked.
8. Payslips are released to employees.

Why this matters:

- the product turns daily truth into payroll truth

## 8.4 Leave to payroll

1. Leave policies define eligibility and balance behavior.
2. The employee submits leave.
3. Managers and HR review it.
4. Approved leave affects workforce posture and attendance behavior.
5. Payroll receives the approved state later in the process.

Why this matters:

- time-off decisions are not isolated from payroll and operations

## 8.5 Growth, review, and compliance

1. Learning assigns required development or compliance work.
2. Employees complete learning.
3. Performance cycles and reviews happen in structured stages.
4. Managers and HR gain clearer talent visibility.
5. Reporting and AI consume the governed outcomes later.

Why this matters:

- talent growth and compliance become measurable, not anecdotal

## 8.6 Safe operations and launch readiness

1. Documents, assets, and lifecycle work are tracked.
2. Integrations move data in and out.
3. Observability shows operational pressure.
4. Resilience shows recovery readiness.
5. Release and readiness pages support controlled go-live decisions.

Why this matters:

- the product supports enterprise operations, not only HR transactions

## 9. How to demo PhoenixHRMS well

## 9.1 The best general demo flow

1. Start with Sign In or Foundation.
2. Explain role-aware access and workspace visibility.
3. Show Organization and Employees to establish the system of record.
4. Show Attendance, Leave, and Payroll as the operational core.
5. Show Recruitment, Performance, and Learning as the talent layer.
6. Show Operations as the control tower.
7. Show Reporting and AI last, because they make the most sense after the audience understands the governed upstream modules.

## 9.2 Best demo flow for HR leaders

Start here:

- Foundation
- Organization
- Employees
- Leave
- Performance
- Learning
- Reporting Workforce

Core message:

- PhoenixHRMS gives HR one governed operating system instead of disconnected tools.

## 9.3 Best demo flow for employees

Start here:

- Sign In
- Self-Service Profile
- Self-Service Documents
- Self-Service Assets
- My Attendance
- Leave Requests
- My Pay
- My Learning

Core message:

- employees get transparency and self-service without overexposure

## 9.4 Best demo flow for managers

Start here:

- Foundation
- Team-scoped employee visibility
- Attendance Operational Review
- Leave Approvals
- Performance Reviews
- Reporting Team dashboard

Core message:

- managers can act on visible team operations in one controlled environment

## 9.5 Best demo flow for payroll and finance teams

Start here:

- Organization defaults
- Employee compensation and payroll setup
- Attendance and leave as payroll inputs
- Payroll Overview
- Payroll Review
- Payroll Run Console
- Payroll dashboard and employee payslip view

Core message:

- payroll is based on governed upstream truth and repeatable control states

## 9.6 Best demo flow for recruiters

Start here:

- Recruitment Overview
- Requisitions
- Candidates
- Candidate detail
- Hire handoff
- Employee onboarding tie-in

Core message:

- recruitment is connected to the rest of the workforce lifecycle

## 9.7 Best demo flow for IT and operations teams

Start here:

- Access
- Operations Overview
- Documents
- Assets
- Integrations
- Observability
- Resilience
- Release
- Readiness

Core message:

- the platform supports controlled enterprise operations, not only HR administration

## 10. What is live today, and what is intentionally limited

### 10.1 What is live today

The product already provides real value today across:

- secure sign-in and recovery
- governed access administration
- organization and employee management
- self-service
- attendance
- leave
- payroll and compensation
- recruitment
- performance
- learning
- documents
- assets
- integrations
- operations control
- reporting
- governed AI assistance
- localization-aware behavior

### 10.2 What is intentionally controlled or narrower today

- The AI Assistant is governed and limited to supported answer and recommendation scenarios rather than broad autonomous action.
- Mobile readiness exists, but a separate mobile app package is not the main shipped artifact in this repository today.
- Some platform capabilities such as workflow, notifications, and audit are already deeply active in the product, but they are experienced mostly through embedded module flows rather than separate standalone end-user workspaces.
- Guided demo access is available for product storytelling, but its availability can depend on how the environment is configured.

## 11. The rule for keeping this document true

This document should be updated whenever any of the following change:

- a new business module is added
- a new routed page is added
- a page is repurposed
- a customer-facing workflow changes
- access rules change in a way that changes how the product is explained
- a module moves from planned to live
- a major cross-module dependency changes

The product bible should stay readable for non-technical audiences.

Technical detail belongs in the companion [Technical Guide](./09-technical-guide.md), not here.
