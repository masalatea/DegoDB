# Promotion and optional fallback stack checkpoint / promotion・optional fallback stack checkpoint

Date: 2026-07-13

## Summary

This checkpoint records the local integration state after completing:

- #867-#876 SQLite-first to MySQL/MariaDB offline promotion;
- #877-#883 optional deterministic scan / local fallback generalization.

## Local git state

Branch:

- `feature/sqlite-to-mysql-promotion-plan`

After `git fetch --all --prune`:

- worktree is clean;
- branch is `32` commits ahead of `origin/develop`;
- `origin/develop` has `0` commits not in this branch;
- no push was performed in this checkpoint.

The branch name started as the promotion lane, but the stack now also includes the optional fallback generalization lane. If review size is a concern, the two conceptual sections are:

1. SQLite-to-MySQL promotion and Sample33 proof;
2. optional AI fallback generalization around Sample19 task packets.

## Verification evidence

Latest full test evidence from this stack:

- `make test`: `604 tests`, `15238 assertions`, `Skipped: 5`.

Additional focused evidence included:

- promotion collectors/export/import/cutover/rehearsal tests;
- live MySQL/MariaDB target-schema/import/verification checks where applicable;
- SSO app-user promotion resolver test;
- task packet scan/local fallback/Ollama adapter tests;
- docs-only `git diff --check` checks for checkpoint/report slices.

## Supported boundary at checkpoint

SQLite-to-MySQL promotion:

- one-way offline promotion;
- fresh target schema;
- deterministic export/import;
- verification gate before cutover;
- explicit write freeze and rollback window;
- SQLite source retention as rollback evidence;
- no bidirectional sync, MySQL-to-SQLite standard path, or zero-downtime CDC claim.

Optional fallback:

- Codex/Claude remain primary;
- deterministic scan is advisory;
- local fallback writes advisory artifacts only;
- fallback and formal candidates share validation;
- real local Ollama generation remains opt-in/manual and is not part of normal test gates.

## Integration decision

The stack is locally PR-ready.

Next external action requires user direction:

- push current feature branch and create a PR to `develop`; or
- split the stack into smaller PRs if review size is a concern.
