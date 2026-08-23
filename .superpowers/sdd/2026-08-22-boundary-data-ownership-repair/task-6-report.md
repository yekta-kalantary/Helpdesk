# Task 6 Report: Move Feature Ownership Out of Root Application

## Status

Implemented and committed as Task 6 only. The pre-existing `package-lock.json` modification and untracked `.env.testing` were not staged.

## TDD Evidence

1. Added `tests/Feature/ModuleRegistrationTest.php` before any class was moved. It asserts `Gate::getPolicyFor()` resolves Client, Project, Task, and Attachment to their module-owned policy classes and that the `account.active` alias maps to the Identity-owned middleware.
2. `php artisan test --compact tests/Feature/ModuleRegistrationTest.php` failed as expected: the module policy classes did not exist (`Modules\Clients\Presentation\Policies\ClientPolicy` missing) and the alias still pointed at `App\Http\Middleware\EnsureAccountActive`.
3. Moved the policies into Clients/Tasks presentation layers, moved the middleware into Identity, updated `bootstrap/app.php`, and registered policies from `ClientsServiceProvider` / `TasksServiceProvider`. The same test then passed.
4. Focused verification per plan Step 5: `php artisan test --compact tests/Feature/ModuleRegistrationTest.php tests/Feature/IdentityLoginTest.php tests/Feature/ArchitectureBoundariesTest.php`: 12 tests, 12 passed, 86 assertions.

## Boundary Changes

- `ClientPolicy` → `app-modules/clients/src/Presentation/Policies/ClientPolicy.php`; `TaskPolicy` + `AttachmentPolicy` → `app-modules/tasks/src/Presentation/Policies/`; each registered via `Gate::policy()` in its own module provider. Authorization decisions preserved: admin bypass replicated through `AccountDirectory` summaries (`isActive && role === Admin`), eligibility/membership checks unchanged, following the ProjectPolicy pattern from Task 4.
- `EnsureAccountActive` → `Modules\Identity\Presentation\Http\Middleware\EnsureAccountActive` with identical handle logic; the `account.active` alias is registered from the composition root (`bootstrap/app.php`).
- `Activity` → `Modules\Audit\Infrastructure\Models\Activity` (same fillable/casts/scope); its cross-module `actor()`/`project()`/`task()` relations were dropped because Audit must not import feature models (no production consumer used them).
- `ResourceChangedNotification` → `Modules\Notifications\Infrastructure\Notifications\ResourceChangedNotification` (message content, channels, queueing, and afterCommit behavior untouched).
- `ActivityRecorder` → `Modules\Audit\Application\ActivityRecorder`, keeping only the scalar `recordIds` API plus metadata sanitization (the unused object-based `record()` required feature-model imports).
- `NotificationDispatcher` → `Modules\Notifications\Application\NotificationDispatcher` keeping the scalar `sendToAccountIds` API (active filter, actor exclusion, dedupe, per-recipient failure logging preserved). Recipient resolution moved behind a Notifications-owned `NotifiableDirectory` contract whose root technical adapter (`App\Integration\Notifications\EloquentNotifiableDirectory`) resolves active User models, so Laravel's real notify pipeline (database morph types, queued mail) is unchanged while the Notifications module never imports `User`.
- Added `ResourceChangedNotificationFactory` contract + default implementation so Tasks consumers build notifications without importing Notifications infrastructure.
- `CustomerAssignmentRequeuer` → `Modules\Tasks\Application\CustomerAssignmentRequeuer`, refactored to scalar IDs: role checks via `AccountDirectory`, project row locking via a new additive `ProjectMembershipDirectory::findProjectForUpdate()`, admin audience via new additive `AccountDirectory::activeAdministratorIds()`. No Projects→Tasks dependency introduced.
- Root `app/` now contains only framework composition and technical integration: providers, outbox/integration glue, Inertia middleware, base/dashboard controllers. `AppServiceProvider` retains only the `NotifiableDirectory` binding.
- `tests/Unit/LegacyUpgradeTest.php` already referenced module migration paths (Task 2); no legacy path updates were needed there.

## Verification

- `vendor/bin/pint --dirty --format agent`: passed (import ordering/style fixes in `TaskWorkflow`, `TaskCollaboration` imports, dispatcher, provider).
- `git diff --check`: passed.
- Source scan confirmed zero remaining references to `App\Support`, `App\Notifications\*`, `App\Models\Activity`, `App\Policies\*`, or `App\Http\Middleware\EnsureAccountActive`, and no module importing another module's notification infrastructure.
- Full suite after Pint: `php artisan test --compact`: 107 tests, 107 passed, 720 assertions.

## Concerns

- The obsolete ArchitectureBoundariesTest inventory assertion ("before the boundary migration", expecting non-empty violations) was inverted into enforcement: modules must have zero cross-context `\Infrastructure\` imports, and the root application must not contain `app/Policies`, `app/Support`, `app/Notifications`, `app/Models/Activity.php`, or the old middleware path. This resolved the known-stale test left by Task 5 so Step 5 of this task passes.
- `AccountDirectory::activeAdministratorIds()` and `ProjectMembershipDirectory::findProjectForUpdate()` are additive public-contract extensions consumed by the relocated requeuer; Task 7's composer-manifest pruning should reflect them.
