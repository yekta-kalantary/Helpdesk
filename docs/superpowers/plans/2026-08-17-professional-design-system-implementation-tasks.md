# Professional Design System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate the approved Professional Notion-Inspired Helpdesk design system into the existing Blade/Livewire UI without changing backend or product behavior.

**Architecture:** Use `primitive -> semantic -> component tokens -> views`, with shared Blade components as the presentation contract and page patterns migrated progressively. Each task has an explicit scope boundary and verification gate; route, Livewire, authorization, validation, and domain contracts remain outside the migration boundary.

**Tech Stack:** Laravel 13.8, PHP 8.4, Livewire 4.3, Blade, Tailwind CSS 4, Vite 8, Pest 5, IRANYekanXVF.

## Global Constraints

- Preserve route names, URLs, controllers, models, policies, permissions, backend behavior, Livewire bindings/actions, validation, and domain workflow.
- Do not add dependencies, frontend frameworks, fonts, icon libraries, animation libraries, or a dark mode.
- Use `primitive -> semantic -> component` tokens; views and components consume semantic/component tokens only.
- Raw `text-slate-*`, `bg-slate-*`, `border-slate-*`, hex, `rgb()`, `hsl()`, and arbitrary color values are forbidden in in-scope views/components.
- Preserve `IRANYekanXVF`, RTL document flow, mixed RTL/LTR handling, 44px targets, focus-visible, reduced motion, semantic status text/icon, and scoped Kanban overflow.
- Do not change tests merely to turn known failures green. Baseline currently reports 175 tests, 169 passed, and 5 failed; the failures are `DashboardTest`, `ProjectDetailDatesTest`, `ProjectFormAtomicityTest`, `TaskPanelUiTest`, and `ProjectWorkManagementUiTest`; `npm run build` passes.

---

## Task 1: Baseline And Inventory

**Goal:** Produce the authoritative migration inventory and preserve the current behavior baseline.

**Scope files/areas:** Read-only inventory of `resources/css/app.css`, `resources/views/components/ui/**`, `resources/views/layouts/**`, `resources/views/livewire/**`, `app-modules/**/resources/views/**`, `app-modules/**/src/Presentation/Livewire/**`, `app/Livewire/**`, `routes/**`, and relevant `tests/**`. The sub-agent reports the inventory in its review output only; no inventory artifact is created.

**Dependency:** None.

**Independent sub-agent:** Yes. The sub-agent may inspect and report only; it must not modify existing files or create implementation code.

**Change boundary:** No application, test, route, stylesheet, or existing documentation edits. Record raw colors, token definitions, component consumers, page patterns, Livewire bindings/actions, route/deep-link checks, and known failures.

**Acceptance criteria:**

- All in-scope shared components and page groups are listed with their consumers.
- Raw color and raw slate occurrences are categorized by shared component, page, error/auth surface, or generated/welcome content.
- Existing contract-sensitive directives and tests are identified.
- Baseline test/build/route commands and the five known failures (`DashboardTest`, `ProjectDetailDatesTest`, `ProjectFormAtomicityTest`, `TaskPanelUiTest`, and `ProjectWorkManagementUiTest`) are recorded without altering tests.

**Verification command:** `php artisan test --compact`; `npm run build`; `php artisan route:list`; source scan using `rg -n 'slate-|#[0-9A-Fa-f]{3,8}|rgba?\\(|hsla?\\(' resources/views app-modules --glob '*.blade.php'`.

## Task 2: Token Foundation

**Goal:** Establish the three token layers and naming contract in the existing stylesheet location.

**Scope files/areas:** `resources/css/app.css` token/theme sections only, preserving the existing import and Vite pipeline.

**Dependency:** Task 1 inventory.

**Independent sub-agent:** Yes. It owns only token definitions and aliases, not view migration or behavior.

**Change boundary:** May define primitive, semantic, and component variables plus Tailwind theme aliases. Must not rename Livewire/Blade interfaces, add dependencies, or change page markup.

**Acceptance criteria:**

- Primitive values cover the approved palette, 4px spacing scale, typography roles, radius, limited elevation, and motion.
- Semantic aliases cover page/surface/text/muted/border/primary/accent/success/warning/danger/info/focus and state surface/text needs.
- Component aliases expose only property/state purposes and introduce no new palette values.
- Naming follows the approved suffix and purpose rules; primitive values are not consumed by views.

**Verification command:** `npm run build`; `rg -n -- '--(color|font|text|space|radius|border|elevation|motion|[a-z]+)-' resources/css/app.css` followed by manual layer review.

## Task 3: Global CSS Foundation

**Goal:** Apply global typography, RTL, focus, motion, logical layout, and minimum target foundations.

**Scope files/areas:** `resources/css/app.css` base/components layers; global layout selectors only.

**Dependency:** Task 2.

**Independent sub-agent:** Yes. It may change global CSS but not component/page markup or Livewire code.

**Change boundary:** Preserve existing font asset/imports and only establish global presentation behavior. Do not introduce page-specific selectors or raw colors.

**Acceptance criteria:**

- `IRANYekanXVF` remains the UI font with `font-display: swap`.
- RTL uses logical properties and `text-align: start` defaults where applicable.
- Focus-visible, reduced motion, loading stability, image constraints, mixed LTR wrapper guidance, and 44px targets are represented without obscuring focus.
- Motion is limited to opacity/transform and respects reduced motion.

**Verification command:** `npm run build`; `php artisan view:cache`; inspect CSS output and verify no new raw color values outside token definitions.

## Task 4: Shared Foundations And Components

**Goal:** Bring shared UI foundations, controls, data/status, layout, and feedback components under the token and state contract.

**Scope files/areas:** `resources/views/components/ui/**` and related `resources/css/app.css` component selectors. Primary components include button, input, textarea, select, checkbox, badge, table, filter bar, tabs, breadcrumbs, page header, alert, empty state, progress, card, and form actions.

**Dependency:** Tasks 2 and 3.

**Independent sub-agent:** Yes, with one sub-agent restricted to shared components. It must verify consumer compatibility before completion.

**Change boundary:** Presentation classes/markup, accessible attributes, and token usage only. Preserve public props, slots, attributes, `wire:*` directives, field names, and action semantics. Do not migrate feature page composition here.

**Acceptance criteria:**

- Components use semantic/component tokens and have applicable hover, focus, disabled, loading, error, readonly, selected, completed, and empty states.
- Labels/errors and icon naming semantics are preserved or improved.
- Tables have a scoped mobile strategy; controls meet 44px target; overlays retain focus rules where applicable.
- Primary consumers render without contract errors.

**Verification command:** `php artisan test --compact tests/Feature/NavigationTest.php tests/Feature/DashboardTest.php tests/Unit/NavigationShellContractTest.php`; `php artisan view:cache`; `npm run build`.

## Task 5: Shell And Navigation

**Goal:** Migrate the application shell and navigation to the approved low-chrome RTL workspace pattern.

**Scope files/areas:** `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`, navigation-related shared components, and shell-related CSS; inspect `routes/web.php` and navigation tests but do not alter route definitions.

**Dependency:** Task 4.

**Independent sub-agent:** Yes. It owns shell markup/style only and must not change authorization logic.

**Change boundary:** Sidebar, drawer, top bar, breadcrumb, page header, active state, responsive shell, and focus behavior. Authorization remains supplied by existing conditions/data and is not reimplemented.

**Acceptance criteria:**

- `AppShell -> Sidebar/Drawer + TopBar + Main` is usable at 375/768/1024/1440px.
- Current location and deep context remain visible; admin-only navigation remains separated by existing authorization behavior.
- Drawer focus, Escape, backdrop dismissal, focus return, inert/aria state, and keyboard order work.
- No page-level horizontal overflow is introduced.

**Verification command:** `php artisan test --compact tests/Feature/NavigationTest.php tests/Unit/NavigationShellContractTest.php`; `php artisan route:list`; `npm run build`.

## Task 6: Dashboard, Lists, And Forms

**Goal:** Migrate dashboard, client/project/task list, and form page patterns while preserving all existing Livewire contracts.

**Scope files/areas:** `resources/views/livewire/dashboard.blade.php`, `app-modules/clients/resources/views/{index,form}.blade.php`, `app-modules/projects/resources/views/{index,form}.blade.php`, `app-modules/tasks/resources/views/{index,form}.blade.php`, and their existing Livewire classes only where presentation bindings require inspection, not behavior changes.

**Dependency:** Task 5.

**Independent sub-agent:** Yes, but it must stay within dashboard/lists/forms and review each page's bindings before editing.

**Change boundary:** Page composition, command rows, filters/disclosures, list rows, hierarchy, sequential form sections, inline errors, and responsive action bars. Do not change queries, validation, authorization, route parameters, or mutation methods.

**Acceptance criteria:**

- Dashboard has a primary focus block and readable priority work without equal-weight stat-card dependence.
- Lists use search/frequent filters and mobile-safe rows; tables remain only where comparison adds value.
- Forms retain visible labels, helper/error associations, loading space, Livewire binding, and mobile sticky action behavior.
- Mixed identifiers use appropriate LTR wrappers and no page-level overflow appears.

**Verification command:** `php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/CoreModulesTest.php tests/Feature/Mvp/ProjectFormAtomicityTest.php tests/Feature/Mvp/TaskFormAtomicityTest.php`; `php artisan view:cache`; `npm run build`.

## Task 7: Project Workspace And Task Detail

**Goal:** Migrate project workspace, Kanban, and task detail composition to the approved main-column/rail patterns.

**Scope files/areas:** `app-modules/projects/resources/views/show.blade.php`, `app-modules/tasks/resources/views/show.blade.php`, project/task domain components and their existing Livewire presentation classes.

**Dependency:** Task 6.

**Independent sub-agent:** Yes. It owns project/task detail presentation and may not move workflow or authorization rules.

**Change boundary:** Breadcrumb/title/description/action, tabs, Kanban layout, contextual rail, task reference/status/action, checklist, conversation, attachments, activity disclosure, completed/readonly presentation. Preserve all existing action names, bindings, and permission checks.

**Acceptance criteria:**

- Project workspace keeps overview/tasks/activity/members/management context and a keyboard/single-pointer alternative to drag movement.
- Task detail separates main content from metadata rail and keeps reference, status, completed/readonly, reopen, and moderation semantics explicit.
- Kanban horizontal scroll is scoped to the board only; other page content does not scroll horizontally.
- Project/task feature, authorization, audit, and deep-link tests retain behavior.

**Verification command:** `php artisan test --compact tests/Feature/ProjectWorkManagement tests/Feature/TaskProjectMembershipTest.php tests/Feature/Mvp/TaskPanelUiTest.php`; `npm run build`.

## Task 8: Notifications And Authentication Surfaces

**Goal:** Migrate notifications, profile/user management, login, password reset, guest, and error surfaces.

**Scope files/areas:** `resources/views/livewire/notifications/index.blade.php`, `app-modules/identity/resources/views/**`, `resources/views/layouts/guest.blade.php`, `resources/views/errors/4xx.blade.php`, and `resources/views/errors/5xx.blade.php`.

**Dependency:** Task 4 and Task 5; Task 6 for shared form contracts.

**Independent sub-agent:** Yes. It owns these surfaces only and must not alter authentication or notification backend behavior.

**Change boundary:** Presentation, semantic unread/read state, empty/error states, auth form accessibility, and token cleanup. Preserve notification links/actions, auth bindings, validation, session behavior, and error status handling.

**Acceptance criteria:**

- Notification read/unread is not communicated by color alone; source, timestamp, context, destination, loading, and destructive confirmation remain clear.
- Auth and profile/user forms preserve labels, errors, password semantics, bindings, and focus behavior.
- 4xx/5xx views comply with raw-color policy and remain useful at narrow widths.

**Verification command:** `php artisan test --compact tests/Feature/NotificationsTest.php tests/Feature/PasswordResetTest.php tests/Feature/UserManagementTest.php`; `php artisan view:cache`; `npm run build`.

## Task 9: Raw Utility Cleanup

**Goal:** Remove remaining raw color utilities and arbitrary colors from all in-scope views/components after page migration.

**Scope files/areas:** `resources/views/**/*.blade.php`, `app-modules/**/resources/views/**/*.blade.php`, and only the related shared CSS selectors. Generated/vendor content is excluded only when it is not an application UI surface and the exclusion is documented.

**Dependency:** Tasks 4 through 8.

**Independent sub-agent:** Yes. It may perform mechanical class replacement and small presentation fixes, but no domain or contract changes.

**Change boundary:** Color utility/token references only, plus the minimum semantic text/icon markup needed to make status meaning accessible. Do not rewrite tests or alter behavior.

**Acceptance criteria:**

- No forbidden raw slate utility, hex, `rgb()`, `hsl()`, or arbitrary color remains in in-scope views/components.
- Primitive tokens appear only in token/theme definitions.
- Every status color has text or icon semantics; no new component palette exists outside token layers.

**Verification command:** `rg -n 'slate-|#[0-9A-Fa-f]{3,8}|rgba?\\(|hsla?\\(' resources/views app-modules --glob '*.blade.php'`; `npm run build`; `php artisan view:cache`.

## Task 10: Accessibility And Responsive Audit

**Goal:** Verify the complete migrated UI against the approved RTL, accessibility, responsive, and mixed-content rules.

**Scope files/areas:** All migrated views/components and `resources/css/app.css`; fixes are limited to presentation/accessibility defects discovered by the audit.

**Dependency:** Tasks 1 through 9.

**Independent sub-agent:** Yes, preferably a review-only sub-agent first, followed by bounded fixes in the same UI areas. It must not change backend contracts or weaken tests.

**Change boundary:** Focus/keyboard semantics, labels/ARIA, logical properties, responsive CSS, overflow, contrast, reduced motion, state semantics, and LTR wrappers only.

**Acceptance criteria:**

- 375px, 768px, 1024px, and 1440px layouts meet the specified rules.
- Keyboard-only navigation, focus-visible, dialog/drawer focus return, Escape, and 44px targets work.
- Contrast targets, long labels/URLs, zoom, bidi content, wrapping, loading/error/readonly/completed/empty states, and scoped Kanban overflow pass review.
- No visual reordering breaks DOM/keyboard reading order.

**Verification command:** `npm run build`; `php artisan view:cache`; `php artisan test --compact`; browser/manual audit at the four viewport widths plus keyboard-only review.

## Task 11: Final Verification

**Goal:** Establish final evidence for the migration without masking unrelated baseline failures.

**Scope files/areas:** Entire in-scope UI, route/deep-link surface, existing tests, build output, and source policy. No implementation edits are expected; any follow-up fix returns to the responsible task boundary.

**Dependency:** Tasks 1 through 10.

**Independent sub-agent:** Yes, as an independent reviewer. It reports failures and can propose bounded follow-up tasks, but must not silently edit tests or application behavior.

**Change boundary:** Verification and review artifacts only. Do not change existing files to make a command pass; no commit is part of this plan.

**Acceptance criteria:**

- Full test, route, view-cache, build, and raw-color/token-policy checks are run.
- Existing baseline failures are compared with final output and each difference is classified as fixed, unchanged, or regression.
- Feature tests, authorization, deep links, Livewire bindings/actions, backend validation, and permission boundaries show no unintended behavior change.
- The final review confirms all spec acceptance criteria, including token architecture, shared contract, progressive migration, responsive/accessibility gates, and no raw colors in views.

**Verification command:** `php artisan test --compact`; `npm run build`; `php artisan route:list`; `php artisan view:cache`; `rg -n 'slate-|#[0-9A-Fa-f]{3,8}|rgba?\\(|hsla?\\(' resources/views app-modules --glob '*.blade.php'`.

## Execution Notes

- Each task is small enough for a fresh sub-agent and a separate review. A sub-agent must not cross the listed change boundary to repair an adjacent task.
- If a verification command exposes a pre-existing failure, preserve the test and classify it against the Task 1 baseline. Only a behavior regression or a spec violation is in scope for correction.
- No task includes a commit. The user explicitly requested documentation-only planning at this stage.
