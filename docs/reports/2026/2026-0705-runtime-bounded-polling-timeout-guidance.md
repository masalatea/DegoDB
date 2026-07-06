# Runtime Bounded Polling Timeout Guidance

Date: 2026-07-05

## Status

Done locally. Not pushed.

## Context

#191 capped live outbox polling at three attempts. The internal state stopped at `timeout`, but the user-facing flow still read like a generic status check. This slice makes the stopped state explicit.

## Implemented Slice

- Added a user-facing timeout message after capped polling stops.
- Updated runtime flow text to say the item is still queued after bounded checks.
- Points users to Refresh preview or the outbox detail as the next step.
- Updated browser smoke expectations for the timeout flow and guidance text.

## Boundary Notes

- No inline processing.
- No retry mutation.
- No change to the polling cap.
- Terminal done / review-needed mappings remain unchanged.

## Next Candidates

- Add demo-processing smoke for the terminal `done` flow state.
- Add a status-only user-facing route if admin outbox detail links should be hidden from runtime users.
- Add retry mutation planning only after the read-only tracking experience is settled.

