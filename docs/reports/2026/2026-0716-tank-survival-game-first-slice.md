# 2026-0716 Tank Survival Game First Slice

## Summary

Added `sample43-tank-survival-game` as a cut-out-friendly multiplayer tank survival sample.

The sample continues the sample36〜38 shared-state handoff direction and the sample42 game sample direction: Mtool-shaped packets, URL rooms, loopback Node.js server, and room-scoped SSE updates.

## Product shape

- Open a room by URL.
- Join as a tank.
- Allow any number of tanks.
- Allow mid-game joins.
- Move in any direction by turning and driving.
- Block movement with obstacles.
- Fire bullets only forward.
- Reduce HP on hit.
- Mark HP-zero tanks as exploded.
- Play simple browser-generated fire / explosion sounds.
- Declare last alive tank as winner.
- Reset the room state after 7 inactive days while preserving the room registry.

## Boundary

This is not a production game server.

It does not include:

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

- `reference/tank-survival-game-input.sample.json`
- `scripts/validate-mtool-artifact-linkage.mjs`

The linkage validator:

- emits Mtool `sync-server-input.json` and `sync-client-input.json` to a temporary directory;
- confirms both packets use the `SAMPLE43` project key;
- confirms the tank game packet references the shared-state contracts;
- checks the tank contract against runtime `TANK_RULES`;
- checks unlimited join and mid-game join boundaries;
- confirms the packet does not claim production game-server generation, matchmaking, anti-cheat, authoritative production tick loop, or token storage.

## Validation

```bash
node sample/tutorials/sample43-tank-survival-game/scripts/validate-sample.mjs
node sample/tutorials/sample43-tank-survival-game/scripts/validate-mtool-artifact-linkage.mjs
```

The validator checks:

- required files;
- no npm dependency;
- room page and room slug injection;
- multi-player join;
- mid-game join;
- omnidirectional movement;
- obstacle collision;
- forward bullet hit and HP reduction;
- explosion and last-alive winner;
- browser-generated fire / explosion sounds without external assets;
- 7-day inactive reset;
- same-room SSE `tank.updated`;
- keyboard/SSE browser hooks.
