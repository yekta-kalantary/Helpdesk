# Identity Profile Settings Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a card-based Identity Profile page with independent personal-information, contact-information, and password updates.

**Architecture:** Identity owns a dedicated profile controller, Form Requests, and three application actions. The Inertia page renders three independent `useForm` instances inside the authenticated `AppShell`; each endpoint mutates only its own field group and redirects back with a scoped status message.

**Tech Stack:** Laravel 13, PHP 8.4, Inertia.js 3, Vue 3, TypeScript, Tailwind CSS 4, shadcn-vue, Lucide, Pest 5.

## Global Constraints

- Use English for source code, identifiers, tests, documentation, and commit messages.
- Add matching English and Persian Identity translations for every user-facing string.
- Keep profile routes, validation, actions, and page code inside `app-modules/identity` except shared shell integration.
- Do not accept or mutate role, client, active state, permissions, or verification state from Profile forms.
- Do not pass Eloquent models directly to Inertia; expose only `id`, `name`, `last_name`, `email`, and `mobile`.
- Use independent Inertia forms and status/error state for each card.
- Use Lucide icons with localized labels for icon-only controls and `aria-hidden="true"` for decorative icons.
- Email/mobile verification is out of scope and must not be represented as verified.
- Require `current_password` for password changes; keep the current session authenticated after success.
- Verify and commit the completed task before starting the next task.

---

### Task 1: Profile Read Contract and Navigation

**Files:**
- Create: `app-modules/identity/src/Presentation/Http/Controllers/ProfileController.php`
- Modify: `app-modules/identity/routes/web.php`
- Modify: `resources/js/components/app-shell/UserMenu.vue`
- Modify: `resources/js/Layouts/AppShell.vue`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `resources/lang/en/app.php`
- Modify: `resources/lang/fa/app.php`
- Test: `tests/Feature/IdentityProfileTest.php`

**Interfaces:**
- Produces GET `profile.edit` at `/profile`.
- Produces serialized Inertia prop `profile.user` with `id`, `name`, `last_name`, `email`, and `mobile`.
- Adds an authenticated User Menu Profile link using the localized `profile` label and Lucide `UserRound` icon.

- [ ] Add a failing guest-access test for `profile.edit` and an authenticated Inertia contract test.
- [ ] Run `php artisan test --compact tests/Feature/IdentityProfileTest.php` and verify the new assertions fail before implementation.
- [ ] Implement the Identity-owned profile controller and authenticated route with scalar presentation props only.
- [ ] Add the User Menu Profile action and matching English/Persian labels while preserving logout behavior.
- [ ] Run the focused test and `npx vue-tsc --noEmit`.
- [ ] Commit with `Add Identity profile read contract`.

### Task 2: Personal and Contact Update Actions

**Files:**
- Create: `app-modules/identity/src/Application/UpdateUserPersonalInformation.php`
- Create: `app-modules/identity/src/Application/UpdateUserContactInformation.php`
- Create: `app-modules/identity/src/Presentation/Http/Requests/UpdatePersonalInformationRequest.php`
- Create: `app-modules/identity/src/Presentation/Http/Requests/UpdateContactInformationRequest.php`
- Modify: `app-modules/identity/src/Presentation/Http/Controllers/ProfileController.php`
- Modify: `app-modules/identity/routes/web.php`
- Modify: `app-modules/identity/resources/lang/en/messages.php`
- Modify: `app-modules/identity/resources/lang/fa/messages.php`
- Test: `tests/Feature/IdentityProfileTest.php`

**Interfaces:**
- POST `profile.personal.update` accepts `name` and `last_name`.
- POST `profile.contact.update` accepts `email` and optional `mobile`.
- Both actions return to `profile.edit` with separate localized status keys.

- [ ] Add failing tests proving personal updates do not change contact fields and contact updates do not change personal fields.
- [ ] Add failing validation tests for missing names, invalid email, duplicate email, and overlong mobile values.
- [ ] Implement Form Requests with bounded validation and current-user email uniqueness.
- [ ] Implement single-purpose application actions using validated arrays and the authenticated User model.
- [ ] Add English/Persian validation and status messages.
- [ ] Run focused profile tests, Pint, and commit with `Add Identity profile information updates`.

### Task 3: Password Update Action

**Files:**
- Create: `app-modules/identity/src/Application/UpdateUserPassword.php`
- Create: `app-modules/identity/src/Presentation/Http/Requests/UpdateUserPasswordRequest.php`
- Modify: `app-modules/identity/src/Presentation/Http/Controllers/ProfileController.php`
- Modify: `app-modules/identity/routes/web.php`
- Modify: `app-modules/identity/resources/lang/en/messages.php`
- Modify: `app-modules/identity/resources/lang/fa/messages.php`
- Test: `tests/Feature/IdentityProfileTest.php`

**Interfaces:**
- POST `profile.password.update` accepts `current_password`, `password`, and `password_confirmation`.
- The action verifies the current password, writes only the new password and remember token, and keeps the current session authenticated.

- [ ] Add failing tests for a successful password change, incorrect current password, weak password, and confirmation mismatch.
- [ ] Implement Form Request validation and localized current-password failure handling.
- [ ] Implement password mutation through the existing User hashed cast and regenerate `remember_token`.
- [ ] Run focused profile and Identity authentication tests, Pint, and commit with `Add Identity profile password update`.

### Task 4: Card-Based Profile Page and Final Verification

**Files:**
- Create: `app-modules/identity/resources/js/Pages/Profile/Edit.vue`
- Modify: `app-modules/identity/resources/lang/en/messages.php`
- Modify: `app-modules/identity/resources/lang/fa/messages.php`
- Modify: `docs/frontend/README.md`
- Test: `tests/Feature/IdentityProfileTest.php`

**Interfaces:**
- The page consumes the `profile.user` contract from Task 1.
- The page submits the three forms to the route paths from Tasks 2 and 3 and displays scoped status/errors.

- [ ] Add page contract assertions for all three cards, localized labels, independent status keys, and no verification UI.
- [ ] Implement the responsive two-column desktop and single-column mobile card layout.
- [ ] Implement independent `useForm` instances, scoped processing states, accessible errors, localized live-region success messages, and password visibility toggles.
- [ ] Confirm the existing module-aware Inertia glob resolves `Identity/Profile/Edit.vue`; no resolver change is expected.
- [ ] Update the frontend queue to mark Profile as implemented or add it to the Identity page sequence.
- [ ] Run the full Pest suite, `vendor/bin/pint --dirty --format agent`, `npx vue-tsc --noEmit`, `npm run build`, and `git diff --check`.
- [ ] Commit with `Implement Identity profile settings`.
