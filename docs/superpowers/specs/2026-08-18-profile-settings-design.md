# Identity Profile Settings Design

## Status

Approved card-based direction. This document records the implementation contract.

## Goal

Allow an authenticated Identity user to manage personal information, contact information, and password from one Profile page while saving each area independently.

## Page Structure

The page is rendered at `/profile` inside the authenticated `AppShell` and contains three visible cards:

1. **Personal Information**
   - `name`
   - `last_name`
   - Independent save action and feedback state.

2. **Contact Information**
   - `email`
   - `mobile`
   - Independent save action and feedback state.
   - Email and mobile verification are explicitly deferred to a later Todo; this page does not imply verified status.

3. **Password**
   - `current_password`
   - `password`
   - `password_confirmation`
   - Independent save action and feedback state.

Desktop uses a two-column card grid with the password card spanning the available content width when appropriate. Mobile uses a single-column stack. Each card has a clear Lucide icon, visible title, helper text where needed, and a minimum 44px action control.

## Data Flow

- Identity owns the profile route, Form Requests, application actions, password verification, user mutation, and translations.
- The page receives a serializable user presentation object from Inertia: `id`, `name`, `last_name`, `email`, and `mobile`.
- Each card uses an independent Inertia `useForm` instance and submits to a dedicated endpoint.
- Successful updates return to the Profile page with a localized status message scoped to the updated card.
- Validation errors remain scoped to their card and do not reset unrelated form state.

## Security and Validation

- All endpoints require authentication and active-account middleware.
- Users may update only their own presentation fields; role, client, active state, and permissions are never accepted from the page.
- Name fields are required strings with bounded lengths.
- Email is required, valid, and unique except for the authenticated user.
- Mobile is optional and bounded; its verification is deferred.
- Password changes require the current password, a new password, confirmation, and the existing password policy.
- Password fields are never returned through Inertia props or included in logs.

## UX States

Each card explicitly supports ready, processing, validation error, server error, and successful save states. Success feedback uses a live region and remains local to the card. Buttons disable only while their own form is processing.

## Navigation and Icons

- Add a `Profile` action to the authenticated User Menu using the Lucide `UserRound` icon.
- Use Lucide icons for the three card headings and password visibility controls.
- Decorative icons use `aria-hidden="true"`; icon-only controls have localized accessible names.
- Profile remains outside the Login, Password Recovery, and Password Reset flows.

## Localization

All visible and accessible text must have matching English and Persian Identity translation entries. Product-facing verification language is not added until verification is implemented.

## Verification Criteria

- Guests cannot open or submit Profile endpoints.
- Authenticated users see their own current values.
- Personal information saves without changing contact or password data.
- Contact information saves without changing personal or password data.
- Duplicate email and invalid field values return scoped validation errors.
- Password changes require the current password and matching new-password confirmation.
- Successful password changes keep the current session authenticated; invalidating other sessions is outside this feature.
- Profile navigation is available from User Menu and uses the authenticated shell.
- English and Persian tests, TypeScript validation, Pint, and Vite build pass.
