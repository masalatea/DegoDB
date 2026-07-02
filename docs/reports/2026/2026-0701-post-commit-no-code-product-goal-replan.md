# 2026-0701 Post-Commit No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Deeper runtime capability** as the next mainline direction, with **Generated required validation enforcement first slice** as the next implementation item.

The no-code minimum milestone is now committed locally and the worktree is clean. The next product-facing gap is not another handoff document or operator summary. It is runtime behavior: generated form fields already expose required/readonly metadata as hints, but the generated runtime still needs the smallest deterministic validation enforcement path before dispatching an action intent.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Larger product surface | Replan first; likely 2 - 5 days for a narrow first slice | Deferred. Publishing/approval/app packaging should follow after generated runtime input behavior is stronger. |
| Deeper runtime capability | Replan first; likely 2 - 5 days for a narrow first slice | Selected. Required validation is a concrete, bounded gap that builds directly on existing metadata hints. |
| Operational hardening | Replan first; likely 1 - 3 days after scope selection | Deferred. Useful, but less product-visible than closing the basic validation behavior gap. |
| Pause for review / push decision | 0.25 day | Deferred. The user asked to continue and push remains disabled. |

## Next Implementation Slice

Generated required validation enforcement first slice.

Initial boundary:

- Enforce existing required metadata in generated runtime preview before emitting an action intent.
- Keep server/persistence behavior unchanged.
- Keep validation deterministic and browser-local for this slice.
- Cover sample28 generated runtime and browser smoke expectations.

Out of scope:

- Full validation DSL.
- Cross-field validation.
- Server-side validation behavior.
- Visual builder.
- Publishing workflow.
- Remote sync transport.

## Verification

Planning/report update only.
