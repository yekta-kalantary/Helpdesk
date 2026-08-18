# Task 5 Report

## Scope

Implemented the first authenticated application-shell integration only. No domain CRUD pages or dashboard queries were added.

## Changes

- Added `DashboardController` with localized title and summary presentation props.
- Added `Dashboard.vue` as an Inertia page that consumes `AppShell` as its layout.
- Added the authenticated, account-active `dashboard` route.
- Removed the global shell wrapper so Identity login, recovery, and reset pages remain outside the authenticated shell.
- Updated shared navigation to target the named dashboard route.
- Added accessibility and integration coverage for authentication protection, identity-page separation, landmarks, RTL direction, active route state, focus behavior, and mobile drawer behavior.
- Marked the Application Shell implemented while keeping the full Dashboard page queued and pending.

## Verification

- Focused Pest suite: 24 tests passed, 247 assertions.
- `vendor/bin/pint --dirty --format agent`: passed and formatted the route imports.
- `npx vue-tsc --noEmit`: passed.
- `npm run build`: passed.
- `git diff --check`: passed.
- Full Pest suite: 58 passed, 1 failed, 76 errors out of 135 tests.

## Concerns

The full suite contains pre-existing domain/MVP expectations for routes and views that are not part of Task 5, including `tasks.show`, `projects.show`, `users.show`, `clients.index`, and `components.ui.date`. One existing dashboard query-bound test also expects the later data-rich Dashboard implementation. Those requirements were intentionally not implemented because this task requires a minimal presentation-only dashboard and explicitly defers domain CRUD pages.

## Task 5 Review Fix

### Accessibility Coverage

`ApplicationShellAccessibilityTest` now deterministically checks the complete source-level contract available without a browser harness:

- App shell landmarks: page wrapper, `header`, `aside`, `nav`, and `main`.
- Focus-visible styling on shell controls and navigation controls.
- Mobile dialog semantics, accessible labels, and `aria-modal`.
- Escape-key handling and focus restoration to the mobile navigation trigger.
- Focus-trap selector coverage and inclusion of the backdrop control in the focusable collection.
- Active navigation `aria-current` behavior.
- Reduced-motion transition classes for opacity and drawer movement.
- Dashboard layout consumption and identity-page shell separation.

The test includes an explicit comment that runtime browser interaction is unavailable; it does not claim to prove actual focus movement or keyboard interaction in a browser.

### Fresh Command Outputs

- `php artisan test --compact tests/Feature/ApplicationShellRenderTest.php tests/Feature/ApplicationShellPropsTest.php tests/Feature/ApplicationShellAccessibilityTest.php tests/Feature/IdentityLoginTest.php tests/Feature/IdentityPasswordRecoveryTest.php tests/Feature/IdentityPasswordResetTest.php tests/Feature/LocaleSwitchTest.php` -> **24 passed, 267 assertions**.
- `php artisan test --compact` -> **135 tests: 58 passed, 1 failed, 76 errors, 405 assertions**.
- `vendor/bin/pint --dirty --format agent` -> **passed**.
- `npx vue-tsc --noEmit` -> **passed**.
- `npm run build` -> **passed**; Vite transformed 3,021 modules and built the production bundle.
- `git diff --check` -> **passed**.

### Failure Classification

- **1 failed test: deferred Task 5 behavior.** `Issue43QueryBoundsTest` expects the later data-rich dashboard to render `Visible dashboard activity`; Task 5 intentionally provides only the localized Dashboard title and summary.
- **76 errors: pre-existing unrelated domain gaps.** They are caused by missing deferred/domain routes such as `tasks.show`, `projects.show`, `users.show`, and `clients.index`, plus the missing `components.ui.date` view. No dependencies, CRUD behavior, or dashboard data queries were added to address them.

## Final Review Fix Wave

### Changes

- Moved root `app.php` translations into the configured `resources/lang/{en,fa}` path; existing `navigation.php` files already followed that path.
- Added literal English and Persian Inertia assertions so locale tests verify rendered values rather than unresolved translation keys.
- Removed `resources/js/navigation.test.mjs`; it imported TypeScript directly and had no runnable Node test loader or package script. No dependency was added.
- Added Escape handling to the UserMenu trigger while retaining panel handling; both paths close the menu and restore focus to the trigger after the panel is removed.
- Reassessed the deferred MobileNavigation `aria-current` minor independently. It remains deferred because this fix wave did not introduce a browser/component test harness and does not touch that behavior.

### Fresh Command Outputs

- `php artisan test --compact tests/Feature/ApplicationShellRenderTest.php tests/Feature/ApplicationShellPropsTest.php tests/Feature/ApplicationShellAccessibilityTest.php tests/Feature/IdentityLoginTest.php tests/Feature/IdentityPasswordRecoveryTest.php tests/Feature/IdentityPasswordResetTest.php tests/Feature/IdentityLogoutTest.php tests/Feature/LocaleSwitchTest.php` -> **27 passed, 300 assertions**.
- `php artisan tinker --execute 'dump(lang_path()); dump(__('navigation.dashboard')); dump(__('app.navigation.label'));'` -> **`resources/lang`; `داشبورد`; `ناوبری برنامه`**.
- `npx vue-tsc --noEmit` -> **passed**.
- `npm run build` -> **passed**; Vite transformed 3,021 modules and built the production bundle.
- `vendor/bin/pint --dirty --format agent` -> **passed**.
- `git diff --check` -> **passed**.

### Finding Reproduction

- Before removal, `node --test resources/js/navigation.test.mjs` failed with **`ERR_UNKNOWN_FILE_EXTENSION`** for `resources/js/navigation.ts`.
- Before relocation, the new literal locale assertion failed because `app.navigation.label` resolved to its key; `navigation.dashboard` already resolved from `resources/lang`.
