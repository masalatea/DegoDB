# Sample 49: AI Plugin Maze Race

This sample is a Node-backed browser maze race game.

Four racers start from the four corners. The goal is in the center of a large scrolling maze. Human players can join the same room from multiple browser windows. Empty slots remain AI racers.

## Rule

- The facing arrow rotates at 90 degrees per second while Space is not pressed.
- Hold Space to stop rotation and move straight in the current direction.
- While Space is held, the racer does not rotate.
- Reach the center goal before the other racers.

Touch / mouse hold also works as Space for quick mobile testing.

## First-slice implementation

The sample uses browser standard APIs plus a small local Node.js room server:

- no npm dependencies;
- no database;
- no external image or audio files;
- no game engine project.

The maze is deterministic per room. Opponents are either human racers in the same room or simple local server-side AI racers that use the same rotate-or-drive movement constraint.

## Validate

```bash
node sample/tutorials/sample49-ai-plugin-maze-race/scripts/validate-sample.mjs
```

## Run locally

Start:

```bash
node sample/tutorials/sample49-ai-plugin-maze-race/src/server.mjs
```

Open the same room in multiple browsers:

```text
http://127.0.0.1:8791/r/general
```

This is a local sample multiplayer proof, not a production multiplayer service, matchmaking system, anti-cheat implementation, or runtime plugin.
