# Maze race game sample / maze race game sample

English companion:
This report records a static competitive maze race sample.

## Summary / 要約

Added `sample49-ai-plugin-maze-race`, then extended it to a local Node-backed room race.

The sample implements:

- four racers starting from the four corners;
- a large 35 x 35 maze with camera scrolling;
- center goal race;
- player control by Space hold only;
- arrow/facing rotation at 90 degrees per second while Space is released;
- no rotation while Space is held;
- local AI opponents using the same rotate-or-drive constraint;
- same-room human players through HTTP commands and SSE updates.

## Boundary / 境界

This sample uses a small local Node.js room server. It does not add production multiplayer, matchmaking, anti-cheat, a game engine project, dependencies, external assets, build, publish, or deployment behavior.

## Files / ファイル

- `sample/tutorials/sample49-ai-plugin-maze-race/README.md`
- `sample/tutorials/sample49-ai-plugin-maze-race/reference/maze-race-input.sample.json`
- `sample/tutorials/sample49-ai-plugin-maze-race/public/index.html`
- `sample/tutorials/sample49-ai-plugin-maze-race/public/styles.css`
- `sample/tutorials/sample49-ai-plugin-maze-race/public/game.js`
- `sample/tutorials/sample49-ai-plugin-maze-race/src/maze-room-store.mjs`
- `sample/tutorials/sample49-ai-plugin-maze-race/src/server.mjs`
- `sample/tutorials/sample49-ai-plugin-maze-race/scripts/validate-sample.mjs`

## Follow-up / 次候補

Possible next steps:

- browser smoke for sample49;
- shared-state sync AI plugin;
- code-facing plugin interface.
