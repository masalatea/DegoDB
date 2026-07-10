# Runtime Data Read-Model Shape Boundary

Status: DONE
Date: 2026-07-05

## Summary

This note fixes the current boundary before broadening `runtime-data.json` beyond the first live row refresh behavior.

The endpoint is already useful for current/alias public runtime previews: it returns authenticated, read-only, no-store, versioned `no-code-runtime-data-v0` payloads. The current response is intentionally screen-render oriented so the runtime can merge fresh data into existing generated screens without changing immutable artifact-key previews.

## Current Shape

- Route scope: current and alias only; artifact-key previews remain static.
- Method/cache: `GET` only with `no-store`.
- Contract version: `no-code-runtime-data-v0`.
- Response identity: project key, selection kind, alias key, artifact key, revision id, screen definition version, runtime preview version.
- Screen payload: rendered screen items with `screen_key`, `screen_type`, `contract_key`, `data`, and `source`.
- Data source: generated DBAccess list reads under the public runtime DB environment binding.
- List behavior: renders the rows returned by the generated list method.
- Detail/form behavior: renders from the first returned row as the current item.
- Failure behavior: fail closed JSON with `ok: false` and an error, without mutating preview data.

## Recommended Next Slice

Add screen-level metadata without changing route semantics or requiring new query parameters.

Useful additive fields:

- row count for each screen render
- selected key for detail/form renders
- source kind / contract key already present, kept stable
- freshness timestamp or generated-at marker if the runtime needs visible stale/fresh feedback later

This gives the UI and smoke tests more explicit facts without committing yet to pagination, filters, detail-selection routing, or form-default query semantics.

## Deferred Shape Work

- Query-driven pagination and page-size controls.
- Filter parameters derived from generated screen/operation metadata.
- Detail selection by key instead of always first row.
- Form default behavior for create/update screens.
- Browser-level synchronous demo-processing proof against a deterministic shared runtime DB.

## Verification

- Inspected `mtool/app/no_code_public_runtime_page.php` runtime-data response and screen rendering path.
- Inspected `mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php` runtime-data smoke expectations.
- Inspected `mtool/app/no_code_runtime.php` runtime-data client merge path.
- `git diff --check`

Push was not performed.
