# Domain-Driven Modular Monolith

## Status

Approved on 2026-08-19.

## Purpose

This directory is the canonical architecture documentation for the Helpdesk modular monolith. The documents are intentionally split by concern so that agents and contributors can load only the context needed for a task.

## Reading Order

1. [Scope and Principles](01-scope-and-principles.md)
2. [Bounded Contexts and Data Ownership](02-bounded-contexts-and-data-ownership.md)
3. [Clean Architecture](03-clean-architecture.md)
4. [Event-Driven Communication](04-event-driven-communication.md)
5. [Architecture Enforcement](05-architecture-enforcement.md)

## Mandatory Rule

Every business datum has exactly one owning bounded context. The owning context is the only context allowed to create, update, or delete it. Other contexts may keep read-only projections, but projections are never sources of truth.

All changes must also follow the global preflight and postflight gates in `.ai/rules/agent-workflow.md`. Existing violations are migration work and must not be treated as approved patterns.

## Document Selection

| Task | Read |
| --- | --- |
| Defining or reviewing module boundaries | `01`, `02` |
| Adding a model, table, or relationship | `02`, `03` |
| Adding a use case or service | `01`, `03` |
| Adding an event or consumer | `02`, `04` |
| Adding an architecture test or dependency rule | `01`, `05` |
| Modifying code with an existing boundary violation | `02`, `03`, `05` |
