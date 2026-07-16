# Sample 41 Production Hardening Checklist

This sample is intentionally small. Before cutting it out into a real shared whiteboard, treat this checklist as the handoff boundary.

## Already covered by the sample

- Touch / mouse / pen drawing through Pointer Events.
- Pen color and size controls.
- Text placement.
- Eraser, undo, clear, and PNG export.
- URL-named rooms.
- Operation append and latest board fetch.
- Stale revision conflict rejection.
- Same-room SSE `board.updated` notification.
- No per-operation TTL.
- Whole-board clear after 7 inactive days.
- Room name / URL registry preservation.

## Required before production

### Identity and room access

- Decide whether boards are public-by-link, passcode-protected, SSO-protected, or invite-only.
- Avoid treating the room slug as the only security boundary.
- Add authentication if board membership must be private.
- Define whether anonymous drawing is allowed.
- Add rate limits for room creation and operation append.

### Persistence

- Replace the JSON store with a production-suitable store before real user data.
- Add schema/version migration if using SQLite or another DB.
- Define backup and restore behavior.
- Define maximum board size and operation history limits.
- Decide whether old operations are compacted into snapshots.

### Retention and cleanup

- Keep the no per-operation TTL rule.
- Clear the whole board after 7 inactive days, or make that retention window explicit and configurable.
- Preserve room registry only if that is acceptable for privacy.
- Add operator tooling for forced room clear/delete.
- Run cleanup from a scheduled worker or supervised background process.

### Realtime transport

- SSE is sufficient for a first single-process sample.
- For production, decide whether SSE remains enough or WebSocket is required.
- Document reconnect behavior.
- Add latest-board fetch after reconnect.
- Define event replay expectations; this sample does not guarantee replay.
- For multi-process deployment, add shared pub/sub or route room traffic to the owning process.

### Drawing operation safety

- Validate operation type, size, point count, coordinates, text length, and color server-side.
- Reject extremely large strokes or abusive operation payloads.
- Consider simplifying strokes before storage.
- Consider snapshotting to avoid replaying an unbounded operation list.
- Keep eraser behavior explicit: this sample models eraser as a white stroke, not vector deletion.

### Abuse and moderation

- Add spam/rate controls.
- Add room reporting or operator removal if public use is expected.
- Decide whether board history is auditable.
- Avoid logging full operation payloads if drawings may contain sensitive content.

### Deployment

- Serve behind HTTPS.
- Set explicit host, port, and proxy behavior.
- Add health checks.
- Add structured logs without sensitive drawing payloads.
- Add graceful shutdown for open SSE connections.
- Monitor disk, memory, request volume, SSE subscriber count, and cleanup failures.

## Still intentionally out of scope

- SSO implementation.
- Public multi-node deployment.
- Redis/pubsub.
- Guaranteed offline replay.
- Moderation UI.
- Native app packaging.
- Long-term media/image storage.
