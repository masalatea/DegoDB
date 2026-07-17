# Sample 50: Height Map RPG View

This sample is a static browser proof for adding height information to an action RPG style map.

It generates a smooth deterministic random height map and renders it from a 45-degree view. The sample is separate from the Node-backed Sample47 RPG runtime and does not change its shared-state contract.

## First-slice implementation

- deterministic smooth random terrain;
- tile height values from 0.0 to 1.0;
- 45-degree projected view;
- simple player marker placed on the terrain;
- keyboard panning for map inspection;
- no npm dependencies;
- no Node.js server;
- no database;
- no external image assets.

## Validate

```bash
node sample/tutorials/sample50-height-map-rpg-view/scripts/validate-sample.mjs
```

## Run locally

Open:

```text
sample/tutorials/sample50-height-map-rpg-view/public/index.html
```

Controls:

- arrow keys / WASD pan the camera;
- `R` regenerates the deterministic height seed sequence.

This is a static map-rendering proof, not a production terrain engine, collision system, pathfinding system, world streaming system, or RPG runtime replacement.
