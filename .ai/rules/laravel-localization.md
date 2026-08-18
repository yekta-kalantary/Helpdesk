# Laravel Localization

All user-facing text must follow Laravel's localization structure.

- Never hardcode user-facing labels, messages, headings, button text, validation text, notification text, or accessible labels in application code or frontend components.
- Use English translation keys and English source text as the canonical definition.
- Add a matching Persian translation for every new user-facing translation key.
- Store translations in the appropriate Laravel language files under `resources/lang/en` and `resources/lang/fa`; module-owned translations belong under that module's `resources/lang/en` and `resources/lang/fa` directories.
- Use Laravel translation APIs such as `__()`, `trans()`, `Lang::get()`, or localized data passed from Laravel to Inertia pages.
- Frontend components must receive translated labels and messages from Laravel or a shared localization contract; do not duplicate translation dictionaries inside Vue components.
- Keep translation keys stable, descriptive, and grouped by bounded context or feature.
- When a new key is introduced, verify that both English and Persian entries exist before completing the task.
