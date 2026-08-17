# ADR: Shared Component Contract

## Title

Token-aware, behavior-preserving contracts for shared Blade components.

## Status

Accepted

## Context

The approved spec identifies shared foundations, shell/navigation, content/layout, data/status, form/action, feedback/overlay, and domain-composition components. Existing UI components are Blade views under `resources/views/components/ui`, while feature pages and Livewire contracts live under `app-modules/**` and `app/Livewire`. A visual migration can affect many consumers at once, so shared components need a contract that separates presentation from domain behavior.

The component contract must support RTL, mixed LTR identifiers, keyboard access, loading/error/readonly/empty states, and responsive behavior while keeping existing slots, attributes, `wire:model`, `wire:click`, action names, route parameters, validation, authorization, and mutation behavior intact.

## Decision

Use shared Blade components as the presentation boundary with the following contract:

- A foundation component has no business logic. `Typography`, `Icon`, `Link`, `Divider`, `VisuallyHidden`, and `FocusRing` expose semantic markup, token classes, accessible names, and passed-through attributes only.
- Shell components own layout context and current-location presentation. `AppShell`, `Sidebar`, `MobileDrawer`, `TopBar`, `Breadcrumbs`, `PageHeader`, and `SectionTabs` do not reimplement authorization; they render navigation data and current state supplied by existing backend/Livewire code.
- Content/layout components own grouping and rhythm. `Section`, `Surface/Card`, `Stack`, `Grid`, `ContextRail`, `ListRow`, and `Disclosure` do not turn every page into equal-weight cards.
- Data/status components render explicit meaning. `Table`, `DataList`, `StatRow`, `Badge`, `Progress`, `Avatar`, `ActivityItem`, and `EmptyState` must provide text/icon semantics for states and mobile alternatives where tables cannot fit.
- Form/action components preserve existing field names, values, bindings, validation output, loading targets, and action semantics. Labels remain visible; errors remain adjacent and associated with their controls; disabled, readonly, and loading states retain correct HTML/ARIA semantics.
- Feedback/overlay components own feedback mechanics only. `Alert`, `Toast`, `Dialog`, `Popover`, `Tooltip`, `ConfirmDialog`, `Skeleton`, and `LoadingIndicator` must preserve focus management, Escape behavior, focus return, and non-blocking toast boundaries.
- Domain components such as `ProjectSummary`, `TaskRow`, `KanbanColumn`, `TaskCard`, `Checklist`, `CommentThread`, `AttachmentList`, `ActivityHistory`, and `NotificationItem` receive domain data and authorized actions from existing code. They do not move authorization, mutation, or workflow rules into the view.
- Every interactive component documents or implements `hover`, `focus-visible`, `selected`, `disabled`, `loading`, `error`, `readonly`, `completed`, and `empty` states where applicable. Icon-only controls have an accessible name; decorative icons use `aria-hidden`.
- Components consume semantic/component tokens only, use logical layout properties for RTL, keep data-native LTR values in `dir="ltr"` wrappers, and provide a minimum `44px` interactive target.
- Public Blade inputs, slots, attribute forwarding, Livewire directives, and emitted events are preserved unless a separate approved contract change exists. A visual refactor is not permission to rename or remove them.

## Alternatives Considered

### Style each page independently

This offers local flexibility but repeats state, accessibility, and token decisions, increases raw utility drift, and makes visual regressions difficult to isolate.

### Move all UI behavior into a new frontend component framework

This conflicts with the non-goal of preserving Blade/Livewire and adds migration risk and dependencies without solving backend-contract preservation.

### Create domain-aware shared components that own mutations

This might reduce page markup but would blur authorization and mutation boundaries, making Livewire behavior harder to reason about and test.

## Consequences

- Shared components become reviewable units with stable consumer contracts.
- A component change requires verification of its primary consumers and relevant Livewire tests.
- Some existing components may need explicit state props or slot conventions, but those additions must remain presentation-only and backward-compatible with current consumers.
- Pages become simpler and more consistent, while domain-specific composition remains in feature views.

## Constraints

- No route, controller, model, policy, permission, backend workflow, Livewire action, binding, or dependency changes.
- No raw colors in views/components; no new palette hidden in component classes.
- Preserve RTL reading order, mixed-content handling, mobile touch targets, focus visibility, reduced motion, and scoped Kanban overflow.
- Existing tests are evidence of behavior, not styling fixtures to rewrite without a behavioral reason.

## Verification

- Inventory every shared component consumer before changing its contract and verify the primary consumer views after the change.
- Run focused Pest tests for navigation, dashboard, lists, forms, project/task workflows, notifications, and authorization as each area migrates.
- Run a Blade source scan for raw colors and direct primitive consumption.
- Use `npm run build` after stylesheet/component changes.
- Manually or browser-verify focus, keyboard order, labels/errors, drawer/dialog focus return, loading stability, readonly/completed states, and 375/768/1024/1440px layouts.
