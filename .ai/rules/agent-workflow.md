# Agent Workflow

These gates are mandatory for every task. Do not edit files before completing Preflight, and do not claim completion before completing Postflight.

## Preflight

1. Open `.ai/rules/index.md`.
2. Read every rule whose glob covers any file that may be changed.
3. Search `.ai/rules` for task keywords to catch relevant rules not obvious from path matching.
4. Read the relevant canonical documentation linked by those rules.
5. Inspect the existing implementation, sibling files, tests, and current Git status.
6. For domain work, identify the owning bounded context, owned data, public contract, and allowed dependency direction before editing.
7. If the requested change conflicts with a rule or ownership is unclear, stop and ask for a decision.

## Implementation

- Make the smallest coherent change that satisfies the request and all loaded rules.
- Preserve unrelated user or agent changes.
- Add or update tests for changed behavior and architecture boundaries.
- Do not introduce a documented exception without explicit user approval.

## Postflight

1. Re-read the applicable rules and inspect the final diff against them.
2. Run the narrowest relevant tests, then required formatting, static analysis, architecture checks, and build commands.
3. Inspect `git status` and `git diff --check`.
4. Report each verification command and whether it passed, failed, or could not run.
5. Do not claim the task is complete if a mandatory check failed or an architectural violation remains.
