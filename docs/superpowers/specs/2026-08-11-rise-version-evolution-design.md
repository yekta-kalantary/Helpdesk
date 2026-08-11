# RISE Version Evolution Research Design — 2026-08-11

## Goal

Upgrade the RISE research by analysing the official Fairsketch changelog from version 3.0 through 4.0 and separating stable product/domain patterns from version-specific evolution.

## Source of truth

Primary source: `https://risedocs.fairsketch.com/doc/category/4` and the official release pages linked from that category.

Existing Git references:

- `docs/research/rise-crm-product-analysis.md`
- `docs/research/rise-crm-revalidation-2026-08-11.md`
- `docs/research/README.md`

## Chosen approach

Use a three-layer research model:

1. `rise-crm-product-analysis.md` — stable domain map and baseline requirements.
2. `rise-crm-revalidation-2026-08-11.md` — current-state validation and important present-day deltas.
3. `rise-crm-version-evolution-3-to-4.md` — chronological evolution from 3.0 to 4.0, grouped by product/domain impact rather than copied release notes.

This avoids turning the baseline research into a changelog dump while preserving evidence about how RISE's domain model evolved.

## Scope

Analyse official releases from 3.0 through 4.0 inclusive. Patch releases that contain only fixes or environment preparation are recorded as low-impact releases rather than expanded into artificial product conclusions.

Versions 1.x and 2.x are outside the primary scope. They should only be consulted later if a current concept cannot be understood from 3.0+ evidence.

## Classification model

Each meaningful release change is classified into one of these categories:

- Domain model / data relationship
- Access / permission / visibility
- Workflow / lifecycle
- Collaboration / communication
- UX / presentation
- Reporting / analytics
- Integration / platform
- Security / reliability
- Extension / plugin

Only source-backed behavior is described as observed product behavior. Architectural consequences are explicitly labelled as engineering inference.

## Key questions

The evolution document should answer:

- How did Project/Task scope evolve from project-only tasks toward general/contextual tasks?
- How did Task hierarchy evolve through subtasks, checklist behavior and Gantt relationships?
- How did Client Contact permissions and project-scoped customer collaboration evolve?
- How did project/task visibility and role permissions become more granular?
- How did File Manager, reminders, notifications, tickets and client portal capabilities become separate subsystems?
- Which later capabilities represent product expansion rather than core task collaboration?

## Important expected patterns

The research should specifically track evidence for:

- project-specific task statuses
- task priority and task ID/reference visibility
- internal projects
- general/contextual tasks
- task movement between projects
- subtasks and multi-level hierarchy
- task/client-contact mentions
- client-contact permission customization
- client account/contact relationship changes
- project/client access permissions
- project file/folder permission behavior
- ticket automation and mobile support
- PWA/push notification evolution
- multiple client/lead managers
- AI subsystem introduction in 4.0

## Guardrails

- Do not copy the full changelog into Git.
- Do not infer an undocumented feature from a bug fix unless the fix clearly proves the feature exists.
- Do not promote RISE features into the project MVP automatically.
- Do not modify the MVP PRD as part of this research task.
- Preserve the Persian/English Markdown direction conventions already used in `docs/`.

## Deliverables

- Create `docs/research/rise-crm-version-evolution-3-to-4.md`.
- Update `docs/research/rise-crm-revalidation-2026-08-11.md` with insights strengthened by the complete 3.0→4.0 history.
- Update `docs/research/README.md` and `docs/README.md` to include the new evolution document and reading order.

## Self-review

- No TBD/TODO placeholders.
- The evolution document is analytical, not a verbatim changelog mirror.
- Stable baseline, current-state revalidation and historical evolution have distinct responsibilities.
- Source limitations are stated explicitly.
- MVP scope remains governed by the PRD, not by RISE feature breadth.
