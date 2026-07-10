# Runtime Data Initial URL Query Replay First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#270 replans after the runtime-data browser URL query mirror slice and chooses initial URL query replay before richer filter/sort models or broader read-model shape. #271 implements the first small slice.

The goal is to make a copied or reloaded current/alias `runtime-preview.html` URL useful: known read-only runtime-data query parameters in the browser URL are consumed once on initial preview load through the same `runtime-data.json` refresh path used by the generated controls.

## Planned / Implemented

- Parse known runtime-data browser URL parameters: `selected_key`, `q`, `page`, `page_size`, `filter[field]`, and `sort[field]`.
- On initial preview load, replay those parameters once for current/alias previews that have a `runtime_data_url` binding.
- Reuse the existing read-only `runtime-data.json` refresh path so returned payloads still drive list/detail/form rendering and retained query-control state.
- Preserve immutable artifact-key preview behavior because artifact-key pages do not receive a runtime-data binding.
- Extend generated HTML coverage and the public runtime browser smoke with an initial URL replay probe.

## Boundary

- In scope: one initial-load replay of known runtime-data URL query parameters.
- Out of scope: `popstate` / back-forward replay, endpoint contract changes, richer filter/sort models, selected-row semantics changes, mutation behavior, artifact-key preview changes, and push.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11124 assertions`, `1 skipped`)
