# Documentation Organization — Design

Date: 2026-08-13

## Goal

Reorganize the repository documentation so product scope, engineering documentation, implementation evidence, research, and internal Superpowers artifacts have clear ownership and discoverability. The reorganization must also remove or correct stale documentation that still describes the pre-MVP simplified `Users / Projects / Tasks` model.

## Source of truth

The documentation hierarchy after this change is:

1. `docs/product/client-task-management-mvp.md` — authoritative MVP product specification.
2. Current implementation and repository state — source of truth for engineering architecture, database structure, installation, and verification evidence.
3. `docs/research/` — product/engineering references only; research never expands MVP scope by itself.
4. `docs/internal/superpowers/` — process history, design specs, and execution plans; useful for audit but not primary product documentation.

If any legacy document conflicts with the PRD or the current `dev` implementation, the legacy content must be corrected or removed rather than preserved as a competing source of truth.

## Target structure

```text
docs/
├── README.md
├── product/
│   └── client-task-management-mvp.md
├── engineering/
│   ├── architecture.md
│   ├── database.md
│   └── installation.md
├── implementation/
│   └── client-task-management-mvp-verification.md
├── research/
│   ├── README.md
│   ├── jira-cloud-product-analysis.md
│   ├── jira-cloud-revalidation-2026-08-13.md
│   ├── rise-crm-product-analysis.md
│   ├── rise-crm-revalidation-2026-08-11.md
│   ├── rise-crm-version-evolution-3-to-4.md
│   ├── worksuite-product-analysis.md
│   └── worksuite-revalidation-2026-08-11.md
└── internal/
    └── superpowers/
        ├── specs/
        └── plans/
```

## Documentation responsibilities

### Root `README.md`

The repository root README is an onboarding page, not a duplicated architecture or database document. It should contain a concise project purpose, supported runtime stack at a high level, quick-start installation commands, links to the canonical documentation index and MVP PRD, and a short quality/test command reference if useful. It must not duplicate database schemas, authorization rules, or detailed domain behavior that can drift independently.

### `docs/README.md`

This is the canonical documentation index. It must show the actual current folder tree; explain the role of Product, Engineering, Implementation, Research, and Internal documentation; define the recommended reading order; preserve the rule that PRD controls MVP scope; link to Jira, RISE CRM, and Worksuite research; explain naming, UTF-8, RTL/LTR direction-entity conventions; and clearly mark `docs/internal/` as non-authoritative process history.

### `docs/product/`

Contains product specifications only. The existing MVP PRD remains in place and its product scope is not changed by this work.

### `docs/engineering/architecture.md`

Replace the stale simplified three-module description with the current MVP architecture documented by implementation verification and repository state. It should cover at minimum: identity roles (`Admin`, `Customer`); Client vs User distinction; Project ownership and Project Membership visibility; Task visibility inherited from Project Membership; task assignment as responsibility, not visibility; task lifecycle and assignment invariants at architecture level; collaboration, private attachments, activity/audit, notifications; authorization layers and isolation boundaries; major domain services/workflows where architecture-relevant; and concurrency/transaction invariants that remain important.

It should explain architecture, not duplicate the full PRD requirement table.

### `docs/engineering/database.md`

Replace the stale four-table schema with the current implemented domain data model. It should document the current tables/entities and their important relationships/invariants, including Client, User, Project, Membership, Task, collaboration, activity, and notification-related persistence where owned by the application.

Framework tables may be summarized separately. Exact details must be taken from the current implementation/migrations rather than inferred from old documentation.

### `docs/engineering/installation.md`

Move the current installation guide under Engineering and reconcile it with the current repository setup. It should cover supported prerequisites, fresh setup, environment/database configuration, migrations/seeding, application start, asset build, and the canonical verification commands that actually exist in the repository.

### `docs/implementation/`

Keep implementation verification separate from architecture. `client-task-management-mvp-verification.md` remains an evidence document showing how current implementation maps to PRD requirements and tests.

### `docs/research/`

Keep existing product research grouped together. Research documents are not renamed unless a naming inconsistency or broken link is found. The Research README remains the research-specific index and source hierarchy.

### `docs/internal/superpowers/`

Move all existing Superpowers specs and plans from `docs/superpowers/` into `docs/internal/superpowers/`. Preserve history instead of deleting completed artifacts. Update relative links inside these files when moves would break them.

This documentation-organization spec itself is created directly in the final internal location.

## Stale-content policy

A document is stale when it describes an architecture, database schema, installation flow, feature set, or source hierarchy that materially conflicts with the current `dev` implementation or authoritative PRD.

For stale content:

- correct it when the document still has a valid ongoing responsibility;
- remove duplicated sections when another canonical document already owns that information;
- preserve historical Superpowers artifacts under `docs/internal/` even when they describe past planning decisions, because their role is audit history rather than current truth;
- do not preserve stale operational statements in current-facing README/Engineering docs merely for history.

## Move and link strategy

Moves must be performed as content-preserving relocations where possible:

- `docs/architecture.md` → `docs/engineering/architecture.md`
- `docs/database.md` → `docs/engineering/database.md`
- `docs/installation.md` → `docs/engineering/installation.md`
- `docs/superpowers/plans/*` → `docs/internal/superpowers/plans/*`
- `docs/superpowers/specs/*` → `docs/internal/superpowers/specs/*`

After moves, every repository Markdown reference to old paths must be updated. Broken relative links are not acceptable.

## Naming and formatting

- Directory and file names remain English, lowercase, and kebab-case except conventional `README.md`.
- Markdown is UTF-8.
- Existing `&rlm;` / `&lrm;` direction entities are preserved where needed for mixed Persian/English rendering.
- Current-facing documents should use consistent English technical terminology inside Persian prose.
- No generated documentation format or external docs site is introduced.

## Scope boundaries

This work may change Markdown documentation structure and content only.

It must not change PHP/Laravel runtime behavior, migrations or database schema, the MVP PRD requirements, introduce Jira/RISE/Worksuite features into MVP, delete audit-useful Superpowers history, or restructure unrelated source-code folders.

## Verification

The implementation plan must include verification that:

1. the final `docs/` tree matches the target structure;
2. no old `docs/architecture.md`, `docs/database.md`, `docs/installation.md`, or `docs/superpowers/` files remain after successful moves;
3. all Markdown links under repository-owned documentation resolve to existing repository paths where they are relative links;
4. root README and docs index no longer describe the obsolete four-table / three-entity system as current architecture;
5. Engineering architecture/database content is grounded in current implementation and MVP verification evidence;
6. `docs/product/client-task-management-mvp.md` is unchanged;
7. runtime/source files are unchanged;
8. repository tests/quality checks are run if the execution environment supports them, while documentation-specific verification remains mandatory regardless.

## Success criteria

A new engineer should be able to open `README.md` → `docs/README.md` and immediately know what the product is; which document defines MVP scope; where current architecture/database/setup documentation lives; where implementation verification lives; where external product research lives; which documents are internal historical planning artifacts; and which documents are authoritative for each type of decision.
