# Project Work Management PRDs Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend the existing Client Task Management MVP on `main` with Project-owned task statuses/Kanban, optional five-level Work Groups, and one-level checklist Subtasks while preserving authorization, audit history, collaboration data, immutable task references, and No Hard Delete.

**Architecture:** Keep the existing modular Laravel structure. Project lifecycle (`projects.status = active|completed`) stays unchanged; task workflow becomes a first-class Project-scoped entity (`ProjectTaskStatus`) referenced by every Task. Work Groups are Project-scoped structural containers, while checklist items are Task-owned execution details; these dimensions remain independent and are coordinated through transactional application services.

**Tech Stack:** PHP 8.4, Laravel 13.8, Livewire 4.3, MariaDB 11.4, Pest 5, Tailwind/Vite.

## Global Constraints

- `main@a54e365224f76bad4374da7adb4189d22d12ee98` is the implementation baseline.
- The four product PRDs under `docs/product/` are the Source of Truth; newer PRDs supersede conflicting fixed-status MVP behavior.
- Preserve the existing MVP rather than rebuilding it.
- No normal UI operation may hard-delete Work Groups, Project Task Statuses, checklist items, comments, attachments, Projects, Clients, or Users.
- Work Group depth is derived from `parent_id` and may never exceed 5.
- Every Project must have at least two active task statuses, at least one active open status, and exactly one active Done status.
- Every Task must reference exactly one active status belonging to its own Project.
- Any active Admin or Customer Project member may change any visible Task's Project Status regardless of assignee.
- Completed Projects are read-only; a Done Task may only be reopened after its Project is reopened.
- Subtasks/checklist items are one level only and never receive assignment, priority, due date, status, comments, attachments, notification, or independent reference.
- All status changes and material Work Group/Subtask mutations must be audit-recorded with stable snapshot metadata.
- Existing Task references, comments, attachments, activities, assignments, and `completed_at` timestamps must survive migration.
- Follow existing Laravel/module conventions; do not add dependencies.

---

## File Map

### Create
- `app-modules/projects/database/migrations/2026_08_16_130000_create_project_task_statuses_table.php` — Project-owned task workflow schema.
- `app-modules/projects/database/migrations/2026_08_16_130100_create_work_groups_table.php` — optional Project hierarchy schema.
- `app-modules/tasks/database/migrations/2026_08_16_130200_add_project_work_structure_to_tasks.php` — nullable transition FKs for workflow/work-group migration.
- `app-modules/tasks/database/migrations/2026_08_16_130300_create_task_checklist_items_table.php` — one-level checklist schema.
- `app-modules/tasks/database/migrations/2026_08_16_130400_backfill_project_task_workflows.php` — deterministic legacy status backfill.
- `app-modules/tasks/database/migrations/2026_08_16_130500_finalize_project_task_workflow.php` — enforce non-null Project Status and remove legacy fixed `tasks.status`.
- `app-modules/projects/src/Infrastructure/Models/ProjectTaskStatus.php` — ordered Project status model.
- `app-modules/projects/src/Infrastructure/Models/WorkGroup.php` — Project hierarchy model.
- `app-modules/projects/src/Application/ProjectWorkflowManager.php` — Admin workflow definition mutations and invariants.
- `app-modules/projects/src/Application/WorkGroupManager.php` — create/move/rename/reorder/inactivate Work Groups.
- `app-modules/tasks/src/Infrastructure/Models/TaskChecklistItem.php` — logical-removal checklist model.
- `app-modules/tasks/src/Application/TaskChecklist.php` — inherited Task-authorized checklist mutations.
- `tests/Feature/ProjectWorkManagement/ProjectWorkflowTest.php`
- `tests/Feature/ProjectWorkManagement/WorkGroupTest.php`
- `tests/Feature/ProjectWorkManagement/TaskChecklistTest.php`
- `tests/Feature/ProjectWorkManagement/KanbanTaskWorkflowTest.php`
- `tests/Feature/ProjectWorkManagement/LegacyWorkflowMigrationTest.php`

### Modify
- `app-modules/projects/src/Infrastructure/Models/Project.php`
- `app-modules/projects/src/Application/ProjectLifecycle.php`
- `app-modules/projects/src/Presentation/Livewire/Show.php`
- `app-modules/projects/resources/views/show.blade.php`
- `app-modules/tasks/src/Infrastructure/Models/Task.php`
- `app-modules/tasks/src/Application/TaskWorkflow.php`
- `app-modules/tasks/src/Application/TaskCollaboration.php`
- `app-modules/tasks/src/Application/TaskNotificationRouter.php`
- `app-modules/tasks/src/Presentation/Livewire/Form.php`
- `app-modules/tasks/src/Presentation/Livewire/Show.php`
- `app-modules/tasks/resources/views/form.blade.php`
- `app-modules/tasks/resources/views/show.blade.php`
- `app-modules/tasks/resources/lang/fa/messages.php`
- `app/Livewire/Dashboard.php`
- affected existing MVP tests that encode superseded fixed-status behavior.

### Remove after all references are gone
- `app-modules/tasks/src/Domain/Enums/TaskStatus.php`

---

### Task 1: Persist Project-owned workflow, Work Groups, and checklist items

**Interfaces:**
- Produces `ProjectTaskStatus`, `WorkGroup`, `TaskChecklistItem`, `tasks.project_status_id`, and nullable `tasks.work_group_id`.
- Existing `projects.status` remains the Project lifecycle enum and is not repurposed.

- [ ] **Step 1: Write failing schema/migration tests**

```php
it('gives every project a valid project-owned workflow and every task a matching status', function (): void {
    $project = Project::factory()->create();
    $task = Task::factory()->for($project)->create();

    expect($project->taskStatuses()->active()->count())->toBeGreaterThanOrEqual(2)
        ->and($project->taskStatuses()->active()->where('is_done', true)->count())->toBe(1)
        ->and($task->refresh()->project_status_id)->not->toBeNull()
        ->and($task->projectStatus->project_id)->toBe($project->id);
});
```

- [ ] **Step 2: Run the narrow test and confirm RED**

Run: `php artisan test --compact tests/Feature/ProjectWorkManagement/LegacyWorkflowMigrationTest.php`
Expected: FAIL because workflow tables/relations do not exist.

- [ ] **Step 3: Add focused migrations**

Create Project Status with `project_id`, `title`, `position`, `is_done`, `is_active`, `created_by`, timestamps, `inactivated_at`; Work Group with `project_id`, nullable `parent_id`, `title`, nullable `description`, `position`, lifecycle status, `created_by`, timestamps; Task checklist with `task_id`, `title`, `is_completed`, `position`, `created_by`, timestamps, `removed_at`.

Transition Tasks through nullable foreign keys first. Backfill every Project with ordered statuses `باز`, `در حال انجام`, `انجام‌شده`; map legacy `in_progress` to `در حال انجام`, `completed` or a non-null historical `completed_at` to Done, and all remaining legacy statuses to `باز`. Preserve existing non-null `completed_at`; when a legacy `completed` record lacks it, use its existing `updated_at` as the consistency fallback. Then make `project_status_id` non-null and drop the legacy `status` column.

- [ ] **Step 4: Add models/relations/casts**

```php
public function projectStatus(): BelongsTo
{
    return $this->belongsTo(ProjectTaskStatus::class, 'project_status_id');
}

public function workGroup(): BelongsTo
{
    return $this->belongsTo(WorkGroup::class);
}

public function checklistItems(): HasMany
{
    return $this->hasMany(TaskChecklistItem::class)->whereNull('removed_at')->orderBy('position');
}
```

`ProjectTaskStatus` exposes `active()` scope. `WorkGroup` exposes parent/children relations without a persisted depth column. `TaskChecklistItem` casts completion/removal timestamps.

- [ ] **Step 5: Re-run migration/schema tests**

Run: `php artisan test --compact tests/Feature/ProjectWorkManagement/LegacyWorkflowMigrationTest.php`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/projects app-modules/tasks tests/Feature/ProjectWorkManagement/LegacyWorkflowMigrationTest.php
git commit -m "feat: add project workflow work groups and checklist schema"
```

---

### Task 2: Enforce Project workflow invariants and Work Group hierarchy

**Interfaces:**
- `ProjectWorkflowManager::create(User $actor, Project $project, string $title): ProjectTaskStatus`
- `ProjectWorkflowManager::rename(User $actor, ProjectTaskStatus $status, string $title): ProjectTaskStatus`
- `ProjectWorkflowManager::reorder(User $actor, Project $project, array $orderedStatusIds): void`
- `ProjectWorkflowManager::setDone(User $actor, ProjectTaskStatus $status): void`
- `ProjectWorkflowManager::inactivate(User $actor, ProjectTaskStatus $status): void`
- `WorkGroupManager::create(User $actor, Project $project, array $data): WorkGroup`
- `WorkGroupManager::update(User $actor, WorkGroup $group, array $data): WorkGroup`
- `WorkGroupManager::move(User $actor, WorkGroup $group, ?WorkGroup $parent): WorkGroup`
- `WorkGroupManager::reorder(User $actor, Project $project, array $orderedIds): void`
- `WorkGroupManager::inactivate(User $actor, WorkGroup $group): WorkGroup`

- [ ] **Step 1: Write failing workflow invariant tests** covering Admin-only mutation, minimum two active statuses, exactly one Done, atomic Done replacement, cross-Project rejection, populated-status inactivation rejection, and no hard delete.
- [ ] **Step 2: Run workflow tests and confirm RED.**
- [ ] **Step 3: Implement `ProjectWorkflowManager` with `DB::transaction()` and row locks.**

Changing Done locks the Project's active statuses, flips the previous Done to open and target to Done in one transaction, then asserts `active >= 2`, `done === 1`, `open >= 1` before commit. Inactivation refuses a status that owns Tasks and refuses any result that violates invariants.

- [ ] **Step 4: Write failing Work Group tests** for depth 1-5, depth 6 rejection, cycle/self/descendant rejection, cross-Project parent rejection, branch-move depth validation, inactivation constraints, logical lifecycle, and customer read/Admin mutate permissions.
- [ ] **Step 5: Run Work Group tests and confirm RED.**
- [ ] **Step 6: Implement `WorkGroupManager`.**

Derive depth by traversing the current Project graph; before a move calculate `newParentDepth + subtreeHeight` and reject values greater than 5. Inactivation rejects active child groups and Tasks whose `projectStatus.is_done = false`. Record `work_group.created`, `work_group.renamed`, `work_group.moved`, and `work_group.inactivated` activities.

- [ ] **Step 7: Run both focused suites**

Run: `php artisan test --compact tests/Feature/ProjectWorkManagement/ProjectWorkflowTest.php tests/Feature/ProjectWorkManagement/WorkGroupTest.php`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app-modules/projects tests/Feature/ProjectWorkManagement
git commit -m "feat: enforce project workflow and work group rules"
```

---

### Task 3: Replace fixed TaskStatus behavior with Project Status transitions

**Interfaces:**
- `TaskWorkflow::changeStatus(User $actor, Task $task, ProjectTaskStatus $status): Task`
- `TaskWorkflow::createForAdmin(...)` and `createForCustomer(...)` accept optional `project_status_id` and optional `work_group_id`.
- `Task::isDone(): bool` is metadata-based.
- `Task::scopeOverdue()` joins/filters `ProjectTaskStatus.is_done = false` and never inspects status titles.

- [ ] **Step 1: Write failing Kanban workflow tests** for any Project member moving any visible Task regardless of assignee, unauthorized Project isolation, open→done timestamp, done→open timestamp clearing, open→open preservation, create directly in Done, default first open status, cross-project/inactive status rejection, status activity snapshots, Work Group preservation, and completed-Project reopen rejection.
- [ ] **Step 2: Run focused tests and confirm RED.**
- [ ] **Step 3: Refactor `Task`** to remove enum cast and fixed status/assignee integrity. Keep assignment validation independent: nullable, or active Admin, or active Customer member of the Task's Client/Project. Add Project Status and Work Group same-Project integrity checks.
- [ ] **Step 4: Refactor `TaskWorkflow`.**

Status transitions lock Project, Task, and target status. Access is Project-membership based. `Open -> Done` sets `completed_at = now()`, `Done -> Open` clears it, `Open -> Open` leaves the value unchanged. Status transition never changes assignee. Activity metadata stores IDs and title snapshots:

```php
[
    'previous_status_id' => $old->id,
    'previous_status_title_snapshot' => $old->title,
    'new_status_id' => $new->id,
    'new_status_title_snapshot' => $new->title,
]
```

- [ ] **Step 5: Update collaboration/project lifecycle/notifications/dashboard** to use `is_done` metadata and generic status changes. Remove hard-coded Admin Queue semantics and fixed-status notification routing.
- [ ] **Step 6: Remove all production references to `TaskStatus`, then remove the enum file.**
- [ ] **Step 7: Run Kanban + existing workflow/collaboration/project tests** and update only assertions that are explicitly superseded by the newer PRD.
- [ ] **Step 8: Commit**

```bash
git add app app-modules tests
git commit -m "feat: move task workflow to project-owned statuses"
```

---

### Task 4: Implement lightweight checklist Subtasks

**Interfaces:**
- `TaskChecklist::add(User $actor, Task $task, string $title): TaskChecklistItem`
- `TaskChecklist::rename(User $actor, TaskChecklistItem $item, string $title): TaskChecklistItem`
- `TaskChecklist::toggle(User $actor, TaskChecklistItem $item, bool $completed): TaskChecklistItem`
- `TaskChecklist::reorder(User $actor, Task $task, array $orderedItemIds): void`
- `TaskChecklist::remove(User $actor, TaskChecklistItem $item): TaskChecklistItem`

- [ ] **Step 1: Write failing checklist tests** for Admin/member parity, unauthorized isolation, no nested model/parent field, no independent workflow fields, task/checklist completion independence, done/completed-project read-only behavior, reopen preservation, logical removal, reorder, Work Group/status move preservation, audit actions, and zero independent notifications.
- [ ] **Step 2: Run checklist tests and confirm RED.**
- [ ] **Step 3: Implement `TaskChecklist`.**

All mutation resolves authorization from parent Task and rejects Project completed or Task Done. Blank titles are rejected. Removal sets `removed_at`; no `delete()` path exists. Reorder validates that every supplied active item belongs to the same Task. Audit uses parent Task activity: `subtask.added`, `subtask.renamed`, `subtask.completed`, `subtask.uncompleted`, `subtask.removed`. Do not invoke `NotificationDispatcher`.

- [ ] **Step 4: Run checklist tests**

Run: `php artisan test --compact tests/Feature/ProjectWorkManagement/TaskChecklistTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app-modules/tasks tests/Feature/ProjectWorkManagement/TaskChecklistTest.php
git commit -m "feat: add lightweight task checklist subtasks"
```

---

### Task 5: Deliver Livewire Kanban, workflow management, hierarchy, and checklist UX

**Interfaces:**
- Project Show owns Kanban actions, Admin workflow controls, Work Group controls, and search/filter state.
- Task Form creates/edits with active Project Status and optional Work Group.
- Task Show exposes status transition to every Project member and checklist actions to every Task member while mutable.

- [ ] **Step 1: Add Livewire component tests for Project Show and Task Show interactions.**
- [ ] **Step 2: Run UI tests and confirm RED.**
- [ ] **Step 3: Convert Project Show task section to Kanban.**

Render exactly one column per active Project Status ordered by `position`. Task cards show reference, title, assignee, priority, due date, and Work Group context. Add HTML5/Alpine drag/drop that calls the same server `moveTask(taskId, statusId)` action used by non-drag controls. Add Project-wide task search before hierarchy/column grouping so collapsed hierarchy never hides search matches.

- [ ] **Step 4: Add Admin Project workflow controls** for create, rename, reorder, set Done, and logical inactivation; Customers receive read-only columns only.
- [ ] **Step 5: Add Work Group UI** only when groups exist or Admin explicitly opens management. Render Root Tasks clearly, nested groups to maximum five levels, progress as completed Tasks / non-cancelled-equivalent active task set using Done metadata, and Admin create/rename/move/reorder/inactivate controls.
- [ ] **Step 6: Update Task Form** to load active statuses and Work Groups from selected Project. Both Admin and Customer can select any active initial status including Done; omitted status defaults server-side to first open. Customer still cannot set assignee/priority/due date.
- [ ] **Step 7: Update Task Detail** with generic Project Status selector and checklist add/rename/toggle/reorder/remove controls. Hide/disable mutation controls for Done Task or Completed Project, except status reopen control when Project itself is active.
- [ ] **Step 8: Add Persian labels/activity translations** without deriving semantics from status titles.
- [ ] **Step 9: Run focused Livewire tests and build**

Run: `php artisan test --compact --filter='Kanban|Checklist|WorkGroup|ProjectWorkflow'`
Run: `npm run build`
Expected: PASS.

- [ ] **Step 10: Commit**

```bash
git add app-modules/projects app-modules/tasks resources tests
git commit -m "feat: add kanban hierarchy and checklist interfaces"
```

---

### Task 6: Regression closure and release verification

- [ ] **Step 1: Run the complete test suite**

Run: `php artisan test`
Expected: all tests PASS.

- [ ] **Step 2: Fix every regression without weakening Source-of-Truth requirements.** Existing tests that encode superseded fixed-status rules are rewritten to assert Project-owned workflow behavior; unrelated MVP tests remain intact.

- [ ] **Step 3: Run formatting**

Run: `vendor/bin/pint --dirty --format agent`
Then run: `php artisan test`
Expected: PASS.

- [ ] **Step 4: Run CI-equivalent checks**

```bash
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

Expected: all commands exit 0.

- [ ] **Step 5: PRD gap audit**

Re-read all four PRDs and confirm: Work Group max-depth/cycle/isolation/inactivation; checklist one-level/no-notification/logical removal/read-only; Project Status invariants/activity snapshots/migration; Kanban Project ownership/member transitions; No Hard Delete; Project completion/overdue semantics; preservation of existing MVP authorization/collaboration/audit.

- [ ] **Step 6: Commit final fixes, open PR to `main`, wait for GitHub Actions CI, and inspect failed job logs if any.**
- [ ] **Step 7: Only after green CI and final PRD audit, merge into `main` and verify the merged commit status.**
