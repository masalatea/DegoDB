# Fresh Runtime Data Endpoint Contract Plan

Status: DONE
Date: 2026-07-05

## Scope

This plan fixes the first implementation contract for a fresh runtime data endpoint. It follows the boundary inventory and still does not implement the endpoint.

## Routes

First implementation targets moving public selections only:

- `GET /runs/no-code/{project}/current/runtime-data.json`
- `GET /runs/no-code/{project}/alias/{alias}/runtime-data.json`

The immutable artifact-key route remains static. Add artifact-key data routes later only if fixed-artifact read debugging becomes a real need.

## Response Contract

Use a versioned read-only contract:

```json
{
  "ok": true,
  "contract_version": "no-code-runtime-data-v0",
  "project_key": "SAMPLE28",
  "selection": {
    "kind": "current",
    "alias_key": "",
    "artifact_key": "ARTIFACT",
    "revision_id": "REVISION"
  },
  "screen_definition_version": "no-code-screen-definition-v0",
  "runtime_preview_version": "no-code-runtime-v0",
  "screens": [],
  "error": ""
}
```

`screens` should mirror the generated runtime screen keys and screen types so browser code can reconcile the live data response with the already-loaded artifact. Each screen entry can start with:

- `screen_key`
- `screen_type`
- `contract_key`
- `data`
- `source`

`data` should use the existing runtime cell shape where practical: `{ "value": mixed, "display_value": string }`.

## Failure Semantics

- Non-GET requests return method-not-allowed.
- Invalid project or alias binding fails closed.
- Missing current/alias candidate returns a JSON error and a non-success status.
- Missing artifact files, invalid screen definition, or unsupported generated read binding returns a JSON error.
- The endpoint does not enqueue work, process outbox, retry mutation, regenerate artifacts, publish candidates, or change current/alias selection.
- Responses use `Cache-Control: no-store` and `X-Content-Type-Options: nosniff`.

## Data Source

The endpoint should derive live rows from generated DBAccess or an equivalent read-model materialization layer. It should not read rows from static `runtime-preview.json` except as fallback metadata for screen shape, version markers, and reconciliation fields.

The first implementation can limit support to generated read methods that are already discoverable from the project DBAccess bootstrap catalog. Unsupported screens should be represented explicitly rather than silently returning static artifact rows.

## Smoke Matrix

Minimum first implementation checks:

- current route returns JSON with `contract_version`, project key, artifact key, revision id, and `no-store`.
- alias route returns the same contract shape and preserves the alias key in `selection`.
- invalid alias returns a JSON error.
- non-GET method is rejected.
- the endpoint does not create a new sync outbox item.
- sample28 verifies one changed row can be read after generated server DBAccess processing.
- sample29 and sample31 can be added immediately after sample28 using the existing multi-profile smoke pattern.

## Recommended Next Slice

Implement the route helpers and a sample28-focused smoke first. After that passes, expand the same smoke to sample29 and sample31.
