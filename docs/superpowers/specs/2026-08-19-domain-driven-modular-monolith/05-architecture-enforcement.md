# Architecture Enforcement

The architecture is not complete until it is mechanically enforced.

## Mandatory Change Contract

Every change must follow `.ai/rules/agent-workflow.md`. For domain work, the agent or contributor must identify these items before implementation:

- Owning bounded context
- Business data being read or changed
- Public command, query, contract, projection, or event used at each context boundary
- Allowed dependency direction
- Tests that will prove behavior and boundary compliance

If ownership is unclear or the requested design requires direct access to another context's internals, implementation must stop until the boundary is decided.

## Required Checks

- Architecture tests reject imports from another context's `Infrastructure` namespace.
- Each module's Composer manifest declares only allowed public dependencies.
- Static analysis checks module dependency direction.
- Tests verify that consumers react to integration events without accessing producer tables.
- Database migrations are scoped to the owning module.
- Every new business table and model is assigned an owner in the ownership matrix.
- Pull requests document new cross-context commands, queries, events, and projections.

## Required Evidence

A completion report must include:

- Applicable rules and architecture documents reviewed
- Ownership decision for changed business data
- Tests added or updated
- Exact verification commands and outcomes
- Remaining known violations in changed files
- Git status and diff inspection outcome

Passing behavior tests alone is insufficient when an architecture boundary changed.

## Existing Violations

Existing boundary violations are migration work, not precedent for new code.

- Do not copy or extend a violating pattern merely because it already exists.
- Do not increase the number of cross-context infrastructure dependencies.
- When a changed file contains an existing violation, remove it when that removal is necessary for the requested change and can be verified safely.
- If safe removal is outside the current task, report the violation explicitly and avoid making it worse.
- A changed file must not introduce a new violation under the label of backward compatibility or consistency.

## Stop Conditions

Do not continue implementation when:

- Data ownership cannot be assigned to exactly one bounded context.
- The change requires another context's infrastructure model or table.
- A public contract would expose an Eloquent model or mutable internal object.
- Required architecture verification is unavailable and the change affects a boundary.
- The requested behavior contradicts the ownership matrix or dependency direction.

Resolve the design conflict before editing further.

## Definition of Done

A context is compliant when:

- Its business data has one documented owner.
- Its domain layer has no framework dependency.
- Its database tables and migrations are owned by the context.
- Other contexts use contracts or events instead of internal models.
- Its public commands, queries, and integration events are documented.
- Its consumers are idempotent and tested.
- Its architecture tests pass.
- It can be understood and tested without reading another context's implementation details.

A task is complete only when its changed files satisfy the applicable rules, all mandatory checks pass, and the completion report contains the required evidence.
