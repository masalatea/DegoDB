# Runtime Data Browser Two-Sort Controls First Slice / runtime-data browser two-sort controls first slice

Status: DONE

#318 chooses browser-visible multi-sort after the endpoint accepted bounded ordered sort maps. #319 implements the first generated browser slice by exposing one secondary sort row.

## Why This Slice / この slice の理由

The read-only current/alias `runtime-data.json` endpoint can now accept up to three ordered `sort[field]=asc|desc` entries. The generated browser UI should expose that power gradually: two visible sort rows are enough to prove request construction, payload echo, retained control state, URL mirroring, and initial/browser-history replay without introducing dynamic row builders.

## Implemented / 実装

- Added `Sort 2` and `Direction 2` controls to generated current/alias runtime-data controls.
- Captured primary and secondary sort fields from generated controls into a `sorts` array while preserving legacy `sortField` / `sortDirection` values for existing code paths.
- Sent ordered sort maps through combined runtime-data requests.
- Replayed ordered sort maps from browser URL query parameters.
- Synced primary and secondary sort controls from `runtime-data.json` `query.sort` payload metadata.
- Extended the shared browser smoke probe to assert secondary sort request, retained controls, URL mirroring, and combined query behavior for sample28/sample29/sample31 profiles.

## Boundaries / 境界

- In scope: two visible generated sort rows, read-only current/alias runtime-data requests, URL mirror/replay, smoke probe coverage.
- Out of scope: endpoint contract changes, third visible sort row, dynamic sort-row builders, sortable column headers, numeric/date-aware comparison, mutation behavior, artifact-key preview changes, and push.

## Verification / 検証

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

Full `make test` result: 337 tests, 11136 assertions, 1 skipped.
