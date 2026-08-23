# Clean Architecture

## Module Structure

Each module should use focused layers:

```text
app-modules/<context>/src/
  Domain/
    Entities/
    ValueObjects/
    Events/
    Repositories/
    Services/
  Application/
    Commands/
    Queries/
    Handlers/
    DTOs/
  Infrastructure/
    Persistence/
    Eloquent/
    EventBus/
    Providers/
  Presentation/
    Http/
    Consumers/
```

## Layer Responsibilities

### Domain

Domain entities, value objects, domain services, repository interfaces, and domain events contain business rules and invariants. They must remain framework-independent.

### Application

Application handlers coordinate commands and queries, call domain behavior, enforce use-case authorization, and define transaction boundaries. They depend on domain contracts rather than concrete persistence classes.

### Infrastructure

Infrastructure implements repositories, persistence, event dispatching, Laravel providers, and external adapters. Eloquent models are persistence adapters and must not become the public API of a module.

### Presentation

Presentation maps HTTP requests, console commands, and event consumers to application commands and queries. It must not contain domain decisions or direct cross-context database access.

## Rules

- Domain must not depend on Laravel, Eloquent, HTTP, queues, or database implementations.
- Application must not query another context's tables.
- Infrastructure must not expose internal models as cross-context contracts.
- Presentation must use Form Requests, DTOs, commands, and queries instead of embedding business logic.
- Repositories belong to the context that owns the data they persist.
- Root `app/` is limited to framework bootstrapping and genuinely technical integration.
