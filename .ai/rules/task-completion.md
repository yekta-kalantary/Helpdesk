# Task Completion

Each task is an independent unit of work. Verify and inspect it before starting unrelated work.

- Run verification appropriate to the completed task.
- Inspect Git status and the diff before reporting completion.
- Do not modify, stage, revert, or include unrelated changes.
- Commit only when the user explicitly requests it or the active workflow explicitly authorizes it.
- Before starting unrelated work with existing uncommitted changes, identify ownership and avoid conflicts; ask the user only when ownership is genuinely ambiguous.
- Report verification failures honestly; never hide failed tests or builds.
- Do not claim completion when required verification has not run or has failed.
- After an authorized commit, check `git status` before proceeding.
