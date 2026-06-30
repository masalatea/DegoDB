# Post-Server-Side Sync No-Code Product Goal Replan

Status: `DONE`

Date: 2026-06-30

## Scope

Completed the docs-only replan after the sample30 server-side sync processing follow-up.

The goal was to choose the next product-facing no-code implementation after sample30 proved both App-local outbox handling and generated server DBAccess processing.

## Decision

Selected `Reusable partial-update server merge policy first slice` as the next active implementation item.

Sample30 now proves that a generated no-code action can enqueue a managed operation sync intent, process it through the App-local handler, and process another outbox item through generated server DBAccess. The follow-up also exposed the next concrete gap: no-code update actions naturally carry partial editable input, while the generated server DBAccess update method currently expects a full row-shaped DataClass payload.

The next smallest useful slice is a reusable read/merge/write policy for generated server DBAccess update operations. It should remove sample30's sample-specific full-row payload completion while keeping conflict resolution, remote transport, retry scheduling, create/delete semantics, and multi-row merge out of scope.

## Candidates Considered

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Reusable partial-update / server merge policy | 1 - 3 days | Selected. It addresses the most concrete behavior gap exposed by sample30's server-side processing proof. |
| Sync handoff visibility polish | 0.5 - 2 days | Deferred. Presentation polish should follow the data behavior fix. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. The operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## First Slice Boundary

In scope:

- Update operation only.
- Existing generated server DBAccess read/update methods.
- One row keyed by the sync intent.
- Deterministic merge of existing row data with partial input.
- Sample30 smoke coverage.

Out of scope:

- Conflict resolution.
- Remote transport.
- Retry scheduling.
- Multi-row merge.
- Create/delete semantics.
- Visual builder.
- Native / Flutter output targets.

## Plan Promotion

`docs/current-plans.md` now lists `Reusable partial-update server merge policy first slice` as `ACTIVE_NEXT`.
