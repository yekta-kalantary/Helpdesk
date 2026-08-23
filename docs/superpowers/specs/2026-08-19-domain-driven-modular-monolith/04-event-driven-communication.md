# Event-Driven Communication

## Domain Events

Domain events describe meaningful state changes inside the owning context:

```text
ProjectCreated
ProjectMemberAdded
TaskCreated
TaskAssigned
TaskStatusChanged
CommentAdded
AttachmentUploaded
```

## Integration Events

Only stable, public integration events cross context boundaries. They contain identifiers and immutable data, not Eloquent models.

```json
{
  "event": "ProjectCreated.v1",
  "event_id": "uuid",
  "occurred_at": "timestamp",
  "producer": "projects",
  "project_id": "uuid",
  "client_id": "uuid",
  "name": "string"
}
```

Required event properties:

- Stable event name and version
- Unique event identifier
- Occurrence timestamp
- Producer context
- Correlation and causation identifiers where applicable
- Immutable payload
- Explicit schema ownership

## Reliability Requirements

- Use the Outbox Pattern when publishing events after database changes.
- Consumers must be idempotent.
- Store processed event identifiers or use an equivalent deduplication mechanism.
- Support retry with backoff and a dead-letter path.
- Do not publish an event before the owning transaction commits.
- Do not use events for simple in-process return values that require an immediate response.

## Cross-Context Workflows

Cross-context operations must be orchestrated explicitly.

Example: creating a project for a client:

1. Projects receives `CreateProject`.
2. Projects validates the client through a public Client contract or trusted projection.
3. Projects creates the project in its own transaction.
4. Projects writes `ProjectCreated.v1` to its outbox.

If a workflow spans multiple contexts, use an explicit process manager. Do not update another context's tables inside the current transaction.
