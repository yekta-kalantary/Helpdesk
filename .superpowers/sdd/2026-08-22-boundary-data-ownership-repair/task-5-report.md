# Task 5 Report: Refactor Tasks to Consume Contracts and Events

## Status

Implemented and committed as Task 5 only. The pre-existing `package-lock.json` modification was not staged.

## TDD Evidence

1. Added `tests/Feature/TaskEventConsumerTest.php` before the consumers existed.
2. `php artisan test --compact tests/Feature/TaskEventConsumerTest.php` failed as expected: both consumer classes were missing.
3. Implemented idempotent membership-removal and status-change consumers with `ProcessedIntegrationEventRepository` delivery claims.
4. The same consumer test passed: 2 tests, 5 assertions.
5. `php artisan test --compact tests/Feature/Mvp/EmployeeAuthorizationTest.php tests/Feature/TaskEventConsumerTest.php` passed before the later parallel test-database race: 5 tests, 12 assertions.

## Boundary Changes

- Added task-owned project-state query and writer contracts, immutable task context DTOs, and the `ProjectTaskState` adapter.
- `ProjectTaskState` consumes only `ProjectMembershipDirectory` and owns task-row assignment and completion mutations.
- Consumers claim an event before mutating task state, so duplicate delivery is harmless.
- Removed `User`, `Project`, `ProjectTaskStatus`, and `WorkGroup` imports and relations from Task-owned models.
- Removed model-level external state validation. `TaskWorkflow` now validates account, project, membership, status, and work-group facts through public Identity, Clients, and Projects contracts.
- Preserved scalar persistence references and immutable task reference/project guards.

## Verification

- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed before the final documentation-only report edit.
- `php artisan test --compact tests/Feature/Mvp/EmployeeAuthorizationTest.php tests/Feature/TaskEventConsumerTest.php`: passed before the test-database race.
- `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php`: before the race, failed only at the known Task 4 ledger limitation: the inventory still expects a removed `clients` violation.
- A later parallel execution of two Laravel feature test commands raced the shared `helpdesk_testing` database and dropped the migration table. Attempts to rebuild it were blocked because the non-test `helpdesk` database account is denied. No source failure was observed after the passing narrow run, but final reruns could not complete in the damaged local test database.

## Concerns

- `TaskCollaboration`, `TaskChecklist`, and `TaskNotificationRouter` still use the legacy Task external relations. Their complete contract refactor is outside the explicit Task 5 file list but will need follow-up before those flows can run against the relation-free Task model.
- The architecture inventory remains intentionally stale until the later boundary-verification task updates its expected violations.

## Fix Round 1

### Findings Addressed

1. Eliminated every remaining `Modules\Identity\Infrastructure\Models\User` import from Tasks application code. `TaskCollaboration`, `TaskChecklist`, and `TaskNotificationRouter` now accept scalar account IDs; account facts come from `AccountDirectory` and role checks through `AccountAuthenticationEligibility`.
2. Added task-owned `TaskAccess` (`canAccess`, `canAccessTaskId`, `assertMutable`, `assertAdmin`) consuming Identity and Projects public contracts. Replaced all removed Task APIs: `visibleTo` in `TaskCollaboration`, `TaskChecklist`, root `TaskPolicy`, root `AttachmentPolicy`; `project()`/client reads in collaboration/checklist mutability checks; `isTerminal()`/`isDone()` via active-status lookup plus task `completed_at`; router audience now uses scalar `created_by`/`assigned_to`. Authorization behavior preserved (admin bypass, membership requirement, customer same-client rule, read-only done tasks/closed projects).
3. Wired production event delivery: `OutboxRecorder` now schedules each recorded event on the existing `AfterCommitOutboxDispatcher`; `TasksServiceProvider` registers typed listeners so `ProjectMembershipRemovedV1` and `ProjectTaskStatusChangedV1` reach the Tasks consumers after commit. Consumers wrap claim + task mutation in a single database transaction, so a failed mutation rolls back the claim and allows retry.
4. Restored create/update/status-change activity recording and notifications inside `TaskWorkflow` using `ActivityRecorder::recordIds` and `NotificationDispatcher::sendToAccountIds` (Audit/Notifications paths), including assignee/status/work-group/priority/due-date change records and completed/reopened records. `CustomerAssignmentRequeuer` was repaired for the removed relations (open-task filter via `completed_at`, project locking by ID, admin recipients queried at composition root) since it referenced removed Task APIs.
5. Excluded `.env.testing` and `package-lock.json` from the commit.

### TDD Evidence

1. New failing tests first: outbox delivery test failed with assigned member not unassigned (event never reached consumer); activity test failed with missing `task.created`/`task.status_changed` rows; policy test errored on removed `Builder::visibleTo()`; checklist test errored on `User` type-hint receiving int.
2. After implementation: focused suites passed — `php artisan test --compact tests/Feature/Mvp/EmployeeAuthorizationTest.php tests/Feature/TaskEventConsumerTest.php`: 9 passed, 22 assertions (3.1s).
3. Full suite: `php artisan test --compact`: 104 tests, 103 passed, 710 assertions; sole failure is `ArchitectureBoundariesTest` inventory assertion, which still requires non-empty violations containing the already-eliminated `clients` entry. All cross-context infrastructure imports are now gone, making this pre-existing characterization assertion obsolete by design until the later boundary-verification task inverts it.

### Verification

- `vendor/bin/pint --dirty --format agent`: passed (fixed import order in `TasksServiceProvider`, style fixes in `TaskCollaboration`).
- `git diff --check`: passed.
- Source scan found no remaining `visibleTo`, `isTerminal`, `projectStatus`/`creator`/`assignee` relation calls, or Identity infrastructure imports under `app-modules/tasks/src` (remaining grep hits are the legacy-upgrade morph-map script and the private `workGroup()` helper method name).
- Committed only the 16 staged Task 5 files; working tree left with untouched `package-lock.json` and untracked `.env.testing`.

### Fix Commit

Task 5 fix-round cohesive commit: `Complete task boundary event wiring` (`acdf778`).
