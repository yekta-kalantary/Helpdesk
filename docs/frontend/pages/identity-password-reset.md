# Identity Password Reset Page

## Status

Approved and implemented.

## Ownership

- Bounded context: Identity
- Module: `app-modules/identity`
- Frontend page: `app-modules/identity/resources/js/Pages/Auth/ResetPassword.vue`
- Route owner: Identity module
- Backend entry point: Identity password reset application use case

## Goal

Allow a user with a valid, unexpired reset link to choose a new password safely, understand the password requirements, and return to Login after success.

## UX Direction

UI/UX Pro Max recommends a minimal Swiss-style security surface: clear hierarchy, high contrast, restrained motion, and no decorative distractions.

Project-specific decisions:

- Reuse the Login and Password Recovery shell and spacing.
- Use a focused two-field form with visible password requirements.
- Use the local IRANYekanXVF font and the installed Lucide icon package only.
- Provide password visibility toggles for both password fields.
- Use semantic success and error states; do not use color alone.
- Do not show the raw token or technical token errors in the interface.

## Layout

### Desktop

- Full viewport RTL layout.
- Centered panel matching the Login and Password Recovery maximum width.
- Brand label and reset heading above the form.
- Short explanation of the reset action.
- Password and confirmation fields in a vertical stack.
- Compact password requirements block.
- Full-width primary action.
- Return-to-login link below the form.

### Mobile

- Full-width panel with safe horizontal padding.
- No fixed-height content area.
- Password requirements wrap without horizontal scrolling.
- Visibility controls remain reachable and have an accessible name.
- Error and success content remains readable at 375px.

## Required Page Data

Laravel must provide a typed Inertia contract:

```text
auth:
  user: null
  canLogin: boolean
  locale: string
  direction: "rtl" | "ltr"

reset:
  email: string
  token: string
```

The token is required by the reset request but must never be rendered as visible text, logged, or included in analytics payloads.

## Form Contract

Fields:

- `email`
- `password`
- `password_confirmation`
- `token` as a submitted hidden value or server-preserved route value

Browser behavior:

- Password fields use `autocomplete="new-password"`.
- Allow password managers and paste.
- Provide a show/hide password control for each password field.
- Do not expose the token in a visible input.
- Do not log password or token values.

Submission behavior:

- Use Inertia `useForm` for state, progress, errors, and submission.
- Submit through the Identity password reset route.
- Disable the active submit action while processing.
- Preserve the email value after a validation failure.
- Clear password fields after a failed or completed submission where appropriate.
- Redirect to Login after a successful reset with a localized success message.

## Password Requirements

The requirements displayed by the page must be derived from the Identity backend policy, not duplicated as an unrelated frontend rule.

At minimum, the page must communicate:

- Required password length
- Confirmation must match
- Password must not be empty
- Any additional server-defined policy

Server validation remains authoritative.

## States

The page must explicitly design and test these states:

- Initial ready state
- Submitting state
- Password validation failure
- Password confirmation mismatch
- Invalid or expired token
- Missing reset email
- Rate-limited reset attempt
- Generic reset failure
- Successful reset confirmation

Invalid or expired token behavior:

- Show a localized explanation that the link is no longer valid.
- Provide a clear link to request a new recovery email.
- Do not show the raw token, exception class, or database details.

Successful reset behavior:

- Replace the form with a confirmation state or redirect immediately to Login.
- Announce the success state through a live region.
- Provide a clear return-to-login action.

## Accessibility

- Use visible labels for both password fields.
- Associate every label and inline error with its input.
- Use `aria-invalid` and `aria-describedby` for invalid fields.
- Give each show/hide control an accessible name and pressed state.
- Keep focus indicators visible and high contrast.
- Move focus to the first invalid field after a failed submission.
- Move focus to the success or invalid-token heading when the state changes.
- Preserve keyboard tab order and Enter-to-submit behavior.
- Do not rely on color alone for password policy, errors, or success.
- Respect `prefers-reduced-motion`.
- Maintain at least 4.5:1 contrast for normal text.
- Keep controls at least 44px high.

## Localization Contract

All visible and accessible text must use Identity-owned Laravel translation keys.

Suggested keys:

```text
identity.auth.password_reset.brand
identity.auth.password_reset.title
identity.auth.password_reset.description
identity.auth.password_reset.email_label
identity.auth.password_reset.password_label
identity.auth.password_reset.password_confirmation_label
identity.auth.password_reset.show_password
identity.auth.password_reset.hide_password
identity.auth.password_reset.requirements_title
identity.auth.password_reset.requirement_length
identity.auth.password_reset.requirement_confirmation
identity.auth.password_reset.submit
identity.auth.password_reset.submitting
identity.auth.password_reset.return_to_login
identity.auth.password_reset.request_new_link
identity.auth.password_reset.invalid_token
identity.auth.password_reset.success
identity.auth.password_reset.generic_error
```

Every key must exist in both:

```text
app-modules/identity/resources/lang/en/messages.php
app-modules/identity/resources/lang/fa/messages.php
```

The key names and canonical source text are English. Persian text belongs only in the Persian translation file.

## Module Boundaries

- Reset route, token verification, password policy, password mutation, and reset notification behavior belong to Identity.
- The page must not import Identity Eloquent models.
- The page must not validate or decode the token client-side.
- The page must not access password-reset tables directly.
- Invalid-token behavior must be returned by the Identity application layer as a typed page state or localized validation error.

## Responsive Acceptance Criteria

- Works at 375px without horizontal scrolling.
- Works at 768px with stable panel alignment.
- Works at 1024px and 1440px without excessive decorative whitespace.
- Supports RTL text and mixed LTR email values.
- Keeps password requirements readable on small screens.
- Keeps the return-to-login action visible in all terminal states.

## Verification Criteria

- Guests can open a valid reset link.
- Valid token and matching passwords reset the account successfully.
- Invalid or expired tokens show a localized recovery path.
- Password policy failures show inline accessible errors.
- Password confirmation mismatch is clearly identified.
- Reset token and password values never appear in rendered text, logs, or analytics.
- English and Persian localization keys both exist.
- No external assets are requested.
- TypeScript validation and Vite build pass.
- Identity feature tests cover success, invalid token, validation, and authorization behavior.

## Approval Checklist

- [x] Layout and relationship to Login/Recovery approved
- [x] Password visibility interaction approved
- [x] Password requirements presentation approved
- [x] Invalid-token behavior approved
- [x] Success and error states approved
- [x] Localization key structure approved
- [x] Responsive behavior approved
- [x] Accessibility requirements approved
- [x] Identity module boundary approved
- [x] Verification criteria approved
