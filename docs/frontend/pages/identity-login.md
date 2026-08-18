# Identity Login Page

## Status

Approved and implemented.

## Ownership

- Bounded context: Identity
- Module: `app-modules/identity`
- Frontend page: `app-modules/identity/resources/js/Pages/Auth/Login.vue`
- Route owner: Identity module
- Backend entry point: Identity application authentication use case

## Goal

Allow an active Identity user to authenticate safely and reach the authorized application landing page with clear feedback and no unnecessary visual noise.

## UX Direction

UI/UX Pro Max guidance for this page recommends a modern SaaS authentication surface with restrained layered surfaces, strong contrast, subtle motion, and a focused call to action.

Project-specific decisions:

- Use a centered RTL authentication panel on a quiet neutral background.
- Use a restrained elevated surface instead of heavy glass blur.
- Use the local IRANYekanXVF font; do not load the suggested external Google font.
- Use Indigo as the primary action color and semantic red only for errors.
- Use Lucide icons when an icon communicates meaning; never use emoji icons.
- Keep the page focused on authentication; do not add marketing sections or decorative illustrations.

## Layout

### Desktop

- Full viewport RTL layout.
- Centered panel with a maximum width of approximately 420px.
- Brand mark or product name above the form.
- Clear page heading and short supporting description.
- Form fields in a single vertical stack.
- Primary submit action at full panel width.
- Password recovery link below the primary action.

### Mobile

- Use the full viewport width with safe horizontal padding.
- Keep the form panel visually distinct without forcing a fixed height.
- Keep the primary action reachable without scrolling where possible.
- Preserve browser zoom and password-manager behavior.

## Required Page Data

The page must receive localized and typed data from Laravel rather than defining text dictionaries in Vue.

```text
auth:
  user: null
  canResetPassword: boolean
  canRememberSession: boolean
  locale: string
  direction: "rtl" | "ltr"
```

The page must not receive an Eloquent model.

## Form Contract

Fields:

- `email` or the Identity-defined login identifier
- `password`
- `remember` when supported by the backend contract

Browser behavior:

- Login identifier uses the correct `autocomplete` value.
- Password uses `autocomplete="current-password"`.
- Password managers and paste must remain enabled.
- Do not disable browser autofill.
- Do not log or expose password values.

Submission behavior:

- Use Inertia `useForm` for state, progress, errors, and submission.
- Submit through the Identity route; do not use ad-hoc `fetch` or Axios.
- Disable only the active submit action during processing.
- Preserve entered identifier after a failed submission unless the backend explicitly rejects it.
- Redirect only after the server confirms authentication.

## States

The page must explicitly design and test these states:

- Initial ready state
- Submitting state
- Invalid field state
- Invalid credentials state
- Inactive account state
- Rate-limited state
- Expired session state
- Server failure state
- Password recovery unavailable state

Errors must be shown next to the affected field when applicable and announced through `role="alert"` or an equivalent live region.

## Accessibility

- Use visible labels, not placeholder-only labels.
- Associate each label with its input.
- Connect inline errors with `aria-describedby`.
- Use `aria-invalid` for invalid fields.
- Provide an accessible name for every icon-only action.
- Keep focus visible with a high-contrast outline.
- Move focus to the first invalid field after a failed submission when appropriate.
- Preserve keyboard tab order and Enter-to-submit behavior.
- Do not rely on color alone to communicate errors.
- Respect `prefers-reduced-motion`.
- Maintain at least 4.5:1 contrast for normal text.
- Keep interactive controls at least 44px high.

## Localization Contract

All visible and accessible text must use Identity-owned Laravel translation keys.

Suggested keys:

```text
identity.auth.login.title
identity.auth.login.description
identity.auth.login.identifier_label
identity.auth.login.password_label
identity.auth.login.remember_label
identity.auth.login.submit
identity.auth.login.submitting
identity.auth.login.forgot_password
identity.auth.login.invalid_credentials
identity.auth.login.inactive_account
identity.auth.login.rate_limited
identity.auth.login.generic_error
identity.auth.login.success
```

Every key must exist in both:

```text
app-modules/identity/resources/lang/en/messages.php
app-modules/identity/resources/lang/fa/messages.php
```

The source key names and canonical source text are English. Persian text belongs only in the Persian translation file.

## Server and Module Boundaries

- Login route, request validation, authentication use case, and authorization behavior belong to Identity.
- The Vue page must not import Identity Eloquent models.
- The page must not query Users, Clients, Projects, or Tasks directly.
- Successful authentication may emit an Identity integration event only when another module has an explicit need.
- Navigation after login must be determined by a Laravel response or localized shared page contract.

## Responsive Acceptance Criteria

- Works at 375px without horizontal scrolling.
- Works at 768px with a stable centered form.
- Works at 1024px and 1440px without excessive empty space.
- Supports RTL text and mixed LTR email values.
- Handles browser zoom without clipped controls.
- Keeps errors readable on narrow screens.

## Verification Criteria

- Unauthorized users can reach the login page.
- Valid credentials redirect to the authorized landing page.
- Invalid credentials show a localized accessible error.
- Inactive accounts cannot authenticate.
- Rate-limit feedback is localized and accessible.
- Password manager autofill works.
- English and Persian localization keys are both present.
- No external assets are requested.
- TypeScript validation and Vite build pass.
- Identity feature tests cover authentication and authorization behavior.

## Approval Checklist

- [ ] Layout direction and visual density approved
- [ ] Form fields and labels approved
- [ ] Error and loading states approved
- [ ] Localization key structure approved
- [ ] Responsive behavior approved
- [ ] Accessibility requirements approved
- [ ] Module ownership and backend boundary approved
- [ ] Verification criteria approved
