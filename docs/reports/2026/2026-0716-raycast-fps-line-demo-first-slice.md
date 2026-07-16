# 2026-0716 Raycast FPS Line Demo First Slice

## Summary

Added `sample44-raycast-fps-line-demo` as a cut-out-friendly classic raycasting FPS line demo.

The sample continues the sample36〜38 shared-state handoff direction and the sample42 / sample43 game sample direction: Mtool-shaped packets, URL rooms, loopback Node.js server, and room-scoped SSE updates.

## Product shape

- Open a room by URL.
- Join as a player.
- Render pseudo-3D first-person view with canvas lines only.
- Rotate by small 5-degree steps rather than 90-degree-only turns.
- Move forward/backward.
- Block movement with grid-map walls.
- Shoot forward along the current view angle.
- Reduce HP on hit.
- Mark HP-zero players as defeated.
- Declare last alive player as winner.
- Reset the room state after 7 inactive days while preserving the room registry.

## Boundary

This is not a production FPS server.

It does not include:

- WebGL;
- textures or model assets;
- authentication;
- matchmaking;
- authoritative production tick loop;
- anti-cheat;
- prediction / reconciliation;
- production persistence;
- public deployment.

## Mtool artifact linkage

The sample sits on the tool structure rather than remaining a standalone technical demo.

Added:

- `reference/raycast-fps-line-input.sample.json`
- `scripts/validate-mtool-artifact-linkage.mjs`

The linkage validator:

- emits Mtool `sync-server-input.json` and `sync-client-input.json` to a temporary directory;
- confirms both packets use the `SAMPLE44` project key;
- confirms the raycast FPS packet references the shared-state contracts;
- checks the FPS contract against runtime `FPS_RULES`;
- checks line-only rendering, no WebGL, no textures, and 5-degree angle granularity;
- confirms the packet does not claim production game-server generation, matchmaking, anti-cheat, authoritative production tick loop, or token storage.

## Validation

```bash
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-sample.mjs
node sample/tutorials/sample44-raycast-fps-line-demo/scripts/validate-mtool-artifact-linkage.mjs
```

The validator checks:

- required files;
- no npm dependency;
- room page and room slug injection;
- multi-player join;
- fine-grained angle turning;
- wall collision;
- raycast wall hit;
- forward-angle shot and HP reduction;
- transient shot line / muzzle flash feedback;
- defeat and last-alive winner;
- 7-day inactive reset;
- same-room SSE `fps.updated`;
- line-only canvas rendering;
- Web Audio API sound hooks without external assets.
