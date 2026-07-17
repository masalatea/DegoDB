# 2026-0717 API Height Map RPG Runtime Sample

## Summary

Added `sample52-api-height-map-rpg-runtime` as a Node-backed sample that exposes a Mtool-style height-map packet API and renders the returned packet with vendored Three.js.

## Scope

- `GET /api/map` returns a structured `mtool_height_map_packet.v1` packet.
- `src/map-packet.mjs` represents the Mtool-facing internal provider boundary.
- `src/server.mjs` exposes the provider result as a runtime API.
- `public/game.js` fetches the packet and renders terrain from packet parameters.
- The runtime keeps Sample51's 3D terrain, camera orbit, player marker, and height following behavior.
- `R` refetches `/api/map` with a seed override.

## Boundary

This is a first-slice local runtime/API proof. It does not claim production collision, pathfinding, persistence, auth, deployment, or engine project generation.

## Validation

Static validation is available through:

```bash
node sample/tutorials/sample52-api-height-map-rpg-runtime/scripts/validate-sample.mjs
```

The local shell did not provide `node` during implementation, so runtime validation was done through the available Node-backed browser/tooling environment where possible.
