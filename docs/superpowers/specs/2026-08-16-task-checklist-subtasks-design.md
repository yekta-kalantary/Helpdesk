# Lightweight Task Subtasks — Design Spec

Date: 2026-08-16

Status: Approved design, awaiting implementation planning

Related product requirements:

- `docs/product/client-task-management-mvp.md`
- `docs/product/hybrid-project-work-hierarchy-prd.md`
- `docs/product/task-checklist-subtasks-prd.md`

## 1. Context

The platform serves projects with very different sizes and disciplines. The Hybrid Work Group model handles structural decomposition at the Project level, but individual Tasks still need a lightweight way to represent small execution steps.

Creating a full Task for every small step would add unnecessary assignment, status, priority, notification, conversation, and list noise. The approved solution is therefore a lightweight Subtask model that behaves as a checklist item inside a Task.

## 2. Core Decision

A Subtask is not a Task entity and does not participate in the Task workflow.

Hierarchy:

```text
Project
├── Work Group (optional, max 5 levels)
│   └── Task
│       ├── Subtask
│       ├── Subtask
│       └── Subtask
└── Task
    ├── Subtask
    └── Subtask
```

Subtasks are one level only. A Subtask cannot contain another Subtask.

Task remains the unit for:

- assignment
- status
- priority
- due date
- comments
- attachments
- notifications
- completion
- global task listing and search

Subtask remains a checklist item for:

- title
- completed / not completed
- ordering

## 3. Permissions

Subtask authorization inherits entirely from the parent Task.

Any user who has permission to interact with the parent Task may:

- create a Subtask
- rename a Subtask
- complete a Subtask
- uncomplete a Subtask
- reorder Subtasks
- logically remove a Subtask

This applies to Admin and Customer members with Task access.

There is no per-Subtask permission or visibility model.

Users without parent Task access cannot read or mutate its Subtasks.

## 4. Lifecycle Rules

Subtask completion and Task completion are intentionally independent.

Rules:

1. Completing all Subtasks does not automatically complete the Task.
2. Completing a Task does not automatically complete open Subtasks.
3. A Task may be explicitly completed while one or more Subtasks remain open.
4. Completed or Cancelled Tasks expose their Subtasks as read-only.
5. Reopening a Task preserves the existing checked/unchecked state of every Subtask.
6. Completed Projects make all contained Task checklists read-only until the Project is reopened.
7. Moving a Task between Work Groups preserves all Subtasks and their state.
8. Subtasks are never hard-deleted from the UI; removal is logical and audit-preserving.

## 5. Data Model

Recommended product-level fields:

```text
TaskChecklistItem
- id
- task_id
- title
- is_completed
- position
- created_by
- created_at
- updated_at
- removed_at nullable
```

`task_id` is the only structural parent relation.

There is no `parent_id` because nested Subtasks are explicitly out of scope.

There is no assignee, status workflow, priority, due date, comment relation, attachment relation, or notification relation on the Subtask.

The exact database table/class naming is an implementation detail, but using a name such as `task_checklist_items` is preferable to a generic `tasks.parent_id` model because it prevents accidental expansion of Subtasks into full Task semantics.

## 6. Domain Boundaries

The Task aggregate owns the checklist.

Subtask operations should be validated through the same Task-level authorization boundary used by the existing Task domain. A Subtask must not become a second authorization path into Project data.

The Work Group hierarchy is orthogonal to Subtasks:

```text
Project -> Work Group -> Task -> Subtask
```

A Subtask never points directly to a Project or Work Group. Those contexts are resolved through its parent Task.

## 7. Progress Semantics

A Task may display local checklist progress:

```text
completed active subtasks / all active subtasks
```

Example: `3/5 completed`.

This value is informational only.

It does not:

- change Task status
- complete a Task
- alter Work Group progress directly
- create a separate Project progress model

Work Group progress remains based on completed Tasks, not completed Subtasks.

## 8. Audit Behavior

Subtasks do not receive an independent Activity Timeline.

Important changes are recorded in the parent Task activity context:

- Subtask Added
- Subtask Renamed
- Subtask Completed
- Subtask Uncompleted
- Subtask Removed

Simple reorder operations do not require Activity entries unless later product requirements demand that level of audit detail.

No independent notification is generated for Subtask changes.

## 9. UX Behavior

Subtasks live inside Task Detail and do not have a standalone page.

The UI should provide:

- quick inline creation
- checkbox toggle
- inline or lightweight title editing
- ordering controls
- logical remove action
- optional `x/y completed` summary

The UI must not show Task-level controls on Subtasks such as Assignee, Priority, Due Date, Status, Comment, or Attachment.

The checklist must remain usable on mobile.

When the Task or Project is read-only, checklist mutation controls are disabled or hidden.

## 10. Error and Validation Rules

The server must reject:

- blank Subtask titles
- mutations by users without parent Task access
- mutations on Completed/Cancelled Tasks
- mutations inside Completed Projects
- attempts to create nested Subtasks
- attempts to attach a Subtask to an invalid Task

Logical removal must preserve audit/history.

## 11. Testing Requirements

Implementation must cover at least:

1. Admin creates, edits, toggles, reorders, and removes Subtasks.
2. Customer with Task access can perform the same operations.
3. Unauthorized Customer cannot read or mutate another Project's Subtasks.
4. Completing all Subtasks does not change Task status.
5. Task can be completed with open Subtasks.
6. Completing Task does not modify Subtask completion states.
7. Reopening Task preserves Subtask states.
8. Completed/Cancelled Task checklist is read-only.
9. Completed Project checklist is read-only.
10. Logical removal preserves record/audit history.
11. Moving Task between Work Groups preserves checklist state.
12. Subtask changes do not generate independent notifications.
13. Nested Subtask creation is rejected.

## 12. Explicit Non-Goals

Not included:

- full Task-style Subtasks
- nested Subtasks
- independent assignee
- independent status workflow
- independent priority
- independent due date
- independent comments or attachments
- independent notifications
- independent reference/URL
- dependencies
- time tracking
- estimates/story points
- per-Subtask permissions
- automatic parent completion

## 13. Final Design Principle

**Subtask is a checklist item inside a Task; it is not a smaller Task.**

This keeps the Hybrid hierarchy flexible for large projects while preserving low ceremony for small execution steps.