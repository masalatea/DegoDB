# Runtime Submit Live Outbox Status UI

Date: 2026-07-05

## Status

Done locally. Not pushed.

## Context

#189 added a narrow read-only sync outbox status JSON route. The next product-facing step was to let generated runtime users see that status after submit, instead of only seeing a detail path plus manual refresh guidance.

## Implemented Slice

- Generated runtime now derives `{detail_path}.json` after an accepted server submit.
- Runtime performs a same-origin GET against that status JSON route.
- Runtime feedback and flow display the live outbox status message.
- Pending/running statuses remain tracking states; done becomes complete; failed becomes review-needed.
- Browser smoke now observes POST submit fetch and GET status fetch separately.

## Boundary Notes

- No inline processing was added.
- No retry mutation was added.
- The status check uses the authenticated same-origin route added in #189.
- The existing manual refresh affordance remains available after submit and status check.

## Next Candidates

- Add bounded repeated polling with a timeout/backoff instead of a single live status check.
- Add a done-state smoke using the synchronous demo processing gate.
- Decide whether public users should see an admin outbox detail link, a user-facing status-only link, or both.

