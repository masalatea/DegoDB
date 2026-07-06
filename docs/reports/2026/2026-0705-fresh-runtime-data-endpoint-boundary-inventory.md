# Fresh Runtime Data Endpoint Boundary Inventory

Status: DONE
Date: 2026-07-05

## Scope

This inventory records the boundary before adding any live business-data reload behavior to generated no-code runtime screens. It does not implement a new endpoint.

## Current Artifact Runtime

- `runtime-preview.html` is generated from the runtime preview payload and embeds that payload in `#no-code-runtime-preview-data`.
- `runtime-preview.json` and `screen-definition.json` are emitted into the artifact bundle together with the HTML preview.
- The browser runtime reads the embedded preview JSON. It does not fetch dynamic rows after page load.
- Artifact-key public preview URLs use immutable cache headers and serve the approved artifact bundle.
- Current and alias public preview URLs use `no-store`, but still serve the selected approved artifact bundle with execution binding injected.

## Existing Execution And Processing

- Public runtime execution endpoints already exist for artifact-key, current, and alias paths.
- Execution normalizes POST data into action intent, enqueues a managed-operation sync outbox item, and can optionally run one explicit demo-processing pass when the demo gate is enabled.
- The status JSON endpoint observes outbox progress without exposing intent payloads and without processing inline.
- Generated server DBAccess processing is already proven by direct endpoint and outbox processing smokes for multiple samples.
- None of the current runtime refresh behavior reads fresh business rows back into the preview UI.

## Boundary Decisions

- Do not add a fresh-data endpoint to immutable artifact-key preview first. Artifact-key preview should remain a static, cacheable artifact snapshot.
- Put the first fresh-data candidate under current and alias scope, where `no-store` and moving publication pointers already make sense.
- Keep the endpoint read-only. It should not process outbox, retry mutation, regenerate artifacts, publish candidates, or switch current revision inline.
- Use the same project/current/alias permission boundary as public runtime execution, with a read-only response surface.
- Return `Cache-Control: no-store`.
- Source live rows from generated DBAccess or an equivalent read-model materialization layer, not from the static artifact `runtime-preview.json`.
- Version the response separately from generated preview artifacts, for example `no-code-runtime-data-v0`.
- Include enough context for the browser to reconcile the response with the loaded artifact, such as project key, artifact key or revision id, screen key, generated contract version, and rows/detail values.
- Preserve the existing artifact reload wording until a fresh-data endpoint is actually wired into the runtime UI.

## Candidate Route Shape

The most natural first route family is current/alias only:

- `/runs/no-code/{project}/current/runtime-data.json`
- `/runs/no-code/{project}/alias/{alias}/runtime-data.json`

Artifact-key `runtime-data.json` should remain deferred unless a specific need appears for read-only debugging against a fixed artifact selection.

## Recommended Next Slice

Plan the response contract and smoke matrix before implementing the endpoint. The next slice should name the response schema, permission checks, stale-artifact behavior, and sample28/sample29/sample31 smoke expectations.
