# ADR: Design System Token Architecture

## Title

Primitive, semantic, and component token layers for the Helpdesk UI.

## Status

Accepted

## Context

The approved design-system spec defines a warm, professional, RTL workspace without changing product behavior. The current stylesheet already contains workspace variables, but the mapping is incomplete and views still contain raw Tailwind slate utilities and occasional arbitrary colors. Direct palette usage makes visual changes inconsistent, makes accessibility decisions difficult to audit, and couples views to implementation details.

The system must preserve the existing Blade, Livewire, route, backend, authorization, and dependency contracts. It must also support the required viewports, mixed RTL/LTR content, explicit interaction states, and a later theme mapping without introducing dark mode in this migration.

## Decision

Adopt the following one-way token architecture:

```text
primitive tokens -> semantic tokens -> component tokens -> views
```

- Primitive tokens hold palette, typography, spacing, radius, elevation, and motion values. They may use hue names and concrete values, and are defined only in the token/theme layer.
- Semantic tokens express product purpose, including page, surface, text, muted, border, primary, accent, success, warning, danger, info, focus, and state surfaces/text. They alias primitives and are the place for accessibility and future theme mapping.
- Component tokens express a component property or state, such as button primary background or input focus ring. They alias semantic tokens and cannot introduce a new palette or hex value.
- Views and Blade components consume semantic or component tokens only. Primitive tokens are not valid view dependencies.
- Token names follow `--{category}-{name}-{variant}-{state}` with optional parts omitted. Semantic names describe purpose, not hue; state suffixes use the fixed vocabulary `hover`, `active`, `focus`, `selected`, `disabled`, `loading`, and `error`.
- Raw `text-slate-*`, `bg-slate-*`, `border-slate-*`, hex, `rgb()`, `hsl()`, and arbitrary color values are forbidden in views and components. Existing raw slate utilities are migrated to semantic/component utilities rather than retained as shortcuts.
- Status colors always have text or icon semantics. Contrast targets are at least `4.5:1` for normal text and `3:1` for large text; focus remains visibly distinguishable from its background and surroundings.
- The existing `IRANYekanXVF`, Tailwind/Vite setup, and current stylesheet location remain in use. No dependency or frontend framework is added.

## Alternatives Considered

### Keep direct Tailwind palette utilities

This is the smallest short-term change, but it preserves the current inconsistency, prevents centralized theme and accessibility decisions, and violates the approved raw-color policy.

### Use only one global semantic layer

This removes direct palette coupling but forces component state decisions into every view and makes shared component contracts difficult to review. It does not sufficiently separate product meaning from component property/state behavior.

### Adopt a third-party design-token or component library

This could provide conventions, but it adds a dependency explicitly excluded by the spec, risks changing markup and behavior, and is unnecessary for the current Blade/Tailwind system.

## Consequences

- Visual values can change centrally without editing every page.
- Shared components become the stable boundary between tokens and page patterns.
- Token additions require a documented purpose and consumer, which slows unreviewed styling changes but prevents palette drift.
- Migration must temporarily map existing values to semantic aliases and verify consumers after each shared-component change.
- The architecture enables a future theme mapping, but dark mode is not implemented by this decision.

## Constraints

- Preserve Livewire bindings/actions, Blade inputs/slots, route names/parameters, backend validation, authorization, and domain workflow.
- Keep `IRANYekanXVF` with `font-display: swap`; do not add fonts, icon libraries, animation libraries, or component libraries.
- Keep the warm white, charcoal, warm border, coral accent, earthy semantic color direction; no gradient, glassmorphism, or heavy shadow.
- Use the 4px spacing scale, 6-8px default radius, limited elevation, and opacity/transform-only motion with reduced-motion support.
- Primitive tokens may exist only in token definitions/theme mapping; views may consume semantic/component tokens only.

## Verification

- Run a source scan over `resources/views/**/*.blade.php` and `app-modules/**/resources/views/**/*.blade.php` for raw slate utilities, color functions, hex values, and arbitrary color classes; expected result is no in-scope matches.
- Inspect token definitions for layer direction, naming, aliases, and absence of component-level palette values.
- Run `npm run build` to verify Tailwind/Vite token generation.
- Run the relevant feature/UI tests without changing tests merely to make them green; compare failures with the baseline recorded in the implementation plan.
- Verify contrast, focus-visible, reduced motion, and status text/icon behavior during the accessibility/responsive gate.
