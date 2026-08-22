# Task 4 Report: Make Projects the Sole Owner of Project State

## Status

Completed. This report is included in the Task 4 cohesive commit; its hash is reported with the commit result.

## TDD Evidence

1. Added the membership-removal outbox assertion before implementing the event record. `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php` failed as expected: 10 passed, 1 error because `OutboxMessage::sole()` found no `ProjectMembershipRemovedV1` record.
2. Added the Done-status outbox assertion before implementing its outbox record. The same command failed as expected: 11 passed, 1 error because no `ProjectTaskStatusChangedV1` record existed.
3. After implementation, `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php` passed: 12 tests, 35 assertions.

## Contract Bindings

- `ProjectMembershipDirectory` exposes project summaries, active membership checks, default and active task-status summaries, and active work-group summaries.
- `ProjectAccessQuery` exposes `canAccessProject(int $projectId, int $accountId): bool`.
- `EloquentProjectMembershipDirectory` is the only Projects adapter that queries Projects models and the Projects-owned `project_user` persistence.
- `EloquentProjectAccessQuery` receives `AccountDirectory`, `ClientStatusQuery`, and `ProjectMembershipDirectory` through constructor injection. It preserves active-admin access, customer same-client and active-client access, and employee active-membership access.
- `ProjectsServiceProvider` binds both Project contracts to their owner-local Eloquent adapters.
- `ProjectPolicy` consumes `ProjectAccessQuery` so Project authorization no longer depends on the removed `Project::visibleTo()` scope.

## Events

- `ProjectMembershipRemovedV1` is an immutable `IntegrationEvent` with scalar `project_id`, `account_id`, and `actor_id` payload facts. It is recorded inside the membership-removal transaction.
- `ProjectTaskStatusChangedV1` is an immutable `IntegrationEvent` with scalar `project_id`, `project_task_status_id`, `is_done`, and `actor_id` payload facts. It is recorded inside the Done-status transaction.

## Boundary Changes

- Removed Projects model imports and relations to Client, User, and Task: Project client/member/task relations, ProjectTaskStatus task/creator relations, and WorkGroup task/creator relations.
- Replaced Projects application service actor and member model arguments with scalar account IDs. Account facts now come from `AccountDirectory`; client activity comes from `ClientStatusQuery`.
- Project-owned membership rows remain persisted through `project_user` queries in Projects infrastructure.

## Verification

- `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php`: passed, 12 tests and 35 assertions.
- `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php`: failed as an expected ledger/test-inventory limitation, 2 passed and 1 failed. The inventory still requires `clients` and `identity` violations after Task 3 removed them; its first missing expected module is `clients`. Projects has no cross-context infrastructure imports.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.

## Behavior Caveats

- Task reassignment and task completion synchronization are now represented by durable integration events. Task consumers are intentionally deferred to Task 5, so this task does not mutate Task-owned records.
- Audit and notification side effects formerly coupled to Projects were removed from these application services; their event-driven ownership is deferred to Task 6.
- Project status and work-group inactivation no longer inspect Task-owned records. Task 5 must own validation or consumer behavior for those task-state constraints.
- The existing `tests/Feature/ArchitectureBoundariesTest.php` inventory assertion must be updated in a later boundary-verification task to expect only remaining violating modules.

## Commit

Task 4 cohesive commit: `Make Projects sole owner of project state`.

## Fix Round 1

### Findings Addressed

1. `ProjectTaskStatusChangedV1` now includes nullable scalar `previous_done_status_id`. `ProjectWorkflowManager::setDone()` records the prior Done status ID before it changes, allowing the future Task consumer to reopen tasks for that prior status.
2. `CoreModulesTest` and `EmployeeAuthorizationTest` now pass scalar account IDs to `ProjectMembershipManager`. Membership assertions use `ProjectMembershipDirectory::hasActiveMembership()`. Task assignment and employee project-access checks use that same public contract rather than the removed `Project::hasActiveMember()` model method.
3. `ProjectCreator` owns Project creation and default workflow status initialization. It receives account facts through `AccountDirectory`, requires an active admin creator, and persists that creator ID on every default status. `Project::booted()` now retains only the client immutability guard.
4. `ProjectPolicy` moved to `Modules\Projects\Presentation\Policies` and is registered by `ProjectsServiceProvider`. The root provider and root policy file no longer own Project authorization. The module policy consumes Identity public application contracts and does not import Identity infrastructure.

### TDD Evidence

1. Before the fix, `php artisan test --compact tests/Feature/CoreModulesTest.php tests/Feature/Mvp/EmployeeAuthorizationTest.php` failed: 0 passed, 4 errors from object arguments passed to the scalar membership API, and 1 failure because the expected authorization exception was a `TypeError`.
2. Added regressions for the prior Done-status payload ID, application-level creator status ownership, and module-owned policy registration. `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php` failed as expected: 11 passed, 1 missing payload key assertion, and 2 missing-class errors.
3. After the implementation and Composer autoload regeneration, `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php tests/Feature/CoreModulesTest.php tests/Feature/Mvp/EmployeeAuthorizationTest.php` passed: 19 tests and 52 assertions.

### Verification

- Source scans found no `Project::hasActiveMember()` usage, no object-based Project membership manager calls, no `auth()` or `static::created()` usage in the Project model, and no cross-context infrastructure imports in Projects source.
- `vendor/bin/pint --dirty --format agent` completed successfully.
- `git diff --check` completed successfully.

### Fix Commit

Task 4 fix-round cohesive commit: `Repair project ownership boundaries`.
