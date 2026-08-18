# Application Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Build the shared authenticated Helpdesk workspace shell with responsive navigation, role-aware presentation, locale switching, and Identity-owned logout.

**Architecture:** Shared Vue layout components live under `resources/js/Layouts` and consume serializable shared Inertia props. Navigation is a presentation configuration filtered by backend-provided capabilities; authorization remains in Laravel. Identity owns logout, while the root application owns the locale-switch contract.

**Tech Stack:** Laravel 13, PHP 8.4, Inertia.js 3, Vue 3, TypeScript, Vite 8, Tailwind CSS 4, shadcn-vue, Reka UI, Pest 5.

## Global Constraints

- Use English for source code, identifiers, tests, documentation, and commit messages.
- Add matching English and Persian Laravel translations for every new user-facing string.
- Keep Identity behavior in `app-modules/identity`; keep shared shell composition in `resources/js`.
- Do not pass Eloquent models directly to Inertia pages.
- Do not place domain authorization rules in Vue components.
- Use local assets only: IRANYekanXVF and the installed Lucide icon package.
- Use Tailwind CSS v4 utilities and existing shadcn-vue primitives.
- Verify each task and commit it before starting the next task.

---

### Task 1: Shared Shell Contract

**Files:**
- Create: `resources/js/Layouts/AppShell.vue`
- Create: `resources/js/types/navigation.ts`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Test: `tests/Feature/ApplicationShellPropsTest.php`

**Deliverable:** A typed shared shell contract with app locale, direction, authenticated user presentation data, capabilities, and navigation data exposed through shared Inertia props.

**Required behavior:**
- Share only scalar and array presentation data.
- Derive `direction` from the current locale.
- Expose a default navigation list for Dashboard, Users, Clients, Projects, and Tasks.
- Include capability identifiers for restricted items.
- Render the shell frame and default content slot without hardcoding domain rules.

**Verification:** Run the focused feature test, `npx vue-tsc --noEmit`, and commit with `Implement shared application shell contract`.

### Task 2: Navigation Model and Role-Aware Presentation

**Files:**
- Modify: `resources/js/types/navigation.ts`
- Create: `resources/js/navigation.ts`
- Modify: `resources/js/Layouts/AppShell.vue`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `lang/en/app.php`
- Modify: `lang/fa/app.php`
- Test: `tests/Feature/ApplicationShellPropsTest.php`

**Deliverable:** Localized navigation sections with active-route state and capability-based display filtering.

**Required behavior:**
- Keep navigation item keys stable and English.
- Use named route URLs supplied by Laravel or safe route paths already registered.
- Show Users and other restricted sections only when their capability is present.
- Never treat frontend visibility as authorization.
- Preserve RTL ordering and accessible navigation landmarks.

**Verification:** Run the focused feature tests and TypeScript validation, then commit with `Add localized shell navigation`.

### Task 3: Responsive Sidebar and Header

**Files:**
- Create: `resources/js/components/app-shell/Sidebar.vue`
- Create: `resources/js/components/app-shell/MobileNavigation.vue`
- Create: `resources/js/components/app-shell/TopBar.vue`
- Modify: `resources/js/Layouts/AppShell.vue`
- Test: `tests/Feature/ApplicationShellRenderTest.php`

**Deliverable:** Desktop persistent sidebar, mobile drawer, top bar, active navigation state, keyboard interaction, and responsive content frame.

**Required behavior:**
- Sidebar is persistent at desktop widths and becomes a modal drawer below the mobile breakpoint.
- Drawer has an accessible name, close control, Escape handling, and no horizontal overflow at 375px.
- Use 44px minimum interactive controls and visible focus rings.
- Respect `prefers-reduced-motion` for drawer transitions.
- Keep page content in the default slot and avoid coupling the shell to a specific page.

**Verification:** Run frontend type checking, Vite build, and shell render feature coverage, then commit with `Add responsive application shell navigation`.

### Task 4: User Menu, Locale Switch, and Logout

**Files:**
- Create: `resources/js/components/app-shell/UserMenu.vue`
- Modify: `resources/js/Layouts/AppShell.vue`
- Modify: `app-modules/identity/routes/web.php`
- Modify: `app-modules/identity/src/Presentation/Http/Controllers/AuthenticationController.php`
- Modify: `lang/en/app.php`
- Modify: `lang/fa/app.php`
- Test: `tests/Feature/IdentityLogoutTest.php`

**Deliverable:** Accessible user menu with localized user context, locale switch link, and working Identity logout.

**Required behavior:**
- Logout uses a POST request with CSRF protection and invalidates the session.
- Successful logout redirects to the named Login route.
- Locale switching uses a root application route, preserves the current path when safe, and validates the locale against `en` and `fa`.
- All action and accessible labels come from Laravel translations.

**Verification:** Run logout and locale feature tests, the existing Identity suite, Pint, and TypeScript validation, then commit with `Add shell user actions and logout`.

### Task 5: Integration, Accessibility, and Final Verification

**Files:**
- Create: `app/Http/Controllers/DashboardController.php`
- Create: `resources/js/Pages/Dashboard.vue`
- Modify: `routes/web.php`
- Modify: `docs/frontend/README.md`
- Modify: `docs/frontend/pages/application-shell.md`
- Test: `tests/Feature/ApplicationShellAccessibilityTest.php`

**Deliverable:** First authenticated page integration, documentation status update, and final verification of the complete shell.

**Required behavior:**
- The named `dashboard` route is guest-protected and renders `resources/js/Pages/Dashboard.vue` through `AppShell` without duplicating layout markup.
- Login pages remain outside the authenticated shell.
- Screen-reader landmarks, focus behavior, active route state, RTL layout, and mobile drawer behavior are covered.
- No external asset requests are introduced.

**Verification:** Run the complete relevant Pest suite, `vendor/bin/pint --dirty --format agent`, `npx vue-tsc --noEmit`, `npm run build`, and `git diff --check`. Commit with `Complete application shell integration`.
