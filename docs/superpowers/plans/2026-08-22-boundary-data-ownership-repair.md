# Boundary and Data Ownership Repair Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert Helpdesk into an enterprise-ready modular monolith with explicit data owners, Clean Architecture boundaries, and durable event-driven cross-context communication.

**Architecture:** Identity, Clients, Projects, Tasks, Audit, and Notifications own their respective business data. Public application contracts use immutable DTOs and scalar IDs; no context imports another context's infrastructure. The root application supplies only technical composition, including a transactional outbox that dispatches versioned immutable integration events after commit.

**Tech Stack:** PHP 8.4, Laravel 13.24, Inertia 3, Pest 5, Laravel Pint, Internachi Modular 3.

## Global Constraints

- All user-facing copy uses Laravel translation keys with matching English and Persian entries.
- All new or modified repository content is English unless it is explicit Persian localization data.
- Cross-context imports from `Modules\*\Infrastructure` and cross-context Eloquent relations, joins, foreign keys, and table queries are forbidden.
- Events contain only scalar immutable facts, an event ID, version, occurrence timestamp, and correlation ID.
- Consumers must record event delivery before side effects and be safe to retry.
- Root `app/` contains only framework composition and technical integration; no feature-owned models, policies, or business services.
- Add no third-party dependency. Internal module Composer requirements must reflect only public-contract dependencies.
- Do not commit unless the user explicitly requests it.

---

### Task 1: Enforce Module Boundaries Before Refactoring

**Files:**
- Modify: `tests/Feature/ArchitectureBoundariesTest.php`
- Modify: `tests/Unit/LegacyUpgradeTest.php`
- Test: `tests/Feature/ArchitectureBoundariesTest.php`

**Interfaces:**
- Consumes: current module source trees and migration locations.
- Produces: a non-failing inventory of current boundary violations and the strict assertion helpers enabled after the migration.

- [ ] **Step 1: Write characterization assertions that inventory prohibited imports and root ownership**

```php
it('inventories cross-context infrastructure imports before the boundary migration', function (): void {
    foreach (glob(base_path('app-modules/*/src/**/*.php')) as $file) {
        $source = file_get_contents($file);
        $module = basename(dirname(dirname(dirname($file))));

        $violations[$file] = preg_match('/use Modules\\(?!'.preg_quote(ucfirst($module), '/').'\\)\\w+\\Infrastructure\\/', $source) === 1;
    }
    expect(array_filter($violations))->not->toBeEmpty();
});
```

- [ ] **Step 2: Run the characterization test to verify it documents the current violations**

Run: `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php`

Expected: PASS while proving that Projects, Tasks, Identity, and Clients currently import another context's `Infrastructure` namespace.

- [ ] **Step 3: Add reusable recursive scanning helpers; keep strict rejection disabled until Task 8**

```php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app-modules')));

foreach ($files as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $relativePath = str_replace(base_path('app-modules/'), '', $file->getPathname());
    [$module] = explode('/', $relativePath, 2);
    $source = file_get_contents($file->getPathname());

    $violations[] = [$relativePath, $source];
}
```

- [ ] **Step 4: Update legacy migration assertions to locate Audit and Notifications migrations through module paths**

```php
expect(base_path('app-modules/audit/database/migrations'))->toBeDirectory()
    ->and(base_path('app-modules/notifications/database/migrations'))->toBeDirectory();
```

- [ ] **Step 5: Run the architecture and legacy test files**

Run: `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php tests/Unit/LegacyUpgradeTest.php`

Expected: PASS.

### Task 2: Add Technical Event Outbox and Supporting Contexts

**Files:**
- Create: `app/Integration/Events/IntegrationEvent.php`
- Create: `app/Integration/Outbox/OutboxRecorder.php`
- Create: `app/Integration/Outbox/AfterCommitOutboxDispatcher.php`
- Create: `app/Integration/Outbox/ProcessedIntegrationEventRepository.php`
- Create: `app/Models/OutboxMessage.php`
- Create: `database/migrations/2026_08_22_000000_create_outbox_messages_table.php`
- Create: `database/migrations/2026_08_22_000003_create_processed_integration_events_table.php`
- Create: `app-modules/audit/composer.json`
- Create: `app-modules/audit/src/AuditServiceProvider.php`
- Move: `database/migrations/0001_01_01_000500_create_activities_table.php` to `app-modules/audit/database/migrations/0001_01_01_000500_create_activities_table.php`
- Create: `app-modules/notifications/composer.json`
- Create: `app-modules/notifications/src/NotificationsServiceProvider.php`
- Move: `database/migrations/0001_01_01_000501_create_notifications_table.php` to `app-modules/notifications/database/migrations/0001_01_01_000501_create_notifications_table.php`
- Modify: `composer.json`
- Modify: `bootstrap/providers.php`
- Test: `tests/Feature/IntegrationOutboxTest.php`

**Interfaces:**
- Produces: `IntegrationEvent`, `OutboxRecorder::record(IntegrationEvent $event): void`, and a persisted after-commit delivery boundary.

- [ ] **Step 1: Write failing outbox tests for durable event records and duplicate-safe delivery**

```php
it('persists an immutable event in the transaction that changes business state', function (): void {
    DB::transaction(function (): void {
        app(OutboxRecorder::class)->record(new ProjectMembershipRemovedV1(
            eventId: (string) Str::uuid(),
            correlationId: (string) Str::uuid(),
            occurredAt: now()->toIso8601String(),
            projectId: 1,
            accountId: 2,
            actorId: 3,
        ));
    });

    expect(OutboxMessage::query()->count())->toBe(1);
});
```

- [ ] **Step 2: Run the new test to verify it fails because the integration boundary does not exist**

Run: `php artisan test --compact tests/Feature/IntegrationOutboxTest.php`

Expected: FAIL with missing `OutboxRecorder` and event classes.

- [ ] **Step 3: Implement the immutable event and outbox record contract**

```php
interface IntegrationEvent
{
    public function eventId(): string;

    public function eventType(): string;

    public function version(): int;

    public function occurredAt(): string;

    public function correlationId(): string;

    /** @return array<string, bool|int|string|null> */
    public function payload(): array;
}
```

```php
final class OutboxRecorder
{
    public function record(IntegrationEvent $event): void
    {
        OutboxMessage::query()->firstOrCreate(['event_id' => $event->eventId()], [
            'event_type' => $event->eventType(),
            'event_version' => $event->version(),
            'correlation_id' => $event->correlationId(),
            'occurred_at' => $event->occurredAt(),
            'payload' => $event->payload(),
        ]);
    }
}
```

```php
final class ProcessedIntegrationEventRepository
{
    public function claim(string $eventId, string $consumer): bool
    {
        return DB::table('processed_integration_events')->insertOrIgnore([
            'event_id' => $eventId,
            'consumer' => $consumer,
            'processed_at' => now(),
        ]) === 1;
    }
}
```

- [ ] **Step 4: Add Audit and Notifications module providers, migration loading, and root Composer path-package registration**

```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
}
```

Move the existing Activity and Notification migration files in this task so a fresh database has exactly one migration per table. Add `modules/audit` and `modules/notifications` to the root path repository versions and root `require` section. Register both service providers in `bootstrap/providers.php`.

- [ ] **Step 5: Run the outbox test and migration discovery checks**

Run: `php artisan test --compact tests/Feature/IntegrationOutboxTest.php tests/Unit/LegacyUpgradeTest.php`

Expected: PASS.

### Task 3: Expose Clients and Identity Public Contracts

**Files:**
- Create: `app-modules/clients/src/Application/Contracts/ClientStatusQuery.php`
- Create: `app-modules/clients/src/Application/DTOs/ClientStatusSummary.php`
- Create: `app-modules/clients/src/Infrastructure/Queries/EloquentClientStatusQuery.php`
- Modify: `app-modules/clients/src/Application/DTOs/ClientSummary.php`
- Modify: `app-modules/clients/src/Application/Queries/ActiveClientDirectory.php`
- Modify: `app-modules/clients/src/ClientsServiceProvider.php`
- Create: `app-modules/identity/src/Application/Contracts/AccountDirectory.php`
- Create: `app-modules/identity/src/Application/DTOs/AccountSummary.php`
- Create: `app-modules/identity/src/Infrastructure/Queries/EloquentAccountDirectory.php`
- Create: `app-modules/identity/src/Application/AccountAuthenticationEligibility.php`
- Modify: `app-modules/identity/src/IdentityServiceProvider.php`
- Modify: `app-modules/identity/src/Infrastructure/Models/User.php`
- Modify: `app-modules/clients/src/Infrastructure/Models/Client.php`
- Test: `tests/Feature/Mvp/IdentityClientTest.php`

**Interfaces:**
- Produces: `ClientStatusQuery::find(int $clientId): ?ClientStatusSummary` and `AccountDirectory::find(int $accountId): ?AccountSummary`.
- Consumes: only each owner’s own Eloquent models.

- [ ] **Step 1: Write failing contract tests for active client and account summaries**

```php
expect(app(ClientStatusQuery::class)->find($activeClient->id))
    ->toMatchObject(new ClientStatusSummary($activeClient->id, true));

expect(app(AccountDirectory::class)->find($customer->id))
    ->toMatchObject(new AccountSummary($customer->id, UserRole::Customer, true, $activeClient->id));
```

- [ ] **Step 2: Run the contract tests to verify the interfaces are unresolved**

Run: `php artisan test --compact tests/Feature/Mvp/IdentityClientTest.php`

Expected: FAIL with an unresolvable contract binding.

- [ ] **Step 3: Implement immutable DTOs and owner-local Eloquent adapters**

```php
final readonly class AccountSummary
{
    public function __construct(
        public int $id,
        public UserRole $role,
        public bool $isActive,
        public ?int $clientId,
    ) {}
}
```

```php
final class AccountAuthenticationEligibility
{
    public function __construct(private ClientStatusQuery $clients) {}

    public function canAuthenticate(AccountSummary $account): bool
    {
        return $account->isActive
            && ($account->role !== UserRole::Customer || $account->clientId !== null && $this->clients->find($account->clientId)?->isActive === true);
    }
}
```

- [ ] **Step 4: Remove `User::client()`, `Client::users()`, and `Client::projects()` relationships; inject eligibility service at Identity presentation boundaries**

Ensure `User` has no `Modules\Clients` import and no model method queries the Clients table.

- [ ] **Step 5: Run Identity and Client behavior tests**

Run: `php artisan test --compact tests/Feature/Mvp/IdentityClientTest.php tests/Feature/IdentityLoginTest.php tests/Feature/IdentityUserManagementTest.php`

Expected: PASS.

### Task 4: Make Projects the Sole Owner of Project State

**Files:**
- Create: `app-modules/projects/src/Application/Contracts/ProjectMembershipDirectory.php`
- Create: `app-modules/projects/src/Application/Contracts/ProjectAccessQuery.php`
- Create: `app-modules/projects/src/Application/DTOs/ProjectSummary.php`
- Create: `app-modules/projects/src/Application/DTOs/ProjectTaskStatusSummary.php`
- Create: `app-modules/projects/src/Application/DTOs/WorkGroupSummary.php`
- Create: `app-modules/projects/src/Infrastructure/Queries/EloquentProjectMembershipDirectory.php`
- Create: `app-modules/projects/src/Infrastructure/Queries/EloquentProjectAccessQuery.php`
- Modify: `app-modules/projects/src/ProjectsServiceProvider.php`
- Modify: `app-modules/projects/src/Infrastructure/Models/Project.php`
- Modify: `app-modules/projects/src/Infrastructure/Models/ProjectTaskStatus.php`
- Modify: `app-modules/projects/src/Infrastructure/Models/WorkGroup.php`
- Modify: `app-modules/projects/src/Application/ProjectMembershipManager.php`
- Modify: `app-modules/projects/src/Application/ProjectLifecycle.php`
- Modify: `app-modules/projects/src/Application/ProjectWorkflowManager.php`
- Modify: `app-modules/projects/src/Application/WorkGroupManager.php`
- Test: `tests/Feature/Mvp/ProjectMembershipTest.php`

**Interfaces:**
- Consumes: `AccountDirectory`, `ClientStatusQuery`, and `OutboxRecorder`.
- Produces: project summary/query contracts and `ProjectMembershipRemovedV1` / `ProjectTaskStatusChangedV1` events.

- [ ] **Step 1: Write failing tests proving Projects receives account facts through `AccountDirectory` and publishes an immutable membership event**

```php
$event = OutboxMessage::query()->where('event_type', ProjectMembershipRemovedV1::class)->sole();

expect($event->payload)->toMatchArray([
    'project_id' => $project->id,
    'account_id' => $member->id,
    'actor_id' => $admin->id,
]);
```

- [ ] **Step 2: Run the membership test to verify the event is not yet published**

Run: `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php`

Expected: FAIL because no membership event is recorded.

- [ ] **Step 3: Implement the owner-local directory and event records**

```php
interface ProjectMembershipDirectory
{
    public function findProject(int $projectId): ?ProjectSummary;

    public function hasActiveMembership(int $projectId, int $accountId): bool;

    public function defaultOpenTaskStatus(int $projectId): ?ProjectTaskStatusSummary;

    public function findActiveTaskStatus(int $statusId): ?ProjectTaskStatusSummary;

    public function findActiveWorkGroup(int $workGroupId): ?WorkGroupSummary;
}
```

```php
final readonly class ProjectMembershipRemovedV1 implements IntegrationEvent
{
    public function payload(): array
    {
        return ['project_id' => $this->projectId, 'account_id' => $this->accountId, 'actor_id' => $this->actorId];
    }
}
```

- [ ] **Step 4: Remove Projects model imports and relations to Client, User, and Task**

Remove `Project::client()`, `Project::members()`, `Project::tasks()`, `ProjectTaskStatus::tasks()`, and `WorkGroup::tasks()`. Keep project-owned membership persistence through `project_user` queries inside Projects infrastructure only. Move creator selection and visibility decisions into application queries using `AccountSummary`.

- [ ] **Step 5: Run Projects behavior and architecture tests**

Run: `php artisan test --compact tests/Feature/Mvp/ProjectMembershipTest.php tests/Feature/ArchitectureBoundariesTest.php`

Expected: PASS for Projects source; remaining Tasks and root violations may still fail the global boundary test.

### Task 5: Refactor Tasks to Consume Contracts and Events

**Files:**
- Create: `app-modules/tasks/src/Application/Contracts/ProjectTaskStateQuery.php`
- Create: `app-modules/tasks/src/Application/Contracts/ProjectTaskStateWriter.php`
- Create: `app-modules/tasks/src/Application/Consumers/ProjectMembershipRemovedConsumer.php`
- Create: `app-modules/tasks/src/Application/Consumers/ProjectTaskStatusChangedConsumer.php`
- Create: `app-modules/tasks/src/Application/DTOs/TaskProjectContext.php`
- Create: `app-modules/tasks/src/Application/DTOs/TaskStatusContext.php`
- Create: `app-modules/tasks/src/Application/DTOs/TaskWorkGroupContext.php`
- Modify: `app-modules/tasks/src/TasksServiceProvider.php`
- Modify: `app-modules/tasks/src/Infrastructure/Models/Task.php`
- Modify: `app-modules/tasks/src/Infrastructure/Models/TaskComment.php`
- Modify: `app-modules/tasks/src/Infrastructure/Models/Attachment.php`
- Modify: `app-modules/tasks/src/Infrastructure/Models/TaskChecklistItem.php`
- Modify: `app-modules/tasks/src/Application/TaskWorkflow.php`
- Modify: `app-modules/tasks/src/Application/TaskCollaboration.php`
- Test: `tests/Feature/Mvp/EmployeeAuthorizationTest.php`
- Test: `tests/Feature/TaskEventConsumerTest.php`

**Interfaces:**
- Consumes: `AccountDirectory`, `ProjectMembershipDirectory`, `IntegrationEvent` payloads.
- Produces: task-owned state queries/writers and idempotent event consumers.

- [ ] **Step 1: Write failing consumer tests for membership removal and status completion synchronization**

```php
app(ProjectMembershipRemovedConsumer::class)->handle(new ProjectMembershipRemovedV1(
    eventId: $eventId,
    correlationId: $correlationId,
    occurredAt: now()->toIso8601String(),
    projectId: $project->id,
    accountId: $customer->id,
    actorId: $admin->id,
));

expect($task->fresh()->assigned_to)->toBeNull();

app(ProjectMembershipRemovedConsumer::class)->handle($event);
expect(Task::query()->whereKey($task)->count())->toBe(1);
```

- [ ] **Step 2: Run consumer tests to verify the handlers are missing**

Run: `php artisan test --compact tests/Feature/TaskEventConsumerTest.php`

Expected: FAIL with missing consumer classes.

- [ ] **Step 3: Implement contract-based validation and idempotent consumers**

```php
final class ProjectMembershipRemovedConsumer
{
    public function __construct(private ProcessedIntegrationEventRepository $processed) {}

    public function handle(ProjectMembershipRemovedV1 $event): void
    {
        if (! $this->processed->claim($event->eventId(), self::class)) {
            return;
        }

        Task::query()
            ->where('project_id', $event->projectId)
            ->where('assigned_to', $event->accountId)
            ->update(['assigned_to' => null]);
    }
}
```

- [ ] **Step 4: Remove `User`, `Project`, `ProjectTaskStatus`, and `WorkGroup` imports/relations from Task-owned models**

Move all external validation from `Task::assertStateIntegrity()` into `TaskWorkflow`, using `AccountDirectory` and `ProjectMembershipDirectory`. Retain only Task-owned comment, attachment, and checklist relations in Task models.

- [ ] **Step 5: Run task behavior, consumer, and architecture tests**

Run: `php artisan test --compact tests/Feature/Mvp/EmployeeAuthorizationTest.php tests/Feature/TaskEventConsumerTest.php tests/Feature/ArchitectureBoundariesTest.php`

Expected: PASS for Tasks source; root ownership violations may still fail global checks.

### Task 6: Move Feature Ownership Out of Root Application

**Files:**
- Move: `app/Policies/ClientPolicy.php` to `app-modules/clients/src/Presentation/Policies/ClientPolicy.php`
- Move: `app/Policies/ProjectPolicy.php` to `app-modules/projects/src/Presentation/Policies/ProjectPolicy.php`
- Move: `app/Policies/TaskPolicy.php` to `app-modules/tasks/src/Presentation/Policies/TaskPolicy.php`
- Move: `app/Policies/AttachmentPolicy.php` to `app-modules/tasks/src/Presentation/Policies/AttachmentPolicy.php`
- Move: `app/Http/Middleware/EnsureAccountActive.php` to `app-modules/identity/src/Presentation/Http/Middleware/EnsureAccountActive.php`
- Move: `app/Models/Activity.php` to `app-modules/audit/src/Infrastructure/Models/Activity.php`
- Move: `app/Notifications/ResourceChangedNotification.php` to `app-modules/notifications/src/Infrastructure/Notifications/ResourceChangedNotification.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app-modules/clients/src/ClientsServiceProvider.php`
- Modify: `app-modules/projects/src/ProjectsServiceProvider.php`
- Modify: `app-modules/tasks/src/TasksServiceProvider.php`
- Modify: `app-modules/identity/src/IdentityServiceProvider.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/ModuleRegistrationTest.php`

**Interfaces:**
- Consumes: module model classes and technical Laravel Gate/middleware registration.
- Produces: module-owned policy and middleware registration, with Audit and Notifications as data owners.

- [ ] **Step 1: Write failing registration tests for module-owned policies and Identity middleware**

```php
expect(Gate::getPolicyFor(Client::class))->toBeInstanceOf(Modules\Clients\Presentation\Policies\ClientPolicy::class)
    ->and(Gate::getPolicyFor(Project::class))->toBeInstanceOf(Modules\Projects\Presentation\Policies\ProjectPolicy::class)
    ->and(Gate::getPolicyFor(Task::class))->toBeInstanceOf(Modules\Tasks\Presentation\Policies\TaskPolicy::class);
```

- [ ] **Step 2: Run the registration test to verify root policies are still registered**

Run: `php artisan test --compact tests/Feature/ModuleRegistrationTest.php`

Expected: FAIL because policy classes remain under `App\Policies`.

- [ ] **Step 3: Move classes without changing authorization decisions and register them from their module providers**

```php
public function boot(): void
{
    Gate::policy(Task::class, TaskPolicy::class);
    Gate::policy(Attachment::class, AttachmentPolicy::class);
}
```

Retain only technical service providers and event outbox bindings in `AppServiceProvider`. Preserve the `account.active` alias by registering the Identity middleware class from the composition root.

- [ ] **Step 4: Move activity and notification persistence to their supporting modules and consume events rather than models**

Audit and Notifications consumers receive `IntegrationEvent` values. They must not import `Project`, `Task`, `User`, or any feature model. Their migrations were moved in Task 2 and retain their table names so existing data remains addressable.

- [ ] **Step 5: Run policy, middleware, and architecture tests**

Run: `php artisan test --compact tests/Feature/ModuleRegistrationTest.php tests/Feature/IdentityLoginTest.php tests/Feature/ArchitectureBoundariesTest.php`

Expected: PASS.

### Task 7: Remove Cross-Context Foreign Keys and Align Package Dependencies

**Files:**
- Create: `database/migrations/2026_08_22_000010_remove_cross_context_foreign_keys.php`
- Modify: `app-modules/identity/database/migrations/0001_01_01_000200_create_users_table.php`
- Modify: `app-modules/projects/database/migrations/0001_01_01_000300_create_projects_table.php`
- Modify: `app-modules/projects/database/migrations/0001_01_01_000301_create_project_user_table.php`
- Modify: `app-modules/projects/database/migrations/0001_01_01_000302_create_project_task_statuses_table.php`
- Modify: `app-modules/projects/database/migrations/0001_01_01_000303_create_work_groups_table.php`
- Modify: `app-modules/tasks/database/migrations/0001_01_01_000400_create_tasks_table.php`
- Modify: `app-modules/projects/composer.json`
- Modify: `app-modules/tasks/composer.json`
- Modify: `app-modules/identity/composer.json`
- Modify: `app-modules/clients/composer.json`
- Test: `tests/Feature/ArchitectureBoundariesTest.php`

**Interfaces:**
- Consumes: scalar reference columns and public-contract module dependencies.
- Produces: indexes instead of cross-context FKs and Composer manifests without infrastructure-driven coupling.

- [ ] **Step 1: Write failing schema assertions for indexed scalar references without cross-context constraints**

```php
expect(Schema::getForeignKeys('users'))->not->toContain(fn (array $foreignKey): bool => $foreignKey['foreign_table'] === 'clients')
    ->and(Schema::getForeignKeys('tasks'))->not->toContain(fn (array $foreignKey): bool => in_array($foreignKey['foreign_table'], ['users', 'projects', 'project_task_statuses', 'work_groups'], true));
```

- [ ] **Step 2: Run the architecture test to verify existing foreign keys fail the assertion**

Run: `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php`

Expected: FAIL because cross-context foreign keys exist.

- [ ] **Step 3: Replace FK definitions with indexed scalar IDs and add a forward migration for existing databases**

```php
$table->unsignedBigInteger('client_id')->nullable()->index();
$table->unsignedBigInteger('project_id')->index();
$table->unsignedBigInteger('assigned_to')->nullable()->index();
```

The forward migration drops only foreign-key constraints, preserving every existing reference value and adding an index where no index exists.

- [ ] **Step 4: Prune internal module requirements to public-contract dependencies only**

Keep a module dependency only when its source imports that module's `Application\Contracts`, `Application\DTOs`, or root technical event contract. Do not add an inverse Projects-to-Tasks requirement.

- [ ] **Step 5: Rebuild the test database and run schema and package validation**

Run: `php artisan migrate:fresh --seed --force`

Run: `composer validate --no-check-publish`

Run: `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php`

Expected: PASS.

### Task 8: Complete Cross-Context Verification

**Files:**
- Modify: `tests/Feature/ArchitectureBoundariesTest.php`
- Modify: `tests/Feature/Mvp/IdentityClientTest.php`
- Modify: `tests/Feature/Mvp/ProjectMembershipTest.php`
- Modify: `tests/Feature/Mvp/EmployeeAuthorizationTest.php`
- Modify: `tests/Feature/IntegrationOutboxTest.php`
- Modify: `tests/Feature/TaskEventConsumerTest.php`
- Modify: `tests/Feature/ModuleRegistrationTest.php`

**Interfaces:**
- Consumes: completed public contracts, event consumers, policies, and migrations.
- Produces: a regression suite that proves data ownership, durable events, and behavior preservation.

- [ ] **Step 1: Add negative architecture coverage for every forbidden boundary**

```php
expect($moduleSource)->not->toContain('DB::table(\'users\')')
    ->and($moduleSource)->not->toContain('DB::table(\'projects\')')
    ->and($moduleSource)->not->toContain('belongsTo(User::class)')
    ->and($moduleSource)->not->toContain('belongsTo(Project::class)');
```

- [ ] **Step 2: Add event contract and retry coverage**

```php
expect($event->payload())->each(fn (mixed $value) => expect($value)->toBeString()->or->toBeInt()->or->toBeBool()->or->toBeNull());

Queue::fake();
app(AfterCommitOutboxDispatcher::class)->dispatchPending();
app(AfterCommitOutboxDispatcher::class)->dispatchPending();
expect(Activity::query()->where('event_id', $event->eventId())->count())->toBe(1);
```

- [ ] **Step 3: Run narrow suites until all expected behavior and boundary assertions pass**

Run: `php artisan test --compact tests/Feature/ArchitectureBoundariesTest.php tests/Feature/Mvp/IdentityClientTest.php tests/Feature/Mvp/ProjectMembershipTest.php tests/Feature/Mvp/EmployeeAuthorizationTest.php tests/Feature/IntegrationOutboxTest.php tests/Feature/TaskEventConsumerTest.php tests/Feature/ModuleRegistrationTest.php`

Expected: PASS.

- [ ] **Step 4: Run required final verification**

Run: `vendor/bin/pint --dirty --format agent`

Run: `php artisan test --compact`

Run: `composer validate --no-check-publish`

Run: `npm run build`

Run: `git diff --check`

Expected: every command exits successfully.
