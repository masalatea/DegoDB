# Post-Sample28 Endpoint Success No-Code Product Goal Replan

Status: `DONE`

Date: 2026-07-04

## Decision

After sample28 current/alias execution endpoints accepted authenticated direct POSTs, the next smallest product-facing slice is an authenticated browser real-submit smoke.

This validates the actual user path more closely than the direct endpoint smoke: a browser logs in, opens the current or alias runtime preview, clicks the generated `Submit to server` control, and observes the server response through the existing UI state transitions.

## Scope

- Keep the generated runtime UI behavior unchanged.
- Reuse the existing current/alias execution endpoints.
- Extend the Playwright smoke to support a real fetch mode.
- Verify the real response still produces a pending managed-operation sync intent.

## Not In This Slice

- UI result refresh from the outbox item.
- Processing the pending outbox item.
- Direct mutation of sample28 business rows.
- New operator sync outbox UI behavior.

## Next Work Unit

#129 Sample28 authenticated browser real-submit smoke.

