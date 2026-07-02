# Post-schema-form consumer notes no-code product goal replan

## Status

`DONE`

## Decision

Select `Generated runtime visual polish follow-up` as the next small no-code product-facing implementation.

## Context

The React bridge and schema-form comparison lanes now have build/browser/runtime smoke coverage plus generated consumer notes. The next useful product-facing step is to return to the generated runtime preview and improve visible scanability without changing runtime behavior.

## Candidates

| Candidate | First slice estimate | Decision | Reason |
| --- | --- | --- | --- |
| Generated runtime visual polish follow-up | 0.5 - 2 days | Selected | Adapter confidence and handoff documentation are now stable enough to return to the generated runtime surface. A small scanability improvement is low risk and directly user-visible. |
| React bridge/schema-form artifact parity notes | 0.5 - 1 day | Deferred | Useful if consumer handoff remains the priority, but recent notes already improved that boundary. |
| Retry audit trail | 0.5 - 2 days | Deferred | Useful for operator accountability, but not the next no-code runtime presentation gap. |

## Boundary

In scope:

- small generated runtime preview scanability polish;
- DOM attributes for focused smoke coverage;
- docs and plan updates.

Out of scope:

- new visual builder;
- changing action execution semantics;
- replacing the custom React bridge;
- JSON Forms / rjsf product runtime adoption;
- transport, sync, or conflict resolution behavior.

## Verification

Planning/report update only. The selected implementation should use focused runtime smoke and run full tests if shared runtime behavior changes.
