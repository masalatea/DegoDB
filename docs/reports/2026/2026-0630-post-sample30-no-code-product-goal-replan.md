# Post-Sample30 No-Code Product Goal Replan

Status: `DONE`

Date: 2026-06-30

## Scope

Completed the docs-only replan after `sample30-no-code-app-local-sync-demo`.

The goal was to choose the next product-facing no-code implementation after the first sync-backed no-code demonstration.

## Decision

Selected `Server-side sync processing follow-up first slice` as the next active implementation item.

Sample30 proved that generated no-code action intent can become a managed operation sync intent, enter the sync outbox, and be processed by the App-local SQLite handler. The smallest useful continuation is to process the same style of managed operation sync item on the server side through generated DBAccess binding, while keeping remote transport, conflict resolution, and retry scheduling out of scope.

## Candidates Considered

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Server-side sync processing follow-up | 1 - 3 days | Selected. It extends the sample-visible sync path using existing generated DBAccess foundations. |
| Sync handoff visibility polish | 0.5 - 2 days | Deferred. Presentation polish should follow a concrete visible gap. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. The operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## First Slice Boundary

In scope:

- One server-side processing proof for an existing managed operation sync intent.
- Generated DBAccess binding discovery / candidate selection.
- A sample-visible server row update.
- Focused PHPUnit or sample pack runtime smoke.

Out of scope:

- Remote transport.
- Conflict resolution.
- Retry scheduling beyond the existing outbox lifecycle.
- Visual builder.
- Native / Flutter output targets.

## Plan Promotion

`docs/current-plans.md` now lists `Server-side sync processing follow-up first slice` as `ACTIVE_NEXT`.
