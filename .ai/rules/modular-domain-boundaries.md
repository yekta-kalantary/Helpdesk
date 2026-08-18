# Modular Domain Boundaries

This project is a modular monolith. Each business domain must remain inside its owning module.

- Keep domain rules, application use cases, infrastructure models, presentation adapters, routes, migrations, factories, and translations inside the owning module.
- Do not place module-specific code in another module or in the root `app/` directory.
- Treat `Identity`, `Clients`, `Projects`, and `Tasks` as isolated bounded contexts with explicit ownership.
- Do not import another module's infrastructure models, database tables, internal services, or presentation classes directly.
- Cross-module communication must use stable contracts, value objects, or domain/integration events rather than internal implementation details.
- A module may depend on a lower-level shared contract, but modules must not create circular dependencies or bypass another module's public boundary.
- Keep shared root-level code limited to genuinely cross-cutting infrastructure and framework integration; it must not contain business rules owned by a module.
- Keep module-specific localization, validation, authorization, routes, and frontend page contracts in the owning module.
- When a feature spans multiple domains, place orchestration in an explicit application or integration layer and keep each domain's business decision inside its own module.
