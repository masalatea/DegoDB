# 2026-0701 Post-React-Bridge Required Enforcement No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Validation feedback polish** as the next implementation slice.

Generated runtime and React bridge required validation now fail closed consistently. The next smallest product-facing gap is that validation failures still expose raw machine codes such as `input.missing:body` directly to users. The first slice should keep those codes for tests/integration while adding a human-readable message for display.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Validation feedback polish | 0.5 - 1.5 days | Selected. Behavior is now consistent enough to polish presentation without changing validation semantics. |
| Schema-form validation parity check | 0.5 - 1.5 days | Deferred. Useful, but the current user-facing feedback gap is smaller and more visible. |
| Larger product surface | Replan first; likely 2 - 5 days | Deferred. Validation presentation should be cleaned up before broad product-surface expansion. |
| Commit cleanup | 0.25 day | Deferred. The previous React bridge parity slice was already committed locally and the worktree was clean. |

## Boundary

In scope:

- Generated runtime dispatch result message.
- Generated runtime preview browser feedback text.
- Generated React bridge action-intent result message.
- Generated React bridge App feedback wiring.
- Focused smoke/test coverage.

Out of scope:

- New validation DSL.
- Cross-field validation.
- Server-side persistence validation.
- Schema-form/rjsf behavior change.
- Publishing workflow.

## Verification

Planning/report update only.
