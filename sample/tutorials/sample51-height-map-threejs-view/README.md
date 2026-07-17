# Sample 51: Height Map Three.js View

This sample renders the height-map RPG terrain as a real WebGL scene using a vendored Three.js module.

It is a separate visual proof from Sample50. Sample50 keeps the dependency-free Canvas 2D projection; Sample51 shows the same kind of deterministic smooth height map as a 3D mesh with a 45-degree camera.

## Vendored dependency

- `vendor/three/three.module.js`
- `vendor/three/three.core.js`
- Version: `0.185.1`
- Source package: `three`
- License: MIT

The sample does not require npm install. The vendored files are committed so the sample can run without a CDN.

## First-slice implementation

- deterministic smooth random height map;
- terrain mesh with vertex colors;
- 45-degree camera;
- directional and ambient light;
- player marker;
- mouse drag orbit and wheel zoom;
- no npm dependencies;
- no Node.js app server;
- no external image assets.

Because ES modules are used, run through a local static server instead of opening the HTML directly.

```bash
python3 -m http.server 8891 -d sample/tutorials/sample51-height-map-threejs-view
```

Then open:

```text
http://127.0.0.1:8891/public/
```

## Validate

```bash
node sample/tutorials/sample51-height-map-threejs-view/scripts/validate-sample.mjs
```

This is a static 3D visualization proof, not a production terrain engine, collision system, pathfinding system, asset pipeline, or RPG runtime replacement.
