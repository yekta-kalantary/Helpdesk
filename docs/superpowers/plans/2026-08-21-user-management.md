# User Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an administrator-only Identity users table with a create-user modal, role/client validation, and manual or email-based password setup.

**Architecture:** Keep all user-management behavior inside the Identity module. The controller will provide a paginated user read contract and active client options to Inertia; a Form Request will validate conditional role/password rules; an application action will create the user transactionally and invoke the existing password-reset mechanism for email mode. The Vue page will use the existing AppShell and shared UI components, with all user-facing copy supplied through Laravel translations.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 5, Inertia.js, Vue 3, TypeScript, Tailwind CSS 4, `@lucide/vue`.

## Global Constraints

- Identity owns users, roles, account status, and password setup.
- Customer records remain owned by Clients and are only read as active client options.
- Customer users require an active `client_id`; admin and employee users must not receive a client assignment.
- Email password mode creates an inactive user without a password and sends the existing reset-link flow.
- All new user-facing strings require matching English and Persian translations in the Identity module.
- Do not add dependencies or introduce a second UI/component pattern.
- Preserve unrelated working-tree changes and do not commit them.

## File Map

- Create `app-modules/clients/src/Application/DTOs/ClientSummary.php` for the cross-context client option shape.
- Create `app-modules/clients/src/Application/Queries/ActiveClientDirectory.php` as the public read contract for active client summaries.
- Create `app-modules/identity/src/Presentation/Http/Controllers/UserManagementController.php` for the users page and create endpoint.
- Create `app-modules/identity/src/Presentation/Http/Requests/CreateUserRequest.php` for authorization and conditional validation.
- Create `app-modules/identity/src/Application/CreateUser.php` for the transactional creation and optional reset-link dispatch.
- Modify `app-modules/identity/routes/web.php` to register administrator-only users routes.
- Modify `app/Http/Middleware/HandleInertiaRequests.php` to expose the Identity users translation contract if the page requires shared translations.
- Create `app-modules/identity/resources/js/Pages/Users/Index.vue` for the table and modal.
- Modify `app-modules/identity/resources/lang/en/messages.php` and `app-modules/identity/resources/lang/fa/messages.php` with matching users copy and validation messages.
- Create `tests/Feature/IdentityUserManagementTest.php` for HTTP, authorization, validation, creation, and password-mode behavior.

### Task 1: Add failing backend contract tests

**Files:**
- Create: `tests/Feature/IdentityUserManagementTest.php`

**Interfaces:**
- The tests will define the required route names: `users.index` and `users.store`.
- The create endpoint will accept `name`, `last_name`, `email`, `mobile`, `role`, `client_id`, `is_active`, `password_mode`, `password`, and `password_confirmation`.

- [ ] **Step 1: Write tests for access and page props.**

```php
it('allows administrators to view users and active client options', function (): void {
    $admin = User::factory()->admin()->create();
    $activeClient = Client::factory()->create();
    Client::factory()->inactive()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Identity/Users/Index')
            ->has('users.data')
            ->where('clients', fn ($clients) => collect($clients)->contains('id', $activeClient->id))
            ->where('clients', fn ($clients) => ! collect($clients)->contains('name', 'Inactive client')));
});

it('rejects non-admin users from user management', function (): void {
    $employee = User::factory()->employee()->create();

    $this->actingAs($employee)->get(route('users.index'))->assertForbidden();
});
```

- [ ] **Step 2: Add validation and creation behavior tests.** Cover manual creation, customer-without-client rejection, inactive-client rejection, non-customer client rejection, missing manual password, and email mode creation with reset-link dispatch. Use `Password::fake()` for reset-link assertions.

- [ ] **Step 3: Run the focused test file.**

Run: `php artisan test --compact tests/Feature/IdentityUserManagementTest.php`

Expected: FAIL because the routes and Identity management classes do not exist yet.

### Task 2: Expose the client read contract

**Files:**
- Create: `app-modules/clients/src/Application/DTOs/ClientSummary.php`
- Create: `app-modules/clients/src/Application/Queries/ActiveClientDirectory.php`

**Interfaces:**
- `ClientSummary::fromModel(Client $client): self` maps only `id` and `name`.
- `ActiveClientDirectory::execute(): array<int, ClientSummary>` returns active clients ordered by name.

- [ ] **Step 1: Implement the directory inside the Clients module.** Keep the Eloquent query and `Client` model import inside Clients. Return immutable summaries, not Eloquent models.

- [ ] **Step 2: Add focused assertions to the feature test.** Assert inactive clients are excluded and active results are ordered by name.

- [ ] **Step 3: Run the focused test.**

Run: `php artisan test --compact tests/Feature/IdentityUserManagementTest.php`

Expected: The client-directory behavior passes; user route assertions still fail because the Identity endpoint does not exist.

### Task 3: Implement the Identity backend

**Files:**
- Create: `app-modules/identity/src/Presentation/Http/Controllers/UserManagementController.php`
- Create: `app-modules/identity/src/Presentation/Http/Requests/CreateUserRequest.php`
- Create: `app-modules/identity/src/Application/CreateUser.php`
- Modify: `app-modules/identity/routes/web.php`

**Interfaces:**
- `UserManagementController::index(): \Inertia\Response` returns the `Identity/Users/Index` page with paginated users, active clients, and role options.
- `UserManagementController::store(CreateUserRequest $request, CreateUser $createUser): RedirectResponse` redirects to `users.index` with a localized success message.
- `CreateUser::execute(array $attributes): User` creates the user and handles `password_mode`.
- `UserManagementController` consumes `ActiveClientDirectory` and never imports a Clients infrastructure model.

- [ ] **Step 1: Implement administrator authorization in the Form Request.** Return true only when the authenticated user is an active admin. Add conditional rules using `Rule::enum(UserRole::class)`, `Rule::requiredIf`, `Rule::prohibitedIf`, and an active-client existence rule for customer role.

- [ ] **Step 2: Implement `CreateUser::execute`.** Remove `password_mode` before persistence. For manual mode, persist the submitted password and active flag. For email mode, persist `password` as null and `is_active` as false, then call `Password::sendResetLink(['email' => $user->email])`; convert throttling or failed dispatch into a localized validation error without claiming success.

- [ ] **Step 3: Implement the controller read contract.** Query users with `with('client:id,name')`, order newest first, paginate, and serialize only needed fields. Map `ActiveClientDirectory::execute()` summaries to client options. Provide role values and translated role labels through props.

- [ ] **Step 4: Register routes inside the existing authenticated web group.** Add `GET /users` named `users.index` and `POST /users` named `users.store`, both protected by `web`, `auth`, and `account.active`; enforce admin authorization through the request/controller policy path.

- [ ] **Step 5: Run the focused tests.**

Run: `php artisan test --compact tests/Feature/IdentityUserManagementTest.php`

Expected: PASS for all backend behavior tests.

### Task 4: Add localized UI contract and page

**Files:**
- Modify: `app-modules/identity/resources/lang/en/messages.php`
- Modify: `app-modules/identity/resources/lang/fa/messages.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Create: `app-modules/identity/resources/js/Pages/Users/Index.vue`

**Interfaces:**
- Page props include `users`, `clients`, `roles`, `direction`, and `translations.identity.users`.
- The form submits to `/users` with Inertia's `useForm` and preserves validation errors.

- [ ] **Step 1: Add matching English and Persian translation trees.** Include page title/description, create/open/close labels, table headings, role labels, active/inactive labels, customer selection labels, password mode labels, helper text, submit/loading/success copy, and all conditional validation messages.

- [ ] **Step 2: Implement the responsive users table.** Use AppShell, shared Card/Button/Input components, Lucide icons from `@lucide/vue`, a labeled primary create action, and a scrollable table region on small screens. Render pagination links from the server contract.

- [ ] **Step 3: Implement the modal form.** Include name, last name, email, mobile, role, conditional client select, active checkbox, password mode controls, and conditional password/confirmation inputs. Clear `client_id` when switching away from customer and force the active checkbox off for email mode.

- [ ] **Step 4: Implement accessibility behavior.** Add dialog semantics, Escape handling, focus return to the trigger, visible labels, `aria-describedby` for errors, `role="alert"` field errors, a processing state, and a localized close label. Do not close while submitting.

- [ ] **Step 5: Extend the Inertia shared translation props.** Expose the Identity users translation tree without duplicating English or Persian dictionaries in Vue.

- [ ] **Step 6: Run the frontend checks.**

Run: `npm run build`

Expected: Vite completes successfully with the new page included in the manifest.

### Task 5: Verify end-to-end behavior and quality

**Files:**
- Modify only files discovered by failing verification, keeping changes within the Identity feature.

- [ ] **Step 1: Run the focused Pest suite.**

Run: `php artisan test --compact tests/Feature/IdentityUserManagementTest.php`

- [ ] **Step 2: Run PHP formatting.**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 3: Run the full test suite and frontend build.**

Run: `php artisan test --compact`

Run: `npm run build`

- [ ] **Step 4: Inspect the final diff and worktree.**

Run: `git diff --check`

Run: `git status --short`

Confirm that unrelated pre-existing changes are not staged or modified by this feature.
