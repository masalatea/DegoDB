# Post-Sample29 No-Code Product Goal Replan

Status: `DONE`

Date: 2026-06-30

## Scope

Completed the docs-only replan after `sample29-no-code-support-case-demo`.

The goal was to choose the next product-facing no-code implementation after the second generated Web/runtime domain proof.

## Decision

Selected `App-local sync no-code demonstration first slice` as the next active implementation item.

Sample29 proved that the current generated runtime path can handle a second data-first domain with readonly context fields and editable operation input. It did not expose a blocking generated runtime or metadata gap, so the next useful product story is to connect the generated no-code action path more visibly to the existing App-local persistence and managed operation sync foundations.

## Candidates Considered

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| App-local sync demonstration | 2 - 5 days | Selected. It shows the next product story after the second generated Web/runtime domain proof. |
| Sample29 follow-up domain pressure | 1 - 3 days | Deferred. Useful only if sample29 exposes a concrete gap; none is blocking right now. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. The operator surface still needs clearer scope. |
| Targeted runtime polish from sample29 | 0.5 - 2 days | Deferred until a concrete presentation gap is identified. |

## First Slice Boundary

In scope:

- One small sync-backed no-code demonstration.
- Existing shared contract / App-local persistence / managed operation foundations.
- A sample-visible handoff from generated no-code action intent to managed operation sync intent / outbox or equivalent proof.
- Focused PHPUnit or sample pack runtime smoke.

Out of scope:

- New visual builder.
- Conflict resolution.
- Full offline runtime.
- Remote server transport.
- Operator/admin publishing workflow.
- Native / Flutter output targets.

## Plan Promotion

`docs/current-plans.md` now lists `App-local sync no-code demonstration first slice` as `ACTIVE_NEXT`.
