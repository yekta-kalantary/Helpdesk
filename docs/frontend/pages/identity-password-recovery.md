# Identity Password Recovery Page

## Status

Approved and implemented.

## Ownership

- Bounded context: Identity
- Module: `app-modules/identity`
- Frontend page: `app-modules/identity/resources/js/Pages/Auth/ForgotPassword.vue`
- Route owner: Identity module
- Backend entry point: Identity password recovery application use case

## Goal

Allow a user to request a password reset link without revealing whether an account exists, while providing clear next steps and a calm, trustworthy experience.

## UX Direction

UI/UX Pro Max recommends a minimal, Swiss-style authentication surface for password recovery: clear hierarchy, generous whitespace, high contrast, subtle motion, and no unnecessary decoration.

Project-specific decisions:

- Reuse the Login page shell, spacing, surface, local font, and RTL behavior.
- Use a single focused email form with one primary action.
- Use the local IRANYekanXVF font and the installed Lucide icon package only.
- Do not use external fonts, illustrations, security badges, or marketing sections.
- Use a neutral success surface rather than a visually alarming confirmation state.
- Keep the recovery path obvious: submit request, show confirmation, provide a return-to-login action.

## Layout

### Desktop

- Full viewport RTL layout.
- Centered panel with the same maximum width and visual language as Login.
- Brand label and page heading above the form.
- One email field with a visible label.
- Primary full-width action.
- Return-to-login link below the form.

### Mobile

- Full-width panel with safe horizontal padding.
- No fixed-height panel or clipped confirmation text.
- Primary action remains reachable without unnecessary scrolling.
- Email input and submit control remain at least 44px high.

## Required Page Data

The page must receive a typed, localized contract from Laravel:

```text
auth:
  user: null
  canLogin: boolean
  locale: string
  direction: "rtl" | "ltr"
```

The page must not receive an Eloquent model or password-reset token.

## Form Contract

Fields:

- `email`

Browser behavior:

- Use `type="email"`.
- Use `autocomplete="email"`.
- Allow password managers and paste behavior.
- Do not log or expose the submitted email beyond the intended form response.

Submission behavior:

- Use Inertia `useForm` for form state, progress, and errors.
- Submit through the Identity password recovery route.
- Disable only the active submit action while processing.
- Preserve the email value after a validation failure.
- Do not use Axios, ad-hoc fetch, or a client-side reset-token flow.

## Privacy and Security Behavior

The response for a syntactically valid email must not reveal whether the email belongs to an account.

- Show the same neutral confirmation message for an existing or unknown email.
- Do not display account existence errors.
- Apply server-side password reset throttling.
- Never include the reset token in Inertia props, HTML, JavaScript, logs, or client-side state.
- Generate reset URLs server-side.
- Keep token expiry and invalid-token handling inside Identity.
- Do not expose mail provider details to the user.

## States

The page must explicitly design and test these states:

- Initial ready state
- Submitting state
- Invalid email format
- Required email error
- Rate-limited request
- Neutral request confirmation
- Server or mail delivery failure
- Session expired

The confirmation state must provide:

- A concise explanation that an email may have been sent.
- A reminder to check spam or junk folders.
- A return-to-login action.
- No indication of whether the account exists.

Errors must be announced using `role="alert"` or an equivalent live region. Field errors must be connected with `aria-describedby`.

## Accessibility

- Use a visible label associated with the email field.
- Use `aria-invalid` for invalid input.
- Connect the inline error with `aria-describedby`.
- Move focus to the first invalid field after a failed submission when appropriate.
- Move focus to the confirmation heading after a successful request.
- Keep focus indicators visible and high contrast.
- Preserve keyboard submission with Enter.
- Do not rely on color alone to communicate errors or success.
- Respect `prefers-reduced-motion`.
- Maintain at least 4.5:1 contrast for normal text.
- Keep interactive controls at least 44px high.

## Localization Contract

All visible and accessible text must use Identity-owned Laravel translation keys.

Suggested keys:

```text
identity.auth.password_recovery.brand
identity.auth.password_recovery.title
identity.auth.password_recovery.description
identity.auth.password_recovery.email_label
identity.auth.password_recovery.submit
identity.auth.password_recovery.submitting
identity.auth.password_recovery.return_to_login
identity.auth.password_recovery.confirmation_title
identity.auth.password_recovery.confirmation_description
identity.auth.password_recovery.check_spam
identity.auth.password_recovery.rate_limited
identity.auth.password_recovery.generic_error
```

Every key must exist in both:

```text
app-modules/identity/resources/lang/en/messages.php
app-modules/identity/resources/lang/fa/messages.php
```

The key names and canonical source text are English. Persian text belongs only in the Persian translation file.

## Module Boundaries

- Recovery route, request validation, token generation, throttling, mail dispatch, and token verification belong to Identity.
- The Vue page must not import Identity Eloquent models.
- The Vue page must not receive reset tokens or query the database.
- Mail delivery must be triggered by the Identity application use case or an Identity event listener.
- Any notification or audit integration must consume a stable Identity event rather than reaching into Identity internals.

## Responsive Acceptance Criteria

- Works at 375px without horizontal scrolling.
- Works at 768px with stable panel alignment.
- Works at 1024px and 1440px without excessive decorative whitespace.
- Supports RTL text and mixed LTR email values.
- Handles browser zoom without clipped errors or confirmation text.
- Keeps the return-to-login action visible in both initial and confirmation states.

## Verification Criteria

- Guests can reach the password recovery page.
- Valid and unknown emails produce the same neutral confirmation response.
- Invalid email input returns a localized inline error.
- Rate limiting returns a localized accessible message.
- No reset token appears in the response payload or rendered page.
- English and Persian localization keys both exist.
- No external assets are requested.
- TypeScript validation and Vite build pass.
- Identity feature tests cover privacy-safe recovery behavior and throttling.

## Approval Checklist

- [ ] Layout and visual relationship to Login approved
- [ ] Privacy-safe confirmation behavior approved
- [ ] Form and error states approved
- [ ] Localization key structure approved
- [ ] Responsive behavior approved
- [ ] Accessibility requirements approved
- [ ] Identity module boundary approved
- [ ] Verification criteria approved
