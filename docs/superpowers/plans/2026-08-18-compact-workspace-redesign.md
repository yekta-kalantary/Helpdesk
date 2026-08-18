# Compact Workspace Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply the approved Restrained Slate + Muted Teal visual system across all current Helpdesk frontend modules without changing functionality or data contracts.

**Architecture:** Establish the visual contract in `resources/css/app.css`, then make shared UI primitives the source of truth for controls and surfaces. Apply those primitives and compact spacing to the authenticated shell, dashboard, and Identity auth/profile pages while preserving existing Inertia props, routes, localization, RTL behavior, and Lucide usage.

**Tech Stack:** Vue 3, TypeScript, Inertia.js, Tailwind CSS 4, shadcn-vue primitives, Lucide Vue, Vite.

## Global Constraints

- Use English for source code, identifiers, tests, documentation, and commit messages.
- Keep existing routes, data flow, authorization, localization, and user actions unchanged.
- Keep IRANYekanXVF and `@lucide/vue`; do not add dependencies or remote assets.
- Preserve RTL-aware layout, visible focus indicators, keyboard behavior, and reduced-motion behavior.
- Do not reduce essential interactive hit areas below the existing accessibility contract.
- Use Tailwind CSS v4 utilities and existing shadcn-vue primitives.

---

### Task 1: Establish Global Compact Visual Tokens

**Files:**
- Modify: `resources/css/app.css`
- Test: `npx vue-tsc --noEmit`, `npm run build`

**Interfaces:**
- Produces global Tailwind theme tokens for neutral surfaces, muted teal accent, semantic colors, borders, focus rings, and compact typography.

- [ ] **Step 1: Replace the indigo-heavy theme values with neutral and teal semantic tokens.**

Define named theme values for background, foreground, card, muted, border, input, primary, primary foreground, accent, destructive, and ring. Keep the local font and add base smoothing/line-height rules that support 14px body text.

- [ ] **Step 2: Tighten global focus and body defaults without removing keyboard visibility.**

Use a 2px teal outline with a restrained offset, keep the minimum page width, and set the body background and default text to the new neutral tokens.

- [ ] **Step 3: Run frontend validation.**

Run `npx vue-tsc --noEmit` and `npm run build`.

Expected: both commands exit 0.

- [ ] **Step 4: Commit the token layer.**

```bash
git add resources/css/app.css
git commit -m "Refine compact workspace visual tokens"
```

### Task 2: Compact Shared UI Primitives

**Files:**
- Modify: `resources/js/components/ui/button/index.ts`
- Modify: `resources/js/components/ui/input/Input.vue`
- Modify: `resources/js/components/ui/card/Card.vue`
- Modify: `resources/js/components/ui/card/CardHeader.vue`
- Modify: `resources/js/components/ui/card/CardTitle.vue`
- Modify: `resources/js/components/ui/card/CardDescription.vue`
- Modify: `resources/js/components/ui/card/CardContent.vue`
- Modify: `resources/js/components/ui/card/CardFooter.vue`
- Modify: `resources/js/components/ui/badge/index.ts`
- Modify: `resources/js/components/ui/badge/Badge.vue`
- Test: `npx vue-tsc --noEmit`, `npm run build`

**Interfaces:**
- Consumes the semantic CSS tokens from Task 1.
- Produces compact defaults that existing pages can use without changing props or slots.

- [ ] **Step 1: Reduce default button visual height and padding while preserving icon variants.**

Set the default button around 36px high, small buttons around 32px, large buttons around 40px, and keep icon-only variants at accessible sizes. Use muted teal primary states and restrained outline/ghost hover states through semantic tokens.

- [ ] **Step 2: Make input defaults compact and consistent.**

Use a 36px default input height, 13px to 14px readable text, subtle borders, compact padding, and semantic focus/error-compatible classes. Preserve all native input attributes through the current component.

- [ ] **Step 3: Remove heavy card defaults and tighten card subcomponent spacing.**

Keep a thin border and neutral surface, remove the default heavy shadow, and use compact header/content/footer padding and title/description hierarchy.

- [ ] **Step 4: Make badges compact and semantic.**

Use smaller horizontal padding, 12px text, moderate weight, and tinted semantic variants that retain readable foreground text and do not rely on color alone.

- [ ] **Step 5: Run type checking and build.**

Run `npx vue-tsc --noEmit` and `npm run build`.

Expected: both commands exit 0.

- [ ] **Step 6: Commit the primitive layer.**

```bash
git add resources/js/components/ui
git commit -m "Compact shared interface primitives"
```

### Task 3: Redesign the Authenticated Shell

**Files:**
- Modify: `resources/js/Layouts/AppShell.vue`
- Modify: `resources/js/components/app-shell/Sidebar.vue`
- Modify: `resources/js/components/app-shell/TopBar.vue`
- Modify: `resources/js/components/app-shell/MobileNavigation.vue`
- Modify: `resources/js/components/app-shell/UserMenu.vue`
- Modify: `resources/js/components/app-shell/NavigationIcon.vue`
- Modify: `resources/js/Pages/Dashboard.vue`
- Test: relevant existing shell feature tests, `npx vue-tsc --noEmit`, `npm run build`

**Interfaces:**
- Consumes the existing `ApplicationShellProps`, navigation sections, current URL, and localized labels unchanged.
- Produces the same responsive shell and focus behavior with a denser visual presentation.

- [ ] **Step 1: Reduce shell frame dimensions and page padding.**

Use an approximately 14rem desktop sidebar, 3.5rem top bar, compact main padding, and a neutral workspace background. Keep the existing breakpoint and slot structure.

- [ ] **Step 2: Refine desktop and mobile navigation hierarchy.**

Use smaller section labels, compact item gaps, 36px to 40px visual rows, restrained teal active states, and a slim active cue. Preserve `aria-current`, disabled presentation, RTL behavior, and icon mapping.

- [ ] **Step 3: Refine top bar and user menu.**

Use compact horizontal spacing and understated hover surfaces. Keep the mobile menu button, localized labels, profile link, logout action, menu focus return, and existing Inertia logout behavior unchanged.

- [ ] **Step 4: Apply the new page heading hierarchy to Dashboard.**

Reduce the dashboard heading and summary scale and remove excessive vertical whitespace while preserving the current title/summary props and shell layout.

- [ ] **Step 5: Verify shell behavior.**

Run the relevant existing shell tests, `npx vue-tsc --noEmit`, and `npm run build`.

Expected: behavior tests remain green and frontend commands exit 0.

- [ ] **Step 6: Commit the shell redesign.**

```bash
git add resources/js/Layouts/AppShell.vue resources/js/components/app-shell resources/js/Pages/Dashboard.vue
git commit -m "Redesign compact authenticated workspace shell"
```

### Task 4: Apply the System to Identity Pages

**Files:**
- Modify: `app-modules/identity/resources/js/Pages/Auth/Login.vue`
- Modify: `app-modules/identity/resources/js/Pages/Auth/ForgotPassword.vue`
- Modify: `app-modules/identity/resources/js/Pages/Auth/ResetPassword.vue`
- Modify: `app-modules/identity/resources/js/Pages/Profile/Edit.vue`
- Test: existing Identity feature tests, `npx vue-tsc --noEmit`, `npm run build`

**Interfaces:**
- Consumes all existing localized Inertia props and form contracts unchanged.
- Produces visually consistent compact auth and profile experiences using the shared tokens and primitives.

- [ ] **Step 1: Replace oversized auth-page decoration and spacing.**

Remove the large blurred indigo ornaments, use a quiet neutral canvas, reduce heading/card spacing, use 20px to 24px page headings, 16px card padding, and compact form gaps.

- [ ] **Step 2: Replace direct indigo utility styling with semantic teal states.**

Update submit buttons, links, checkbox focus, validation states, and password visibility focus states to use the shared semantic colors without changing labels, form submission, or error behavior.

- [ ] **Step 3: Compact the profile page hierarchy.**

Reduce page heading scale and header spacing, use the compact card primitives, reduce icon tile size/radius, tighten grid gaps and form spacing, and keep the three independent forms and their status/error regions unchanged.

- [ ] **Step 4: Verify Identity behavior and frontend output.**

Run the relevant Identity feature tests, `npx vue-tsc --noEmit`, and `npm run build`.

Expected: existing form, authentication, localization, and profile tests remain green; frontend commands exit 0.

- [ ] **Step 5: Commit the Identity redesign.**

```bash
git add app-modules/identity/resources/js/Pages/Auth app-modules/identity/resources/js/Pages/Profile/Edit.vue
git commit -m "Apply compact visual system to identity pages"
```

### Task 5: Final Quality Verification

**Files:**
- Verify all files changed by Tasks 1-4.

- [ ] **Step 1: Run the complete relevant test suite.**

Run `php artisan test --compact`.

Expected: no failures.

- [ ] **Step 2: Run PHP formatting if any PHP files changed.**

Run `vendor/bin/pint --dirty --format agent` only if the implementation touched PHP files.

- [ ] **Step 3: Run frontend checks.**

Run `npx vue-tsc --noEmit` and `npm run build`.

Expected: both commands exit 0.

- [ ] **Step 4: Check the final diff.**

Run `git diff --check` and inspect `git status --short`.

Expected: no whitespace errors and only intended frontend/spec/plan files are changed.

- [ ] **Step 5: Commit any final verification-only fixes.**

```bash
git add resources/css/app.css resources/js app-modules/identity/resources/js
git commit -m "Verify compact workspace redesign"
```
