# Runtime Data Visible Filter Row Expansion First Slice

Date: 2026-07-07

Status: `DONE`

## Summary

#306 chooses the visible filter-row expansion first implementation slice. #307 completes the first slice by exposing one additional generated runtime-data filter row while keeping the existing current/alias read-only endpoint contract unchanged.

## Implemented

- Added a third generated runtime-data filter row with field, operator, and value controls.
- Reused the existing additive query contract: `filter[field]=value` plus `filter_op[field]=contains|eq`.
- Preserved the endpoint's existing max-8 filter support without adding a new endpoint shape.
- Carried the third filter through generated query capture, live payload control sync, URL construction, initial URL replay, URL mirror, and browser history replay.
- Extended browser smoke expectations so sample28 proves a three-filter current/alias query can be submitted, retained, and replayed.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`

`make test` result: 337 tests, 11134 assertions, 1 skipped.

## Boundary

- In scope: the third visible generated filter row and first-slice generated runtime/browser smoke coverage.
- Out of scope: arbitrary add/remove filter rows, exposing all eight endpoint-supported filters, grouped filter layout redesign, logical condition grouping, numeric/date operator families, multi-column sort, mutation behavior, and push.
