# Sample 41: Simple Whiteboard

This sample is a small, cut-out-friendly whiteboard site.

It is intentionally dependency-free and static-first so it can be copied into another project or later connected to shared-state / room sync.

## Product idea

- Draw freehand strokes on a canvas.
- Draw with a finger on phones/tablets or with a mouse/trackpad on desktop.
- Choose pen color.
- Choose pen size.
- Add text to the board.
- Erase with an eraser tool.
- Clear the board.
- Export the board as a PNG.

## First-slice implementation

The first slice uses only browser APIs:

- no npm dependencies;
- no server;
- no storage backend;
- no realtime sync;
- no authentication provider.

Drawing uses Pointer Events, so the same implementation handles touch, mouse, and pen input.

The implementation keeps the drawing model in JavaScript as serializable operations:

- `stroke`
- `text`

This keeps a path open for a future shared-state sample where operations can be synced through room state.

## Planned room-sync retention rule

The room-sync version should not delete individual strokes or text entries after 24 hours.
Unlike chat messages, whiteboard operations depend on each other visually.

The intended rule is:

- keep board operations together while the room is active;
- clear the entire board after 7 inactive days;
- preserve the room name / URL registry;
- reopen the same URL as an empty board after clear.

## Validate

```bash
node sample/tutorials/sample41-simple-whiteboard/scripts/validate-sample.mjs
```

## Run locally

Open the HTML file directly:

```text
sample/tutorials/sample41-simple-whiteboard/public/index.html
```

Or serve it with any static file server.

This is a sample whiteboard, not a production collaborative drawing app.
