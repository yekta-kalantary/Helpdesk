# Commit Before Next Task

Each task is an independent unit of work. The current task must be verified and committed before the next task begins.

- Run verification appropriate to the completed task.
- Inspect Git status, the diff, and staged files.
- Stage only changes belonging to the current task and commit them with a short, specific message.
- Do not start the next task with uncommitted changes; ask the user to clarify ownership first.
- Report verification failures honestly; never hide failed tests or builds.
- Commit incomplete work or work with failing tests only with the user's explicit approval.
- Check `git status` after committing, then proceed to the next task.
