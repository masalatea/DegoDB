# Post-Runtime Submit Detail Path No-Code Product Goal Replan

Status: `DONE`

Date: 2026-07-04

## Decision

After generated runtime submit feedback began showing the operator sync outbox detail path, the next smallest slice is to improve the linked detail page's read-only processing handoff.

The current detail page is mostly oriented around failed-item retry. Runtime submit usually lands on a `pending` item first, so the operator page should explain that the item is queued for the existing processor without processing it inline.

## Scope

- Keep the sync outbox detail page read-only for normal inspection.
- Add a processing handoff summary for pending, running, done, failed, and unknown statuses.
- Keep retry mutation limited to eligible failed items.
- Avoid scheduler, transport, conflict resolution, or generated runtime retry behavior.

## Next Work Unit

#139 Operator sync outbox detail processing handoff.
