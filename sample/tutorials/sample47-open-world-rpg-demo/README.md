# Sample 47: Open World RPG Demo

This sample is a cut-out-friendly Node.js communication game in an old action RPG style.

It follows the sample36〜38 shared-state handoff direction and the sample42〜44 game sample direction: Mtool-shaped input packets, URL-named rooms, loopback Node.js server, server tick, room-scoped SSE updates, and browser-only UI.

## Product idea

- Open a room by URL.
- Join as a hero.
- Move around a wider 2D map.
- Trees, rocks, and a pond act as simple blocked terrain.
- Enemies spawn a little away from players.
- Enemies move slowly and weakly attack nearby players.
- Space swings a short sword.
- Defeating enemies grants EXP and Gold.
- Player HP slowly regenerates while idle.
- Multiple players can exist in the same room, but player-vs-player combat is intentionally disabled.

## First-slice implementation

The first slice uses only Node.js and browser standard APIs:

- no npm dependencies;
- no database;
- no authentication provider;
- no production deployment config.

The server is intentionally simple and authoritative for the validated actions:

- player join;
- movement command;
- terrain collision;
- short sword attack command;
- enemy spawn and movement tick;
- enemy weak attack;
- idle HP regeneration;
- EXP / Gold reward;
- SSE `rpg.updated` event.

## Mtool handoff structure

This is not just an ad-hoc game demo. The sample includes a Mtool-shaped RPG handoff packet:

- `reference/open-world-rpg-input.sample.json`

The packet sits on top of the shared-state sync packet boundary from sample36〜38:

- Mtool emits `sync-server-input.json`;
- Mtool emits `sync-client-input.json`;
- the RPG packet maps those shared-state contracts to the `open_world_rpg` state key, `move` / `attack` commands, and room-scoped `rpg.updated` events;
- the runtime keeps production game-server ownership outside Mtool.

## Validate

```bash
node sample/tutorials/sample47-open-world-rpg-demo/scripts/validate-sample.mjs
node sample/tutorials/sample47-open-world-rpg-demo/scripts/validate-mtool-artifact-linkage.mjs
```

## Run locally

```bash
node sample/tutorials/sample47-open-world-rpg-demo/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8790/r/general
```

This is a sample RPG, not a production realtime game server.
