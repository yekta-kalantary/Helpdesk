# Compact Workspace Redesign

## Status

Approved visual direction: Restrained Slate + Muted Teal.

## Goal

Redesign the existing Helpdesk interface across all current frontend modules so it feels compact, professional, modern, and balanced without changing routes, data flow, authorization, or existing user actions.

## Scope

- Shared authenticated application shell and layout frame.
- Desktop sidebar, top bar, mobile navigation drawer, and user menu.
- Dashboard page and shared UI primitives used by future modules.
- Identity login, password recovery, password reset, and profile settings surfaces.
- Shared CSS tokens, typography, focus treatment, surfaces, controls, cards, badges, and inputs.

Out of scope:

- New domain functionality or navigation items.
- Backend behavior, route changes, authorization changes, or data contracts.
- Replacing the local IRANYekanXVF font or Lucide icon source.
- Adding dependencies or remote assets.

## Visual System

- Use a cool neutral canvas based on slate tones with white content surfaces.
- Use muted teal for active navigation, primary actions, links, and focus indicators.
- Use low-saturation semantic status colors with light tinted backgrounds and readable dark text.
- Prefer borders and surface contrast over large shadows. Reserve shadows for dropdowns and the mobile drawer.
- Use 6px to 8px corner radii for controls and cards; avoid oversized pill shapes except for compact status badges where appropriate.
- Keep body text at approximately 14px, labels and metadata at 12px, page headings at 20px to 24px, and use 600 as the primary heading weight.
- Maintain clear hierarchy through size, weight, and tone rather than oversized typography or saturated color.

## Density and Layout

- Reduce the desktop sidebar from 16rem to approximately 14rem while keeping labels and icons readable.
- Reduce the top bar to approximately 3.5rem.
- Use compact visual controls around 2.25rem high while retaining accessible hit areas for icon-only and touch interactions.
- Reduce default page padding and vertical section spacing while preserving breathing room between meaningful groups.
- Use 16px card padding and 20px to 24px section spacing as the primary compact scale.
- Keep content width fluid and maximize useful viewport area without introducing horizontal scrolling.
- Preserve the existing responsive breakpoint behavior and RTL-aware ordering.

## Component Rules

- Sidebar navigation uses a restrained active background, a slim teal state cue, lighter section labels, and compact gaps.
- Top bar uses a quiet border, compact horizontal padding, and a visually subordinate mobile menu control.
- User menu uses a compact trigger and menu rows with consistent icon alignment and localized accessible labels.
- Cards use a thin border, subtle surface separation, compact headers, and no default heavy shadow.
- Buttons use compact vertical padding, clear hierarchy between primary, secondary, and destructive variants, and visible hover/focus feedback.
- Inputs use a compact height, clear labels, restrained borders, and accessible error/focus states.
- Badges communicate status through tone and text, not color alone.
- Keep Lucide icons at consistent sizes and mark decorative icons as hidden from assistive technology.

## Accessibility and Interaction

- Preserve visible keyboard focus indicators with sufficient contrast.
- Do not reduce essential interactive hit areas below the project accessibility contract.
- Keep all visible and accessible text localized through the existing Laravel/Inertia translation contract.
- Preserve mobile drawer focus management, Escape handling, and reduced-motion behavior.
- Ensure muted text and status combinations remain readable and do not rely on color alone.
- Preserve responsive usability at 375px, 768px, 1024px, and wide desktop viewports.

## Implementation Strategy

1. Establish shared color, typography, radius, focus, and density tokens in the global stylesheet.
2. Update shared primitives for buttons, cards, inputs, and badges.
3. Apply the compact visual system to the authenticated shell and mobile navigation.
4. Apply the same primitives and spacing rules to Dashboard and Identity pages.
5. Verify visual consistency, TypeScript, build output, relevant feature tests, and diff hygiene.

## Verification Criteria

- All current frontend modules use the same neutral surface, typography, radius, spacing, and accent system.
- Existing routes, forms, navigation behavior, localization, authorization visibility, and logout behavior remain unchanged.
- No new hardcoded user-facing strings or icon sources are introduced.
- `npx vue-tsc --noEmit` passes.
- `npm run build` passes.
- Relevant Laravel/Pest tests pass.
- `git diff --check` passes.
