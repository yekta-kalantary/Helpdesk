# MVP PRD Review Design — 2026-08-11

## Goal

Review the existing Client Task Management MVP PRD against the current RISE CRM and Worksuite research without expanding MVP scope unnecessarily.

## Inputs

- `docs/product/client-task-management-mvp.md`
- `docs/research/rise-crm-product-analysis.md`
- `docs/research/rise-crm-revalidation-2026-08-11.md`
- `docs/research/worksuite-product-analysis.md`
- `docs/research/worksuite-revalidation-2026-08-11.md`

## Approaches considered

1. **Patch-only** — change only incorrect lines. Lowest churn, but leaves structural ambiguity.
2. **Full rewrite** — regenerate the PRD from scratch. Cleanest text, but destroys useful requirement continuity and makes Git history harder to audit.
3. **Controlled revision** — preserve the current domain structure and requirement IDs where possible, remove contradictions, add only missing invariants, and explicitly keep reference-product features out of scope.

Chosen approach: **Controlled revision**.

## Decisions

### Client and login identity

Keep `Client` as the customer account boundary and `Customer User` as the login identity attached directly to a Client. Do not add a standalone Contact entity in MVP. This preserves the useful Client-vs-person boundary observed in RISE and Worksuite without introducing CRM scope.

### Membership as the access boundary

Project Membership remains the only source of Customer project/task visibility. Membership removal must be auditable through `Joined At` / `Removed At`; re-adding a user reactivates the same logical membership instead of creating duplicate history.

### Project lifecycle

Reduce MVP project states to `Active` and `Completed`. Project Archive is not required for the core collaboration loop and moves to Post-MVP. Completing a project requires all tasks to already be terminal (`Completed` or `Cancelled`).

### Admin queue instead of a fixed admin

The old PRD implicitly assumed a single Admin when Customer-created tasks were auto-assigned. Replace that assumption with an `Admin Queue` represented by `Status=Waiting Admin` and `Assignee=null`. Any active Admin can claim/assign the task. Domain logic must not depend on a hard-coded Admin ID.

### Task state invariants

- `Waiting Customer` requires an active Customer member as Assignee.
- `Waiting Admin` cannot have a Customer Assignee and may be unassigned.
- `Todo` and `In Progress` require a valid active Assignee.
- Customer handoff back to Admin clears the Customer Assignee and returns the task to Admin Queue.

### Stable task reference

Add a human-readable, unique, immutable Task Reference that is conceptually separate from the internal database primary key. Exact formatting belongs to Technical Design.

### Identity uniqueness

User Email must be globally unique and case-insensitive, including inactive users. Deactivation must not release an email for reuse.

### Collaboration immutability

Do not add comment editing to MVP. Comments remain immutable after creation; corrections use a new comment. Admin can hide inappropriate content while preserving audit history.

### Privacy

Customer-visible project member lists expose only collaboration-safe fields such as display name. Email, mobile and private profile fields of other users are not exposed.

## Explicitly not added to MVP

Project Archive, Contact Directory, Task Dependency, Time Tracking, Kanban, Gantt, Ticket/Helpdesk, general Chat, AI, custom roles, plugins/add-ons, recurring tasks and other reference-product features remain outside MVP unless the PRD is changed explicitly later.

## Acceptance impact

The existing E2E scenarios remain eight in count, but are tightened to test:

- Admin Queue behavior without a fixed Admin ID
- membership removal/reactivation with history preservation
- project completion only after all tasks are terminal
- Waiting Admin / Waiting Customer assignment invariants
- stable Task Reference
- global Email uniqueness

## Self-review

- No TBD/TODO placeholders.
- No reference-product feature is promoted into MVP implicitly.
- Client, User, Membership, Assignment and Visibility have distinct responsibilities.
- The revised state model has deterministic assignment invariants.
- Scope remains focused on Client → Project → Membership → Task → Collaboration → Completion.
