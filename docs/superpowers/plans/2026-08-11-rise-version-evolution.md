# RISE Version Evolution Research Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the official RISE 3.0→4.0 changelog into maintainable, source-backed research documents without expanding the project MVP scope.

**Architecture:** Preserve the existing baseline research as the stable domain map, keep the dated re-validation as current-state evidence, and add a separate version-evolution document for historical product evolution. Update only the indexes and re-validation conclusions that are materially strengthened by the broader release history.

**Tech Stack:** Markdown, GitHub repository documentation, official Fairsketch RISE Docs changelog.

## Global Constraints

- Primary source is the official Fairsketch changelog category and its linked release pages.
- Analyse releases 3.0 through 4.0 inclusive.
- Distinguish observed product behavior from engineering inference.
- Do not copy complete release notes verbatim.
- Do not modify MVP scope or PRD in this task.
- Preserve UTF-8 and existing `&rlm;` / `&lrm;` Markdown direction conventions.

---

### Task 1: Build the version-evolution research document

**Files:**
- Create: `docs/research/rise-crm-version-evolution-3-to-4.md`

**Interfaces:**
- Consumes: official RISE changelog releases 3.0→4.0 and existing baseline/re-validation research.
- Produces: chronological and domain-oriented evidence used by future product/engineering decisions.

- [ ] **Step 1:** Confirm the full official release range from the Change Logs category.
- [ ] **Step 2:** Extract product-relevant changes per release and ignore purely cosmetic/low-signal fixes unless they prove a domain capability.
- [ ] **Step 3:** Group evolution into phases: 3.0–3.2, 3.3–3.5, 3.6–3.8, 3.9, 4.0.
- [ ] **Step 4:** Add cross-version analysis for Client/Contact, Project/Task, permissions, collaboration, files, support, notifications and extensibility.
- [ ] **Step 5:** Add explicit source limitations for releases whose standalone page cannot be retrieved.
- [ ] **Step 6:** Read back the Git file and verify the first/last sections and key version markers.

### Task 2: Strengthen the current RISE re-validation

**Files:**
- Modify: `docs/research/rise-crm-revalidation-2026-08-11.md`

**Interfaces:**
- Consumes: `rise-crm-version-evolution-3-to-4.md`.
- Produces: a concise current-state document that points to historical evidence without duplicating it.

- [ ] **Step 1:** Add the official Change Logs category and evolution document to the source set.
- [ ] **Step 2:** Add only materially supported conclusions: general/contextual tasks, move-between-projects, multi-level subtasks, client-contact permission evolution, internal projects, multiple client managers, and the progression toward AI in 4.0.
- [ ] **Step 3:** Preserve existing `RV-RISE-*` IDs; add new IDs only for genuinely new reusable requirements.
- [ ] **Step 4:** Read back and verify no existing current-state conclusions were accidentally removed.

### Task 3: Update documentation navigation

**Files:**
- Modify: `docs/research/README.md`
- Modify: `docs/README.md`

**Interfaces:**
- Consumes: the new version-evolution document.
- Produces: a stable reading order for future work.

- [ ] **Step 1:** Add the evolution file to the RISE section.
- [ ] **Step 2:** Define reading order as PRD → Base Research → Version Evolution when historical context matters → Latest Re-validation.
- [ ] **Step 3:** State that historical product breadth does not expand MVP scope.
- [ ] **Step 4:** Read back both indexes and verify links/paths.

### Task 4: Final source and Git verification

**Files:**
- Verify all files above.

**Interfaces:**
- Consumes: committed Git state.
- Produces: evidence for completion.

- [ ] **Step 1:** Confirm official category lists versions 3.0 through 4.0.
- [ ] **Step 2:** Verify key evidence in source pages: 3.0 project-specific task statuses; 3.1 task priority; 3.2 client/contact model and internal projects; 3.5 general/contextual tasks; 3.6 contact permissions; 3.7 context-free tasks; 3.8 client-contact mentions and task movement; 3.9 permissions/client manager evolution; 4.0 AI/general-task time logs.
- [ ] **Step 3:** Fetch committed Markdown files from GitHub and confirm source sections and conclusions exist.
- [ ] **Step 4:** Report source limitations instead of filling them with assumptions.

## Self-review

- Spec coverage: all design deliverables are represented by Tasks 1–4.
- Placeholder scan: no TBD/TODO/unspecified implementation steps.
- Scope consistency: PRD is explicitly excluded from modification.
