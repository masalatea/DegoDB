# Runtime Live Outbox Bounded Polling

Date: 2026-07-05

## Status

Done locally. Not pushed.

## Context

#190 added a first live status check after generated runtime submit. That proved the read-only status JSON route can be consumed from the runtime, but a single check still leaves users with a very short snapshot. The next safe slice was bounded polling, without adding inline processing or retry mutation.

## Implemented Slice

- Runtime now repeats outbox status checks for queued / processing states.
- Polling is capped at three attempts.
- Runtime status and feedback text update in place instead of appending duplicate live-check messages.
- Pending/running after the final attempt stops with `timeout`.
- Browser smoke now verifies the status JSON GET count and timeout state for the async pending case.

## Boundary Notes

- No infinite polling loop.
- No inline processing.
- No retry mutation.
- Terminal done / review-needed flow states remain supported by the #190 mapping.
- Manual refresh remains the explicit result reload action.

## Next Candidates

- Add demo-processing smoke for the terminal `done` flow state.
- Add user-facing copy that distinguishes "still queued" from "timed out while waiting".
- Revisit whether the admin outbox detail link should have a user-facing status-only companion.

