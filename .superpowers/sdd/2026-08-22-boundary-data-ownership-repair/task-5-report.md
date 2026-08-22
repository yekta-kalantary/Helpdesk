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
