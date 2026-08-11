# Client Task Management MVP — Implementation Verification

Date: 2026-08-11

Authoritative specification: `docs/product/client-task-management-mvp.md`

This document records the implementation decisions and requirement-by-requirement verification for the Client Task Management MVP. It does not change product scope. Where implementation details conflict with other research or legacy behavior, the PRD remains authoritative.

## 1. Implemented architecture

The implementation keeps the MVP domain intentionally small:

`Client → Customer User → Project → Project Membership → Task → Collaboration → Completion`

Primary boundaries:

- `Client` is a customer account and never an authentication identity.
- `User` is the authentication identity with exactly two system roles: `Admin` and `Customer`.
- A Customer User belongs to exactly one Client.
- A Project belongs to exactly one immutable Client.
- Customer Project visibility is granted only by active Project Membership.
- Task visibility is inherited from Project Membership and never from task assignment.
- Task assignment represents responsibility for the next action only.
- Comments, attachments, activity, and notifications remain contextual to the Project/Task domain.

Framework-native Laravel/Livewire patterns are used. No repository layer, generic workflow engine, custom permission builder, API abstraction, or speculative Post-MVP architecture was added.

## 2. Identity and Client requirements

| Requirement | Implementation | Automated evidence |
|---|---|---|
| Exactly two system roles: Admin / Customer | `UserRole` enum; no role builder | `IdentityClientTest`, `UserManagementTest` |
| Customer belongs to exactly one Client | User model invariant + required Client on Admin onboarding | `IdentityClientTest` |
| Admin does not belong to a Client | User model invariant | `IdentityClientTest` |
| Client lifecycle is Active / Inactive | `ClientStatus` enum and Admin Client UI | `IdentityClientTest`, `EndToEndMvpTest` E2E-006 |
| Inactive Customer cannot authenticate | `canAuthenticate()` + active-account middleware | `IdentityClientTest`, E2E-006 |
| Inactive Client blocks Customer authentication | `canAuthenticate()` checks Client status | `IdentityClientTest`, E2E-006 |
| Email is globally unique and case-insensitive | normalized lowercase email + DB uniqueness | `IdentityClientTest`, `UserManagementTest` |
| Inactive identities retain email reservation | no hard delete; same unique index remains | `IdentityClientTest` |
| No public registration | no registration route; Admin creates Customer Users | route verification + `UserManagementTest` |
| Customer setup/reset password flow | Laravel password broker + forgot/reset Livewire flows | `UserManagementTest` |
| Customer self-edit limited to name/password | dedicated Profile component | `UserManagementTest` |
| Customer cannot change Client or Role | Customer profile exposes neither; Admin edit also preserves both | `UserManagementTest` |
| Customer deactivation preserves task assignment invariants | open assignments are moved to Admin Queue atomically before deactivation | `UserDeactivationAssignmentIntegrityTest` |
| No operational hard delete | no delete UI/routes for Client/User | architecture review |

## 3. Project and Membership requirements

| Requirement | Implementation | Automated evidence |
|---|---|---|
| Project belongs to exactly one Client | `projects.client_id` FK | architecture/schema tests |
| Project Client immutable after creation | Project model guard + edit form does not allow change | project tests / form review |
| Project statuses only Active / Completed | `ProjectStatus` enum | `ProjectLifecycleTest` |
| Customer access requires active Membership | `Project::visibleTo()` | `ProjectMembershipTest`, `TaskProjectMembershipTest`, E2E-002 |
| Same Client alone does not grant Project access | Membership scope is mandatory | `TaskProjectMembershipTest`, E2E-002 |
| Membership has joined/removed lifecycle | `joined_at`, `removed_at` | `ProjectMembershipTest`, `CoreModulesTest` |
| Membership removal is non-destructive | `removed_at` is set; row retained | `ProjectMembershipTest`, E2E-005 |
| Reactivation reuses same row | existing row updated with new join lifecycle | `ProjectMembershipTest`, E2E-005 |
| Only active same-Client Customers may be members | `ProjectMembershipManager` validation | `ProjectMembershipTest` |
| Membership changes are auditable | Activity records for add/remove | `ActivityNotificationTest` |
| Removed member loses access immediately | visibility queries require `removed_at IS NULL` | E2E-005, `TaskProjectMembershipTest` |
| Removing an assigned Customer does not leave invalid Tasks | open assigned Tasks atomically become `Waiting Admin` with null assignee | `MembershipAssignmentIntegrityTest` |
| Terminal historical assignments are preserved | Completed/Cancelled Tasks are not rewritten on membership removal | `MembershipAssignmentIntegrityTest` |
| Project cannot complete with non-terminal Tasks | `ProjectLifecycle::complete()` guard | `ProjectLifecycleTest`, E2E-007 |
| Completed Project is Customer read-only | task creation/collaboration require active Project | `CollaborationFileSecurityTest`, E2E-007 |
| Admin can reopen Project | `ProjectLifecycle::reopen()` | `ProjectLifecycleTest`, E2E-007 |
| Project save + membership changes are atomic | eligibility checked before write; Project/member diff inside transaction | `ProjectFormAtomicityTest` |

## 4. Task requirements

Task statuses are exactly:

- `Todo`
- `In Progress`
- `Waiting Admin`
- `Waiting Customer`
- `Completed`
- `Cancelled`

Priorities are exactly `Low`, `Normal`, and `High`.

| Requirement | Implementation | Automated evidence |
|---|---|---|
| Every Task belongs to one Project | `tasks.project_id` FK | schema tests |
| Task Project immutable | model guard + edit form immutable Project | `TaskWorkflowTest`, E2E-008 |
| Stable human-readable Reference separate from PK | `TSK-XXXXXXXX`, unique DB column | `TaskWorkflowTest` |
| Reference immutable | direct update and mass-assignment guards | `TaskWorkflowTest`, E2E-008 |
| Customer-created Task enters Admin Queue | `Waiting Admin`, `Normal`, `assigned_to = null` | `TaskWorkflowTest`, E2E-003 |
| Multiple Admins supported | active Admin query; no hard-coded Admin ID | `ActivityNotificationTest` |
| Visibility is membership-based, not assignee-based | Task scope delegates to Project visibility | `TaskWorkflowTest`, `TaskProjectMembershipTest` |
| Waiting Customer requires valid active same-Client member | model state invariant | `TaskWorkflowTest`, E2E-008 |
| Waiting Admin cannot retain Customer assignee | workflow normalization + model invariant | `TaskWorkflowTest`, E2E-003 |
| Waiting Admin may be null or active Admin | state invariant | `TaskWorkflowTest` |
| Todo / In Progress require active assignee | state invariant | `TaskWorkflowTest`, E2E-008 |
| Completed sets `completed_at` | workflow | `TaskWorkflowTest`, E2E-003 |
| Reopen clears `completed_at` | workflow | `TaskWorkflowTest`, E2E-008 |
| Customer status changes require self-assignment | customer transition guard | `TaskWorkflowTest` |
| Customer cannot Cancel | allowed-customer-transition whitelist | E2E-008 |
| Customer cannot change Priority / Assignee / Project | customer form/workflow exposes no such mutation | `UserManagementTest` + workflow/route review |
| Admin can claim/assign Admin Queue Tasks | Admin form + workflow supports Admin assignee or null | workflow tests / UI review |
| Search by Reference/title | paginated Task index | feature/UI review |
| Project/Status/Priority/Assignee/Overdue filters | paginated Task index | feature/UI review |
| Updated/Due sorting | paginated Task index | feature/UI review |

## 5. Comments and attachments

| Requirement | Implementation | Automated evidence |
|---|---|---|
| Collaboration only inside Task context | `task_comments` / Task Show | schema/UI review |
| No general chat | not implemented | scope review |
| Comments chronological | ordered by ID ascending | model/query review |
| No nested threads | flat comment schema | schema review |
| No comment editing | no update route/action | route/UI review |
| Admin can hide content without deleting history | `hidden_at`, `hidden_by` | collaboration review |
| Attachment storage is private | Laravel `local` private disk + authenticated controller | `CollaborationFileSecurityTest`, E2E-004 |
| Direct file URL cannot bypass authorization | download controller re-authorizes parent Task | `CollaborationFileSecurityTest`, E2E-002/E2E-004 |
| Hidden Customer attachment access denied | download authorization | file-security tests / policy review |
| Size, extension, MIME validation | configurable 20 MB + allowlists | `CollaborationFileSecurityTest` |
| Upload rate limiting | per-user RateLimiter | implementation review |
| Failed DB write cleans stored file | exception cleanup | implementation review |
| Terminal Tasks / Completed Projects reject new collaboration | server-side collaboration guard | `CollaborationFileSecurityTest` |

## 6. Activity / audit requirements

`ActivityRecorder` stores actor, Project, Task, action, timestamp, and sanitized metadata.

Covered activity events include:

- Task created
- Assignee changed
- Status changed
- Priority changed
- Due Date changed
- Completed
- Reopened
- Cancelled
- Comment added
- Attachment added
- Comment/Attachment hidden
- Membership added/removed
- Project status changed

Sensitive metadata keys containing password/token/secret/credential are recursively excluded.

Automated evidence: `ActivityNotificationTest`, `ProjectLifecycleTest`, `MembershipAssignmentIntegrityTest`, `UserDeactivationAssignmentIntegrityTest`.

## 7. Notification requirements

| Requirement | Implementation | Automated evidence |
|---|---|---|
| Channels only In-app and Email | database + mail notification channels | notification tests |
| Queued delivery | notification implements `ShouldQueue` and `afterCommit()` | implementation review |
| Delivery failure does not rollback Task/Comment transaction | notification dispatch occurs after domain transaction and dispatcher isolates exceptions | implementation review |
| Inactive recipients excluded | dispatcher + active Admin queries | `ActivityNotificationTest` |
| Actor does not receive self-notification | dispatcher filters actor | implementation review |
| Customer-created Admin Queue task notifies active Admins | active Admin query | `ActivityNotificationTest` |
| Resource authorization is rechecked when notification is opened | notification center resolves resource through `visibleTo()` | `ActivityNotificationTest` |
| Removed Customer cannot use old notification to regain access | reauthorization returns 404 and notification remains unread | `ActivityNotificationTest` |
| No SMS/Slack/Telegram/push/websocket channel | not implemented | scope review |

## 8. Security / isolation verification

Backend authorization is enforced independently of UI visibility.

Defense layers:

1. Active-account middleware for authenticated routes.
2. `Project::visibleTo()` membership scope.
3. `Task::visibleTo()` inherited Project scope.
4. Policies for Client, Project, Task, Attachment.
5. Domain workflow guards for state transitions and assignment rules.
6. Authorized private file delivery.
7. Laravel CSRF/session/password hashing mechanisms.
8. Login/reset/upload rate limits where relevant.
9. Customer-facing route/component queries are scoped server-side.

Negative isolation coverage includes:

- Customer A cannot access Client B Project/Task/File.
- Same-Client non-member cannot access Project/Task.
- Removed member immediately loses access.
- Old notification cannot restore revoked access.
- Direct attachment URL cannot bypass Task authorization.
- Inactive User/Client cannot use authenticated Customer flows.
- Cross-Client Project members are rejected before any Project write.
- Waiting Customer cannot point to non-member Customer.
- Waiting Admin cannot retain Customer assignee.
- Todo/In Progress without valid assignee fails.
- Customer cannot Cancel or mutate Admin-only task fields.
- Task Reference/Project cannot be changed.

Automated evidence: E2E-002, E2E-004, E2E-005, E2E-006, E2E-008, `DashboardIsolationTest`, `ProjectFormAtomicityTest`, `MembershipAssignmentIntegrityTest`.

## 9. Dashboard and UI

- Persian-first RTL layout.
- Responsive navigation for mobile/desktop with viewport configuration and responsive Tailwind layouts.
- Customer navigation is limited to Dashboard, Projects, Tasks, Notifications, and Profile.
- Admin additionally receives Clients and Customer Users.
- Dashboard metrics and recent resources use the same authorization scopes as list/detail pages.
- Project and Task lists are paginated.
- Task list implements required search/filter/sort controls.
- No Kanban, Gantt, calendar, dashboard builder, general chat, workflow builder, custom theme builder, or complex analytics were added.

Automated isolation evidence: `DashboardTest`, `DashboardIsolationTest`.
Frontend verification is performed by the CI Vite build and existing font-asset checks. There is no separate browser screenshot/visual-regression suite in this MVP repository.

## 10. E2E acceptance scenarios

All PRD E2E scenarios are represented as named automated tests in `tests/Feature/Mvp/EndToEndMvpTest.php`:

| Scenario | Coverage |
|---|---|
| E2E-001 Onboarding | Client → Customer → Project → Membership → visible Task |
| E2E-002 Isolation | cross-Client Project/Task/File denial and search isolation |
| E2E-003 Customer Request | Customer request → Admin Queue → Admin work → Customer action → completion |
| E2E-004 File Security | member download allowed; non-member denied |
| E2E-005 Membership Removal | immediate access loss, retained row, same-row reactivation |
| E2E-006 Client Deactivation | Customer blocked, Admin history retained, reactivation restores access |
| E2E-007 Closed Project | open-task completion guard, Customer read-only, Admin reopen |
| E2E-008 Task State Integrity | assignment/status/completed-at/reference invariants |

Additional regression coverage addresses atomic Project membership validation, membership-removal assignment integrity, Customer deactivation assignment integrity, notification reauthorization, and dashboard isolation.

## 11. Database changes

MVP data model:

- `clients`
- `users` with `client_id`, `role`, `is_active`, `last_login_at`
- `projects` with immutable `client_id`, lifecycle status, optional dates
- `project_user` with `joined_at`, `removed_at`
- `tasks` with Reference, creator, assignee, status, priority, due/completed dates
- `task_comments`
- `attachments`
- `activities`
- Laravel `notifications`

Legacy `users.is_admin`, `projects.title`, and `tasks.is_done` semantics were migrated to the authoritative MVP model.

## 12. Automated verification

The GitHub Actions pipeline executes:

```bash
composer install --no-interaction --prefer-dist --no-progress
composer validate --no-check-publish
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```

It also verifies the Vite font assets and builds the deployable source archive.

Latest behavioral verification before this documentation-only commit:

- `php artisan test`: **62 passed, 217 assertions**
- migration + seed: passed
- route list: passed
- Blade view cache: passed
- Pint: passed
- frontend build: passed
- Vite font-asset verification: passed

A fresh CI run on the final PR head is required before merge/closure of this verification record.

## 13. Explicitly deferred Post-MVP scope

Not implemented by design:

- Leads / Sales Pipeline
- Contact Directory
- Proposal / Estimate / Contract
- Invoice / Payment
- HR / Attendance / Leave
- standalone Ticket/Helpdesk
- Knowledge Base
- Product / Order / Subscription
- Time Tracking
- Milestone / Gantt / Kanban / Calendar
- general Chat
- custom roles / permission builder
- custom fields / workflow automation
- AI
- public API / Webhook
- recurring Tasks
- Task dependencies / subtasks / private Tasks
- moving Tasks between Projects
- comment editing / nested comments
- SMS / Push / Slack / Telegram / realtime websocket

## 14. Remaining PRD gaps

No known functional or security requirement from `docs/product/client-task-management-mvp.md` is intentionally deferred inside the MVP scope.

The final completion claim is conditional on a fresh successful GitHub Actions run for the exact final PR head after this document is committed.
