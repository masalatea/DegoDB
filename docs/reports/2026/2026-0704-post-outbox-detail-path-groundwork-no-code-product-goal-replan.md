# Post Outbox Detail Path Groundwork No-Code Product Goal Replan

Status: `DONE`

Push: not performed.

## Decision

After exposing the accepted operator sync outbox detail path as structured DOM state, the next implementation slice is a copy affordance.

Chosen next work:

- Runtime outbox detail path copy affordance.

## Why This Next

The current runtime success message already prints the operator detail path, and #147 made that path available without parsing display text.

A copy control is smaller and less disruptive than full anchor rendering. It helps tryout users carry the operator path into another tab, notes, or debugging while keeping generated runtime text simple.

## Deferred Candidates

- Render the outbox path as an actual link.
- Add live polling or result refresh automation.
- Add synchronous endpoint processing for local/demo workflows.
- Add runtime retry mutation for failed outbox items.
- Prove the same flow in another no-code sample.
- Commit stack review / push cleanup.

## Estimate

0.25 day for replan, then 0.25 - 0.5 day for copy UI, JS, smoke assertion, focused test update, and verification.

