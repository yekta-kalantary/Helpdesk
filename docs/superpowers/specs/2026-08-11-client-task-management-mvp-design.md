# Client Task Management MVP — Technical Design

Date: 2026-08-11

## Source of truth

`docs/product/client-task-management-mvp.md` is authoritative. RISE and Worksuite research are implementation references only. If existing behavior conflicts with the PRD, the implementation changes and the PRD does not.

## Current state

The application is a Laravel 13.8 / PHP 8.4 / Livewire 4.3 modular monolith with three local modules: `identity`, `projects`, and `tasks`.

Current behavior is intentionally smaller than the new MVP PRD:

- users use `is_admin` and have no Client relationship;
- projects contain only title/description plus a destructive `project_user` pivot;
- tasks contain only project/title/description/`is_done`;
- customer task visibility is already project-membership based;
- customer task creation, task assignment, workflow statuses, comments, attachments, notifications, activity, Client management, and auditable membership lifecycle are absent;
- project and task UI currently exposes hard delete;
- list queries are unpaginated.

The existing modular boundaries, Eloquent-first implementation style, Livewire UI, authentication/session handling, and project-based visibility query pattern are retained where they remain correct.

## Design goals

1. Satisfy every MVP requirement without adding Post-MVP features.
2. Make authorization and tenant isolation explicit and server-side.
3. Keep assignment separate from visibility.
4. Keep task state invariants impossible to bypass through normal model writes.
5. Preserve lifecycle history instead of deleting operational records.
6. Use framework-native Laravel/Livewire mechanisms and avoid speculative abstractions.

## Architecture

The existing modular monolith remains. The MVP adds a small `Clients` module alongside `Identity`, `Projects`, and `Tasks` because Client is a first-class account concept and must not be conflated with the login identity.

Each module owns its models, migrations, Livewire screens, translations, and routes. Cross-module business rules use focused classes only where a rule spans multiple writes or needs a single enforcement point:

- `ProjectMembershipManager` owns add/remove/reactivate rules and audit writes.
- `TaskWorkflow` owns task creation/update/transition invariants that depend on status and assignee.
- `ActivityRecorder` owns sanitized audit recording.
- notification classes use Laravel Notifications; email delivery is queued so delivery failure cannot rollback the business transaction.

Authorization is implemented with Laravel policies registered centrally. Customer-facing list queries use `visibleTo(User $user)` scopes in addition to policies so hidden records never enter list/dashboard results.

## Data model

### clients

- `id`
- `name`
- `description` nullable
- `status`: `active|inactive`
- timestamps

No UI hard delete.

### users

- existing identity fields
- `client_id` nullable; required for Customer, null for Admin
- `role`: `admin|customer`
- `is_active`
- `last_login_at` nullable
- normalized lowercase `email`, globally unique
- password hash and framework authentication fields

`is_admin` is removed as a domain source of truth after migration/backfill. Role is the only role discriminator.

### projects

- `id`
- `client_id`
- `name`
- `description` nullable
- `status`: `active|completed`
- `start_date` nullable
- `due_date` nullable
- timestamps

`client_id` is immutable after creation. No archive and no hard delete.

### project_user

One historical row per `(project_id, user_id)`:

- `project_id`
- `user_id`
- `joined_at`
- `removed_at` nullable
- timestamps
- unique `(project_id, user_id)`

Active membership means `removed_at IS NULL`. Re-add reactivates the existing row and refreshes `joined_at`; it never creates a duplicate historical relationship.

### tasks

- `id`
- `reference`: unique immutable human-facing reference
- `project_id`: immutable
- `created_by`
- `assigned_to` nullable only where the state permits it
- `title`
- `description` nullable
- `status`: `todo|in_progress|waiting_admin|waiting_customer|completed|cancelled`
- `priority`: `low|normal|high`
- `due_date` nullable
- `completed_at` nullable
- timestamps

Reference format is `TSK-` plus a short uppercase random token. It is generated once and is not derived from the database primary key as a product concept.

### task_comments

- `id`
- `task_id`
- `user_id`
- `body` nullable only when the comment has an attachment
- `hidden_at` nullable
- `hidden_by` nullable
- timestamps

Comments are chronological, non-threaded, and non-editable.

### attachments

- `id`
- `task_id`
- `comment_id` nullable
- `uploaded_by`
- `original_name`
- `storage_path`
- `mime_type`
- `size`
- `hidden_at` nullable
- `hidden_by` nullable
- timestamps

Files are stored on the private local disk and can only be downloaded through an authorized controller. Default max file size is 20 MB and is configuration-driven. Extension, MIME type, size, and authorization are validated server-side.

### activities

- `id`
- `actor_id` nullable for system-originated changes
- `project_id` nullable
- `task_id` nullable
- `action`
- `metadata` JSON
- timestamp

Metadata contains only safe old/new operational values and never credentials, tokens, password values, or authentication secrets.

### notifications

Use Laravel's standard database notification table for in-app delivery. Email is produced by queued notification delivery.

## Authorization model

### Admin

An active `role=admin` user can manage all MVP resources without Project Membership. No code assumes a specific Admin ID.

### Customer

A Customer must satisfy all of the following before project/task access is granted:

1. user is active;
2. role is Customer;
3. related Client exists and is active;
4. an active membership exists for the target Project;
5. the Project belongs to the same Client as the Customer.

Belonging to the same Client without Project Membership is insufficient.

### Task visibility

Visibility is derived from:

`Customer User -> active Project Membership -> Project -> Task`

`assigned_to` is never used as the main visibility boundary. A Project member can see all tasks in that Project.

### Assignment

- Customer assignee: active Customer, same Client as Project, active membership in that Project.
- Admin assignee: active Admin; no Project Membership required.
- inactive users cannot be new assignees.

### Attachments

Attachment access resolves the parent Task and authorizes the current user against that Task on every request. Storage URLs are not public.

## Task lifecycle invariants

The following invariants are checked server-side on every relevant task write:

- `Waiting Customer` requires a valid active Customer member assignee.
- `Waiting Admin` permits `assigned_to = null` or an active Admin only; a Customer assignee is cleared when returning to this state.
- `Todo` and `In Progress` require a valid active assignee.
- `Completed` sets `completed_at`.
- leaving `Completed` clears `completed_at`.
- `Cancelled` and `Completed` are read-only to Customer.
- Customer may change status only on a task assigned to them and only to `Todo`, `In Progress`, `Waiting Admin`, or `Completed`.
- Customer cannot change priority, assignee, project, or cancel a task.
- task `project_id` and `reference` are immutable.

Customer-created tasks are always created as:

- `status = waiting_admin`
- `priority = normal`
- `assigned_to = null`

This is the Admin Queue. All active Admin users are notified; no fixed user ID is used.

## Project lifecycle

Project statuses are only `active` and `completed`.

- only Admin can create/manage Projects and Memberships;
- a Project can only be created for an active Client;
- only active Customers from the same Client can be members;
- removing membership sets `removed_at` and immediately removes future access;
- completing a Project is rejected while any task is not `completed` or `cancelled`;
- a completed Project is read-only for Customer collaboration;
- Admin can reopen a completed Project;
- Project Client cannot change after creation.

## Client and user lifecycle

- Client can be active/inactive and is never hard-deleted from UI.
- inactivating a Client blocks login for all of its Customer users without deleting history.
- Customer has exactly one Client.
- Admin has no Client requirement.
- no public registration exists.
- Admin creates Customer users and sends account setup/password reset email.
- email is trimmed/lowercased before storage and unique globally, including inactive users.
- Customer can edit own name/password only; Client, role, status, and email are Admin-controlled.

## Collaboration

Task detail contains chronological comments and attachments. New collaboration is allowed only while both Project and Task are active/open. Admin can hide inappropriate comments/files without deleting their records; the hide operation creates Activity.

A comment requires either body text or at least one attachment. Customer-visible hidden content is omitted; Admin can see that hidden content exists and its audit trail.

## Activity and notifications

Activity is recorded for the PRD-required events:

- task created;
- assignee/status/priority/due date changes;
- complete/reopen/cancel;
- comment added;
- attachment added/hidden;
- membership added/removed;
- project status changed.

In-app notifications use Laravel database notifications. Email notifications use queued delivery. Recipients are filtered to active users, actors do not receive duplicate self-notifications, and resource authorization is always rechecked when a notification link is opened.

## UI

The existing Persian/RTL shell is retained and simplified around the PRD navigation.

Customer navigation: Dashboard, Projects, Tasks, Notifications.

Admin navigation: Dashboard, Clients, Users, Projects, Tasks, Notifications.

Critical lists use pagination. Task list supports title/reference search and Project/Status/Priority/Assignee/Overdue filters plus Updated/Due sorting. Customer task creation remains short and never exposes priority/assignee controls.

Desktop, tablet, and mobile use the same Livewire flows with responsive layouts; no Kanban, Gantt, calendar, chat, theme builder, or dashboard builder is added.

## Security decisions

- policies and scoped queries are both used for defense in depth;
- route model access is re-authorized at the backend;
- login/reset/upload operations use rate limiting;
- file delivery never uses public storage;
- file MIME, extension, and size are validated;
- standard Laravel CSRF/session/password hashing/output escaping remain enabled;
- operational mutations that also write activity/membership/task state use database transactions;
- sensitive values are excluded from Activity and Notification metadata.

## Testing strategy

Pest feature tests cover the PRD E2E scenarios plus negative authorization/invariant cases. Tests intentionally exercise server-side routes/components, not only hidden UI controls.

Required suites include:

- identity/client lifecycle and case-insensitive email uniqueness;
- project membership add/remove/reactivate and cross-client rejection;
- task visibility independent of assignment;
- every task state/assignee invariant;
- customer mutation restrictions;
- immutable task reference/project and immutable project Client;
- project completion guard;
- private attachment download isolation;
- comment/attachment closed-resource restrictions;
- activity and notification recipient behavior;
- E2E-001 through E2E-008.

The release gate is a fresh full test run, Pint check, frontend build, and requirement-by-requirement PRD review. Any residual gap must be reported explicitly rather than being called complete.
