# Runtime Data Combined Query Controls First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#258 replans after query-control state persistence and chooses combined query behavior. #259 implements the first small slice.

The goal is to make generated runtime-data controls behave like one read-only query surface: search, field filter, sort, page, and page-size operations should preserve one another's current values when requesting `runtime-data.json`.

## Planned / Implemented

- Add a combined runtime-data URL builder for `q`, one `filter[field]=value`, one `sort[field]=asc|desc`, `page`, and `page_size`.
- Read current generated control values from the runtime-data controls before Search / Filter / Sort / pagination requests.
- Preserve active search/filter/sort/page-size values across those requests.
- Keep selected-key row selection as an explicit selected-row request.
- Keep no-query Refresh as a full-list reload.
- Keep immutable artifact-key previews and submit/outbox mutation behavior separate.
- Extend browser smoke coverage for a combined search + filter + sort + page-size request and retained controls.

## Boundary

- In scope: combined browser-side query construction for existing bounded endpoint parameters.
- Out of scope: multiple filters, multi-column sort, URL/history persistence, new reset controls, endpoint contract changes, visual redesign, and mutation behavior.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11116 assertions`, `1 skipped`)
