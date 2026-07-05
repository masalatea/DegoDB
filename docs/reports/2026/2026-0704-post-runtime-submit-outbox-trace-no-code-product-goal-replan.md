# Post-Runtime Submit Outbox Trace No-Code Product Goal Replan

Status: `DONE`

Date: 2026-07-04

## Decision

After runtime submit feedback exposed the accepted outbox item id and operation key, the next smallest product-facing slice is to show the existing operator sync outbox detail path when a dedupe key is available.

This keeps generated runtime submit asynchronous and read-only after enqueue. It gives tryout users a concrete follow-up path without adding live data refresh, synchronous processing, or retry mutation to the generated runtime.

## Scope

- Use the existing project-scoped sync outbox detail route shape.
- Build the detail path from project key and returned outbox `dedupe_key`.
- Show the path in generated runtime submit status and action feedback.
- Verify the path through sample28 real-submit smoke coverage.

## Not In This Slice

- Rendering an admin-auth navigation component inside the generated runtime.
- Processing the outbox item inline.
- Retry/requeue mutation from generated runtime.
- Live business-row refresh after processing.

## Next Work Unit

#137 Runtime submit operator outbox detail path feedback.
