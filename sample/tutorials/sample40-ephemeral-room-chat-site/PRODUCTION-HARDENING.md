# Sample 40 Production Hardening Checklist

This sample is intentionally small. Before cutting it out into a real ephemeral chat site, treat this checklist as the handoff boundary.

## Already covered by the sample

- URL-named rooms can be opened and recreated.
- Messages expire after 24 hours.
- Inactive rooms expire after 7 days.
- Room registry can remain after room cleanup.
- Image bytes are stored outside message state.
- SQLite is the default local durable store.
- JSON store remains as a fallback for first-slice comparison.
- Browser-facing API routes are validated over loopback HTTP.

## Required before production

### Identity and room access

- Decide whether rooms are public-by-link, passcode-protected, SSO-protected, or invite-only.
- Add authentication if room membership must be private.
- Avoid treating room slug secrecy as the only security boundary.
- Add rate limits for room creation, message posting, and image upload.

### SQLite operation

- Decide whether a single local SQLite file is enough for the expected traffic.
- Enable and document a backup plan before running with real user data.
- Decide whether WAL mode is appropriate for the deployment.
- Add migration versioning before schema changes become frequent.
- Add a recovery story for corrupted or missing SQLite files.
- Keep JSON fallback for development only, not as production persistence.

### Cleanup and retention

- Run cleanup from a scheduled worker or supervised background job.
- Make retention windows configurable but explicit.
- Document whether room registry is permanent, time-limited, or manually removable.
- Add operator tooling for forced room/message/image deletion.

### Image storage

- Enforce image size limits at the HTTP boundary.
- Validate image MIME type and, for production, inspect actual file content.
- Store images outside the web root and serve only through checked attachment routes.
- Decide whether images should be deleted immediately on message expiry or by a cleanup worker.
- Add disk quota monitoring.

### Realtime behavior

- Decide whether polling is enough.
- If realtime is required, add WebSocket or SSE as a separate transport layer.
- Keep the shared-state contract separate from the transport.
- Define reconnect and latest-state fetch behavior.

### Moderation and abuse

- Add spam/rate controls.
- Add room/message reporting or operator removal if public use is expected.
- Add server-side body length validation and user-visible errors.
- Decide whether anonymous use is allowed.

### Deployment

- Put the server behind HTTPS.
- Set explicit host/port/proxy behavior.
- Add structured logs without message/image secrets.
- Add health checks.
- Add graceful shutdown.
- Add monitoring for disk, SQLite errors, request volume, and cleanup failures.

## Still intentionally out of scope

- SSO implementation.
- Public multi-node deployment.
- Redis/pubsub or distributed realtime fanout.
- Moderation UI.
- Native app packaging.
- Long-term media storage.
