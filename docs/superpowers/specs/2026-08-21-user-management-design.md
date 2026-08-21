# User Management Design

## Goal

Add an Identity-owned user management page for administrators. The page will show users in a table and create users through a modal form. User creation must enforce role and client rules on the server and provide two password setup paths.

## Scope

- Add an administrator-only users page.
- Show users in a paginated table with name, email, role, client, active state, and actions.
- Open a create-user modal from the users page.
- Collect first name, last name, email, mobile, role, optional client, active state, and password setup choice.
- Allow an administrator to set the password directly or send the user an existing password-reset link.
- Require an active client when the selected role is `customer`.
- Keep the new UI responsive, keyboard accessible, localized in English and Persian, and consistent with the existing Vue/Inertia/Tailwind patterns.

Out of scope for this slice:

- Editing or deleting users.
- User detail pages.
- Bulk actions, imports, filtering, or advanced search.
- Changes to project membership.

## Ownership and Architecture

The Identity module owns users, roles, account status, and password setup. Client records remain owned by the Clients module. The Identity presentation layer may load active client choices through the existing client model only where the current application already permits that dependency; no client data is mutated by user creation.

The feature belongs under `app-modules/identity`:

- Presentation routes, controller, and Form Request handle HTTP input and response mapping.
- An Identity application action coordinates creation and optional password-reset notification.
- The User model remains the persistence adapter and keeps its existing role/client invariants.
- Vue receives translated labels and serialized client/role options through Inertia props.

## User Creation Contract

The create endpoint accepts:

- `name`: required string.
- `last_name`: required string.
- `email`: required, valid, unique email address. It is required because the email setup path depends on it and accounts use it for login.
- `mobile`: nullable string with the existing maximum length.
- `role`: required enum value: `admin`, `employee`, or `customer`.
- `client_id`: required and active when `role=customer`; prohibited or null for `admin` and `employee`.
- `is_active`: boolean.
- `password_mode`: required value `manual` or `email`.
- `password`: required and confirmed with at least the existing password minimum when `password_mode=manual`; prohibited when `password_mode=email`.

When `password_mode=email`, the user is created without a password and remains inactive regardless of the submitted active checkbox. The application sends the existing password-reset link flow after creation. The user becomes usable only after setting a password and being activated according to the account workflow.

When `password_mode=manual`, the submitted password is stored through Laravel's existing hashed cast and the submitted active state is preserved, subject to the existing account rules.

## UI and Interaction

The page uses the existing `AppShell` layout and shared UI components. The create modal:

- Opens from a clearly labeled primary action.
- Focuses the first field on open and returns focus to the trigger on close.
- Closes with Escape and its close button, but not by accidental background clicks while a submission is processing.
- Displays field errors next to their fields and exposes them through accessible descriptions/live alerts.
- Shows the client select only when the customer role is selected and clears client selection when switching away from customer.
- Shows manual password and confirmation fields only in manual mode.
- Shows email-mode helper text explaining that the user will receive a password setup link.
- Disables controls during submission and reports success before closing or refreshing the table.

The table should remain usable on narrow screens. It may use a horizontally scrollable table region rather than forcing every column into an unreadable mobile layout.

## Data Flow

1. An authenticated active administrator visits the users route.
2. The controller queries users with their client summary and active clients for the create form, using pagination and eager loading.
3. Inertia renders the table and translated UI contract.
4. The modal submits to the Identity create endpoint.
5. The Form Request validates role/client/password-mode combinations.
6. The Identity application action creates the user inside a transaction.
7. For email mode, the action requests a reset link using the existing password-reset mechanism and leaves the new account inactive.
8. The controller redirects back with a localized success message and the table reloads.

## Authorization and Errors

- The route and controller are administrator-only.
- Non-administrators receive the existing authorization response for protected administration pages.
- Validation errors return through Inertia and preserve modal form state.
- If the password-reset notification cannot be dispatched, user creation must not silently claim success. The transaction and notification strategy should follow the existing application conventions and be covered by a test.
- All new user-facing strings require matching English and Persian translation entries under the Identity module.

## Testing

Add feature coverage for:

- Administrator access to the users page.
- Rejection of non-admin access.
- User table and create-form Inertia contract, including role and client options.
- Successful manual-password creation.
- Customer role requiring an active client.
- Non-customer roles rejecting a client assignment.
- Email password mode creating an inactive user without a password and requesting the reset link.
- Manual mode requiring a password and confirmation.

Add focused frontend/presentation assertions following the existing source-contract tests where browser-level tests are not available.

## Verification

Run the focused Pest feature tests, Laravel Pint on modified PHP files, the relevant TypeScript/Vue checks if configured, and the production Vite build. Inspect `git diff --check` and `git status` before reporting completion.
