# Scope and Principles

## Goal

Build the Helpdesk application as a domain-driven modular monolith with explicit bounded contexts, clean architecture, event-driven internal communication, and strict single ownership of every piece of business data.

## Core Principles

1. Every business datum has exactly one owning bounded context.
2. The owning context is the only context allowed to create, update, or delete that datum.
3. Other contexts may keep read-only projections, but projections are not sources of truth.
4. Contexts communicate through public contracts, application commands, queries, and integration events.
5. A module must not import another module's infrastructure model, repository, migration, internal service, or presentation class.
6. Cross-context Eloquent relationships and cross-context database joins are forbidden.
7. Domain code must not depend on Laravel, Eloquent, HTTP, queues, or database implementations.
8. Events must be durable, versioned, observable, and safe to process more than once.
9. Framework integration belongs at the Infrastructure or Presentation boundary.
10. Shared code is limited to genuinely technical concerns and stable contracts; business concepts remain owned by their context.

## Dependency Direction

```text
Presentation -> Application -> Domain
Infrastructure -> Application and Domain
```

The Domain layer contains business rules and invariants. The Application layer coordinates use cases. Infrastructure implements persistence and framework adapters. Presentation maps external requests to application contracts.

## Current Alignment

The current codebase is not yet compliant. The main violations are direct imports between Projects, Tasks, Clients, and Identity infrastructure models, plus root-level policies and support services that know multiple domain models.

The refactoring target is:

- Identity owns the account model.
- Clients owns client and contact models.
- Projects owns project, membership, work group, and project status models.
- Tasks owns task and checklist models.
- Collaboration owns comments and attachments, or remains an explicitly separated area inside Tasks.
- Root `app/` contains only framework bootstrapping and technical integration.
