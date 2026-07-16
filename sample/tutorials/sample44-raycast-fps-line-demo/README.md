# Sample 44: Raycast FPS Line Demo

This sample is a cut-out-friendly classic raycasting FPS line demo.

It follows the sample36〜38 shared-state handoff direction and the sample42 / sample43 game sample direction: Mtool-shaped input packets, URL-named rooms, loopback Node.js server, and room-scoped SSE updates.

## Product idea

- Open a room by URL.
- Join as a player.
- Render a pseudo-3D first-person view using canvas lines only.
- Move forward / backward.
- Rotate by small 5-degree steps, not just 90-degree turns.
- Collide with grid-map walls.
- Shoot forward along the current view angle.
- Reduce HP on hit.
- Mark HP-zero players as defeated.
- Last alive player wins.
- If a room has no activity for 7 days, its game state resets while the URL room registry remains.

## First-slice implementation

The first slice uses only Node.js and browser standard APIs:

- no npm dependencies;
- no database;
- no WebGL;
- no texture or model assets;
- no authentication provider;
- no production deployment config.

The server is intentionally simple and authoritative for the validated actions:

- player join;
- fine-grained turn command;
- forward/backward move command;
- wall collision;
- forward-angle shoot command;
- HP reduction and defeat state;
- 7-day inactive room reset;
- SSE `fps.updated` event.

## Mtool handoff structure

This is not just an ad-hoc FPS demo. The sample includes a Mtool-shaped game handoff packet:

- `reference/raycast-fps-line-input.sample.json`

The packet sits on top of the shared-state sync packet boundary from sample36〜38:

- Mtool emits `sync-server-input.json`;
- Mtool emits `sync-client-input.json`;
- the FPS packet maps those shared-state contracts to the `raycast_fps` state key, `move` / `turn` / `shoot` commands, and room-scoped `fps.updated` events;
- the runtime keeps production game-server ownership outside Mtool.

## Validate

```bash
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-sample.mjs
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-mtool-artifact-linkage.mjs
```

## Run locally

```bash
node sample/tutorials/sample44-raycast-fps-line-demo/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8790/r/general
```

The sound effects are generated with the browser Web Audio API, so the sample does not need audio files. Browsers require a click or keypress before sound can start.

This is a sample game, not a production realtime FPS server.
