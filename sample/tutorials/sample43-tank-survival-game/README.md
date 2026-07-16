# Sample 43: Tank Survival Game

This sample is a cut-out-friendly multiplayer tank survival game.

It follows the sample36〜38 shared-state handoff direction and the sample42 game sample direction: Mtool-shaped input packets, URL-named rooms, loopback Node.js server, and room-scoped SSE updates.

## Product idea

- Open a room by URL.
- Join as a tank.
- Any number of tanks can join.
- Tanks can join even while a match is already in progress.
- Tanks can turn and drive in any direction.
- Obstacles block movement.
- Bullets fire forward from the tank.
- Hits reduce HP.
- HP zero marks a tank as exploded.
- Last alive tank wins.
- If a room has no activity for 7 days, its game state resets while the URL room registry remains.

## First-slice implementation

The first slice uses only Node.js and browser standard APIs:

- no npm dependencies;
- no database;
- no authentication provider;
- no production deployment config.

The server is intentionally simple and authoritative for the validated actions:

- player join;
- turn command;
- omnidirectional drive command;
- forward fire command;
- obstacle collision;
- bullet hit and HP reduction;
- explosion and winner state;
- browser-generated fire / explosion sounds;
- 7-day inactive room reset;
- SSE `tank.updated` event.

## Mtool handoff structure

This is not just an ad-hoc game demo. The sample includes a Mtool-shaped game handoff packet:

- `reference/tank-survival-game-input.sample.json`

The packet sits on top of the shared-state sync packet boundary from sample36〜38:

- Mtool emits `sync-server-input.json`;
- Mtool emits `sync-client-input.json`;
- the tank game packet maps those shared-state contracts to the `tank_game` state key, `drive` / `turn` / `fire` commands, and room-scoped `tank.updated` events;
- the runtime keeps production game-server ownership outside Mtool.

## Validate

```bash
node sample/tutorials/sample43-tank-survival-game/scripts/validate-sample.mjs
node sample/tutorials/sample43-tank-survival-game/scripts/validate-mtool-artifact-linkage.mjs
```

## Run locally

```bash
node sample/tutorials/sample43-tank-survival-game/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8790/r/general
```

This is a sample game, not a production realtime game server.

The sound effects are generated with the browser Web Audio API, so the sample does not need audio files. Browsers require a click or keypress before sound can start.
