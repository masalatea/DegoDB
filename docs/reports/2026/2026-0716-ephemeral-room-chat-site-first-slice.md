# 2026-0716 Ephemeral Room Chat Site First Slice

## Summary

Added `sample40-ephemeral-room-chat-site` as a cut-out-friendly technical example for a URL-based ephemeral chat site.

The sample is intentionally self-contained under its own directory so it can later be copied into a separate repo or deployed as a small standalone site.

## Product shape

- A named room is opened by URL.
- If the room no longer exists internally, opening the same URL recreates it.
- Room names/slugs remain in a lightweight registry.
- Messages expire after 24 hours.
- Image bytes are stored in an ephemeral local directory while message state keeps metadata only.
- Rooms expire after 7 inactive days.

## First-slice implementation

The first slice uses only Node.js standard libraries:

- no npm dependencies;
- no database server;
- no WebSocket server;
- no authentication provider;
- no production deployment config.

State is stored in SQLite by default through `SqliteRoomChatStore`.
The first-slice JSON file store remains available through `SAMPLE40_STORE_DRIVER=json`.
Image bytes are stored through `EphemeralImageStore`.
This keeps the sample local-durable without adding npm dependencies.

## Validation

```bash
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-sample.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-http-routes.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-sqlite-store.mjs
```

The validator checks:

- room slug normalization;
- same URL opens/reopens a room;
- image-only messages can be posted;
- image bytes stay outside message state;
- unsupported image mime types are rejected;
- expired image attachments are removed with expired messages;
- SQLite store contract matches the JSON first-slice behavior;
- message expiry after 24 hours;
- inactive room expiry after 7 days;
- room registry remains after room cleanup;
- empty message rejection;
- dependency-free / cut-out-friendly boundary.

The HTTP route validator starts a temporary `127.0.0.1` server and checks:

- `GET /r/:roomSlug`;
- `POST /api/rooms/:roomSlug`;
- `GET /api/rooms/:roomSlug/messages`;
- `POST /api/rooms/:roomSlug/messages`;
- `POST /api/rooms/:roomSlug/images`;
- `GET /attachments/:storageKey`;
- `POST /api/cleanup`.

## Boundary

This is not a production chat service.

It does not:

- provide authentication;
- provide real-time WebSocket/SSE sync;
- provide production image storage;
- implement moderation;
- implement production deployment or cleanup workers.

## Next decision

Choose whether to:

- add richer image attachment UI validation;
- add real-time WebSocket/SSE sync;
- add production-hardening checklist;
- or checkpoint/PR before widening scope.
