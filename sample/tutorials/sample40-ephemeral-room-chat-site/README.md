# Sample 40: Ephemeral Room Chat Site

This sample is a small, cut-out-friendly technical example for a URL-based ephemeral chat site.

It is intentionally self-contained under this directory so it can later be copied into a separate repo or deployed as a small standalone site.

## Product idea

- A named room is opened by URL.
- If the room no longer exists internally, opening the same URL recreates it.
- Room names/slugs can remain in a lightweight registry.
- Messages expire after 24 hours.
- Rooms expire after 7 days of inactivity.
- Image bytes are stored in an ephemeral local directory; message state keeps only attachment metadata.
- Production storage is out of scope for the first slice.

## First-slice implementation

The first slice uses only Node.js standard libraries:

- no npm dependencies;
- no database server;
- no WebSocket server;
- no authentication provider;
- no production deployment config.

State is stored in a small JSON file through `EphemeralRoomChatStore`.
Images are stored under the same data directory through `EphemeralImageStore` and are removed when the owning message expires or the inactive room is removed.
That keeps the sample easy to validate and easy to replace with SQLite later.

## Validate

```bash
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-sample.mjs
node sample/tutorials/sample40-ephemeral-room-chat-site/scripts/validate-http-routes.mjs
```

`validate-sample.mjs` checks the store/domain boundary directly.
`validate-http-routes.mjs` starts a temporary loopback server and checks the browser-facing API routes.

## Run locally

```bash
node sample/tutorials/sample40-ephemeral-room-chat-site/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8787/r/general
```

The server is a sample server. It is not a production chat service.
