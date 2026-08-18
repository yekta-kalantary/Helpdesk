# Frontend Page Design Queue

Frontend design and implementation are approved one page at a time.

## Process

1. Prepare one page-specific design document.
2. Review its UX, data contract, states, localization, responsive behavior, and accessibility requirements.
3. Wait for explicit approval of that page.
4. Implement only the approved page.
5. Verify and commit the page before preparing or implementing the next page.

## Page Queue

| Order | Page | Owner | Status | Document |
| --- | --- | --- | --- | --- |
| 1 | Login | Identity | Implemented | [identity-login.md](pages/identity-login.md) |
| 2 | Password Recovery | Identity | Implemented | [identity-password-recovery.md](pages/identity-password-recovery.md) |
| 3 | Password Reset | Identity | Implemented | [identity-password-reset.md](pages/identity-password-reset.md) |
| 4 | Application Shell | Shared frontend | Implemented | [application-shell.md](pages/application-shell.md) |
| 5 | Dashboard | Shared presentation | Pending | Not started |
| 6 | User List | Identity | Pending | Not started |
| 7 | User Create/Edit | Identity | Pending | Not started |
| 8 | User Detail | Identity | Pending | Not started |
| 9 | Client List | Clients | Pending | Not started |
| 10 | Client Create/Edit | Clients | Pending | Not started |
| 11 | Client Detail | Clients | Pending | Not started |
| 12 | Project List | Projects | Pending | Not started |
| 13 | Project Create/Edit | Projects | Pending | Not started |
| 14 | Project Detail | Projects | Pending | Not started |
| 15 | Task List | Tasks | Pending | Not started |
| 16 | Task Create/Edit | Tasks | Pending | Not started |
| 17 | Task Detail | Tasks | Pending | Not started |
| 18 | Notifications | Cross-module presentation | Pending | Not started |
| 19 | Error States | Shared frontend | Pending | Not started |

## Global Constraints

- Use Laravel, Inertia.js, Vue 3, TypeScript, Vite, Tailwind CSS, shadcn-vue, and Reka UI.
- Keep page code inside its owning module.
- Keep shared primitives and shell composition in the shared frontend layer.
- Use local assets only: IRANYekanXVF, Font Awesome Light, and Font Awesome Brands.
- Use Laravel localization with English and Persian entries for every user-facing string.
- Do not pass Eloquent models directly to Inertia pages.
- Do not put domain rules in Vue components.
