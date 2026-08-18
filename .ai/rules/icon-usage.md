# Icon Usage

Use icons throughout the panel when they improve recognition, scanning, navigation, or action clarity. Do not add icons merely to decorate every control.

- Use the installed `@lucide/vue` package as the only icon source.
- Prefer an icon when it communicates a familiar action, navigation destination, status, or content type.
- Keep visible text with icons for unfamiliar, destructive, or high-consequence actions; do not replace necessary labels with icons alone.
- Use the same Lucide icon consistently for the same meaning across the panel.
- Use icon-only controls only when the meaning is unambiguous and provide a localized accessible name with `aria-label` or visible text for assistive technology.
- Mark decorative icons with `aria-hidden="true"` and never use emoji or text glyphs as interface icons.
- Keep icon sizing, stroke weight, alignment, contrast, and interactive hit areas consistent with the surrounding component.
- Do not introduce a new icon library, remote icon asset, inline SVG collection, or custom icon font without explicit approval.
- If the required meaning has no suitable Lucide icon, stop and ask for a product decision before substituting another icon source or inventing a symbol.
