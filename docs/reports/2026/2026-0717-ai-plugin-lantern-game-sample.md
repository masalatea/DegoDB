# AI plugin lantern game sample / AI plugin lantern game sample

English companion:
This report records the first simple static game built from the AI-facing game content and audio plugin examples.

## Summary / 要約

Added `sample48-ai-plugin-lantern-game`.

The sample is a browser-only canvas game:

- move with arrow keys or WASD;
- collect three lantern charms;
- avoid drifting shadows;
- open the shrine gate after collecting all charms.

## Boundary / 境界

The sample does not add a game engine project, package dependencies, Node.js server, database, external image assets, generated audio files, licensing decisions, production runtime, build, publish, or deployment behavior.

Audio is represented by a small browser tone adapter only. The audio plugin remains metadata/handoff authority.

## Files / ファイル

- `sample/tutorials/sample48-ai-plugin-lantern-game/README.md`
- `sample/tutorials/sample48-ai-plugin-lantern-game/reference/lantern-game-input.sample.json`
- `sample/tutorials/sample48-ai-plugin-lantern-game/public/index.html`
- `sample/tutorials/sample48-ai-plugin-lantern-game/public/styles.css`
- `sample/tutorials/sample48-ai-plugin-lantern-game/public/game.js`
- `sample/tutorials/sample48-ai-plugin-lantern-game/scripts/validate-sample.mjs`

## Follow-up / 次候補

Possible next steps:

- add a shared-state sync AI plugin;
- add a code-facing plugin interface;
- add optional browser smoke when Node/browser tooling is available.
