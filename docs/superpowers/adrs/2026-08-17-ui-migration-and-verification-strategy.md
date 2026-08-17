# ADR: UI Migration And Verification Strategy

## Title

Progressive, behavior-preserving migration with explicit verification gates.

## Status

Accepted

## Context

The approved design-system spec covers the application shell, dashboard, client/project/task lists, project workspace, task detail, forms, notifications, authentication surfaces, shared components, and error surfaces. The application is a Laravel Blade/Livewire system with existing feature and unit tests. The current baseline has known test failures, while the asset build currently succeeds. A broad visual rewrite would make it difficult to distinguish intentional visual changes from regressions in routes, authorization, bindings, and domain behavior.

The migration therefore needs independently reviewable phases, a baseline that is not silently overwritten, and gates that cover source policy, build, behavior, accessibility, responsive layout, and mixed RTL/LTR content.

## Decision

Migrate progressively in this fixed order:

1. baseline and inventory;
2. token foundation;
3. global CSS foundation;
4. shared foundations/components;
5. shell/navigation;
6. dashboard/lists/forms;
7. project workspace/task detail;
8. notifications/auth surfaces;
9. raw utility cleanup;
10. accessibility/responsive audit;
11. final verification.

Each phase is independently reviewable and must preserve route names, URLs, controllers, models, policies, permissions, Livewire actions/bindings, backend validation, authorization boundaries, and domain workflow. Phase boundaries are enforced as follows:

- Baseline gate records source inventory, current tests, current build, route/Livewire contracts, and known failures. Existing failures are tracked, not hidden by changing tests for green output.
- Token/global gates verify the three token layers, naming, font, RTL direction, logical properties, focus-visible, reduced motion, and minimum targets before broad view migration.
- Shared-component gate verifies contracts and primary consumers before moving page patterns.
- Each page-pattern gate runs the narrowest relevant feature/UI tests, a source scan for forbidden raw colors, and a build when CSS or Blade changes are present.
- Raw utility cleanup is a separate gate so remaining exceptions are visible rather than accidentally lost in page work.
- Accessibility/responsive gate tests 375px, 768px, 1024px, and 1440px; keyboard-only flow; focus return; contrast; long labels/URLs; mixed bidi content; zoom; loading/error/readonly/completed/empty states; and page overflow. Only Kanban may have scoped horizontal scrolling.
- Final gate runs the full existing suite, build, source-policy scan, route/deep-link checks, and a review of all baseline failures. No failure is declared resolved without a behavior-based explanation.

Visual review may use screenshots or browser inspection, but visual changes must not be implemented by altering domain tests, route tests, or authorization assertions solely to match new markup.

## Alternatives Considered

### Rewrite all views in one pass

This is faster to start but creates a large regression surface, makes review difficult, and conflicts with the spec's incremental migration requirement.

### Migrate only tokens and leave page patterns unchanged

This reduces risk but cannot deliver the approved content-first shell, workspace, detail, form, notification, and responsive contracts.

### Use only visual snapshots as verification

Snapshots can catch appearance drift but cannot prove Livewire behavior, authorization, deep links, keyboard access, focus management, contrast, or mixed-content handling.

## Consequences

- Work is split into small sub-agent-sized units with explicit ownership boundaries.
- The project will temporarily contain mixed old/new styling during migration; each phase must keep that state intentional and reviewable.
- Baseline failures remain visible and may require separate bug work; this migration does not claim unrelated tests are green.
- Verification takes longer than a visual-only pass but provides evidence that behavior and accessibility were preserved.

## Constraints

- Only presentation/style and explicitly approved shared component markup may change; no backend or dependency migration.
- Do not delete or weaken existing tests to pass a gate.
- Keep `IRANYekanXVF`, existing Tailwind/Vite pipeline, current route and Livewire contracts, and semantic token policy.
- No dark mode, Notion trademark/assets, gradient, glassmorphism, heavy shadow, or broad card-grid default.

## Verification

- Baseline command set: `php artisan test --compact`, `npm run build`, `php artisan route:list`, and a raw-color source scan.
- Per-phase command set is listed in the implementation task plan and must be run before review.
- Final commands include `php artisan test --compact`, `npm run build`, `php artisan route:list`, `php artisan view:cache`, and the raw-color/token-policy scan.
- Compare final test output with the recorded baseline: current baseline is 175 tests, 169 passed, 5 failed; the existing build passes. Any remaining or changed failure must be classified, not ignored.
