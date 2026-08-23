# Boundary and Data Ownership Repair

## Status

Proposed on 2026-08-22.

## Goal

Remove cross-context infrastructure dependencies and direct ownership violations before auditing application defects. Preserve the existing user-visible behavior while making the bounded contexts independently understandable, testable, and ready for future scale.

## Ownership

| Data | Owner | Consumer access |
| --- | --- | --- |
| Accounts, credentials, roles, and account status | Identity | Account-facing contracts and opaque account IDs |
| Clients and client lifecycle | Clients | Client summaries and opaque client IDs |
| Projects, memberships, work groups, and task statuses | Projects | Project and membership queries with opaque IDs |
| Tasks, checklists, comments, and attachments | Tasks | Task queries with opaque IDs |
| Activity records | Audit | Immutable activity events and read models |
| Notification delivery records | Notifications | Immutable notification commands and delivery status |

An opaque identifier may be persisted in another context, but no context may query or mutate the owning context's table directly.

## Current Violations

- `Projects` imports Client, Identity, and Task infrastructure models in its models and application services.
- `Tasks` imports Identity and Projects infrastructure models in its models and application services.
- `Identity` imports the Client infrastructure model for relationships and account eligibility checks.
- Root `app/Policies` and `app/Support` contain business rules spanning multiple contexts.
- Module Composer manifests encode dependencies that are currently required only because internal Eloquent models are imported across context boundaries.

## Public Contracts

Each owner exposes immutable application-layer DTOs and query interfaces. Consumers receive scalar IDs and DTO values only, never Eloquent models, builders, relations, or mutable collections.

### Identity

- `AccountSummary`: account ID, role, active status, and optional client ID.
- `AccountDirectory`: look up accounts, validate active accounts, and retrieve authorization facts needed by another context.

### Clients

- Keep `ClientSummary` and `ActiveClientDirectory` as the Client-owned read contract.
- Add a client-status query only where Identity must validate that a customer account may authenticate.

### Projects

- `ProjectSummary` and `ProjectMembershipDirectory`: validate a project, active project membership, and project-owned task status/work group facts.
- `ProjectAccessQuery`: determine whether an account can view a project without exposing the `Project` model.

### Tasks

- Task behavior consumes Identity and Projects contracts in the application layer before persistence.
- The Task Eloquent model retains only Task-owned relations and invariants; it no longer validates external IDs by querying foreign models.

### Audit

- Own the `activities` table, activity persistence, and activity read models.
- Consume versioned immutable events from Identity, Clients, Projects, and Tasks.
- Do not import producer infrastructure models or perform producer-table queries.

### Notifications

- Own notification delivery persistence, channels, and delivery status.
- Consume versioned immutable events and resolve recipients through the Identity public contract.
- Do not import producer infrastructure models or construct resource URLs from Eloquent models.

## Event-Driven Communication

Cross-context state changes publish versioned immutable integration events through a technical integration layer in root `app/`. Each producer writes an event to a transactional outbox in the same database transaction as its business state. An after-commit dispatcher delivers pending events in-process initially; the same outbox contract permits queued delivery later without changing producer APIs.

Every event contains only scalar IDs, timestamps, event version, correlation ID, and immutable business facts. Events never contain an Eloquent model, builder, relation, or mutable collection.

The initial event set is:

- `ProjectMembershipRemovedV1`: project ID, account ID, actor ID, and occurrence timestamp. Tasks releases affected assignments; Audit and Notifications record the result.
- `ProjectTaskStatusChangedV1`: project task status ID, project ID, previous completion state, new completion state, actor ID, and occurrence timestamp. Tasks synchronizes completion timestamps.
- `TaskAssignmentChangedV1`: task ID, previous assignee ID, new assignee ID, actor ID, and occurrence timestamp. Notifications sends recipient-specific messages and Audit records the change.
- `TaskCollaborationChangedV1`: task ID, action, actor ID, and occurrence timestamp. Audit and Notifications consume the event when applicable.

Consumers record a stable event ID before side effects and ignore duplicate deliveries. A failed consumer does not roll back the producer transaction; the outbox retains the event for retry through the event dispatching infrastructure. The integration layer owns only technical event storage and dispatching, not business data or business rules.

## Migration Sequence

1. Add the Audit and Notifications module skeletons, owned migrations, provider registration, immutable event contracts, and idempotent consumer tracking. Add the root technical integration layer with transactional outbox storage and an after-commit dispatcher.
2. Add application DTOs and contracts in the owning modules, with infrastructure implementations registered by their module service providers.
3. Change Projects services to accept account IDs or account summaries. Replace `User`, `Client`, and `Task` relations and queries with project-owned membership/status/work-group persistence, public contracts, and integration events.
4. Change Tasks workflows to validate project membership, project status, work groups, and assignee eligibility through the Identity and Projects contracts before creating or updating a task. Replace reverse project callbacks with event consumers.
5. Change Identity customer eligibility to use the Clients public contract rather than the Client Eloquent relation.
6. Move business policies and support services from root `app/` into their owning module. Move activity and notification records to Audit and Notifications. Keep root `app/` only for framework composition and technical integration.
7. Remove cross-context infrastructure imports, Eloquent relationships, and now-unneeded module Composer dependencies.
8. Update tests and factories to use each module's public commands, queries, and events rather than another module's internal models wherever the test exercises a boundary.

Existing cross-context foreign keys are replaced by indexed scalar references through forward migrations that preserve existing records. New Audit, Notifications, outbox, and consumer-deduplication tables are owned by their declared supporting or technical context. No business-data migration changes are made beyond preserving these identifiers and removing foreign-key constraints.

## Error Handling

Public queries return immutable summaries or explicit absence. Application services convert missing, inactive, unauthorized, or incompatible external facts into the existing domain validation errors. External owners are never queried from an Eloquent model event, relationship, scope, or transaction callback. Event consumers are idempotent, observable, and retried independently of the producer transaction.

## Verification

- Add architecture tests that reject imports from another module's `Infrastructure` namespace in `src/`.
- Add tests proving Projects membership and access decisions use Identity contract data without querying Identity tables.
- Add tests proving Tasks validates project and account facts through public contracts without importing Projects or Identity models.
- Add tests proving Identity customer authentication checks Client status through the Clients contract.
- Add contract tests proving every integration event contains only immutable scalar data and an event version.
- Add consumer tests proving duplicate events cause no duplicate task, audit, or notification side effect.
- Add consumer failure tests proving producer transactions commit while failed event deliveries are retryable.
- Run the affected Pest tests, the full suite, Pint, Composer validation, frontend build, and `git diff --check`.

## Non-Goals

- Do not redesign user-visible workflows or data schema beyond what is necessary to enforce ownership.
- Do not introduce asynchronous integration events in this repair; synchronous application query contracts are sufficient for the current monolith.
- Do not perform speculative refactors outside the identified boundary violations.
