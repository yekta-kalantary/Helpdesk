# Shared UI Kit & Livewire Presentation

The Helpdesk presentation layer is **Livewire 4 + Blade + Tailwind CSS**. Reusable visual primitives live in `resources/views/components/ui` and remain the single source of truth for styling and interaction surfaces.

## Architecture

The UI Kit lives in the root presentation layer rather than a business module. It is a cross-cutting presentation concern, not a bounded context. Domain and Application layers never depend on Livewire, Blade or the UI Kit.

Each business module owns class-based Livewire components under:

```text
app-modules/<module>/src/Presentation/Livewire
```

and registers a module namespace such as `customers::`, `projects::`, `tasks::` or `tickets::` from its Service Provider.

## Page pattern

Use these component shapes consistently:

- `Index`: list/search/filter/delete interactions.
- `Form`: create/edit state, validation and save actions.
- `Show`: record detail and record-specific interactions such as comments, status or replies.

Routes use `Route::livewire()` and retain stable route names/URLs. Write operations should be Livewire actions instead of separate POST/PUT/PATCH/DELETE page routes.

Binary attachment downloads are the intentional exception: they remain authenticated HTTP GET routes after row-scope verification so files can be streamed directly rather than encoded through the Livewire response protocol.

## Shared component API

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

These components pass Livewire attributes such as `wire:model`, `wire:click`, `wire:loading`, `wire:target`, `wire:confirm` and `wire:navigate` to the relevant HTML controls.

## Rules

1. Keep business persistence and invariants in Application/Domain services; Livewire components orchestrate them.
2. Re-check permissions inside every mutating Livewire action. Page-route middleware is not sufficient for later Livewire update requests.
3. Preserve row-level ownership/scope checks for customer, project, task and ticket data.
4. Use `wire:key` in repeated interactive records.
5. Use `wire:confirm` for destructive actions and loading states for network actions.
6. Use `WithFileUploads` for Task/Ticket uploads, but keep downloads as streamed HTTP responses.
7. Keep Tailwind utilities in module views only for page-specific composition such as Kanban grids and conversation spacing.
8. Shared UI components must not query module models or contain business rules.

## Layouts

- `layouts.app` is the default Livewire full-page layout and owns authenticated navigation, global flash/error feedback and Livewire assets.
- `layouts.guest` is used by the Livewire login page and remains usable by error views.
- Internal navigation uses `wire:navigate` for SPA-like page transitions.

## CI contract

CI must pass Composer install/validation, migrations/seed, route discovery, `php artisan view:cache`, Livewire-focused feature tests, Pint, frontend build and archive generation.
