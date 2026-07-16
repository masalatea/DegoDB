# Sample 42: Room Shooter Game

This sample is a small, cut-out-friendly two-player shooter game.

It follows the sample36〜38 shared-state handoff direction and the sample40 / sample41 room direction: Mtool-shaped input packets, URL-named rooms, loopback Node.js server, and room-scoped SSE updates.

## Product idea

- Open a room by URL.
- Join as a player.
- Move in a small arena.
- Shoot projectiles.
- Hit the opponent and reduce HP.
- Receive room-scoped game state updates.

## First-slice implementation

The first slice uses only Node.js and browser standard APIs:

- no npm dependencies;
- no database;
- no authentication provider;
- no production deployment config.

The server is intentionally simple and authoritative for the validated actions:

- player join;
- movement command;
- shoot command;
- projectile hit resolution;
- SSE `game.updated` event.

## Mtool handoff structure

This is not just an ad-hoc game demo. The sample includes a Mtool-shaped game handoff packet:

- `reference/room-shooter-game-input.sample.json`

The packet sits on top of the shared-state sync packet boundary from sample36〜38:

- Mtool emits `sync-server-input.json`;
- Mtool emits `sync-client-input.json`;
- the game packet maps those shared-state contracts to the `game` state key, `move` / `shoot` commands, and room-scoped `game.updated` events;
- the runtime keeps production game-server ownership outside Mtool.

## Validate

```bash
node sample/tutorials/sample42-room-shooter-game/scripts/validate-sample.mjs
node sample/tutorials/sample42-room-shooter-game/scripts/validate-mtool-artifact-linkage.mjs
```

## Run locally

```bash
node sample/tutorials/sample42-room-shooter-game/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8790/r/general
```

This is a sample game, not a production realtime game server.
