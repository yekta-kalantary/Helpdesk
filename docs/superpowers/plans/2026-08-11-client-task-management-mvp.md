# Client Task Management MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the smallest complete Laravel/Livewire implementation that satisfies `docs/product/client-task-management-mvp.md`, including Client isolation, auditable Project Membership, task workflow invariants, contextual collaboration, secure files, notifications, activity, dashboard/list UX, and E2E-001 through E2E-008.

**Architecture:** Keep the existing Laravel modular monolith and direct Eloquent style. Add a small Clients module, register Laravel policies centrally, centralize cross-write Membership/Task workflow rules in focused classes, use private local storage for attachments, Laravel database notifications plus queued mail, and transactional Activity recording. Visibility is always Membership-based; Assignment is responsibility only.

**Tech Stack:** PHP 8.4, Laravel 13.8, Livewire 4.3, Pest 5, MariaDB 11.4, Vite/Node 22.

## Global Constraints

- `docs/product/client-task-management-mvp.md` is the final authority.
- Roles are exactly `Admin` and `Customer`; no custom role/permission builder.
- Customer belongs to exactly one Client; Admin does not require a Client.
- Customer Project/Task visibility comes from active Project Membership, never Assignee.
- Project belongs to one Client and cannot change Client after creation.
- Task belongs to one Project and cannot move after creation.
- Task statuses are exactly `Todo`, `In Progress`, `Waiting Admin`, `Waiting Customer`, `Completed`, `Cancelled`.
- Project statuses are exactly `Active`, `Completed`; no Archive.
- No UI hard delete for operational MVP entities.
- UI is Persian-first, RTL, responsive on desktop/tablet/mobile.
- Notification channels are In-app and Email only.
- Files are private and must pass backend authorization, size, extension, and MIME validation.
- Apply YAGNI; do not add Post-MVP features.

---

## File map

### Clients
- Create `app-modules/clients/src/ClientsServiceProvider.php` — route/view/translation/migration/Livewire registration.
- Create `app-modules/clients/src/Domain/Enums/ClientStatus.php` — `Active|Inactive`.
- Create `app-modules/clients/src/Infrastructure/Models/Client.php` — relationships/scopes/status.
- Create `app-modules/clients/src/Presentation/Livewire/Index.php` — admin list/search/status filter/pagination.
- Create `app-modules/clients/src/Presentation/Livewire/Form.php` — admin create/update/activate/deactivate.
- Create `app-modules/clients/src/Presentation/Livewire/Show.php` — summary, users, projects.
- Create `app-modules/clients/database/migrations/2026_08_11_120000_create_clients_table.php`.
- Create `app-modules/clients/routes/web.php`, Persian/English translations, and views.
- Modify `composer.json` root PSR-4 autoload to register `Modules\\Clients\\` without adding a new Composer dependency.
- Modify `bootstrap/providers.php` to register `ClientsServiceProvider`.

### Identity
- Create `app-modules/identity/src/Domain/Enums/UserRole.php` — `Admin|Customer`.
- Create `app-modules/identity/database/migrations/2026_08_11_121000_upgrade_users_for_mvp.php` — role/client/last-login migration and `is_admin` removal.
- Modify `User.php` — role/client relations, normalized email, active/admin/customer helpers.
- Modify login and user management Livewire components.
- Create profile, forgot-password, reset-password Livewire components/views/routes.
- Modify factory/seeder for role/client behavior.

### Projects
- Create `ProjectStatus.php`.
- Create `ProjectMembershipManager.php`.
- Create forward migration `2026_08_11_122000_upgrade_projects_for_mvp.php`.
- Modify Project model/forms/index and create Project Show screen.
- Replace destructive membership sync with add/remove/reactivate lifecycle.

### Tasks / collaboration
- Create `TaskStatus.php`, `TaskPriority.php`, `ActivityType.php`.
- Create `TaskWorkflow.php` and `ActivityRecorder.php`.
- Create migrations for task upgrade, comments, attachments, activities, notifications.
- Modify Task model/form/index; create Task Show and notification screens.
- Create Comment, Attachment, Activity models and attachment download controller.
- Create Laravel notification classes for task/membership/comment events.

### Authorization / app shell
- Create `app/Policies/ClientPolicy.php`, `ProjectPolicy.php`, `TaskPolicy.php`, `AttachmentPolicy.php`.
- Modify `AppServiceProvider.php` to register policies.
- Modify Dashboard and layout/navigation.
- Add private attachment configuration.

### Tests
- Create focused Pest suites for Client/Identity, Membership, Task workflow, Collaboration/File Security, Notifications/Activity, Dashboard isolation, and E2E-001..008.
- Update legacy tests to the new PRD vocabulary only when their old assertions conflict with the authoritative PRD.

---

### Task 1: RED baseline — PRD schema, identity, and Client boundaries

**Files:**
- Create: `tests/Feature/Mvp/IdentityClientTest.php`
- Create: `tests/Feature/Mvp/ArchitectureSchemaTest.php`
- Modify later in GREEN: Clients and Identity files listed above.

**Interfaces:**
- Produces `Client`, `ClientStatus`, `UserRole`, `User::isAdmin()`, `User::isCustomer()`, `User::canAuthenticate()`, normalized `email`.
- Produces DB constraints used by Projects and Tasks.

- [ ] **Step 1: Write failing schema/entity tests**

```php
it('has the MVP client and identity schema', function (): void {
    expect(Schema::hasColumns('clients', ['name', 'description', 'status']))->toBeTrue()
        ->and(Schema::hasColumns('users', ['client_id', 'role', 'is_active', 'last_login_at']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'is_admin'))->toBeFalse();
});

it('normalizes email and reserves it after deactivation', function (): void {
    User::factory()->create(['email' => ' Customer@Example.COM ', 'is_active' => false]);

    expect(User::query()->firstOrFail()->email)->toBe('customer@example.com');

    expect(fn () => User::factory()->create(['email' => 'CUSTOMER@example.com']))
        ->toThrow(QueryException::class);
});

it('requires a client for customer identities', function (): void {
    expect(fn () => User::factory()->create(['role' => UserRole::Customer, 'client_id' => null]))
        ->toThrow(DomainException::class);
});
```

- [ ] **Step 2: Push RED commit and verify CI fails for missing MVP schema/classes**

Expected failure: missing `clients`, `role/client_id`, enum/classes, or old `is_admin` schema.

- [ ] **Step 3: Implement Client schema/model and upgraded User identity model**

Key rules in production code:

```php
enum UserRole: string { case Admin = 'admin'; case Customer = 'customer'; }
enum ClientStatus: string { case Active = 'active'; case Inactive = 'inactive'; }
```

User email setter must store `Str::lower(trim($value))`. A model `saving` guard rejects Customer without Client and Admin with an invalid role. Login requires active User and, for Customer, active Client.

- [ ] **Step 4: Add Client admin CRUD/list/show and user onboarding/profile/reset flows**

Admin-created Customer always receives `role=customer`, selected active Client, normalized unique Email, active status, and account setup reset link. Customer profile only changes name/last name/password. No public registration route is added.

- [ ] **Step 5: Verify CI GREEN for focused identity/schema tests, then full existing suite**

---

### Task 2: RED/GREEN — Project ownership and auditable membership

**Files:**
- Create: `tests/Feature/Mvp/ProjectMembershipTest.php`
- Create: `app-modules/projects/src/Domain/Enums/ProjectStatus.php`
- Create: `app-modules/projects/src/Application/ProjectMembershipManager.php`
- Create: `app-modules/projects/database/migrations/2026_08_11_122000_upgrade_projects_for_mvp.php`
- Modify: Project model, form/index/routes/views/translations.
- Create: Project Show Livewire/view.

**Interfaces:**
- Produces `Project::visibleTo(User $user)` scope.
- Produces `Project::hasActiveMember(User $user): bool`.
- Produces `ProjectMembershipManager::add(Project $project, User $user, User $actor): void` and `remove(...)`.

- [ ] **Step 1: Write failing membership tests**

```php
it('does not grant access from client ownership alone', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = Project::factory()->for($client)->create();

    $this->actingAs($customer)->get(route('projects.show', $project))->assertNotFound();
});

it('removes and reactivates one historical membership row', function (): void {
    $manager->add($project, $customer, $admin);
    $manager->remove($project, $customer, $admin);
    $removedAt = DB::table('project_user')->where(compact('project_id', 'user_id'))->value('removed_at');
    expect($removedAt)->not->toBeNull();

    $manager->add($project, $customer, $admin);
    expect(DB::table('project_user')->where(compact('project_id', 'user_id'))->count())->toBe(1)
        ->and(DB::table('project_user')->where(compact('project_id', 'user_id'))->value('removed_at'))->toBeNull();
});

it('rejects cross-client project membership', function (): void {
    expect(fn () => $manager->add($projectA, $customerB, $admin))->toThrow(DomainException::class);
});
```

- [ ] **Step 2: Push RED and confirm the failures are due to missing lifecycle/scope behavior**

- [ ] **Step 3: Implement Project fields, immutability, scopes, membership lifecycle, and admin UI**

Project status is `active|completed`; `client_id` is immutable in a model updating guard. Membership add validates active Customer, same Client, active Client, and reuses the unique row. Removal writes `removed_at` and never deletes.

- [ ] **Step 4: Add Project detail and safe customer member projection**

Customer member lists select only `id`, `name`, `last_name`; do not expose email/mobile.

- [ ] **Step 5: Verify focused and full CI GREEN**

---

### Task 3: RED/GREEN — Task model, Admin Queue, assignment, and state integrity

**Files:**
- Create: `tests/Feature/Mvp/TaskWorkflowTest.php`
- Create: `tests/Feature/Mvp/TaskAuthorizationTest.php`
- Create: Task enums and workflow/activity classes.
- Create: `app-modules/tasks/database/migrations/2026_08_11_123000_upgrade_tasks_for_mvp.php`.
- Modify: Task model/form/index/routes/views/translations.
- Create: Task Show Livewire/view.

**Interfaces:**
- `TaskWorkflow::createForAdmin(User $actor, Project $project, array $data): Task`.
- `TaskWorkflow::createForCustomer(User $actor, Project $project, array $data): Task`.
- `TaskWorkflow::updateByAdmin(User $actor, Task $task, array $data): Task`.
- `TaskWorkflow::transitionByCustomer(User $actor, Task $task, TaskStatus $status): Task`.

- [ ] **Step 1: Write failing state-integrity and visibility tests**

```php
it('creates a customer request in the admin queue', function (): void {
    $task = $workflow->createForCustomer($customer, $project, ['title' => 'Need review']);

    expect($task->status)->toBe(TaskStatus::WaitingAdmin)
        ->and($task->priority)->toBe(TaskPriority::Normal)
        ->and($task->assigned_to)->toBeNull();
});

it('makes membership not assignment the visibility rule', function (): void {
    $project->members()->wherePivotNull('removed_at');
    $task = Task::factory()->for($project)->assignedTo($otherMember)->create();

    $this->actingAs($customerMember)->get(route('tasks.show', $task))->assertOk();
});

it('rejects invalid state and assignee combinations', function (TaskStatus $status, ?User $assignee): void {
    expect(fn () => $workflow->updateByAdmin($admin, $task, [
        'status' => $status,
        'assigned_to' => $assignee?->id,
    ]))->toThrow(DomainException::class);
})->with('invalid task state assignments');
```

The dataset covers Waiting Customer without a valid Customer member, Waiting Admin with Customer assignee, Todo/In Progress without assignee, inactive assignees, and cross-client Customer assignees.

- [ ] **Step 2: Push RED and verify expected workflow failures**

- [ ] **Step 3: Implement task schema/reference and immutable boundaries**

Generate unique `TSK-XXXXXXXX` reference at creation. Model update guard rejects reference/project changes. Overdue scope means due date is past and status is not Completed/Cancelled.

- [ ] **Step 4: Implement TaskWorkflow with transactions and Activity writes**

Customer transitions require `assigned_to === actor.id`; allowed targets are Todo, In Progress, Waiting Admin, Completed. Waiting Admin clears a Customer assignee. Completed sets `completed_at`; reopen clears it. Admin may perform all valid transitions.

- [ ] **Step 5: Implement task list/create/detail UI**

Admin fields: Project, Title, Description, Status, Priority, Assignee, Due Date. Customer fields: Project context, Title, Description, optional attachment only. Lists paginate and support title/reference search plus Project/Status/Priority/Assignee/Overdue filters and Updated/Due sorting.

- [ ] **Step 6: Implement Project completion guard**

Reject completion if any project Task status is not Completed/Cancelled. Reopen returns Project to Active.

- [ ] **Step 7: Verify focused and full CI GREEN**

---

### Task 4: RED/GREEN — Comments, private attachments, and closed-resource rules

**Files:**
- Create: `tests/Feature/Mvp/CollaborationFileSecurityTest.php`
- Create migrations/models for `task_comments` and `attachments`.
- Create attachment download controller.
- Modify Task Show for comments/uploads/hide actions.
- Modify task routes and `config/helpdesk.php` for attachment limits/types.

**Interfaces:**
- `TaskWorkflow::addComment(User $actor, Task $task, ?string $body, array $uploads): TaskComment`.
- Attachment download route authorizes parent Task every time.

- [ ] **Step 1: Write failing file-security/collaboration tests**

```php
it('denies a direct attachment URL to a non-member', function (): void {
    Storage::fake('local');
    $attachment = Attachment::factory()->for($taskA)->create();

    $this->actingAs($customerB)
        ->get(route('attachments.download', $attachment))
        ->assertNotFound();
});

it('rejects an empty comment without files', function (): void {
    Livewire::actingAs($member)->test('tasks::show', ['task' => $task->id])
        ->set('comment', '')
        ->call('addComment')
        ->assertHasErrors('comment');
});

it('blocks collaboration on completed project or terminal task', function (): void {
    expect(fn () => $workflow->addComment($member, $closedTask, 'Nope', []))
        ->toThrow(DomainException::class);
});
```

- [ ] **Step 2: Push RED and verify failures are authorization/collaboration gaps**

- [ ] **Step 3: Implement private file persistence and validation**

Allow only configured image/PDF/text/Office/common archive extensions with matching safe MIME types; reject executable/high-risk extensions. Maximum size defaults to 20 MB. Store on `local` private disk with generated storage keys; preserve original name/MIME/size/uploader metadata.

- [ ] **Step 4: Implement comments and admin hide behavior**

No comment editing route/action exists. Hide sets hidden timestamp/admin and records Activity; Customer lists omit hidden content while Admin sees audit context.

- [ ] **Step 5: Verify focused and full CI GREEN**

---

### Task 5: RED/GREEN — Activity and notifications

**Files:**
- Create: `tests/Feature/Mvp/ActivityNotificationTest.php`
- Create activities and notifications migrations/models/classes.
- Create notification Livewire index.
- Wire task/project/membership/comment/attachment mutations to ActivityRecorder/notifications.

**Interfaces:**
- `ActivityRecorder::record(User $actor, string $action, ?Project $project, ?Task $task, array $metadata = []): Activity`.
- Notification payload stores type/resource label/reference only; resource visibility is not trusted when opened.

- [ ] **Step 1: Write failing Activity/Notification tests**

```php
it('notifies all active admins for a customer-created queue task without a fixed id', function (): void {
    Notification::fake();
    $workflow->createForCustomer($customer, $project, ['title' => 'Queue task']);

    Notification::assertSentTo([$adminOne, $adminTwo], TaskChangedNotification::class);
    Notification::assertNotSentTo($inactiveAdmin, TaskChangedNotification::class);
});

it('records safe old and new task values', function (): void {
    $workflow->updateByAdmin($admin, $task, ['priority' => TaskPriority::High]);

    $activity = Activity::query()->latest('id')->firstOrFail();
    expect($activity->metadata)->toMatchArray(['old' => 'normal', 'new' => 'high'])
        ->and(json_encode($activity->metadata))->not->toContain('password', 'token');
});
```

- [ ] **Step 2: Push RED and verify notification/activity gaps**

- [ ] **Step 3: Implement database notification + queued mail delivery**

Notification classes implement `ShouldQueue`, use database and mail channels, and are dispatched only after the main transaction commits. Recipients are active. Actor is filtered out. Mail contains a resource link but no attachment bytes.

- [ ] **Step 4: Implement in-app list/read/unread behavior**

Opening a notification marks it read and redirects only after policy authorization; otherwise return safe 404/403 without leaking resource details.

- [ ] **Step 5: Verify focused and full CI GREEN**

---

### Task 6: RED/GREEN — Dashboard, lists, navigation, and responsive critical flows

**Files:**
- Create: `tests/Feature/Mvp/DashboardIsolationTest.php`
- Modify: `app/Livewire/Dashboard.php`, dashboard view, app layout/navigation, Project/Task/Client/User lists.

**Interfaces:**
- All dashboard metrics derive from the same `visibleTo($user)` scopes used by lists.

- [ ] **Step 1: Write failing dashboard isolation tests**

```php
it('never counts data outside customer membership scope', function (): void {
    $this->actingAs($customerA)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Visible project')
        ->assertDontSee('Client B project');
});
```

Also assert Admin Queue, Waiting Customer, Overdue, Assigned To Me, active projects, and recent activity counts for the correct role.

- [ ] **Step 2: Push RED and verify dashboard/list gaps**

- [ ] **Step 3: Implement dashboard metrics and PRD navigation**

Customer nav: Dashboard, Projects, Tasks, Notifications. Admin adds Clients and Users. Remove destructive delete controls and any obsolete navigation.

- [ ] **Step 4: Make critical Livewire forms/lists responsive**

Use existing UI components and responsive grid/overflow patterns; ensure create Task, add Comment/upload, status action, and notification navigation remain usable at mobile widths without desktop-only controls.

- [ ] **Step 5: Verify focused and full CI GREEN plus `npm run build`**

---

### Task 7: E2E acceptance suite and security negatives

**Files:**
- Create: `tests/Feature/Mvp/EndToEndMvpTest.php`
- Modify production code only for root causes exposed by tests.

**Interfaces:** none new; this task verifies integrated behavior.

- [ ] **Step 1: Encode E2E-001 through E2E-008 as named Pest tests**

Each test follows the exact PRD sequence:

- E2E-001 onboarding Client -> Customer -> Project -> Membership -> Task -> login/visibility.
- E2E-002 two-Client isolation across Project/Task/Attachment/search.
- E2E-003 Customer Request -> Admin Queue -> admin claim -> Waiting Customer -> customer response -> Waiting Admin -> completion.
- E2E-004 authorized upload/download and unauthorized direct URL denial.
- E2E-005 membership removal history, immediate access loss, same-row reactivation.
- E2E-006 Client deactivation blocks Customer login while preserving Admin history; reactivation restores membership-based access.
- E2E-007 open-task completion guard, completed read-only behavior, admin reopen.
- E2E-008 all status/assignee invariants, completed_at set/reset, immutable reference.

- [ ] **Step 2: Add explicit negative tests from PRD**

Cover Customer cannot change Priority/Assignee/Client/Role/Status identity, cannot Cancel, inactive user cannot login/assignee/member, duplicate inactive email fails, non-member cannot access resource IDs, and task Project/reference immutability.

- [ ] **Step 3: Push RED if any integrated scenario fails; fix production root cause only**

Do not weaken acceptance assertions to match implementation.

- [ ] **Step 4: Verify E2E suite and full CI GREEN**

---

### Task 8: Final PRD verification, code review, and release gate

**Files:**
- Create: `docs/implementation/client-task-management-mvp-verification.md`.
- Modify only files required by review findings.

- [ ] **Step 1: Re-read `docs/product/client-task-management-mvp.md` completely**

Map every FR, BR, AC, NFR, E2E, and Definition-of-Done item to implementation/tests. Record any intentionally unimplemented item as a release-blocking gap unless the PRD itself marks it optional/Post-MVP.

- [ ] **Step 2: Run fresh verification through GitHub Actions**

Required commands from CI:

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

- [ ] **Step 3: Perform code review**

Review specifically for authorization bypass, unscoped Customer queries, mass-assignment of role/client/project/reference, N+1 list queries, public file paths, secret leakage in Activity/Notification, hard deletes, fixed Admin IDs, and unsupported Post-MVP abstractions.

- [ ] **Step 4: Fix review findings with RED/GREEN tests**

Every behavior fix starts with a regression test that fails for the finding, then minimal production change, then focused/full verification.

- [ ] **Step 5: Complete verification document**

The document records implemented scope, architecture decisions, migrations, tests, exact fresh CI result, remaining PRD gaps, and intentionally deferred Post-MVP items.
