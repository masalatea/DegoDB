# Post Result Refresh Button No-Code Product Goal Replan

Status: `DONE`

Push: not performed.

## Decision

After adding the manual `Refresh preview` control, the next implementation slice is result refresh guidance wording.

Chosen next work:

- Runtime result refresh guidance wording first slice.

## Why This Next

The runtime already enables a manual refresh button after successful submit. The remaining product gap is wording clarity: users need to distinguish processing the sync outbox item from reloading the generated preview.

This is smaller than live polling or synchronous processing and keeps the accepted outbox-based boundary intact.

## Deferred Candidates

- Live result refresh / polling after submit.
- Synchronous endpoint processing for local/demo workflows.
- Runtime retry mutation for failed outbox items.
- Another no-code sample using the same submit/outbox/operator/refresh handoff.
- Commit cleanup / push, only with explicit user direction.

## Estimate

0.25 day for replan, then 0.25 day for wording/status UI, smoke assertion, focused test update, and verification.
