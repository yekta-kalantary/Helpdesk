# Domain Architecture

The canonical detailed architecture is documented in `docs/superpowers/specs/2026-08-19-domain-driven-modular-monolith/index.md`. Read the relevant document from that index before changing domain code, module boundaries, persistence, or event handling.

- Every business datum has exactly one owning bounded context.
- Only the owning context may create, update, or delete its business data.
- Do not import another context's infrastructure models, repositories, migrations, services, or presentation classes.
- Do not create cross-context Eloquent relationships, foreign keys, joins, or direct table access.
- Use public contracts, DTOs, application commands, queries, projections, or domain/integration events for cross-context communication.
- Keep Domain framework-independent; keep module-specific business code inside its owning module.
- Keep root `app/` limited to framework bootstrapping and genuinely technical integration.
- Before editing domain behavior, state the owning context and verify the change against the ownership matrix.
- Do not declare architecture work complete while a changed file contains a known boundary violation.
