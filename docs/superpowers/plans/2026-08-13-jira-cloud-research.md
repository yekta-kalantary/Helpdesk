# Jira Cloud Research Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Jira Cloud / Jira Software as a source-backed research reference beside RISE CRM and Worksuite without changing the MVP scope.

**Architecture:** Documentation-only change. A stable base analysis captures product/domain patterns, a dated re-validation captures current Jira Cloud terminology and edition caveats, and the research index links both. The PRD remains authoritative and untouched.

**Tech Stack:** Markdown, Git, official Atlassian Jira Cloud documentation.

## Global Constraints

- Scope is Jira Cloud / Jira Software only.
- Jira Service Management and Jira Product Discovery are excluded.
- Use official Atlassian sources only for factual claims.
- Use current terminology (`Space`, `Work item`, `Work type`) while documenting legacy aliases when still relevant to JQL/API/docs.
- Do not modify runtime code or `docs/product/client-task-management-mvp.md`.

---

### Task 1: Base Jira Cloud product analysis

**Files:**
- Create: `docs/research/jira-cloud-product-analysis.md`

**Interfaces:**
- Consumes: official Atlassian documentation and the source/format conventions in `docs/research/README.md`.
- Produces: stable Jira domain map and extracted requirements for future product design.

- [x] **Step 1:** Document source policy, terminology, actors, entities, and domain map.
- [x] **Step 2:** Cover spaces, work items, hierarchy, workflows, boards/backlog, Scrum/Kanban, fields, search/JQL, permissions/security, collaboration, planning/dependencies, components/releases, automation, forms, reporting, and development metadata.
- [x] **Step 3:** For each domain, distinguish observed capability from extracted engineering requirement and future-product implication.
- [x] **Step 4:** Add a feature-adoption matrix that explicitly separates future candidates from current MVP scope.
- [x] **Step 5:** Add an official-source bibliography and scan for unsupported claims.

### Task 2: Current-state re-validation

**Files:**
- Create: `docs/research/jira-cloud-revalidation-2026-08-13.md`

**Interfaces:**
- Consumes: Task 1 base analysis plus official Atlassian pages current on 2026-08-13.
- Produces: dated caveat/delta record for future re-validation.

- [x] **Step 1:** Record the `Project → Space` and `Issue → Work item` terminology rollout and JQL compatibility caveat.
- [x] **Step 2:** Record team-managed vs company-managed configuration distinctions and feature/edition-specific caveats such as advanced plans.
- [x] **Step 3:** Record current limits or rollout notes only when documented officially and clearly date-sensitive.
- [x] **Step 4:** Separate stable product patterns from volatile current-state details.

### Task 3: Research index integration and verification

**Files:**
- Modify: `docs/research/README.md`

**Interfaces:**
- Consumes: Jira base analysis and dated re-validation created above.
- Produces: discoverable Jira research entry beside RISE CRM and Worksuite.

- [x] **Step 1:** Add Jira Cloud section with links to base analysis and re-validation.
- [x] **Step 2:** Update the future-implementation reading rule so Jira is a valid product reference while preserving PRD precedence.
- [x] **Step 3:** Verify all relative links resolve and no MVP/Runtime files changed.
- [x] **Step 4:** Compare `dev` before/after and confirm the diff is documentation-only.

## Verification

- GitHub compare against pre-work `dev` SHA `2ac17ca3fafbc40d806b349c8e8095eaa65d59cb` shows only research/spec/plan Markdown changes.
- `docs/product/client-task-management-mvp.md` and runtime files are unchanged.
- `docs/research/README.md` links to both Jira research files on `dev`.
- Local clone/test execution was unavailable because the execution container could not resolve `github.com`; the repository CI workflow does not run on pushes to `dev`, only `main`, `agent/**`, pull requests to `main`, or manual dispatch.
