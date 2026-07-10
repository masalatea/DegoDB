# Runtime Data Query Reset Affordance First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#264 replans after the selection-basis metadata slice and chooses an explicit query reset affordance before URL/history persistence, visual density polish, richer filter/sort models, or broader read-model shape. #265 implements the first small slice.

The runtime-data controls now preserve combined search, field filter, sort, page, and page-size state. The goal of this slice is to make the no-query reset path visible without changing the read-only endpoint contract or mutation behavior.

## Planned / Implemented

- Add a generated `Clear` control to current/alias runtime-data controls.
- Fetch current/alias `runtime-data.json` without query parameters when Clear is clicked.
- Let returned `query` / `pagination` metadata clear retained search/filter/sort controls and restore default page-size state.
- Keep normal `Refresh preview` behavior available.
- Preserve selected-row requests, pagination, search, filter, sort, immutable artifact-key previews, and submit/outbox mutation boundaries.
- Extend generated HTML and browser smoke coverage for the Clear control.

## Boundary

- In scope: generated Clear control, no-query runtime-data fetch, retained-control reset coverage.
- Out of scope: URL/history persistence, endpoint query contract changes, multi-filter/multi-sort behavior, visual redesign, selected-row behavior changes, and mutation behavior.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11119 assertions`, `1 skipped`)
