# Post-Partial-Update Merge No-Code Product Goal Replan

Status: `DONE`

Date: 2026-06-30

## Context

Reusable partial-update server merge policy is complete for the first slice.

Sample30 now proves both sides of the sync-backed no-code path:

- Generated no-code action intent becomes a managed operation sync intent and is processed by the App-local SQLite handler.
- A second managed operation sync outbox item is processed by generated server DBAccess.
- Partial no-code update input can be merged with the existing server row before generated full-row update execution.

## Decision

Select Sync handoff visibility polish as the next product-facing no-code implementation slice.

## Rationale

The data behavior is now strong enough for a small presentation-oriented slice. The next product risk is that the generated/runtime artifact does not yet make the App-local and server processing states easy to see.

This is smaller and clearer than starting operator/admin workflow, and less foundational than adding another sync edge behavior immediately.

## Candidate Review

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Sync handoff visibility polish | 0.5 - 2 days | Selected. It makes the existing App-local and server processing proof easier to understand as a no-code product story. |
| Operator/admin no-code workflow | 1 - 3 days | Deferred. Operator scope still needs a clearer surface. |
| Additional sync behavior pressure | 1 - 3 days | Deferred. Retry/error or merge edge behavior can follow after the current handoff is visible. |
| Mtool implementation namespace cleanup | 1 - 3 days | Parked until a concrete helper cluster is selected. |

## First Slice Boundary

In scope:

- sample30-visible sync handoff state.
- Existing managed operation outbox lifecycle.
- Existing App-local and server-side handlers.
- Generated/runtime artifact presentation or checker-visible state.
- Focused smoke coverage and docs.

Out of scope:

- Remote transport.
- Conflict resolution.
- Retry scheduling changes.
- New operator/admin workflow.
- Visual builder.
- Native / Flutter output targets.

## Next

Start Sync handoff visibility polish first slice.
