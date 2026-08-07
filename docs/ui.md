# Shared UI Kit

The Helpdesk presentation layer uses anonymous Blade components from `resources/views/components/ui` as the single source of truth for reusable UI primitives.

## Architecture

The UI Kit intentionally lives in the root presentation layer rather than in an application module. It is a cross-cutting presentation concern, not a business bounded context. Domain and application layers never depend on it.

Application modules may depend on the shared UI components from their Blade views only.

## Component API

Shared primitives currently include:

- `x-ui.page-header`
- `x-ui.nav-link`
- `x-ui.button`
- `x-ui.card`
- `x-ui.alert`
- `x-ui.badge`
- `x-ui.input`
- `x-ui.select`
- `x-ui.textarea`
- `x-ui.checkbox`
- `x-ui.filter-bar`
- `x-ui.form-actions`
- `x-ui.table`
- `x-ui.empty-row`
- `x-ui.empty-state`
- `x-ui.stat-card`
- `x-ui.progress`
- `x-ui.meta-item`

## Rules for module views

1. Use `x-ui.*` for reusable controls, cards, tables, badges, alerts, actions, headers and form fields.
2. Keep Tailwind utilities inside a module view only for page-specific composition such as grid columns, Kanban layout or message-thread spacing.
3. Do not add a new `.btn-*`, `.card`, `.badge`, form-control or table primitive to module CSS.
4. Add a new shared component when the same presentation pattern appears in more than one bounded context.
5. Shared components must not query module models or contain domain/business rules.
6. Permission and ownership decisions remain in controllers/policies/application queries. Blade may use `@can` only to conditionally expose already-authorized actions.

## Layouts

- `layouts.app` is the authenticated application shell and owns navigation, flash messages and global validation feedback.
- `layouts.guest` is the guest/error shell used by login and HTTP error pages.

## CI contract

CI runs `php artisan view:cache` so every Blade view and shared component must compile before tests and frontend build are accepted.
