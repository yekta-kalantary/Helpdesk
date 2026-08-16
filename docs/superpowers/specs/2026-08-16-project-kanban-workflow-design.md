# Project Kanban & Custom Task Workflow — Design Spec

Date: 2026-08-16

Status: Approved design, awaiting implementation planning

Related product requirements:

- `docs/product/client-task-management-mvp.md`
- `docs/product/hybrid-project-work-hierarchy-prd.md`
- `docs/product/task-checklist-subtasks-prd.md`
- `docs/product/project-kanban-workflow-prd.md`

## 1. Context

The existing MVP uses fixed task statuses. The platform now needs to support different project types—SEO, WordPress, software development, design, digital marketing, and other agency work—without forcing all projects into one hard-coded workflow.

The approved direction is a Project-owned workflow. Each Project defines its own ordered status set, and the Project task view renders those statuses as Kanban columns.

## 2. Core Model

```text
Project
├── Project Status A [Open]
├── Project Status B [Open]
├── ...
└── Project Status N [Done]

Task
└── project_status_id
```

Rules:

1. Every Project has at least two active statuses.
2. Exactly one active status is marked Done.
3. At least one active status is not Done.
4. Status order defines Kanban column order.
5. Task status belongs to the same Project as the Task.

There is no global task-status enum after this extension becomes authoritative.

## 3. Project Status Aggregate

Recommended product-level fields:

```text
ProjectStatus
- id
- project_id
- title
- position
- is_done
- is_active
- created_by
- created_at
- updated_at
- inactivated_at nullable
```

The exact persistence naming is an implementation detail, but Project Status must be a first-class Project-scoped entity rather than a free-text field on Task.

## 4. Workflow Administration

Only Admin manages workflow definition.

Admin may:

- create a status
- rename a status
- reorder statuses
- select the single Done status
- inactivate a status when integrity rules allow it

Customer members cannot change workflow definition.

Changing which status is Done must be atomic. The system must never persist a valid Project with zero or multiple active Done statuses.

A Project may not be left with fewer than two active statuses.

## 5. Status Inactivation

Project Statuses are not hard-deleted from normal UI operations.

A status with Tasks cannot be inactivated until those Tasks are moved to another active status. This may be performed before inactivation or as one explicit transactional operation in implementation.

The current Done status cannot be inactivated unless another active status becomes Done in the same valid transition.

Historical activity must retain enough status snapshot information to remain understandable after rename or inactivation.

## 6. Task Creation

Any user who is already permitted to create a Task in a Project may choose the initial status.

The Create Task UI exposes every active status of that Project, including Done.

Rules:

1. Selected status must be active and belong to the same Project.
2. Selecting Done is valid.
3. Creating directly in Done sets `completed_at` at creation time.
4. If the creator does not choose a status, the first active non-Done status by `position` is used.

The old rule that Customer-created Tasks must automatically enter `Waiting Admin` is superseded by this design.

## 7. Task Status Mutation

Any Admin or Customer member who can access the Project may change the status of any visible Task in that Project.

Assignment does not restrict status mutation.

Examples that are explicitly allowed:

- Customer moves a Task assigned to another Customer.
- Customer moves a Task assigned to Admin.
- Customer moves a Task into Done.
- Customer reopens a Done Task by moving it back to an Open status.

Authorization is therefore Project-access based, not Assignee based.

## 8. Completion Semantics

`is_done` is the system completion marker. Status labels themselves carry no completion semantics.

Transitions:

```text
Open -> Open
completed_at unchanged

Open -> Done
completed_at = transition time

Done -> Open
completed_at = null
```

A Task created directly in Done receives `completed_at` on creation.

Status changes must not automatically assign, unassign, or otherwise modify Task ownership.

The previous fixed semantics around `Waiting Admin`, `Waiting Customer`, `Todo`, `In Progress`, `Completed`, and `Cancelled` are not carried forward.

## 9. Done Task Mutability

A Task in the Done status is read-only for normal Task content mutations under the existing closed-task behavior.

However, users authorized to change status may move the Task out of Done. That transition reopens the Task, clears `completed_at`, and restores normal editable behavior.

This creates one consistent reopen mechanism: status transition out of Done.

## 10. Kanban View

The primary Project task view is Kanban.

Each active Project Status maps to exactly one Kanban column.

Column order is `ProjectStatus.position`.

Dragging a Task card from one column to another is a status-change action, not a separate board-only concept.

The server-side status-change action must validate the same rules whether invoked by drag-and-drop, a Task Detail control, or API.

Suggested card content:

- Task reference
- title
- assignee
- priority
- due date when present
- Work Group context when useful

Global task list/search may remain for cross-project lookup and reporting. Kanban is the primary Project-scoped task interaction model.

## 11. Work Group Integration

Work Group and Project Status are orthogonal dimensions.

```text
Project
├── Work Group hierarchy
│   └── Task
└── Project workflow
    └── Task status
```

A Task may belong to Root Project or any allowed Work Group and independently has exactly one Project Status.

Kanban columns must never be generated from Work Groups.

Work Group may later be used as a Kanban filter or card context without changing this model.

## 12. Subtask Integration

Lightweight Subtasks remain checklist items only.

They:

- do not receive Project Status
- do not appear as Kanban cards
- do not affect Task status automatically
- do not complete a Task when all are checked

Task remains the unit moved across the Kanban workflow.

## 13. Audit Model

Every Task status transition creates Task Activity.

Minimum audit payload:

```text
- task_id / task_reference
- previous_status_id
- previous_status_title_snapshot
- new_status_id
- new_status_title_snapshot
- actor_user_id
- changed_at
```

The actor is essential because every Project member may move any visible Task.

Status rename must not rewrite historical snapshots.

Task creation activity should include the initial status.

## 14. Project Completion

Project completion depends on the Project's Done status, not a global enum.

A Project can be Completed only when every active Task is in the Project's Done status.

Any Task in any Open status blocks Project completion.

A Task cannot be reopened while the Project itself is Completed; the Project must first be reopened according to Project lifecycle rules.

## 15. Overdue Semantics

Legacy logic based on `Completed` or `Cancelled` status names is superseded.

A Task is overdue when:

```text
due_date < now
AND current_project_status.is_done = false
```

No status title is inspected.

## 16. Legacy Rules Superseded

After this feature is implemented, the following fixed-status behaviors from the initial MVP are no longer authoritative:

- fixed global statuses
- Customer-created Task forced into Waiting Admin
- Assignee constraints tied to fixed status names
- Customer can only change status of Tasks assigned to self
- Admin-only reopen of Completed Task
- Waiting Admin as a hard-coded queue/status
- overdue detection using Completed/Cancelled names

Unrelated Task rules remain intact unless an implementation dependency requires an explicit follow-up product decision.

## 17. Migration Strategy Requirements

Implementation planning must define a deterministic migration from legacy fixed statuses to Project Status records.

Required properties:

1. Every existing Project receives at least two active statuses.
2. Exactly one migrated status is Done.
3. Every existing Task receives a valid `project_status_id` in its own Project.
4. Existing `completed_at` semantics are preserved.
5. References, comments, attachments, activities, Work Groups, and Subtasks are preserved.
6. Migration is repeatable/testable and must not leave orphan status references.

The exact mapping of each legacy status is intentionally deferred to the implementation plan because it depends on the current data and release strategy, but the resulting model must satisfy all invariants above.

## 18. Error Handling

The server must reject:

- status from another Project
- inactive status selected for new or existing Task
- Project workflow with fewer than two active statuses
- workflow with zero Done statuses
- workflow with more than one Done status
- inactivation of a populated status without Task migration
- inactivation of Done without valid replacement
- status mutation by a user without Project access
- reopening a Task while its Project is Completed

Failed transitions must not partially change Task status or `completed_at`.

## 19. Testing Requirements

Implementation must cover at least:

1. Admin creates, renames, reorders, and inactivates valid statuses.
2. Customer cannot modify workflow definition.
3. A Project cannot have fewer than two active statuses.
4. A Project cannot have zero or multiple Done statuses.
5. Changing Done status is atomic.
6. Admin and Customer members can move any visible Project Task regardless of Assignee.
7. Unauthorized Customer cannot move Task in another Project.
8. Open-to-Done sets `completed_at`.
9. Done-to-Open clears `completed_at`.
10. Open-to-Open preserves completion state.
11. Task can be created in any active status, including Done.
12. Task with omitted initial status uses first Open status by position.
13. Cross-project status assignment is rejected.
14. Status transition creates activity with actor and old/new status snapshots.
15. Status with Tasks cannot be inactivated without migration.
16. Done Task may be reopened by an authorized Project member through status change.
17. Work Group association survives status changes.
18. Subtask state survives status changes.
19. Overdue uses `is_done`, not status title.
20. Project completion requires all active Tasks to be in Done.

## 20. Explicit Non-Goals

Not included:

- transition restrictions
- WIP limits
- per-status permissions
- workflow templates
- status automations
- SLA by status
- multiple Done statuses
- hard-coded Cancelled semantics
- swimlanes
- dependencies

## 21. Final Design Principle

**Workflow belongs to the Project. Kanban is the visual representation of that workflow. Every Task has exactly one status from its own Project, and every Project has exactly one Done status. Project members may move any visible Task, and Activity provides the audit trail.**
