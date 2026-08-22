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
