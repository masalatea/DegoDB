# Height map RPG view sample / height map RPG view sample

English companion:
This report records a separate static sample for adding smooth height information to an action RPG style map.

## Summary / 要約

Added `sample50-height-map-rpg-view`.

The sample implements:

- deterministic smooth random height map;
- normalized height values;
- 45-degree isometric-style projection;
- height-based color shading;
- a simple player marker on the terrain;
- camera panning.

## Boundary / 境界

This sample does not modify Sample47. It does not add a production terrain engine, collision system, pathfinding, world streaming, Node.js server, dependencies, external assets, build, publish, or deployment behavior.

## Files / ファイル

- `sample/tutorials/sample50-height-map-rpg-view/README.md`
- `sample/tutorials/sample50-height-map-rpg-view/reference/height-map-rpg-input.sample.json`
- `sample/tutorials/sample50-height-map-rpg-view/public/index.html`
- `sample/tutorials/sample50-height-map-rpg-view/public/styles.css`
- `sample/tutorials/sample50-height-map-rpg-view/public/game.js`
- `sample/tutorials/sample50-height-map-rpg-view/scripts/validate-sample.mjs`

## Follow-up / 次候補

Possible next steps:

- browser smoke for sample50;
- collision/pathfinding height policy as a separate scope;
- integrate height metadata into a shared-state RPG input packet as a separate scope.
