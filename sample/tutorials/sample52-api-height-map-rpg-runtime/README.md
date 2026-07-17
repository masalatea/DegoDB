# Sample 52: API Height Map RPG Runtime

This sample turns the Sample51 terrain proof into a small Mtool-style runtime boundary.

The Node server owns the map API. The browser runtime fetches the map packet and renders it with vendored Three.js.

## Boundary

- `src/map-packet.mjs` acts as the Mtool-facing packet provider.
- `src/server.mjs` exposes the packet through `GET /api/map`.
- `public/game.js` fetches the packet and renders the terrain.
- `reference/api-height-map-packet.sample.json` documents the expected packet shape.

The browser does not own map definition. It only reads the packet, builds the mesh, and runs local camera/player controls.

## Vendored dependency

- `vendor/three/three.module.js`
- `vendor/three/three.core.js`
- Version: `0.185.1`
- Source package: `three`
- License: MIT

The sample does not require npm install. The vendored files are committed so the runtime can run without a CDN.

## Run

```bash
node sample/tutorials/sample52-api-height-map-rpg-runtime/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8892/
```

Map API:

```text
http://127.0.0.1:8892/api/map
```

The API also accepts a seed override:

```text
http://127.0.0.1:8892/api/map?seed=52149
```

## Validate

```bash
node sample/tutorials/sample52-api-height-map-rpg-runtime/scripts/validate-sample.mjs
```

## First-slice implementation

- Node-backed map packet API;
- no package install;
- vendored Three.js runtime;
- terrain mesh from API packet parameters;
- player marker with terrain height following;
- mouse drag orbit and wheel zoom;
- `R` refetches the API with a new seed;
- no production collision, pathfinding, persistence, auth, or deployment claim.
