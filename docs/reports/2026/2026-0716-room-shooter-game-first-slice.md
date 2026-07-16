# 2026-0716 Room Shooter Game First Slice

## Summary

Added `sample42-room-shooter-game` as a cut-out-friendly two-player shooter sample.

The sample continues the sample36〜38 shared-state handoff direction and the sample40 / sample41 room direction: Mtool-shaped packets, URL rooms, loopback Node.js server, and room-scoped SSE updates.

## Product shape

- Open a room by URL.
- Join as one of two players.
- Move in an arena.
- Shoot projectiles.
- Hit the opponent and reduce HP.
- Receive same-room `game.updated` events.

## Boundary

This is not a production game server.

It does not include:

- matchmaking;
- authentication;
- authoritative real-time tick loop;
- anti-cheat;
- prediction / reconciliation;
- production persistence;
- public deployment.

## Mtool artifact linkage correction

The sample must sit on the tool structure, not remain a standalone technical demo.

Added:

- `reference/room-shooter-game-input.sample.json`
- `scripts/validate-mtool-artifact-linkage.mjs`

The linkage validator:

- emits Mtool `sync-server-input.json` and `sync-client-input.json` to a temporary directory;
- confirms both packets use the `SAMPLE42` project key;
- confirms the game packet references the shared-state contracts;
- checks the game contract against runtime `GAME_RULES`;
- checks two-player room capacity and third-player rejection;
- confirms the packet does not claim production game-server generation, matchmaking, anti-cheat, or token storage.

## Validation

```bash
node sample/tutorials/sample42-room-shooter-game/scripts/validate-sample.mjs
node sample/tutorials/sample42-room-shooter-game/scripts/validate-mtool-artifact-linkage.mjs
```

The validator checks:

- required files;
- no npm dependency;
- room page and room slug injection;
- two-player join;
- third-player rejection;
- move command;
- shoot command and HP reduction;
- latest state fetch;
- same-room SSE `game.updated`;
- keyboard/SSE browser hooks.

The Mtool linkage validator additionally checks:

- Mtool server/client packet emission;
- game contract and runtime rule consistency;
- room-scoped event boundary;
- secret-free / no-token packet boundary.
