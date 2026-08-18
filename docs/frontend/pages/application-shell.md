# Application Shell

## Status

Implemented. The first authenticated integration is the minimal Dashboard page; the full Dashboard design remains queued separately.

## Scope

The Application Shell is the shared authenticated workspace frame for the Helpdesk frontend. It includes:

1. A responsive desktop sidebar and mobile navigation drawer.
2. A top bar with page context, mobile navigation control, and user actions.
3. Navigation for Dashboard, Users, Clients, Projects, and Tasks.
4. Role-aware visibility for navigation items without placing authorization rules in Vue.
5. User menu actions for profile context, locale switching, and logout.
6. An Inertia layout contract that later authenticated pages can consume without duplicating shell markup.

## Ownership

- Shared layout composition: `resources/js/Layouts/`
- Shared navigation and presentation contracts: `resources/js/`
- Authentication logout behavior: `app-modules/identity`
- Cross-cutting locale switching: root application integration
- Domain authorization: Laravel policies and backend-provided capabilities

## Visual Direction

- Preserve the existing minimal Swiss-style authentication surfaces.
- Use a dense but readable workspace layout suitable for tables and operational dashboards.
- Use the existing slate and indigo palette, local IRANYekanXVF font, and local icon assets.
- Use Lucide icons consistently for navigation and actions; no emoji or remote icon assets.
- Keep the shell fully RTL-aware while preserving LTR rendering for email and URL values.
- Use visible focus indicators and controls with at least 44px touch targets.

## Layout Contract

Authenticated pages consume the shell as a layout and provide the page content through the default slot. The shell receives only serializable presentation data:

```text
app:
  name: string
  locale: "en" | "fa"
  direction: "rtl" | "ltr"

auth:
  user: { id, name, email, role } | null
  capabilities: string[]

navigation:
  primary: NavigationItem[]
```

Navigation items contain a stable key, localized label, route URL, icon key, and optional capability requirement. The frontend filters display items only; backend authorization remains authoritative.

## Responsive Behavior

- Desktop: persistent sidebar, top bar, and scrollable main content.
- Mobile: sidebar becomes a modal drawer with an explicit close button, Escape support, and focus-safe interaction.
- The main content must never require horizontal scrolling because of the shell.
- Navigation state must remain understandable when the drawer closes after route navigation.
- Respect reduced-motion preferences for drawer and active-state transitions.

## User Actions

- Display the authenticated user's localized name and email in the user menu.
- Provide a locale switch that navigates through the application locale contract and preserves the current path when possible.
- Submit logout through the Identity-owned route using Inertia, then redirect to Login.
- Do not implement role checks, password logic, or domain queries in Vue components.

## Localization

Every visible label, accessible label, navigation label, action label, and status message must come from Laravel translations. Add matching English and Persian entries before implementation is complete.

## Verification Criteria

- The shell renders with serialized Inertia props and no Eloquent model passed directly to Vue.
- Navigation labels and user actions render in English and Persian.
- Navigation visibility changes according to backend-provided capabilities.
- Logout invalidates the authenticated session and redirects to Login.
- Desktop, tablet, and 375px mobile layouts remain usable without horizontal scrolling.
- Mobile navigation is keyboard accessible and closes on Escape.
- TypeScript validation, focused feature tests, and Vite build pass.

## Integration Status

- The named `dashboard` route is protected by authentication and account activity middleware.
- `Dashboard.vue` consumes `AppShell` as an Inertia layout without repeating shell markup.
- Identity authentication pages remain outside the authenticated shell.
- Dashboard domain data and CRUD interactions are intentionally deferred to the Dashboard page queue item.
